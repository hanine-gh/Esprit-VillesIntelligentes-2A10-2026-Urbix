<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\StationModel;
use App\Models\TrajetModel;
use PDO;
use PDOException;

/**
 * ChauffeurController : espace du chauffeur de bus (frontoffice).
 * - Consulter les trajets qui lui sont attribués, avec leurs stations
 * - Ajouter des stations UNIQUEMENT sur ses propres trajets
 *
 * Sécurité : connecté + type de compte "chauffeur_bus" vérifié en base ;
 * l'appartenance du trajet est vérifiée côté serveur (chauffeur_id = id en session),
 * jamais à partir du formulaire. Même contrôle de saisie que l'admin (StationFormTrait).
 */
class ChauffeurController extends Controller
{
    use UserFormTrait;     // field(), verifyCsrfToken()
    use AdminTrait;        // intFromQuery(), csrfOk(), isValidTime()  (requireAdmin non utilisé ici)
    use StationFormTrait;  // readStationInput(), validateStationInput(), fillStation(), nextOrdre(), insertStation()

    protected string $title     = 'Mes trajets | Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // SÉCURITÉ
    // =====================================

    /** Autorise uniquement un chauffeur de bus connecté (vérifié en base). Retourne son id. */
    private function requireDriver(): int
    {
        AuthController::requireAuth();

        $id   = (int) $_SESSION['user_id'];
        $stmt = $this->db->prepare('SELECT type_service FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $type = $stmt->fetchColumn();

        if ($type !== 'chauffeur_bus') {
            http_response_code(403);
            exit("Accès refusé : cet espace est réservé aux chauffeurs de bus.");
        }
        $_SESSION['type_service'] = $type;
        return $id;
    }

    /** Trajet appartenant à ce chauffeur, sinon null. */
    private function findMyTrajet(int $trajetId, int $driverId): ?TrajetModel
    {
        $stmt = $this->db->prepare('SELECT * FROM trajets WHERE id = :id AND chauffeur_id = :cid LIMIT 1');
        $stmt->execute(['id' => $trajetId, 'cid' => $driverId]);
        $row = $stmt->fetch();
        return $row ? TrajetModel::fromArray($row) : null;
    }

    private function driverFlash(string $type, string $message): void
    {
        $_SESSION['driver_flash'] = ['type' => $type, 'message' => $message];
    }

    // =====================================
    // ACTIONS
    // =====================================

    // index.php?page=mes-missions  (quel trajet je fais, avec quel bus)
    public function missionsAction(): void
    {
        $driverId = $this->requireDriver();

        // Mission = trajet attribué + bus à conduire (+ nombre de stations)
        $stmt = $this->db->prepare(
            'SELECT t.*, b.matricule AS bus_matricule, b.marque AS bus_marque, b.modele AS bus_modele,
                    b.capacite AS bus_capacite, b.statut AS bus_statut,
                    (SELECT COUNT(*) FROM stations s WHERE s.trajet_id = t.id) AS nb_stations
             FROM trajets t
             LEFT JOIN buses b ON b.id = t.bus_id
             WHERE t.chauffeur_id = :cid
             ORDER BY (t.statut = \'actif\') DESC, t.heure_depart'
        );
        $stmt->execute(['cid' => $driverId]);
        $missions = array_map([TrajetModel::class, 'fromArray'], $stmt->fetchAll());

        // Bus attitré du chauffeur (s'il en a un)
        $stmt = $this->db->prepare('SELECT * FROM buses WHERE chauffeur_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $driverId]);
        $row = $stmt->fetch();
        $busAttitre = $row ? \App\Models\BusModel::fromArray($row) : null;

        $this->page  = 'mes-missions';
        $this->title = 'Mes missions | Smart City';
        $this->render('driver_missions', [
            'missions'   => $missions,
            'busAttitre' => $busAttitre,
            'busStatuts' => BusController::STATUTS,
        ]);
    }

    // index.php?page=mon-bus  ("Cas de mon bus" : le chauffeur signale l'état de son bus attitré)
    public function busCaseAction(): void
    {
        $driverId = $this->requireDriver();

        // Uniquement SON bus attitré (jamais un id venant du formulaire)
        $stmt = $this->db->prepare('SELECT * FROM buses WHERE chauffeur_id = :cid LIMIT 1');
        $stmt->execute(['cid' => $driverId]);
        $row = $stmt->fetch();
        $bus = $row ? \App\Models\BusModel::fromArray($row) : null;

        $errors  = [];
        $old     = $bus ? ['statut' => $bus->getStatut(), 'etat_commentaire' => (string) $bus->getEtatCommentaire()] : [];
        $success = $_SESSION['driver_flash']['message'] ?? null;
        unset($_SESSION['driver_flash']);

        if ($bus && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'statut'           => $this->field($_POST, 'statut'),
                'etat_commentaire' => $this->field($_POST, 'etat_commentaire'),
            ];

            // --- Contrôle de saisie (identique en JS) ---
            if (!$this->csrfOk()) {
                $errors[] = "Jeton de sécurité invalide. Veuillez réessayer.";
            } else {
                if (!array_key_exists($old['statut'], BusController::STATUTS)) {
                    $errors[] = "Veuillez choisir l'état du bus.";
                }
                $len = mb_strlen($old['etat_commentaire']);
                if ($old['statut'] !== 'disponible' && $len === 0) {
                    $errors[] = "Décrivez le problème du bus (obligatoire s'il n'est pas disponible).";
                } elseif ($len > 0 && ($len < 5 || $len > 255)) {
                    $errors[] = "La description doit contenir entre 5 et 255 caractères.";
                } elseif ($len > 0 && !preg_match('/\p{L}/u', $old['etat_commentaire'])) {
                    $errors[] = "La description doit contenir du texte.";
                }
            }

            if (!$errors) {
                $bus->setStatut($old['statut']);
                $bus->setEtatCommentaire($old['etat_commentaire'] !== '' ? $old['etat_commentaire'] : null);

                $stmt = $this->db->prepare(
                    'UPDATE buses SET statut = :statut, etat_commentaire = :commentaire, etat_maj_at = NOW()
                     WHERE id = :id AND chauffeur_id = :cid'
                );
                $stmt->execute([
                    'statut'      => $bus->getStatut(),
                    'commentaire' => $bus->getEtatCommentaire(),
                    'id'          => $bus->getId(),
                    'cid'         => $driverId,
                ]);

                $this->driverFlash('success', "L'état du bus {$bus->getMatricule()} a été mis à jour. L'administrateur en est informé.");
                self::redirect('mon-bus');
            }
        }

        $this->page  = 'mon-bus';
        $this->title = 'Cas de mon bus | Smart City';
        $this->render('driver_bus_case', [
            'bus'     => $bus,
            'errors'  => $errors,
            'old'     => $old,
            'success' => $success,
            'statuts' => BusController::STATUTS,
        ]);
    }

    // index.php?page=mes-trajets  (consulter mes trajets et leurs stations)
    public function trajetsAction(): void
    {
        $driverId = $this->requireDriver();

        $stmt = $this->db->prepare('SELECT * FROM trajets WHERE chauffeur_id = :cid ORDER BY heure_depart');
        $stmt->execute(['cid' => $driverId]);
        $trajets = array_map([TrajetModel::class, 'fromArray'], $stmt->fetchAll());

        // Stations de chaque trajet, dans l'ordre de passage
        $stations = [];
        if ($trajets) {
            $stmt = $this->db->prepare(
                'SELECT s.* FROM stations s JOIN trajets t ON t.id = s.trajet_id
                 WHERE t.chauffeur_id = :cid ORDER BY s.trajet_id, s.ordre'
            );
            $stmt->execute(['cid' => $driverId]);
            foreach ($stmt->fetchAll() as $row) {
                $stations[(int) $row['trajet_id']][] = StationModel::fromArray($row);
            }
        }

        $flash = $_SESSION['driver_flash'] ?? null;
        unset($_SESSION['driver_flash']);

        $this->page = 'mes-trajets';
        $this->render('driver_trajets', [
            'trajets'  => $trajets,
            'stations' => $stations,
            'flash'    => $flash,
        ]);
    }

    // index.php?page=mes-trajets-station&trajet=...  (ajouter une station à MON trajet)
    public function stationCreateAction(): void
    {
        $driverId = $this->requireDriver();

        $trajet = $this->findMyTrajet($this->intFromQuery('trajet'), $driverId);
        if (!$trajet) {
            // Trajet inexistant ou attribué à un autre chauffeur : on ne dit pas lequel
            $this->driverFlash('error', "Ce trajet n'existe pas ou ne vous est pas attribué.");
            self::redirect('mes-trajets');
        }

        $errors = [];
        $old    = ['ordre' => (string) $this->nextOrdre((int) $trajet->getId())];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $this->readStationInput($_POST);

            $errors = $this->csrfOk()
                ? $this->validateStationInput($old, $trajet)
                : ["Jeton de sécurité invalide. Veuillez réessayer."];

            if (!$errors) {
                $station = new StationModel();
                $station->setTrajetId((int) $trajet->getId());
                $this->fillStation($station, $old);
                try {
                    $this->insertStation($station);
                    $this->driverFlash('success', "La station « {$station->getNom()} » a été ajoutée au trajet {$trajet->getCode()}.");
                    self::redirect('mes-trajets');
                } catch (PDOException $e) {
                    error_log('Driver station create error: ' . $e->getMessage());
                    $errors = ["Une erreur est survenue lors de l'ajout de la station."];
                }
            }
        }

        $this->page  = 'mes-trajets';
        $this->title = 'Ajouter une station | Smart City';
        $this->render('driver_station_form', [
            'trajet' => $trajet,
            'errors' => $errors,
            'old'    => $old,
        ]);
    }
}
