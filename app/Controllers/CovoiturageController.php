<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\CovoiturageReservationModel;
use App\Models\CovoiturageTrajetModel;
use PDO;
use RuntimeException;

/**
 * CovoiturageController : module Covoiturage — frontoffice.
 * Le client saisit sa destination, les propositions des conducteurs (comptes "covoiturage")
 * sont classées par la recherche intelligente ; il accepte celle qui lui convient.
 * Conducteurs et passagers sont les comptes de la table users.
 */
class CovoiturageController extends Controller
{
    use UserFormTrait;
    use FrontTrait;
    use CovoiturageRecommandationTrait;

    private const VILLES = ['Tunis', 'Ariana', 'Ben Arous', 'La Manouba', 'Bizerte', 'Nabeul', 'Hammamet', 'Sousse',
                            'Monastir', 'Mahdia', 'Sfax', 'Kairouan', 'Gabès', 'Djerba', 'Gafsa', 'Tozeur', 'Béja', 'Jendouba'];

    protected string $page      = 'covoiturage';
    protected string $title     = 'Covoiturage | Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Trajets + conducteur + véhicule + note moyenne */
    private static function sqlTrajets(): string
    {
        return "SELECT t.*,
                       u.nom AS conducteur_nom, u.prenom AS conducteur_prenom, u.telephone AS conducteur_telephone,
                       v.marque AS vehicule_marque, v.modele AS vehicule_modele, v.couleur AS vehicule_couleur,
                       COALESCE(v.climatisation, 0) AS climatisation,
                       (SELECT ROUND(AVG(a.note), 1) FROM covoiturage_avis a WHERE a.conducteur_id = t.conducteur_id) AS note_moyenne,
                       (SELECT COUNT(*) FROM covoiturage_avis a WHERE a.conducteur_id = t.conducteur_id) AS nb_avis
                FROM covoiturage_trajets t
                JOIN users u ON u.id = t.conducteur_id
                LEFT JOIN covoiturage_vehicules v ON v.id = t.vehicule_id";
    }

