<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\BusModel;
use App\Models\TrajetModel;
use PDO;
use PDOException;

/**
 * BusController : gestion de la flotte de bus par l'administrateur (backoffice).
 * CRUD complet + attribution d'un bus à un chauffeur (bus attitré).
 * L'attribution d'un bus à un trajet se fait dans le formulaire du trajet (TrajetController).
 *
 * Contrôle de saisie (identique en JS) :
 * - matricule : 3 à 20 caractères (majuscules, chiffres, espaces, tirets), unique, ex : 123 TU 4567
 * - marque / modèle : 2 à 50 caractères
 * - capacité : entier de 10 à 150 places
 * - année : de 1990 à l'année prochaine
 * - statut : disponible, maintenance ou hors service
 * - chauffeur attitré : vide ou un chauffeur de bus qui n'a pas déjà un bus attitré
 * - un bus utilisé par un trajet actif ne peut pas passer en maintenance / hors service
 */
class BusController extends Controller
{
    use UserFormTrait;  // field(), verifyCsrfToken()
    use AdminTrait;     // requireAdmin(), flash(), popFlash(), intFromQuery(), intFromPost(), csrfOk()

    public const STATUTS = [
        'disponible'   => 'Disponible',
        'maintenance'  => 'En maintenance',
        'hors_service' => 'Hors service',
    ];

    private const MATRICULE_PATTERN = '/^[A-Z0-9][A-Z0-9 \-]{2,19}$/';
    private const TEXT_PATTERN      = "/^[\p{L}0-9][\p{L}0-9 '\-.]{1,49}$/u";   // 2 à 50

    protected string $title = 'Bus | Backoffice';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // ACCÈS AUX DONNÉES (requêtes préparées)
    // =====================================

    private const SELECT_BUS =
        'SELECT b.*, u.nom AS chauffeur_nom, u.prenom AS chauffeur_prenom,
                (SELECT COUNT(*) FROM trajets t WHERE t.bus_id = b.id) AS nb_trajets
         FROM buses b
         LEFT JOIN users u ON u.id = b.chauffeur_id';

    /** @return BusModel[] */
    public function getBuses(string $search = '', string $statut = ''): array
    {
        $where  = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(b.matricule LIKE :q1 OR b.marque LIKE :q2 OR b.modele LIKE :q3)';
            $like = '%' . $search . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
        }
        if (array_key_exists($statut, self::STATUTS)) {
            $where[] = 'b.statut = :statut';
            $params['statut'] = $statut;
        }
        $sql = self::SELECT_BUS . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY b.matricule';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([BusModel::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findBus(int $id): ?BusModel
    {
        $stmt = $this->db->prepare(self::SELECT_BUS . ' WHERE b.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? BusModel::fromArray($row) : null;
    }

    /** @return TrajetModel[] Trajets effectués avec ce bus. */
    public function getTrajetsOfBus(int $busId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.*, u.nom AS chauffeur_nom, u.prenom AS chauffeur_prenom
             FROM trajets t LEFT JOIN users u ON u.id = t.chauffeur_id
             WHERE t.bus_id = :id ORDER BY t.heure_depart'
        );
        $stmt->execute(['id' => $busId]);
        return array_map([TrajetModel::class, 'fromArray'], $stmt->fetchAll());
    }

