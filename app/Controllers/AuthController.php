<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\UserModel;
use PDO;
use PDOException;

/**
 * AuthController : TOUTE la logique d'authentification
 * (connexion BD, requêtes préparées, hachage, validation, sessions, CSRF, rôles).
 *
 * Les méthodes "...Action()" sont appelées par le routeur (app/routes.php) :
 * elles jouent le rôle des "case" du switch de index.php dans l'énoncé.
 */
class AuthController extends Controller
{
    // Contrôle de saisie + CSRF partagés avec DriverController (même règles partout)
    use UserFormTrait;

    protected string $title     = 'Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    // Valeurs autorisées (liste blanche) : identiques aux ENUM de la table users
    public const GENRES = [
        'homme' => 'Homme',
        'femme' => 'Femme',
    ];

    public const TYPES_SERVICE = [
        'utilisateur_normal'  => 'Utilisateur normal',
        'chauffeur_bus'       => 'Chauffeur bus',
        'chauffeur_train'     => 'Chauffeur train',
        'louage'              => 'Louage',
        'covoiturage'         => 'Covoiturage',
        'location'            => 'Location',
        'reparation_vehicule' => 'Réparation de véhicule',
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // OUTILS
    // =====================================

    /** Redirige l'utilisateur connecté vers son espace selon son rôle. */
    private static function redirectToSpace(): void
    {
        self::redirect(($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard');
    }

    /**
     * Ouvre la popup de connexion/inscription : les messages sont gardés
     * en session (message "flash"), puis on revient sur la page d'accueil
     * où la popup s'affiche automatiquement sur le bon onglet.
     * $tab : 'login' ou 'register'
     */
    private static function openPopup(string $tab, array $errors = [], array $old = [], ?string $success = null, ?string $info = null): void
    {
        $_SESSION['auth_popup'] = [
            'tab'     => $tab,
            'errors'  => $errors,
            'old'     => $old,
            'success' => $success,
            'info'    => $info,
        ];
        self::redirect('home');
    }

    // =====================================
    // REGISTER
    // =====================================
    public function register(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            $errors[] = "Jeton de sécurité invalide. Veuillez réessayer.";
            return $errors;
        }

        $d        = $this->readUserInput($post);
        $password = is_string($post['password'] ?? null) ? $post['password'] : '';
        $confirm  = is_string($post['password_confirm'] ?? null) ? $post['password_confirm'] : '';

        // --- Validation des entrées (1re barrière contre XSS / données invalides) ---
        $errors = array_merge($this->validateUserInput($d), $this->validatePassword($password, $confirm));
        if (!empty($errors)) {
            return $errors;
        }

        if ($this->findByEmail($d['email'])) {
            $errors[] = "Cette adresse email est déjà utilisée.";
            return $errors;
        }

        // --- Construction du Model ---
        $user = new UserModel();
        $this->fillUser($user, $d);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT)); // hachage irréversible

        // --- Insertion : requête préparée (protection injection SQL) ---
        // La colonne "role" n'est PAS envoyée : la base met 'user' par défaut.
        // Le visiteur ne peut donc jamais se donner lui-même le rôle admin.
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (nom, prenom, email, password, date_naissance, genre, telephone, type_service)
                 VALUES (:nom, :prenom, :email, :password, :date_naissance, :genre, :telephone, :type_service)'
            );
            $stmt->execute([
                'nom'            => $user->getNom(),
                'prenom'         => $user->getPrenom(),
                'email'          => $user->getEmail(),
                'password'       => $user->getPassword(),
                'date_naissance' => $user->getDateNaissance(),
                'genre'          => $user->getGenre(),
                'telephone'      => $user->getTelephone(),
                'type_service'   => $user->getTypeService(),
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = "Cette adresse email est déjà utilisée.";
            } else {
                error_log('Register error: ' . $e->getMessage());
                $errors[] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
            }
        }

