<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\MobiliteLocationModel;
use App\Models\MobiliteVehiculeModel;
use PDO;

/**
 * MobiliteController : module Location (moto, trottinette, vélo) — frontoffice.
 * - Consulter les véhicules (filtre par type)
 * - Réserver, déverrouiller (code PIN), prolonger, signaler un incident, terminer/noter, annuler
 * Les locations appartiennent à l'utilisateur connecté (table users).
 */
class MobiliteController extends Controller
{
    use UserFormTrait;  // verifyCsrfToken()
    use FrontTrait;     // requireUserId(), frontFlash(), popFrontFlash(), frontCsrfOk()

    public const TYPES    = ['moto', 'trottinette', 'velo'];
    public const FILTRES  = ['moto', 'trottinette', 'velo', 'tous'];
    public const STATIONS = ['Station Centre Ville', 'Station Campus Nord', 'Station Gare Centrale', 'Station Parc Urbain'];

    private const TITRES_ACTION = [
        'reserver'  => 'Réserver un véhicule',
        'demarrer'  => 'Déverrouiller / Démarrer une location',
        'prolonger' => 'Prolonger une location',
        'incident'  => 'Signaler un incident',
        'terminer'  => 'Terminer et noter une location',
        'annuler'   => 'Annuler une location',
    ];

    protected string $page      = 'location';
    protected string $title     = 'Location Moto, Trottinette & Vélo | Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // LECTURE
    // =====================================

