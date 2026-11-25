<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\DemandeStopModel;
use App\Models\StationModel;
use App\Models\TrajetModel;
use PDO;
use PDOException;

/**
 * VoyageurController : espace du simple utilisateur (frontoffice).
 * - Consulter les trajets de bus disponibles (actifs) et leurs stations
 * - Faire une demande de STOP à une station d'un trajet
 * - Suivre ses demandes (statut "En attente" : pas encore de réponse côté chauffeur)
 * - Annuler une demande encore en attente
 *
 * Contrôle de saisie de la demande (identique en JS) :
 * - station : obligatoire, doit appartenir au trajet choisi
 * - date du voyage : aujourd'hui ou dans les 30 prochains jours ;
 *   si c'est aujourd'hui, l'heure de passage ne doit pas être déjà dépassée
 * - nombre de personnes : entier de 1 à 10
 * - message : facultatif, 5 à 255 caractères
 * - pas deux demandes identiques en attente (même station, même date)
 */
class VoyageurController extends Controller
{
    use UserFormTrait;  // field(), verifyCsrfToken()
    use AdminTrait;     // intFromQuery(), intFromPost(), csrfOk()  (requireAdmin non utilisé ici)

    public const STATUTS = [
        'en_attente' => 'En attente',
        'acceptee'   => 'Acceptée',
        'refusee'    => 'Refusée',
    ];

    private const MAX_DAYS = 30;

    protected string $title     = 'Trajets disponibles | Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // SÉCURITÉ
    // =====================================

    /** Autorise uniquement un utilisateur "normal" connecté (vérifié en base). Retourne son id. */
    private function requireVoyageur(): int
    {
        AuthController::requireAuth();

        $id   = (int) $_SESSION['user_id'];
        $stmt = $this->db->prepare('SELECT type_service, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row || $row['role'] !== 'user' || $row['type_service'] !== 'utilisateur_normal') {
            http_response_code(403);
            exit("Accès refusé : cet espace est réservé aux voyageurs (compte « Utilisateur normal »).");
        }
        $_SESSION['type_service'] = $row['type_service'];
        return $id;
    }

    private function voyageurFlash(string $type, string $message): void
    {
        $_SESSION['voyageur_flash'] = ['type' => $type, 'message' => $message];
    }

    private function popVoyageurFlash(): ?array
    {
        $f = $_SESSION['voyageur_flash'] ?? null;
        unset($_SESSION['voyageur_flash']);
        return $f;
    }

    // =====================================
    // ACCÈS AUX DONNÉES
    // =====================================

