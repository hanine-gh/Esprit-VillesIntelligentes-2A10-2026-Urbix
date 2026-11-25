<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\StationModel;
use App\Models\TrajetModel;
use PDO;
use PDOException;

/**
 * TrajetController : gestion des trajets de bus par l'administrateur (backoffice).
 * CRUD complet + attribution d'un trajet à un chauffeur de bus.
 *
 * Contrôle de saisie (identique en JS) :
 * - code : 1 à 20 caractères, lettres majuscules / chiffres / tirets, unique
 * - nom : 3 à 100 caractères ; départ / arrivée : 2 à 100 caractères, différents
 * - heures au format HH:MM, arrivée après départ
 * - statut : actif ou inactif
 * - chauffeur : vide (non attribué) ou un chauffeur de bus existant,
 *   sans chevauchement d'horaires avec ses autres trajets actifs
 * - bus : vide ou un bus existant ; pour un trajet actif, le bus doit être "disponible"
 *   et ne pas être utilisé par un autre trajet actif aux mêmes horaires
 * - en modification : les stations existantes doivent rester dans les nouveaux horaires
 */
class TrajetController extends Controller
{
    use UserFormTrait;  // field(), verifyCsrfToken()
    use AdminTrait;     // requireAdmin(), flash(), popFlash(), intFromQuery(), csrfOk(), isValidTime()

    public const STATUTS = [
        'actif'   => 'Actif',
        'inactif' => 'Inactif',
    ];

    private const PLACE_PATTERN = "/^[\p{L}0-9][\p{L}0-9 '\-.,]{1,99}$/u";   // 2 à 100 caractères
    private const NAME_PATTERN  = "/^[\p{L}0-9][\p{L}0-9 '\-.,]{2,99}$/u";   // 3 à 100 caractères
    private const CODE_PATTERN  = '/^[A-Z0-9][A-Z0-9\-]{0,19}$/';

    protected string $title = 'Trajets | Backoffice';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // ACCÈS AUX DONNÉES (requêtes préparées)
    // =====================================

    private const SELECT_TRAJET =
        'SELECT t.*, u.nom AS chauffeur_nom, u.prenom AS chauffeur_prenom,
                b.matricule AS bus_matricule, b.marque AS bus_marque, b.modele AS bus_modele,
                b.capacite AS bus_capacite, b.statut AS bus_statut,
                (SELECT COUNT(*) FROM stations s WHERE s.trajet_id = t.id) AS nb_stations
         FROM trajets t
         LEFT JOIN users u ON u.id = t.chauffeur_id
         LEFT JOIN buses b ON b.id = t.bus_id';

    /** @return TrajetModel[] */
    public function getTrajets(string $search = '', int $chauffeurId = 0): array
    {
        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(t.code LIKE :q1 OR t.nom LIKE :q2 OR t.point_depart LIKE :q3 OR t.point_arrivee LIKE :q4)';
            $like = '%' . $search . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }
        if ($chauffeurId > 0) {
            $where[] = 't.chauffeur_id = :cid';
            $params['cid'] = $chauffeurId;
        }