    /** @return MobiliteVehiculeModel[] */
    private function getVehicules(string $filtre = 'tous', bool $disponiblesSeulement = false): array
    {
        $sql = 'SELECT * FROM location_vehicules WHERE 1';
        $params = [];
        if ($filtre !== 'tous') {
            $sql .= ' AND type = :type';
            $params['type'] = $filtre;
        }
        if ($disponiblesSeulement) {
            $sql .= " AND statut = 'disponible'";
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY id');
        $stmt->execute($params);
        return array_map([MobiliteVehiculeModel::class, 'fromArray'], $stmt->fetchAll());
    }

    private function findVehicule(int $id): ?MobiliteVehiculeModel
    {
        $stmt = $this->db->prepare('SELECT * FROM location_vehicules WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? MobiliteVehiculeModel::fromArray($row) : null;
    }

    /** @return MobiliteLocationModel[] locations de l'utilisateur */
    private function getLocations(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM location_locations WHERE user_id = :u ORDER BY id');
        $stmt->execute(['u' => $userId]);
        return array_map([MobiliteLocationModel::class, 'fromArray'], $stmt->fetchAll());
    }

    /** Une location de l'utilisateur (jamais celle d'un autre) */
    private function findLocation(int $id, int $userId): ?MobiliteLocationModel
    {
        $stmt = $this->db->prepare('SELECT * FROM location_locations WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch();
        return $row ? MobiliteLocationModel::fromArray($row) : null;
    }

    private function setStatutVehicule(?int $vehiculeId, string $statut): void
    {
        if ($vehiculeId === null) {
            return;
        }
        $this->db->prepare('UPDATE location_vehicules SET statut = :s WHERE id = :id')
            ->execute(['s' => $statut, 'id' => $vehiculeId]);
    }

    private function nouvelleReference(): string
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM location_locations WHERE reference = :r');
        do {
            $ref = 'LOC-' . random_int(1000, 9999);
            $stmt->execute(['r' => $ref]);
        } while ((int) $stmt->fetchColumn() > 0);
        return $ref;
    }

    // =====================================
    // PAGES
    // =====================================

    // index.php?page=location
    public function indexAction(): void
    {
        if (isset($_GET['type']) && in_array($_GET['type'], self::FILTRES, true)) {
            $_SESSION['mobilite_filtre'] = $_GET['type'];
        }
        $filtre = $_SESSION['mobilite_filtre'] ?? 'moto';
        $userId = AuthController::isAuthenticated() ? (int) $_SESSION['user_id'] : null;

        $this->render('location', [
            'filtreActuel'     => $filtre,
            'vehiculesFiltres' => $this->getVehicules($filtre),
            'locations'        => $userId !== null ? $this->getLocations($userId) : [],
            'flash'            => $this->popFrontFlash(),
            'extraCss'         => ['assets/css/mobilite.css'],
        ]);
    }

    public function reserverAction(): void  { $this->action('reserver'); }
    public function demarrerAction(): void  { $this->action('demarrer'); }
    public function prolongerAction(): void { $this->action('prolonger'); }
    public function incidentAction(): void  { $this->action('incident'); }
    public function terminerAction(): void  { $this->action('terminer'); }
    public function annulerAction(): void   { $this->action('annuler'); }

    /** Formulaire d'une action ; en POST, exécute l'action puis revient à la page Location. */
    private function action(string $action): void
    {
        $userId = $this->requireUserId();
        $messageErreur = '';

        if ($this->isPost()) {
            if (!$this->frontCsrfOk()) {
                $messageErreur = 'Session expirée, veuillez réessayer.';
            } else {
                $messageErreur = match ($action) {
                    'reserver'  => $this->traiterReservation($userId),
                    'demarrer'  => $this->traiterDemarrage($userId),
                    'prolonger' => $this->traiterProlongation($userId),
                    'incident'  => $this->traiterIncident($userId),
                    'terminer'  => $this->traiterFin($userId),
                    'annuler'   => $this->traiterAnnulation($userId),
                };
            }
            if ($messageErreur === '') {
                self::redirect('location');
            }
        }

        $titrePage = self::TITRES_ACTION[$action];
        $this->page  = 'location-' . $action;
        $this->title = $titrePage . ' | Smart City';

        $this->render('location_action', [
            'action'             => $action,
            'titrePage'          => $titrePage,
            'messageErreur'      => $messageErreur,
            'vehiculesDispo'     => $this->getVehicules('tous', true),
            'locations'          => $this->getLocations($userId),
            'stations'           => self::STATIONS,
            'selectedVehiculeId' => (int) ($_GET['vehicule'] ?? 0),
            'selectedLocationId' => (int) ($_GET['loc'] ?? ($_SESSION['mobilite_selected_loc'] ?? 0)),
            'extraCss'           => ['assets/css/mobilite.css'],
        ]);
    }

    // =====================================
    // TRAITEMENTS ('' si OK, sinon le message d'erreur)
    // =====================================

    private function traiterReservation(int $userId): string
    {
        $vehicule = $this->findVehicule((int) ($_POST['vehicule_id'] ?? 0));
        $stationRetour = trim((string) ($_POST['station_retour'] ?? 'Station Centre Ville'));
        $duree = max(1, (int) ($_POST['duree_heures'] ?? 1));

        if (!in_array($stationRetour, self::STATIONS, true)) {
            $stationRetour = 'Station Centre Ville';
        }
        if ($vehicule === null || $vehicule->getStatut() !== 'disponible') {
            return '';
        }

        $location = new MobiliteLocationModel();
        $location->setReference($this->nouvelleReference());
        $location->setUserId($userId);
        $location->setVehiculeId($vehicule->getId());
        $location->setVehiculeNom($vehicule->getNom());
        $location->setVehiculeType($vehicule->getType());
        $location->setStationDepart($vehicule->getStation());
        $location->setStationRetour($stationRetour);
        $location->setDureeHeures($duree);
        $location->setMontant($duree * $vehicule->getTarifHeure());
        $location->setCodePin((string) random_int(1000, 9999));

        $this->db->prepare(
            'INSERT INTO location_locations
                (reference, user_id, vehicule_id, vehicule_nom, vehicule_type, station_depart, station_retour, duree_heures, montant, code_pin, statut)
             VALUES (:ref, :u, :vid, :vnom, :vtype, :dep, :ret, :duree, :montant, :pin, \'reserve\')'
        )->execute([
            'ref'   => $location->getReference(), 'u' => $location->getUserId(), 'vid' => $location->getVehiculeId(),
            'vnom'  => $location->getVehiculeNom(), 'vtype' => $location->getVehiculeType(),
            'dep'   => $location->getStationDepart(), 'ret' => $location->getStationRetour(),
            'duree' => $location->getDureeHeures(), 'montant' => $location->getMontant(), 'pin' => $location->getCodePin(),
        ]);
        $this->setStatutVehicule($vehicule->getId(), 'reserve');
        $_SESSION['mobilite_selected_loc'] = (int) $this->db->lastInsertId();
        return '';
    }

    private function traiterDemarrage(int $userId): string
    {
        $location = $this->findLocation((int) ($_POST['location_id'] ?? 0), $userId);
        $pinSaisi = trim((string) ($_POST['code_pin'] ?? ''));

        if ($location === null || $location->getStatut() !== 'reserve') {
            return '';
        }
        if ($location->getCodePin() !== $pinSaisi) {
            return 'Code PIN incorrect.';
        }
        $this->db->prepare("UPDATE location_locations SET statut = 'encours' WHERE id = :id")->execute(['id' => $location->getId()]);
        $this->setStatutVehicule($location->getVehiculeId(), 'encours');
        return '';
    }

    private function traiterProlongation(int $userId): string
    {
        $location = $this->findLocation((int) ($_POST['location_id'] ?? 0), $userId);
        $heuresAjout = max(1, (int) ($_POST['heures_ajout'] ?? 1));

        if ($location === null || !in_array($location->getStatut(), ['reserve', 'encours'], true)) {
            return '';
        }
        // Tarif du véhicule ; s'il n'existe plus, tarif déduit de l'ancien montant
        $vehicule = $location->getVehiculeId() !== null ? $this->findVehicule($location->getVehiculeId()) : null;
        $tarif = $vehicule ? $vehicule->getTarifHeure() : $location->getMontant() / max(1, $location->getDureeHeures());

        $nouvelleDuree = $location->getDureeHeures() + $heuresAjout;
        $this->db->prepare('UPDATE location_locations SET duree_heures = :d, montant = :m WHERE id = :id')
            ->execute(['d' => $nouvelleDuree, 'm' => $nouvelleDuree * $tarif, 'id' => $location->getId()]);
        return '';
    }

    private function traiterIncident(int $userId): string
    {
        $location = $this->findLocation((int) ($_POST['location_id'] ?? 0), $userId);
        $description = trim((string) ($_POST['description'] ?? ''));
        if ($description === '') {
            $description = 'Incident signalé';
        }
        if ($location === null) {
            return '';
        }
        $this->db->prepare("UPDATE location_locations SET incident = :i, statut = 'termine' WHERE id = :id")
            ->execute(['i' => mb_substr($description, 0, 255), 'id' => $location->getId()]);
        $this->setStatutVehicule($location->getVehiculeId(), 'maintenance');
        return '';
    }

    private function traiterFin(int $userId): string
    {
        $location = $this->findLocation((int) ($_POST['location_id'] ?? 0), $userId);
        $note = max(1, min(5, (int) ($_POST['note'] ?? 5)));
        if ($location === null) {
            return '';
        }
        $this->db->prepare("UPDATE location_locations SET statut = 'termine', note_donnee = :n WHERE id = :id")
            ->execute(['n' => $note, 'id' => $location->getId()]);
        if ($location->getVehiculeId() !== null) {
            $this->db->prepare(
                "UPDATE location_vehicules SET statut = 'disponible', station = :st, avis_count = avis_count + 1 WHERE id = :id"
            )->execute(['st' => $location->getStationRetour(), 'id' => $location->getVehiculeId()]);
        }
        return '';
    }

    private function traiterAnnulation(int $userId): string
    {
        $location = $this->findLocation((int) ($_POST['location_id'] ?? 0), $userId);
        if ($location === null) {
            return '';
        }
        $this->db->prepare("UPDATE location_locations SET statut = 'termine', annulee = 1 WHERE id = :id")
            ->execute(['id' => $location->getId()]);
        if ($location->getVehiculeId() !== null) {
            $this->db->prepare("UPDATE location_vehicules SET statut = 'disponible' WHERE id = :id AND statut IN ('reserve', 'encours')")
                ->execute(['id' => $location->getVehiculeId()]);
        }
        return '';
    }
}