    /** Trajet actif (disponible pour les voyageurs), sinon null. */
    private function findActiveTrajet(int $id): ?TrajetModel
    {
        $stmt = $this->db->prepare("SELECT * FROM trajets WHERE id = :id AND statut = 'actif' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? TrajetModel::fromArray($row) : null;
    }

    /** @return StationModel[] */
    private function getStations(int $trajetId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM stations WHERE trajet_id = :id ORDER BY ordre');
        $stmt->execute(['id' => $trajetId]);
        return array_map([StationModel::class, 'fromArray'], $stmt->fetchAll());
    }

    // =====================================
    // CONTRÔLE DE SAISIE
    // =====================================

    private function readDemandeInput(array $post): array
    {
        return [
            'station_id'   => $this->field($post, 'station_id'),
            'date_trajet'  => $this->field($post, 'date_trajet'),
            'nb_personnes' => $this->field($post, 'nb_personnes'),
            'message'      => $this->field($post, 'message'),
        ];
    }

    /** @param StationModel[] $stations */
    private function validateDemandeInput(array $d, TrajetModel $trajet, array $stations, int $userId): array
    {
        $errors  = [];
        $station = null;

        // Station : doit appartenir à CE trajet
        foreach ($stations as $s) {
            if ((string) $s->getId() === $d['station_id']) {
                $station = $s;
            }
        }
        if (!$station) {
            $errors[] = "Veuillez choisir une station de ce trajet.";
        }

        // Date : aujourd'hui → +30 jours
        $today = new \DateTimeImmutable('today');
        $date  = \DateTimeImmutable::createFromFormat('!Y-m-d', $d['date_trajet']);
        $dateOk = $date && $date->format('Y-m-d') === $d['date_trajet']
               && $date >= $today && $date <= $today->modify('+' . self::MAX_DAYS . ' days');
        if (!$dateOk) {
            $errors[] = "La date du voyage doit être comprise entre aujourd'hui et dans " . self::MAX_DAYS . " jours.";
        } elseif ($station && $date == $today && $station->getHeurePassage() <= date('H:i')) {
            $errors[] = "Le bus est déjà passé à cette station aujourd'hui ({$station->getHeurePassage()}). Choisissez une autre date.";
        }

        if (!ctype_digit($d['nb_personnes']) || (int) $d['nb_personnes'] < 1 || (int) $d['nb_personnes'] > 10) {
            $errors[] = "Le nombre de personnes doit être un entier entre 1 et 10.";
        }

        $len = mb_strlen($d['message']);
        if ($len > 0 && ($len < 5 || $len > 255)) {
            $errors[] = "Le message doit contenir entre 5 et 255 caractères.";
        }

        // Pas de doublon en attente
        if ($station && $dateOk) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM demandes_stop
                 WHERE user_id = :u AND station_id = :s AND date_trajet = :d AND statut = 'en_attente'"
            );
            $stmt->execute(['u' => $userId, 's' => $station->getId(), 'd' => $d['date_trajet']]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors[] = "Vous avez déjà une demande en attente pour cette station à cette date.";
            }
        }

