<?php
namespace App\Controllers;

use App\Models\BaseModel;
use App\Models\Reservation;
use App\Models\Utilisateur;

/**
 * Interface utilisateur : ouverte depuis l'icône à droite de la barre de navigation.
 */
class UtilisateurController extends Controller {
    protected string $page      = 'espace-utilisateur';
    protected string $title     = 'Mon espace | Urbix Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    public function index(): void {
        $utilisateur = utilisateurConnecte();
        if ($utilisateur === null) {
            $_SESSION['ouvrir_auth'] = $_SESSION['ouvrir_auth'] ?? 'login';
        }
        $reservations = $utilisateur ? Reservation::parPassager((int) $utilisateur['id_utilisateur']) : [];

        $aVenir = array_filter($reservations, fn($r) => $r['statut'] === 'acceptee'
            && $r['date_depart'] . ' ' . $r['heure_depart'] >= date('Y-m-d H:i:s'));
        $depense = array_sum(array_map(fn($r) => $r['statut'] === 'annulee' ? 0 : (float) $r['prix_total'], $reservations));

        $this->render('espace-utilisateur', [
            'utilisateur'  => $utilisateur,
            'reservations' => $reservations,
            'nbAVenir'     => count($aVenir),
            'depense'      => $depense,
            'roles'        => Utilisateur::ROLES,
            'modeDemo'     => BaseModel::modeDemo(),
            'extraJs'      => ['assets/js/covoiturage.js'],
        ]);
    }

    public function connexion(): void {
        $this->exigerPost();
        $retour = $this->retourSur($_POST['retour'] ?? '');
        $email = trim((string) ($_POST['login_email'] ?? ''));
        $mdp   = (string) ($_POST['login_password'] ?? '');

        $erreur = null;
        if (!csrfValide($_POST['csrf'] ?? null)) {
            $erreur = 'Session expirée, veuillez réessayer.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Adresse email invalide.';
        } elseif ($mdp === '') {
            $erreur = 'Veuillez saisir votre mot de passe.';
        }

        $u = null;
        if ($erreur === null) {
            try {
                $u = Utilisateur::authentifier($email, $mdp);
            } catch (\Throwable $e) {
                error_log('[Urbix] Connexion : ' . $e->getMessage());
            }
            if ($u === null) {
                $erreur = 'Email ou mot de passe incorrect.';
            }
        }

        if ($erreur !== null) {
            $_SESSION['auth_form'] = ['onglet' => 'login', 'erreur' => $erreur, 'old' => ['login_email' => $email]];
            $_SESSION['ouvrir_auth'] = 'login';
            redirect($retour);
        }

        session_regenerate_id(true);
        $_SESSION['utilisateur'] = $u;
        flash('succes', 'Bienvenue ' . $u['prenom'] . ' !');
        redirect($retour);
    }

    public function inscription(): void {
        $this->exigerPost();
        $retour = $this->retourSur($_POST['retour'] ?? '');

        $d = [
            'nom'            => trim((string) ($_POST['last_name'] ?? '')),
            'prenom'         => trim((string) ($_POST['first_name'] ?? '')),
            'email'          => trim((string) ($_POST['email'] ?? '')),
            'date_naissance' => trim((string) ($_POST['birthdate'] ?? '')),
            'genre'          => (string) ($_POST['gender'] ?? ''),
            'telephone'      => trim((string) ($_POST['phone'] ?? '')),
            'role'           => (string) ($_POST['role'] ?? ''),
            'mot_de_passe'   => (string) ($_POST['password'] ?? ''),
        ];
        $confirmation = (string) ($_POST['password_confirm'] ?? '');

        $erreur = null;
        $regexNom = '/^[\p{L}\s\'\-]{2,60}$/u';
        if (!csrfValide($_POST['csrf'] ?? null)) {
            $erreur = 'Session expirée, veuillez réessayer.';
        } elseif (!preg_match($regexNom, $d['nom'])) {
            $erreur = 'Le nom doit contenir au moins 2 lettres.';
        } elseif (!preg_match($regexNom, $d['prenom'])) {
            $erreur = 'Le prénom doit contenir au moins 2 lettres.';
        } elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $erreur = 'Adresse email invalide.';
        } elseif ($d['date_naissance'] !== '' && !$this->dateNaissanceValide($d['date_naissance'])) {
            $erreur = 'Vous devez avoir au moins 16 ans.';
        } elseif ($d['genre'] !== '' && !in_array($d['genre'], ['homme', 'femme', 'autre'], true)) {
            $erreur = 'Genre invalide.';
        } elseif (!preg_match('/^[0-9 +]{8,15}$/', $d['telephone'])) {
            $erreur = 'Numéro de téléphone invalide (8 chiffres minimum).';
        } elseif (!array_key_exists($d['role'], Utilisateur::ROLES) || $d['role'] === 'admin') {
            $erreur = 'Veuillez choisir un type de compte.';
        } elseif (strlen($d['mot_de_passe']) < 8 || !preg_match('/\d/', $d['mot_de_passe']) || !preg_match('/\p{L}/u', $d['mot_de_passe'])) {
            $erreur = 'Le mot de passe doit contenir au moins 8 caractères, dont une lettre et un chiffre.';
        } elseif ($d['mot_de_passe'] !== $confirmation) {
            $erreur = 'Les deux mots de passe ne correspondent pas.';
        }

        if ($erreur === null) {
            try {
                if (Utilisateur::parEmail($d['email']) !== null) {
                    $erreur = 'Un compte existe déjà avec cet email.';
                } else {
                    $id = Utilisateur::inscrire($d);
                }
            } catch (\Throwable $e) {
                error_log('[Urbix] Inscription : ' . $e->getMessage());
                $erreur = 'Inscription impossible pour le moment.';
            }
        }

        if ($erreur !== null) {
            $old = $d;
            unset($old['mot_de_passe']);
            $_SESSION['auth_form'] = ['onglet' => 'signin', 'erreur' => $erreur, 'old' => $old];
            $_SESSION['ouvrir_auth'] = 'signin';
            redirect($retour);
        }

        session_regenerate_id(true);
        $u = Utilisateur::parEmail($d['email']);
        unset($u['mot_de_passe']);
        $_SESSION['utilisateur'] = $u ?: [
            'id_utilisateur' => $id, 'nom' => $d['nom'], 'prenom' => $d['prenom'], 'email' => $d['email'],
            'telephone' => $d['telephone'], 'role' => $d['role'], 'genre' => $d['genre'], 'date_naissance' => $d['date_naissance'],
        ];
        flash('succes', 'Compte créé avec succès. Bienvenue ' . $d['prenom'] . ' !');
        redirect($retour);
    }

    public function deconnexion(): void {
        $this->exigerPost();
        if (csrfValide($_POST['csrf'] ?? null)) {
            unset($_SESSION['utilisateur']);
            session_regenerate_id(true);
            flash('info', 'Vous êtes déconnecté(e).');
        }
        redirect(url('home'));
    }

    private function exigerPost(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(url('espace-utilisateur'));
        }
    }

    private function dateNaissanceValide(string $date): bool {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return false;
        }
        return $d <= new \DateTime('-16 years') && $d >= new \DateTime('-110 years');
    }

    private function retourSur(string $retour): string {
        if ($retour !== '' && strpos($retour, BASE_URL) === 0 && strpos($retour, '//') === false) {
            return $retour;
        }
        return url('espace-utilisateur');
    }
}