        $sql = self::SELECT_TRAJET
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY t.code ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([TrajetModel::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findTrajet(int $id): ?TrajetModel
    {
        $stmt = $this->db->prepare(self::SELECT_TRAJET . ' WHERE t.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? TrajetModel::fromArray($row) : null;
    }

    /** @return StationModel[] Stations du trajet, dans l'ordre de passage. */
    public function getStations(int $trajetId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM stations WHERE trajet_id = :id ORDER BY ordre ASC');
        $stmt->execute(['id' => $trajetId]);
        return array_map([StationModel::class, 'fromArray'], $stmt->fetchAll());
    }

    /** Chauffeurs de bus disponibles pour l'attribution : [id => "Prénom Nom"]. */
    public function getDriverOptions(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nom, prenom FROM users WHERE type_service = :type AND role = :role ORDER BY nom, prenom'
        );
        $stmt->execute(['type' => 'chauffeur_bus', 'role' => 'user']);
        $options = [];
        foreach ($stmt->fetchAll() as $row) {
            $options[(int) $row['id']] = $row['prenom'] . ' ' . $row['nom'];
        }
        return $options;
    }

    /** Bus de la flotte pour l'attribution : [id => ['label' => ..., 'statut' => ...]]. */
    public function getBusOptions(): array
    {
        $stmt = $this->db->query('SELECT id, matricule, marque, modele, capacite, statut FROM buses ORDER BY matricule');
        $options = [];
        foreach ($stmt->fetchAll() as $row) {
            $options[(int) $row['id']] = [
                'label'  => $row['matricule'] . ' — ' . $row['marque'] . ' ' . $row['modele'] . ' (' . $row['capacite'] . ' places)',
                'statut' => $row['statut'],
            ];
        }
        return $options;
    }

    // =====================================
    // CONTRÔLE DE SAISIE
    // =====================================

    /** Lit et nettoie les champs du formulaire trajet. */
    private function readTrajetInput(array $post): array
    {
        return [
            'code'          => strtoupper($this->field($post, 'code')),
            'nom'           => $this->field($post, 'nom'),
            'point_depart'  => $this->field($post, 'point_depart'),
            'point_arrivee' => $this->field($post, 'point_arrivee'),
            'heure_depart'  => $this->field($post, 'heure_depart'),
            'heure_arrivee' => $this->field($post, 'heure_arrivee'),
            'statut'        => $this->field($post, 'statut'),
            'chauffeur_id'  => $this->field($post, 'chauffeur_id'),
            'bus_id'        => $this->field($post, 'bus_id'),
        ];
    }

    private function validateTrajetInput(array $d, int $exceptId = 0): array
    {
        $errors = [];

        if (!preg_match(self::CODE_PATTERN, $d['code'])) {
            $errors[] = "Le code doit contenir 1 à 20 caractères (lettres, chiffres ou tirets), ex : L12.";
        }
        if (!preg_match(self::NAME_PATTERN, $d['nom'])) {
            $errors[] = "Le nom du trajet doit contenir 3 à 100 caractères (lettres, chiffres, espaces, - ' . ,).";
        }
        if (!preg_match(self::PLACE_PATTERN, $d['point_depart'])) {
            $errors[] = "Le point de départ doit contenir 2 à 100 caractères.";
        }
        if (!preg_match(self::PLACE_PATTERN, $d['point_arrivee'])) {
            $errors[] = "Le point d'arrivée doit contenir 2 à 100 caractères.";
        }
        if ($d['point_depart'] !== '' && mb_strtolower($d['point_depart']) === mb_strtolower($d['point_arrivee'])) {
            $errors[] = "Le point de départ et le point d'arrivée doivent être différents.";
        }

        $depOk = $this->isValidTime($d['heure_depart']);
        $arrOk = $this->isValidTime($d['heure_arrivee']);
        if (!$depOk) {
            $errors[] = "Heure de départ invalide (format HH:MM).";
        }
        if (!$arrOk) {
            $errors[] = "Heure d'arrivée invalide (format HH:MM).";
        }
        if ($depOk && $arrOk && $d['heure_arrivee'] <= $d['heure_depart']) {
            $errors[] = "L'heure d'arrivée doit être après l'heure de départ.";
        }

        if (!array_key_exists($d['statut'], self::STATUTS)) {
            $errors[] = "Veuillez choisir un statut valide.";
        }

        // Chauffeur : vide (non attribué) ou un chauffeur de bus existant
        if ($d['chauffeur_id'] !== '') {
            if (!ctype_digit($d['chauffeur_id']) || !array_key_exists((int) $d['chauffeur_id'], $this->getDriverOptions())) {
                $errors[] = "Veuillez choisir un chauffeur de bus valide.";
            } elseif ($depOk && $arrOk && $d['statut'] === 'actif' && $d['heure_arrivee'] > $d['heure_depart']) {
                // Un chauffeur ne peut pas conduire deux trajets actifs en même temps
                $stmt = $this->db->prepare(
                    "SELECT code FROM trajets
                     WHERE chauffeur_id = :cid AND statut = 'actif' AND id <> :id
                       AND heure_depart < :arr AND heure_arrivee > :dep
                     LIMIT 1"
                );
                $stmt->execute([
                    'cid' => (int) $d['chauffeur_id'], 'id' => $exceptId,
                    'arr' => $d['heure_arrivee'], 'dep' => $d['heure_depart'],
                ]);
                $conflict = $stmt->fetchColumn();
                if ($conflict !== false) {
                    $errors[] = "Ce chauffeur a déjà le trajet « {$conflict} » sur ces horaires.";
                }
            }
        }

        // Bus : vide (aucun) ou un bus existant, disponible et libre sur ces horaires
        $d['bus_id'] = $d['bus_id'] ?? '';
        if ($d['bus_id'] !== '') {
            $buses = $this->getBusOptions();
            if (!ctype_digit($d['bus_id']) || !array_key_exists((int) $d['bus_id'], $buses)) {
                $errors[] = "Veuillez choisir un bus valide.";
            } elseif ($d['statut'] === 'actif') {
                if ($buses[(int) $d['bus_id']]['statut'] !== 'disponible') {
                    $errors[] = "Ce bus n'est pas disponible (maintenance ou hors service) : il ne peut pas être attribué à un trajet actif.";
                } elseif ($depOk && $arrOk && $d['heure_arrivee'] > $d['heure_depart']) {
                    $stmt = $this->db->prepare(
                        "SELECT code FROM trajets
                         WHERE bus_id = :bid AND statut = 'actif' AND id <> :id
                           AND heure_depart < :arr AND heure_arrivee > :dep
                         LIMIT 1"
                    );
                    $stmt->execute([
                        'bid' => (int) $d['bus_id'], 'id' => $exceptId,
                        'arr' => $d['heure_arrivee'], 'dep' => $d['heure_depart'],
                    ]);
                    $conflict = $stmt->fetchColumn();
                    if ($conflict !== false) {
                        $errors[] = "Ce bus est déjà utilisé par le trajet « {$conflict} » sur ces horaires.";
                    }
                }
            }
        }

        // Code unique
        if (preg_match(self::CODE_PATTERN, $d['code'])) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM trajets WHERE code = :code AND id <> :id');
            $stmt->execute(['code' => $d['code'], 'id' => $exceptId]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors[] = "Ce code de trajet est déjà utilisé.";
            }
        }

        // Modification : les stations doivent rester dans les horaires du trajet
        if ($exceptId > 0 && $depOk && $arrOk) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM stations WHERE trajet_id = :id AND (heure_passage < :dep OR heure_passage > :arr)'
            );
            $stmt->execute(['id' => $exceptId, 'dep' => $d['heure_depart'], 'arr' => $d['heure_arrivee']]);
            $outside = (int) $stmt->fetchColumn();
            if ($outside > 0) {
                $errors[] = "{$outside} station(s) de ce trajet ont une heure de passage en dehors des nouveaux horaires.";
            }
        }