        return $errors;
    }

    // =====================================
    // ACTIONS
    // =====================================

    // index.php?page=trajets-disponibles
    public function trajetsAction(): void
    {
        $this->requireVoyageur();

        $search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';

        $sql    = "SELECT * FROM trajets WHERE statut = 'actif'";
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (code LIKE :q1 OR nom LIKE :q2 OR point_depart LIKE :q3 OR point_arrivee LIKE :q4
                           OR id IN (SELECT trajet_id FROM stations WHERE nom LIKE :q5))';
            $like = '%' . $search . '%';
            $params = ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like];
        }
        $sql .= ' ORDER BY heure_depart';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $trajets = array_map([TrajetModel::class, 'fromArray'], $stmt->fetchAll());

        $stations = [];
        foreach ($trajets as $t) {
            $stations[(int) $t->getId()] = $this->getStations((int) $t->getId());
        }

        $this->page = 'trajets-disponibles';
        $this->render('voyageur_trajets', [
            'trajets'  => $trajets,
            'stations' => $stations,
            'search'   => $search,
            'flash'    => $this->popVoyageurFlash(),
        ]);
    }

    // index.php?page=demande-stop&trajet=...[&station=...]
    public function demandeAction(): void
    {
        $userId = $this->requireVoyageur();

        $trajet = $this->findActiveTrajet($this->intFromQuery('trajet'));
        if (!$trajet) {
            $this->voyageurFlash('error', "Ce trajet n'existe pas ou n'est plus disponible.");
            self::redirect('trajets-disponibles');
        }

        $stations = $this->getStations((int) $trajet->getId());
        if (!$stations) {
            $this->voyageurFlash('error', "Ce trajet n'a pas encore de station : impossible de demander un stop.");
            self::redirect('trajets-disponibles');
        }

        $errors = [];
        $old    = [
            'station_id'   => (string) ($this->intFromQuery('station') ?: ''),
            'date_trajet'  => date('Y-m-d'),
            'nb_personnes' => '1',
            'message'      => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->readDemandeInput($_POST);
            $errors = $this->csrfOk()
                ? $this->validateDemandeInput($old, $trajet, $stations, $userId)
                : ["Jeton de sécurité invalide. Veuillez réessayer."];

            if (!$errors) {
                $demande = new DemandeStopModel();
                $demande->setUserId($userId);
                $demande->setTrajetId((int) $trajet->getId());
                $demande->setStationId((int) $old['station_id']);
                $demande->setDateTrajet($old['date_trajet']);
                $demande->setNbPersonnes((int) $old['nb_personnes']);
                $demande->setMessage($old['message'] !== '' ? $old['message'] : null);

                try {
                    // Le statut n'est pas envoyé : la base met 'en_attente' par défaut
                    $stmt = $this->db->prepare(
                        'INSERT INTO demandes_stop (user_id, trajet_id, station_id, date_trajet, nb_personnes, message)
                         VALUES (:u, :t, :s, :d, :n, :m)'
                    );
                    $stmt->execute([
                        'u' => $demande->getUserId(),
                        't' => $demande->getTrajetId(),
                        's' => $demande->getStationId(),
                        'd' => $demande->getDateTrajet(),
                        'n' => $demande->getNbPersonnes(),
                        'm' => $demande->getMessage(),
                    ]);
                    $this->voyageurFlash('success', "Votre demande de STOP a été envoyée. Elle est en attente.");
                    self::redirect('mes-demandes');
                } catch (PDOException $e) {
                    error_log('Demande stop error: ' . $e->getMessage());
                    $errors = ["Une erreur est survenue lors de l'envoi de la demande."];
                }
            }
        }

        $today = new \DateTimeImmutable('today');
        $this->page  = 'trajets-disponibles';
        $this->title = 'Demande de STOP | Smart City';
        $this->render('voyageur_demande_form', [
            'trajet'   => $trajet,
            'stations' => $stations,
            'errors'   => $errors,
            'old'      => $old,
            'minDate'  => $today->format('Y-m-d'),
            'maxDate'  => $today->modify('+' . self::MAX_DAYS . ' days')->format('Y-m-d'),
        ]);
    }

    // index.php?page=mes-demandes
    public function demandesAction(): void
    {
        $userId = $this->requireVoyageur();

        $stmt = $this->db->prepare(
            'SELECT d.*, t.code AS trajet_code, t.nom AS trajet_nom,
                    s.nom AS station_nom, s.adresse AS station_adresse, s.heure_passage
             FROM demandes_stop d
             JOIN trajets t  ON t.id = d.trajet_id
             JOIN stations s ON s.id = d.station_id
             WHERE d.user_id = :u
             ORDER BY d.date_trajet DESC, s.heure_passage DESC'
        );
        $stmt->execute(['u' => $userId]);
        $demandes = array_map([DemandeStopModel::class, 'fromArray'], $stmt->fetchAll());

        $this->page  = 'mes-demandes';
        $this->title = 'Mes demandes de STOP | Smart City';
        $this->render('voyageur_demandes', [
            'demandes' => $demandes,
            'statuts'  => self::STATUTS,
            'flash'    => $this->popVoyageurFlash(),
        ]);
    }

    // index.php?page=demande-stop-annuler  (POST + CSRF, uniquement SES demandes en attente)
    public function annulerAction(): void
    {
        $userId = $this->requireVoyageur();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('mes-demandes');
        }
        if (!$this->csrfOk()) {
            $this->voyageurFlash('error', "Jeton de sécurité invalide. Veuillez réessayer.");
            self::redirect('mes-demandes');
        }

        $stmt = $this->db->prepare(
            "DELETE FROM demandes_stop WHERE id = :id AND user_id = :u AND statut = 'en_attente'"
        );
        $stmt->execute(['id' => $this->intFromPost('id'), 'u' => $userId]);

        $stmt->rowCount() === 1
            ? $this->voyageurFlash('success', "Votre demande a été annulée.")
            : $this->voyageurFlash('error', "Cette demande ne peut pas être annulée.");
        self::redirect('mes-demandes');
    }
}
