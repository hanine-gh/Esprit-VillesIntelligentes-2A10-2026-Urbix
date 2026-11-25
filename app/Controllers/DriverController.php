<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\UserModel;
use PDO;
use PDOException;

/**
 * DriverController : gestion des chauffeurs de bus par l'administrateur (backoffice).
 * CRUD complet : consulter (liste + détails), créer, modifier, supprimer.
 *
 * Sécurité :
 * - Toutes les actions exigent le rôle admin (re-vérifié en base).
 * - Requêtes préparées uniquement (injection SQL).
 * - Jeton CSRF sur chaque formulaire (création, modification, suppression).
 * - Mêmes contrôles de saisie que l'inscription (UserFormTrait).
 * - Seuls les comptes "chauffeur_bus" de rôle "user" peuvent être modifiés/supprimés ici.
 */
class DriverController extends Controller
{
    use UserFormTrait;
    use AdminTrait;   // requireAdmin(), flash(), popFlash(), intFromQuery()...

    private const TYPE = 'chauffeur_bus';

    protected string $title = 'Chauffeurs de bus | Backoffice';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // ACCÈS AUX DONNÉES (requêtes préparées)
    // =====================================

    /** @return UserModel[] */
    public function getDrivers(string $search = ''): array
    {
        $sql    = 'SELECT * FROM users WHERE type_service = :type AND role = :role';
        $params = ['type' => self::TYPE, 'role' => 'user'];

        if ($search !== '') {
            $sql .= ' AND (nom LIKE :q1 OR prenom LIKE :q2 OR email LIKE :q3 OR telephone LIKE :q4)';
            $like = '%' . $search . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }
        $sql .= ' ORDER BY nom ASC, prenom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([UserModel::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findDriver(int $id): ?UserModel
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE id = :id AND type_service = :type AND role = :role LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'type' => self::TYPE, 'role' => 'user']);
        $row = $stmt->fetch();
        return $row ? UserModel::fromArray($row) : null;
    }

    private function emailTakenByOther(string $email, int $exceptId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $id = $stmt->fetchColumn();
        return $id !== false && (int) $id !== $exceptId;
    }

    // =====================================
    // CRÉER
    // =====================================
    public function createDriver(array $post): array
    {
        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            return ["Jeton de sécurité invalide. Veuillez réessayer."];
        }

        $d = $this->readUserInput($post);
        $d['type_service'] = self::TYPE; // imposé : on crée un chauffeur de bus

        $password = is_string($post['password'] ?? null) ? $post['password'] : '';
        $confirm  = is_string($post['password_confirm'] ?? null) ? $post['password_confirm'] : '';

        $errors = array_merge($this->validateUserInput($d), $this->validatePassword($password, $confirm));
        if (!empty($errors)) {
            return $errors;
        }
        if ($this->emailTakenByOther($d['email'])) {
            return ["Cette adresse email est déjà utilisée."];
        }

        $user = new UserModel();
        $this->fillUser($user, $d);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        try {
            // Le rôle n'est pas envoyé : la base met 'user' par défaut
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
                return ["Cette adresse email est déjà utilisée."];
            }
            error_log('Driver create error: ' . $e->getMessage());
            return ["Une erreur est survenue lors de la création. Veuillez réessayer."];
        }
        return [];
    }

    // =====================================
    // MODIFIER
    // =====================================
    public function updateDriver(UserModel $driver, array $post): array
    {
        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            return ["Jeton de sécurité invalide. Veuillez réessayer."];
        }

        $d = $this->readUserInput($post);
        $d['type_service'] = self::TYPE;

        $errors = $this->validateUserInput($d);

        // Nouveau mot de passe facultatif (laisser vide pour le conserver)
        $password = is_string($post['password'] ?? null) ? $post['password'] : '';
        $confirm  = is_string($post['password_confirm'] ?? null) ? $post['password_confirm'] : '';
        $changePassword = ($password !== '' || $confirm !== '');
        if ($changePassword) {
            $errors = array_merge($errors, $this->validatePassword($password, $confirm));
        }

        if (!empty($errors)) {
            return $errors;
        }
        if ($this->emailTakenByOther($d['email'], (int) $driver->getId())) {
            return ["Cette adresse email est déjà utilisée."];
        }

        $this->fillUser($driver, $d);
        if ($changePassword) {
            $driver->setPassword(password_hash($password, PASSWORD_DEFAULT));
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE users SET nom = :nom, prenom = :prenom, email = :email, password = :password,
                        date_naissance = :date_naissance, genre = :genre, telephone = :telephone
                 WHERE id = :id AND type_service = :type AND role = :role'
            );
            $stmt->execute([
                'nom'            => $driver->getNom(),
                'prenom'         => $driver->getPrenom(),
                'email'          => $driver->getEmail(),
                'password'       => $driver->getPassword(),
                'date_naissance' => $driver->getDateNaissance(),
                'genre'          => $driver->getGenre(),
                'telephone'      => $driver->getTelephone(),
                'id'             => $driver->getId(),
                'type'           => self::TYPE,
                'role'           => 'user',
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ["Cette adresse email est déjà utilisée."];
            }
            error_log('Driver update error: ' . $e->getMessage());
            return ["Une erreur est survenue lors de la modification. Veuillez réessayer."];
        }
        return [];
    }

    // =====================================
    // SUPPRIMER
    // =====================================
    public function deleteDriver(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id AND type_service = :type AND role = :role');
        $stmt->execute(['id' => $id, 'type' => self::TYPE, 'role' => 'user']);
        return $stmt->rowCount() === 1;
    }

    // =====================================
    // ACTIONS (appelées par le routeur)
    // =====================================

    // index.php?page=admin-drivers  (liste + recherche)
    public function indexAction(): void
    {
        $this->requireAdmin();

        $search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $flash  = $this->popFlash();

        $this->page = 'admin-drivers';
        $this->render('admin_drivers', [
            'drivers' => $this->getDrivers($search),
            'search'  => $search,
            'flash'   => $flash,
        ], 'layout_admin');
    }

    // index.php?page=admin-driver-show&id=...  (tous les détails)
    public function showAction(): void
    {
        $this->requireAdmin();

        $driver = $this->findDriver($this->intFromQuery());
        if (!$driver) {
            $this->flash('danger', "Chauffeur introuvable.");
            self::redirect('admin-drivers');
        }

        $flash = $this->popFlash();

        // Trajets attribués à ce chauffeur
        $stmt = $this->db->prepare(
            'SELECT t.*, (SELECT COUNT(*) FROM stations s WHERE s.trajet_id = t.id) AS nb_stations
             FROM trajets t WHERE t.chauffeur_id = :id ORDER BY t.heure_depart'
        );
        $stmt->execute(['id' => $driver->getId()]);
        $trajets = array_map([\App\Models\TrajetModel::class, 'fromArray'], $stmt->fetchAll());

        // Bus attitré de ce chauffeur
        $stmt = $this->db->prepare('SELECT * FROM buses WHERE chauffeur_id = :id LIMIT 1');
        $stmt->execute(['id' => $driver->getId()]);
        $row = $stmt->fetch();
        $bus = $row ? \App\Models\BusModel::fromArray($row) : null;

        $this->page = 'admin-drivers';
        $this->render('admin_driver_show', [
            'driver'  => $driver,
            'flash'   => $flash,
            'trajets' => $trajets,
            'bus'     => $bus,
        ], 'layout_admin');
    }

    // index.php?page=admin-driver-create  (GET : formulaire, POST : création)
    public function createAction(): void
    {
        $this->requireAdmin();

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->createDriver($_POST);
            if (empty($errors)) {
                $this->flash('success', "Le chauffeur a été créé avec succès.");
                self::redirect('admin-drivers');
            }
            $old = $this->readUserInput($_POST); // jamais les mots de passe
        }

        $this->page = 'admin-drivers';
        $this->render('admin_driver_form', [
            'mode'   => 'create',
            'driver' => null,
            'errors' => $errors,
            'old'    => $old,
        ], 'layout_admin');
    }

    // index.php?page=admin-driver-edit&id=...  (GET : formulaire, POST : modification)
    public function editAction(): void
    {
        $this->requireAdmin();

        $driver = $this->findDriver($this->intFromQuery());
        if (!$driver) {
            $this->flash('danger', "Chauffeur introuvable.");
            self::redirect('admin-drivers');
        }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->updateDriver($driver, $_POST);
            if (empty($errors)) {
                $this->flash('success', "Les informations du chauffeur ont été mises à jour.");
                self::redirect('admin-driver-show', ['id' => $driver->getId()]);
            }
            $old = $this->readUserInput($_POST);
        }

        $this->page = 'admin-drivers';
        $this->render('admin_driver_form', [
            'mode'   => 'edit',
            'driver' => $driver,
            'errors' => $errors,
            'old'    => $old,
        ], 'layout_admin');
    }

    // index.php?page=admin-driver-delete  (POST uniquement + CSRF)
    public function deleteAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('admin-drivers');
        }
        if (!$this->csrfOk()) {
            $this->flash('danger', "Jeton de sécurité invalide. Veuillez réessayer.");
            self::redirect('admin-drivers');
        }

        $id = $this->intFromPost('id');

        if ($id > 0 && $this->deleteDriver($id)) {
            $this->flash('success', "Le chauffeur a été supprimé.");
        } else {
            $this->flash('danger', "Suppression impossible : chauffeur introuvable.");
        }
        self::redirect('admin-drivers');
    }
}