        return $errors;
    }

    private function fillTrajet(TrajetModel $t, array $d): void
    {
        $t->setCode($d['code']);
        $t->setNom($d['nom']);
        $t->setPointDepart($d['point_depart']);
        $t->setPointArrivee($d['point_arrivee']);
        $t->setHeureDepart($d['heure_depart']);
        $t->setHeureArrivee($d['heure_arrivee']);
        $t->setStatut($d['statut']);
        $t->setChauffeurId($d['chauffeur_id'] !== '' ? (int) $d['chauffeur_id'] : null);
        $t->setBusId(($d['bus_id'] ?? '') !== '' ? (int) $d['bus_id'] : null);
    }

    private function trajetParams(TrajetModel $t): array
    {
        return [
            'code'          => $t->getCode(),
            'nom'           => $t->getNom(),
            'point_depart'  => $t->getPointDepart(),
            'point_arrivee' => $t->getPointArrivee(),
            'heure_depart'  => $t->getHeureDepart(),
            'heure_arrivee' => $t->getHeureArrivee(),
            'statut'        => $t->getStatut(),
            'chauffeur_id'  => $t->getChauffeurId(),
            'bus_id'        => $t->getBusId(),
        ];
    }

    // =====================================
    // CRÉER / MODIFIER / SUPPRIMER
    // =====================================

    /** @return array [erreurs, id du trajet créé] */
    public function createTrajet(array $post): array
    {
        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            return [["Jeton de sécurité invalide. Veuillez réessayer."], 0];
        }
        $d = $this->readTrajetInput($post);
        $errors = $this->validateTrajetInput($d);
        if ($errors) {
            return [$errors, 0];
        }

        $t = new TrajetModel();
        $this->fillTrajet($t, $d);

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO trajets (code, nom, point_depart, point_arrivee, heure_depart, heure_arrivee, statut, chauffeur_id, bus_id)
                 VALUES (:code, :nom, :point_depart, :point_arrivee, :heure_depart, :heure_arrivee, :statut, :chauffeur_id, :bus_id)'
            );
            $stmt->execute($this->trajetParams($t));
            return [[], (int) $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log('Trajet create error: ' . $e->getMessage());
            return [["Une erreur est survenue lors de la création du trajet."], 0];
        }
    }

    public function updateTrajet(TrajetModel $t, array $post): array
    {
        if (!$this->verifyCsrfToken(is_string($post['csrf_token'] ?? null) ? $post['csrf_token'] : null)) {
            return ["Jeton de sécurité invalide. Veuillez réessayer."];
        }
        $d = $this->readTrajetInput($post);
        $errors = $this->validateTrajetInput($d, (int) $t->getId());
        if ($errors) {
            return $errors;
        }

        $this->fillTrajet($t, $d);

        try {
            $stmt = $this->db->prepare(
                'UPDATE trajets SET code = :code, nom = :nom, point_depart = :point_depart, point_arrivee = :point_arrivee,
                        heure_depart = :heure_depart, heure_arrivee = :heure_arrivee, statut = :statut,
                        chauffeur_id = :chauffeur_id, bus_id = :bus_id
                 WHERE id = :id'
            );
            $stmt->execute($this->trajetParams($t) + ['id' => $t->getId()]);
        } catch (PDOException $e) {
            error_log('Trajet update error: ' . $e->getMessage());
            return ["Une erreur est survenue lors de la modification du trajet."];
        }
        return [];
    }

    /** Supprime le trajet (ses stations sont supprimées automatiquement : ON DELETE CASCADE). */
    public function deleteTrajet(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM trajets WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    // =====================================
    // ACTIONS (appelées par le routeur)
    // =====================================

    // index.php?page=admin-trajets  (liste + recherche + filtre chauffeur)
    public function indexAction(): void
    {
        $this->requireAdmin();

        $search      = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        $chauffeurId = $this->intFromQuery('chauffeur');

        $this->page = 'admin-trajets';
        $this->render('admin_trajets', [
            'trajets'       => $this->getTrajets($search, $chauffeurId),
            'search'        => $search,
            'chauffeurId'   => $chauffeurId,
            'driverOptions' => $this->getDriverOptions(),
            'flash'         => $this->popFlash(),
        ], 'layout_admin');
    }

    // index.php?page=admin-trajet-show&id=...  (détails + stations)
    public function showAction(): void
    {
        $this->requireAdmin();

        $trajet = $this->findTrajet($this->intFromQuery());
        if (!$trajet) {
            $this->flash('danger', "Trajet introuvable.");
            self::redirect('admin-trajets');
        }

        $this->page = 'admin-trajets';
        $this->render('admin_trajet_show', [
            'trajet'   => $trajet,
            'stations' => $this->getStations((int) $trajet->getId()),
            'flash'    => $this->popFlash(),
        ], 'layout_admin');
    }

    // index.php?page=admin-trajet-create
    public function createAction(): void
    {
        $this->requireAdmin();

        $errors = [];
        $old    = [
            'statut'       => 'actif',
            'chauffeur_id' => (string) $this->intFromQuery('chauffeur') ?: '',
            'bus_id'       => (string) $this->intFromQuery('bus') ?: '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$errors, $newId] = $this->createTrajet($_POST);
            if (!$errors) {
                $this->flash('success', "Le trajet a été créé. Vous pouvez maintenant lui ajouter des stations.");
                self::redirect('admin-trajet-show', ['id' => $newId]);
            }
            $old = $this->readTrajetInput($_POST);
        }

        $this->page = 'admin-trajets';
        $this->render('admin_trajet_form', [
            'mode'          => 'create',
            'trajet'        => null,
            'errors'        => $errors,
            'old'           => $old,
            'driverOptions' => $this->getDriverOptions(),
            'busOptions'    => $this->getBusOptions(),
            'statuts'       => self::STATUTS,
        ], 'layout_admin');
    }

    // index.php?page=admin-trajet-edit&id=...
    public function editAction(): void
    {
        $this->requireAdmin();

        $trajet = $this->findTrajet($this->intFromQuery());
        if (!$trajet) {
            $this->flash('danger', "Trajet introuvable.");
            self::redirect('admin-trajets');
        }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->updateTrajet($trajet, $_POST);
            if (!$errors) {
                $this->flash('success', "Le trajet a été mis à jour.");
                self::redirect('admin-trajet-show', ['id' => $trajet->getId()]);
            }
            $old = $this->readTrajetInput($_POST);
        }

        $this->page = 'admin-trajets';
        $this->render('admin_trajet_form', [
            'mode'          => 'edit',
            'trajet'        => $trajet,
            'errors'        => $errors,
            'old'           => $old,
            'driverOptions' => $this->getDriverOptions(),
            'busOptions'    => $this->getBusOptions(),
            'statuts'       => self::STATUTS,
        ], 'layout_admin');
    }

    // index.php?page=admin-trajet-delete  (POST + CSRF)
    public function deleteAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('admin-trajets');
        }
        if (!$this->csrfOk()) {
            $this->flash('danger', "Jeton de sécurité invalide. Veuillez réessayer.");
            self::redirect('admin-trajets');
        }

        $id = $this->intFromPost('id');
        if ($id > 0 && $this->deleteTrajet($id)) {
            $this->flash('success', "Le trajet et ses stations ont été supprimés.");
        } else {
            $this->flash('danger', "Suppression impossible : trajet introuvable.");
        }
        self::redirect('admin-trajets');
    }
}
