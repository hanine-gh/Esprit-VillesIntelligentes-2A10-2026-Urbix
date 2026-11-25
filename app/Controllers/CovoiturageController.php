<?php
namespace App\Controllers;

use App\Models\BaseModel;
use App\Models\Recommandation;
use App\Models\Reservation;
use App\Models\Trajet;

class CovoiturageController extends Controller {
    protected string $page      = 'covoiturage';
    protected string $title     = 'Covoiturage | Urbix Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    /** Page de recherche + propositions des conducteurs. */
    public function index(): void {
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

        if ($rechercheLancee) {
            $erreurs = $this->valider($criteres);
            if (!$erreurs) {
                $utilisateur = utilisateurConnecte();
                $resultat = Recommandation::rechercher(
                    $criteres,
                    $utilisateur ? (int) $utilisateur['id_utilisateur'] : null
                );
            }
        }

        $this->render('covoiturage', [
            'criteres'        => $criteres,
            'erreurs'         => $erreurs,
            'resultat'        => $resultat,
            'rechercheLancee' => $rechercheLancee,
            'villes'          => Trajet::villes(),
            'populaires'      => array_slice(Trajet::aVenir(), 0, 6),
            'modeDemo'        => BaseModel::modeDemo(),
            'extraJs'         => ['assets/js/covoiturage.js'],
        ]);
    }

    /** Le client accepte la proposition d'un conducteur. */
    public function accepter(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(url('covoiturage'));
        }
        $retour = $this->retourSur($_POST['retour'] ?? '');

        if (!csrfValide($_POST['csrf'] ?? null)) {
            flash('erreur', 'Session expirée, veuillez réessayer.');
            redirect($retour);
        }

        $utilisateur = utilisateurConnecte();
        if ($utilisateur === null) {
            flash('info', 'Connectez-vous ou créez un compte pour accepter ce trajet.');
            $_SESSION['ouvrir_auth'] = 'login';
            redirect($retour);
        }

        $idTrajet = (int) ($_POST['id_trajet'] ?? 0);
        $nbPlaces = (int) ($_POST['nb_places'] ?? 1);
        $trajet = Trajet::trouver($idTrajet);

        if ($trajet === null) {
            flash('erreur', 'Ce trajet est introuvable.');
            redirect($retour);
        }
        if ($nbPlaces < 1 || $nbPlaces > 8) {
            flash('erreur', 'Le nombre de places doit être compris entre 1 et 8.');
            redirect($retour);
        }

        try {
            Reservation::creer($idTrajet, (int) $utilisateur['id_utilisateur'], $nbPlaces);
        } catch (\RuntimeException $e) {
            flash('erreur', $e->getMessage());
            redirect($retour);
        } catch (\Throwable $e) {
            error_log('[Urbix] Réservation impossible : ' . $e->getMessage());
            flash('erreur', 'Une erreur est survenue, la réservation n\'a pas été enregistrée.');
            redirect($retour);
        }

        flash('succes', sprintf(
            'Trajet accepté ! %s → %s le %s à %s avec %s %s. Total : %s.',
            $trajet['ville_depart'], $trajet['ville_arrivee'], dateFr($trajet['date_depart']),
            heureFr($trajet['heure_depart']), $trajet['conducteur_prenom'], $trajet['conducteur_nom'],
            prixDT($nbPlaces * (float) $trajet['prix_place'])
        ));
        redirect(url('espace-utilisateur') . '#mes-reservations');
    }

    /** Le client annule une réservation depuis son espace. */
    public function annuler(): void {
        $utilisateur = utilisateurConnecte();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || $utilisateur === null || !csrfValide($_POST['csrf'] ?? null)) {
            redirect(url('espace-utilisateur'));
        }
        $ok = Reservation::annuler((int) ($_POST['id_reservation'] ?? 0), (int) $utilisateur['id_utilisateur']);
        flash($ok ? 'succes' : 'erreur', $ok ? 'Réservation annulée, les places ont été libérées.' : 'Impossible d\'annuler cette réservation.');
        redirect(url('espace-utilisateur') . '#mes-reservations');
    }

    private function valider(array $c): array {
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
        if ($c['depart'] !== '' && $c['destination'] !== ''
            && Recommandation::normaliser($c['depart']) === Recommandation::normaliser($c['destination'])) {
            $erreurs['destination'] = 'La destination doit être différente du départ.';
        }
        if ($c['date'] !== '') {
            $d = \DateTime::createFromFormat('Y-m-d', $c['date']);
            if (!$d || $d->format('Y-m-d') !== $c['date']) {
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

    /** N'autorise que des redirections internes. */
    private function retourSur(string $retour): string {
        if ($retour !== '' && strpos($retour, BASE_URL) === 0 && strpos($retour, '//') === false) {
            return $retour;
        }
        return url('covoiturage');
    }
}