    /**
     * Chauffeurs de bus pouvant recevoir ce bus comme bus attitré :
     * ceux sans bus attitré + le chauffeur actuel de ce bus. [id => "Prénom Nom"]
     */
    public function getAvailableDrivers(int $exceptBusId = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nom, u.prenom FROM users u
             WHERE u.type_service = 'chauffeur_bus' AND u.role = 'user'
               AND NOT EXISTS (SELECT 1 FROM buses b WHERE b.chauffeur_id = u.id AND b.id <> :bid)
             ORDER BY u.nom, u.prenom"
        );
        $stmt->execute(['bid' => $exceptBusId]);
        $options = [];
        foreach ($stmt->fetchAll() as $row) {
            $options[(int) $row['id']] = $row['prenom'] . ' ' . $row['nom'];
        }
        return $options;
    }

    // =====================================
    // CONTRÔLE DE SAISIE
    // =====================================

    private function readBusInput(array $post): array
    {
        return [
            'matricule'    => strtoupper(preg_replace('/\s+/', ' ', $this->field($post, 'matricule'))),
            'marque'       => $this->field($post, 'marque'),
            'modele'       => $this->field($post, 'modele'),
            'capacite'     => $this->field($post, 'capacite'),
            'annee'        => $this->field($post, 'annee'),
            'statut'       => $this->field($post, 'statut'),
            'chauffeur_id' => $this->field($post, 'chauffeur_id'),
        ];
    }

    private function validateBusInput(array $d, int $exceptId = 0): array
    {
        $errors = [];
        $maxYear = (int) date('Y') + 1;

        if (!preg_match(self::MATRICULE_PATTERN, $d['matricule'])) {
            $errors[] = "Le matricule doit contenir 3 à 20 caractères (lettres majuscules, chiffres, espaces, tirets), ex : 123 TU 4567.";
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM buses WHERE matricule = :m AND id <> :id');
            $stmt->execute(['m' => $d['matricule'], 'id' => $exceptId]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors[] = "Ce matricule est déjà enregistré.";
            }
        }
        if (!preg_match(self::TEXT_PATTERN, $d['marque'])) {
            $errors[] = "La marque doit contenir 2 à 50 caractères.";
        }
        if (!preg_match(self::TEXT_PATTERN, $d['modele'])) {
            $errors[] = "Le modèle doit contenir 2 à 50 caractères.";
        }
        if (!ctype_digit($d['capacite']) || (int) $d['capacite'] < 10 || (int) $d['capacite'] > 150) {
            $errors[] = "La capacité doit être un nombre entier entre 10 et 150 places.";
        }
        if (!ctype_digit($d['annee']) || (int) $d['annee'] < 1990 || (int) $d['annee'] > $maxYear) {
            $errors[] = "L'année doit être comprise entre 1990 et {$maxYear}.";
        }
        if (!array_key_exists($d['statut'], self::STATUTS)) {
            $errors[] = "Veuillez choisir un statut valide.";
        } elseif ($exceptId > 0 && $d['statut'] !== 'disponible') {
            // Un bus utilisé par un trajet actif doit rester disponible
            $stmt = $this->db->prepare(
                "SELECT GROUP_CONCAT(code ORDER BY code SEPARATOR ', ') FROM trajets WHERE bus_id = :id AND statut = 'actif'"
            );
            $stmt->execute(['id' => $exceptId]);
            $codes = $stmt->fetchColumn();
            if ($codes) {
                $errors[] = "Ce bus est utilisé par le(s) trajet(s) actif(s) {$codes} : retirez-le de ces trajets avant de changer son statut.";
            }
        }

        if ($d['chauffeur_id'] !== '' &&
            (!ctype_digit($d['chauffeur_id']) || !array_key_exists((int) $d['chauffeur_id'], $this->getAvailableDrivers($exceptId)))) {
            $errors[] = "Veuillez choisir un chauffeur de bus valide (un chauffeur ne peut avoir qu'un seul bus attitré).";
        }

        return $errors;
    }

    private function fillBus(BusModel $b, array $d): void
    {
        $b->setMatricule($d['matricule']);
        $b->setMarque($d['marque']);
        $b->setModele($d['modele']);
        $b->setCapacite((int) $d['capacite']);
        $b->setAnnee((int) $d['annee']);
        $b->setStatut($d['statut']);
        $b->setChauffeurId($d['chauffeur_id'] !== '' ? (int) $d['chauffeur_id'] : null);
    }

    private function busParams(BusModel $b): array
    {
        return [
            'matricule'    => $b->getMatricule(),
            'marque'       => $b->getMarque(),
            'modele'       => $b->getModele(),
            'capacite'     => $b->getCapacite(),
            'annee'        => $b->getAnnee(),
            'statut'       => $b->getStatut(),
            'chauffeur_id' => $b->getChauffeurId(),
        ];
    }

    // =====================================
    // ACTIONS (appelées par le routeur)
    // =====================================

    // index.php?page=admin-buses  (liste + recherche + filtre statut)
    public function indexAction(): void
    {
        $this->requireAdmin();

        $search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $statut = isset($_GET['statut']) && is_string($_GET['statut']) ? $_GET['statut'] : '';

        $this->page = 'admin-buses';
        $this->render('admin_buses', [
            'buses'   => $this->getBuses($search, $statut),
            'search'  => $search,
            'statut'  => $statut,
            'statuts' => self::STATUTS,
            'flash'   => $this->popFlash(),
        ], 'layout_admin');
    }

    // index.php?page=admin-bus-show&id=...
    public function showAction(): void
    {
        $this->requireAdmin();

        $bus = $this->findBus($this->intFromQuery());
        if (!$bus) {
            $this->flash('danger', "Bus introuvable.");
            self::redirect('admin-buses');
        }

        $this->page = 'admin-buses';
        $this->render('admin_bus_show', [
            'bus'     => $bus,
            'trajets' => $this->getTrajetsOfBus((int) $bus->getId()),
            'statuts' => self::STATUTS,
            'flash'   => $this->popFlash(),
        ], 'layout_admin');
    }

    // index.php?page=admin-bus-create
    public function createAction(): void
    {
        $this->requireAdmin();

        $errors = [];
        $old    = ['statut' => 'disponible', 'chauffeur_id' => (string) $this->intFromQuery('chauffeur') ?: ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->readBusInput($_POST);
            $errors = $this->csrfOk() ? $this->validateBusInput($old) : ["Jeton de sécurité invalide. Veuillez réessayer."];

            if (!$errors) {
                $bus = new BusModel();
                $this->fillBus($bus, $old);
                try {
                    $stmt = $this->db->prepare(
                        'INSERT INTO buses (matricule, marque, modele, capacite, annee, statut, chauffeur_id)
                         VALUES (:matricule, :marque, :modele, :capacite, :annee, :statut, :chauffeur_id)'
                    );
                    $stmt->execute($this->busParams($bus));
                    $this->flash('success', "Le bus {$bus->getMatricule()} a été ajouté à la flotte.");
                    self::redirect('admin-bus-show', ['id' => (int) $this->db->lastInsertId()]);
                } catch (PDOException $e) {
                    error_log('Bus create error: ' . $e->getMessage());
                    $errors = ["Une erreur est survenue lors de l'ajout du bus."];
                }
            }
        }

        $this->page = 'admin-buses';
        $this->render('admin_bus_form', [
            'mode'          => 'create',
            'bus'           => null,
            'errors'        => $errors,
            'old'           => $old,
            'statuts'       => self::STATUTS,
            'driverOptions' => $this->getAvailableDrivers(),
        ], 'layout_admin');
    }

    // index.php?page=admin-bus-edit&id=...
    public function editAction(): void
    {
        $this->requireAdmin();

        $bus = $this->findBus($this->intFromQuery());
        if (!$bus) {
            $this->flash('danger', "Bus introuvable.");
            self::redirect('admin-buses');
        }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old    = $this->readBusInput($_POST);
            $errors = $this->csrfOk()
                ? $this->validateBusInput($old, (int) $bus->getId())
                : ["Jeton de sécurité invalide. Veuillez réessayer."];

            if (!$errors) {
                $this->fillBus($bus, $old);
                try {
                    $stmt = $this->db->prepare(
                        'UPDATE buses SET matricule = :matricule, marque = :marque, modele = :modele, capacite = :capacite,
                                annee = :annee, statut = :statut, chauffeur_id = :chauffeur_id
                         WHERE id = :id'
                    );
                    $stmt->execute($this->busParams($bus) + ['id' => $bus->getId()]);
                    $this->flash('success', "Le bus {$bus->getMatricule()} a été mis à jour.");
                    self::redirect('admin-bus-show', ['id' => $bus->getId()]);
                } catch (PDOException $e) {
                    error_log('Bus update error: ' . $e->getMessage());
                    $errors = ["Une erreur est survenue lors de la modification du bus."];
                }
            }
        }

        $this->page = 'admin-buses';
        $this->render('admin_bus_form', [
            'mode'          => 'edit',
            'bus'           => $bus,
            'errors'        => $errors,
            'old'           => $old,
            'statuts'       => self::STATUTS,
            'driverOptions' => $this->getAvailableDrivers((int) $bus->getId()),
        ], 'layout_admin');
    }

    // index.php?page=admin-bus-delete  (POST + CSRF)
    public function deleteAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('admin-buses');
        }
        if (!$this->csrfOk()) {
            $this->flash('danger', "Jeton de sécurité invalide. Veuillez réessayer.");
            self::redirect('admin-buses');
        }

        $bus = $this->findBus($this->intFromPost('id'));
        if (!$bus) {
            $this->flash('danger', "Suppression impossible : bus introuvable.");
            self::redirect('admin-buses');
        }

        // Les trajets qui utilisaient ce bus passent "sans bus" (ON DELETE SET NULL)
        $stmt = $this->db->prepare('DELETE FROM buses WHERE id = :id');
        $stmt->execute(['id' => $bus->getId()]);
        $this->flash('success', "Le bus {$bus->getMatricule()} a été supprimé.");
        self::redirect('admin-buses');
    }
}