        return $errors;
    }

    // =====================================
    // LOGIN
    // =====================================
    private function findByEmail(string $email): ?UserModel
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? UserModel::fromArray($row) : null;
    }

    public function login(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            $errors[] = "Jeton de sécurité invalide. Veuillez réessayer.";
            return $errors;
        }

        $email    = $this->field($post, 'email');
        $password = is_string($post['password'] ?? null) ? $post['password'] : '';

        if ($email === '' || $password === '') {
            $errors[] = "Veuillez saisir votre email et votre mot de passe.";
            return $errors;
        }

        $user = $this->findByEmail($email);

        // Message volontairement générique (ne jamais révéler si l'email existe)
        if (!$user || !password_verify($password, $user->getPassword())) {
            $errors[] = "Email ou mot de passe incorrect.";
            return $errors;
        }

        // Nouvel identifiant de session : protection contre la fixation de session
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user->getId();
        $_SESSION['nom']           = $user->getNom();
        $_SESSION['prenom']        = $user->getPrenom();
        $_SESSION['role']          = $user->getRole();
        $_SESSION['type_service']  = $user->getTypeService();
        $_SESSION['last_activity'] = time();

        return []; // succès
    }

    // =====================================
    // LOGOUT
    // =====================================
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    // =====================================
    // USER SPACE (frontoffice)
    // =====================================
    public function findById(int $id): ?UserModel
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? UserModel::fromArray($row) : null;
    }

    /**
     * Modification du profil de l'utilisateur connecté.
     * - L'id vient de la SESSION, jamais du formulaire (impossible de modifier le compte d'un autre).
     * - Mêmes contrôles de saisie que l'inscription.
     * - Le rôle n'est jamais modifiable ici.
     * - Changement de mot de passe facultatif : exige le mot de passe actuel.
     */
    public function updateProfile(int $id, array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            return ["Jeton de sécurité invalide. Veuillez réessayer."];
        }

        $user = $this->findById($id);
        if (!$user) {
            return ["Compte introuvable."];
        }

        $d = $this->readUserInput($post);
        $errors = $this->validateUserInput($d);

        // Mot de passe : seulement si l'utilisateur veut le changer
        $current = is_string($post['current_password'] ?? null) ? $post['current_password'] : '';
        $new     = is_string($post['password'] ?? null) ? $post['password'] : '';
        $confirm = is_string($post['password_confirm'] ?? null) ? $post['password_confirm'] : '';
        $changePassword = ($new !== '' || $confirm !== '');

        if ($changePassword) {
            if ($current === '' || !password_verify($current, $user->getPassword())) {
                $errors[] = "Le mot de passe actuel est incorrect.";
            }
            $errors = array_merge($errors, $this->validatePassword($new, $confirm));
        }

        if (!empty($errors)) {
            return $errors;
        }

        // Email unique (sauf s'il s'agit de son propre email)
        $existing = $this->findByEmail($d['email']);
        if ($existing && $existing->getId() !== $user->getId()) {
            return ["Cette adresse email est déjà utilisée."];
        }

        $this->fillUser($user, $d);
        if ($changePassword) {
            $user->setPassword(password_hash($new, PASSWORD_DEFAULT));
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE users SET nom = :nom, prenom = :prenom, email = :email, password = :password,
                        date_naissance = :date_naissance, genre = :genre, telephone = :telephone,
                        type_service = :type_service
                 WHERE id = :id'
            );
            $stmt->execute([
                'nom'            => $user->getNom(),
                'prenom'         => $user->getPrenom(),
                'email'          => $user->getEmail(),
                'password'       => $user->getPassword(),
                'date_naissance' => $user->getDateNaissance(),
                'genre'          => $user->getGenre(),
                'telephone'      => $user->getTelephone(),
                'type_service'   => $user->getTypeService(),
                'id'             => $user->getId(),
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ["Cette adresse email est déjà utilisée."];
            }
            error_log('Profile update error: ' . $e->getMessage());
            return ["Une erreur est survenue lors de la mise à jour. Veuillez réessayer."];
        }

        // La session reflète les nouvelles données
        $_SESSION['nom']          = $user->getNom();
        $_SESSION['prenom']       = $user->getPrenom();
        $_SESSION['type_service'] = $user->getTypeService();

        return [];
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /** Ouvre la popup de connexion et arrête l'exécution si l'utilisateur n'est pas authentifié. */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            self::openPopup('login', [], [], null, "Veuillez vous connecter pour accéder à cette page.");
        }
    }

    // =====================================
    // ADMIN SPACE (backoffice)
    // =====================================

    /** Autorisation : bloque (403) si le rôle en session n'est pas celui demandé. */
    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (($_SESSION['role'] ?? '') !== $role) {
            http_response_code(403);
            exit("Accès refusé : vous n'avez pas les permissions nécessaires.");
        }
    }

    /** @return UserModel[] */
    public function getAllUsers(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY id ASC');
        return array_map([UserModel::class, 'fromArray'], $stmt->fetchAll());
    }

    public function countAllUsers(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return (int) $stmt->fetchColumn();
    }

    public function countByTypeService(string $type): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE type_service = :type AND role = :role');
        $stmt->execute(['type' => $type, 'role' => 'user']);
        return (int) $stmt->fetchColumn();
    }

    // =====================================
    // CSRF
    // =====================================
    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // =====================================
    // ACTIONS (appelées par le routeur)
    // =====================================

    // index.php?page=register  (formulaire "Sign in" de la popup)
    public function registerAction(): void
    {
        if (self::isAuthenticated()) {
            self::redirectToSpace();
        }

        // GET : pas de page d'inscription séparée, on ouvre la popup
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::openPopup('register');
        }

        $errors = $this->register($_POST);

        if (!empty($errors)) {
            $old = [];
            foreach (['nom', 'prenom', 'email', 'date_naissance', 'genre', 'telephone', 'type_service'] as $key) {
                $old[$key] = $this->field($_POST, $key);
            }
            self::openPopup('register', $errors, $old);
        }

        // Succès : la popup s'ouvre sur l'onglet Login avec un message vert
        self::openPopup('login', [], ['email' => $this->field($_POST, 'email')],
            "Compte créé avec succès, vous pouvez maintenant vous connecter.");
    }

    // index.php?page=login  (formulaire "Login" de la popup)
    public function loginAction(): void
    {
        if (self::isAuthenticated()) {
            self::redirectToSpace();
        }

        // GET : on ouvre la popup (avec un message si la session a expiré)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::openPopup('login', [], [], null, isset($_GET['expired'])
                ? "Votre session a expiré après une période d'inactivité. Veuillez vous reconnecter."
                : null);
        }

        $errors = $this->login($_POST);

        if (!empty($errors)) {
            self::openPopup('login', $errors, ['email' => $this->field($_POST, 'email')]);
        }

        self::redirectToSpace(); // admin -> backoffice, user -> frontoffice
    }

    // index.php?page=logout
    public function logoutAction(): void
    {
        $this->logout();
        self::redirect('home');
    }

    // index.php?page=dashboard  (rôle user = frontoffice)
    public function dashboardAction(): void
    {
        self::requireAuth();

        $user = $this->findById((int) $_SESSION['user_id']);
        if (!$user) { // compte supprimé alors que la session est encore active
            $this->logout();
            self::redirect('home');
        }
        $_SESSION['role'] = $user->getRole(); // rôle toujours synchronisé avec la base

        // Message "flash" après une modification (succès ou erreurs)
        $flash = $_SESSION['profile_flash'] ?? [];
        unset($_SESSION['profile_flash']);

        $this->page  = 'dashboard';
        $this->title = 'Mon profil | Smart City';
        $this->render('user_space', [
            'user'           => $user,
            'editMode'       => isset($_GET['edit']) || !empty($flash['errors']),
            'profileErrors'  => $flash['errors'] ?? [],
            'profileOld'     => $flash['old'] ?? [],
            'profileSuccess' => $flash['success'] ?? null,
        ]);
    }

    // index.php?page=profile-update  (POST du formulaire "Modifier mon profil")
    public function profileUpdateAction(): void
    {
        self::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('dashboard');
        }

        $errors = $this->updateProfile((int) $_SESSION['user_id'], $_POST);

        if (!empty($errors)) {
            $_SESSION['profile_flash'] = [
                'errors' => $errors,
                'old'    => $this->readUserInput($_POST), // jamais les mots de passe
            ];
            self::redirect('dashboard', ['edit' => 1]);
        }

        $_SESSION['profile_flash'] = ['success' => "Vos informations ont été mises à jour avec succès."];
        self::redirect('dashboard');
    }

    // index.php?page=admin  (rôle admin uniquement = backoffice)
    public function adminAction(): void
    {
        self::requireRole('admin');

        $user = $this->findById((int) $_SESSION['user_id']);
        if (!$user) {
            $this->logout();
            self::redirect('home');
        }
        // Le rôle est re-vérifié en base à chaque requête :
        // un admin rétrogradé perd l'accès immédiatement, même avec une session ouverte.
        if ($user->getRole() !== 'admin') {
            $_SESSION['role'] = $user->getRole();
            http_response_code(403);
            exit("Accès refusé : vous n'avez pas les permissions nécessaires.");
        }

        $this->page  = 'admin';
        $this->title = 'Backoffice | Smart City';
        $this->render('admin_space', [
            'user'         => $user,
            'allUsers'     => $this->getAllUsers(),
            'totalUsers'   => $this->countAllUsers(),
            'totalAdmins'  => $this->countByRole('admin'),
            'totalRegular' => $this->countByRole('user'),
            'totalDrivers' => $this->countByTypeService('chauffeur_bus'),
        ], 'layout_admin');
    }
}