    /** Lignes des trajets ouverts à venir (date + heure non passées) */
    private function trajetsAVenir(): array
    {
        $stmt = $this->db->prepare(self::sqlTrajets() . "
            WHERE t.statut = 'ouvert' AND (t.date_depart > CURDATE() OR (t.date_depart = CURDATE() AND t.heure_depart >= CURTIME()))
            ORDER BY t.date_depart, t.heure_depart");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Ligne SQL (+ score, badges...) -> CovoiturageTrajetModel */
    private static function versModele(array $row): CovoiturageTrajetModel
    {
        $duree = (int) ($row['duree_estimee_min'] ?? 0);
        $row['heure_arrivee'] = $duree > 0 ? date('H:i:s', strtotime($row['heure_depart']) + 60 * $duree) : null;
        $t = CovoiturageTrajetModel::fromArray($row);
        $t->setBadges($row['badges'] ?? []);
        return $t;
    }

    private function villes(): array
    {
        $enBase = $this->db->query('SELECT ville_depart FROM covoiturage_trajets UNION SELECT ville_arrivee FROM covoiturage_trajets')
                           ->fetchAll(PDO::FETCH_COLUMN);
        $villes = array_values(array_unique(array_merge(self::VILLES, $enBase)));
        sort($villes, SORT_NATURAL | SORT_FLAG_CASE);
        return $villes;
    }

    private function valider(array $c): array
    {
        $erreurs = [];
        $regexVille = '/^[\p{L}\s\'\-]{2,60}$/u';

        if ($c['destination'] === '') {
            $erreurs['destination'] = 'Veuillez saisir une destination.';
        } elseif (!preg_match($regexVille, $c['destination'])) {
            $erreurs['destination'] = 'La destination ne doit contenir que des lettres (2 caractères minimum).';
        }
        if ($c['depart'] !== '' && !preg_match($regexVille, $c['depart'])) {
            $erreurs['depart'] = 'La ville de départ ne doit contenir que des lettres.';
        }
        if ($c['depart'] !== '' && $c['destination'] !== '' && self::normaliser($c['depart']) === self::normaliser($c['destination'])) {
            $erreurs['destination'] = 'La destination doit être différente du départ.';
        }
        if ($c['date'] !== '') {
            if (!self::isDate($c['date'])) {
                $erreurs['date'] = 'Date invalide.';
            } elseif ($c['date'] < date('Y-m-d')) {
                $erreurs['date'] = 'La date ne peut pas être dans le passé.';
            }
        }
        if ($c['heure'] !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $c['heure'])) {
            $erreurs['heure'] = 'Heure invalide.';
        }
        if ($c['places'] < 1 || $c['places'] > 8) {
            $erreurs['places'] = 'Entre 1 et 8 places.';
        }
        if ($c['prix_max'] < 0) {
            $erreurs['prix_max'] = 'Le prix maximum doit être positif.';
        }
        return $erreurs;
    }

    // index.php?page=covoiturage : recherche + propositions des conducteurs
    public function indexAction(): void
    {
        $criteres = [
            'depart'      => trim((string) ($_GET['depart'] ?? '')),
            'destination' => trim((string) ($_GET['destination'] ?? '')),
            'date'        => trim((string) ($_GET['date'] ?? '')),
            'heure'       => trim((string) ($_GET['heure'] ?? '')),
            'places'      => (int) ($_GET['places'] ?? 1),
            'prix_max'    => (float) str_replace(',', '.', (string) ($_GET['prix_max'] ?? '0')),
        ];

        $erreurs = [];
        $resultat = null;
        $rechercheLancee = isset($_GET['destination']);
        $aVenir = $this->trajetsAVenir();

        if ($rechercheLancee) {
            $erreurs = $this->valider($criteres);
            if (!$erreurs) {
                $userId = AuthController::isAuthenticated() ? (int) $_SESSION['user_id'] : null;
                $r = self::rechercher($aVenir, $criteres, $userId);
                $resultat = ['type' => $r['type'], 'trajets' => array_map([self::class, 'versModele'], $r['trajets'])];
            }
        }

        $this->render('covoiturage', [
            'criteres'        => $criteres,
            'erreurs'         => $erreurs,
            'resultat'        => $resultat,
            'rechercheLancee' => $rechercheLancee,
            'villes'          => $this->villes(),
            'populaires'      => array_map([self::class, 'versModele'], array_slice($aVenir, 0, 6)),
            'flash'           => $this->popFrontFlash(),
            'extraCss'        => ['assets/css/covoiturage.css'],
            'extraJs'         => ['assets/js/covoiturage.js'],
        ]);
    }

    // index.php?page=covoiturage-accepter (POST) : le client accepte la proposition d'un conducteur
    public function accepterAction(): void
    {
        $userId = $this->requireUserId();
        if (!$this->isPost()) {
            self::redirect('covoiturage');
        }
        if (!$this->frontCsrfOk()) {
            $this->frontFlash('error', 'Session expirée, veuillez réessayer.');
            self::redirect('covoiturage');
        }

        $idTrajet = (int) ($_POST['id_trajet'] ?? 0);
        $nbPlaces = (int) ($_POST['nb_places'] ?? 1);
        if ($nbPlaces < 1 || $nbPlaces > 8) {
            $this->frontFlash('error', 'Le nombre de places doit être compris entre 1 et 8.');
            self::redirect('covoiturage');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM covoiturage_trajets WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $idTrajet]);
            $t = $stmt->fetch();
            if (!$t) {
                throw new RuntimeException("Ce trajet n'existe plus.");
            }
            $dej = $this->db->prepare("SELECT COUNT(*) FROM covoiturage_reservations WHERE trajet_id = :t AND passager_id = :p AND statut = 'acceptee'");
            $dej->execute(['t' => $idTrajet, 'p' => $userId]);

            if ($t['statut'] !== 'ouvert') {
                throw new RuntimeException("Ce trajet n'est plus disponible.");
            }
            if ((int) $t['conducteur_id'] === $userId) {
                throw new RuntimeException('Vous ne pouvez pas réserver votre propre trajet.');
            }
            if ((int) $dej->fetchColumn() > 0) {
                throw new RuntimeException('Vous avez déjà accepté ce trajet. Consultez « Mes réservations ».');
            }
            if ($nbPlaces > (int) $t['places_disponibles']) {
                throw new RuntimeException('Il ne reste que ' . (int) $t['places_disponibles'] . ' place(s) sur ce trajet.');
            }
            if (strtotime($t['date_depart'] . ' ' . $t['heure_depart']) < time()) {
                throw new RuntimeException('Ce trajet est déjà parti.');
            }

            $r = new CovoiturageReservationModel();
            $r->setTrajetId($idTrajet);
            $r->setPassagerId($userId);
            $r->setNbPlaces($nbPlaces);
            $r->setPrixTotal($nbPlaces * (float) $t['prix_place']);
            $r->setStatut('acceptee');

            $this->db->prepare('INSERT INTO covoiturage_reservations (trajet_id, passager_id, nb_places, prix_total, statut) VALUES (:t, :p, :n, :prix, :s)')
                ->execute(['t' => $r->getTrajetId(), 'p' => $r->getPassagerId(), 'n' => $r->getNbPlaces(), 'prix' => $r->getPrixTotal(), 's' => $r->getStatut()]);
            $this->db->prepare("UPDATE covoiturage_trajets
                                SET places_disponibles = places_disponibles - :n,
                                    statut = IF(places_disponibles = 0, 'complet', statut)
                                WHERE id = :id")
                ->execute(['n' => $nbPlaces, 'id' => $idTrajet]);
            $this->db->commit();
        } catch (RuntimeException $e) {
            $this->db->rollBack();
            $this->frontFlash('error', $e->getMessage());
            self::redirect('covoiturage');
        }

        $stmt = $this->db->prepare(self::sqlTrajets() . ' WHERE t.id = :id');
        $stmt->execute(['id' => $idTrajet]);
        $tr = self::versModele($stmt->fetch());
        $this->frontFlash('success', sprintf(
            'Trajet accepté ! %s → %s le %s à %s avec %s %s. Total : %s.',
            $tr->getVilleDepart(), $tr->getVilleArrivee(), dateFr($tr->getDateDepart()), heureFr($tr->getHeureDepart()),
            $tr->getConducteurPrenom(), $tr->getConducteurNom(), prixDT($nbPlaces * $tr->getPrixPlace())
        ));
        self::redirect('covoiturage-reservations');
    }

    // index.php?page=covoiturage-annuler (POST) : le client annule une réservation et libère les places
    public function annulerAction(): void
    {
        $userId = $this->requireUserId();
        if ($this->isPost() && $this->frontCsrfOk()) {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT * FROM covoiturage_reservations WHERE id = :r AND passager_id = :p AND statut = 'acceptee' FOR UPDATE");
            $stmt->execute(['r' => (int) ($_POST['id_reservation'] ?? 0), 'p' => $userId]);
            $r = $stmt->fetch();
            if ($r) {
                $this->db->prepare("UPDATE covoiturage_reservations SET statut = 'annulee' WHERE id = :r")->execute(['r' => $r['id']]);
                $this->db->prepare("UPDATE covoiturage_trajets SET places_disponibles = places_disponibles + :n, statut = 'ouvert' WHERE id = :t")
                    ->execute(['n' => (int) $r['nb_places'], 't' => (int) $r['trajet_id']]);
                $this->db->commit();
                $this->frontFlash('success', 'Réservation annulée, les places ont été libérées.');
            } else {
                $this->db->rollBack();
                $this->frontFlash('error', "Impossible d'annuler cette réservation.");
            }
        }
        self::redirect('covoiturage-reservations');
    }

    // index.php?page=covoiturage-reservations : mes réservations de covoiturage
    public function reservationsAction(): void
    {
        $userId = $this->requireUserId();
        $this->page  = 'covoiturage-reservations';
        $this->title = 'Mes réservations covoiturage | Smart City';

        $stmt = $this->db->prepare(
            'SELECT r.*, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, t.point_rencontre,
                    u.nom AS conducteur_nom, u.prenom AS conducteur_prenom, u.telephone AS conducteur_telephone,
                    v.marque AS vehicule_marque, v.modele AS vehicule_modele
             FROM covoiturage_reservations r
             JOIN covoiturage_trajets t ON t.id = r.trajet_id
             JOIN users u ON u.id = t.conducteur_id
             LEFT JOIN covoiturage_vehicules v ON v.id = t.vehicule_id
             WHERE r.passager_id = :p
             ORDER BY r.date_reservation DESC'
        );
        $stmt->execute(['p' => $userId]);
        $reservations = array_map([CovoiturageReservationModel::class, 'fromArray'], $stmt->fetchAll());

        $maintenant = time();
        $nbAVenir = count(array_filter($reservations, fn($r) => $r->getStatut() === 'acceptee'
            && strtotime($r->getDateDepart() . ' ' . $r->getHeureDepart()) >= $maintenant));
        $depense = array_sum(array_map(fn($r) => $r->getStatut() === 'annulee' ? 0 : $r->getPrixTotal(), $reservations));

        $this->render('covoiturage_reservations', [
            'reservations' => $reservations,
            'nbAVenir'     => $nbAVenir,
            'depense'      => $depense,
            'flash'        => $this->popFrontFlash(),
            'extraCss'     => ['assets/css/covoiturage.css'],
            'extraJs'      => ['assets/js/covoiturage.js'],
        ]);
    }
}
