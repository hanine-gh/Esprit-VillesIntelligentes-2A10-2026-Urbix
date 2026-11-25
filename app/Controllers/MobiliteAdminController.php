<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\MobiliteLocationModel;
use App\Models\MobiliteVehiculeModel;
use PDO;

/**
 * MobiliteAdminController : backoffice du module Location (admin uniquement).
 * Indicateurs, ajout de véhicule, tarif, maintenance, suppression,
 * supervision des locations (clôturer, supprimer, purger).
 */
class MobiliteAdminController extends Controller
{
    use UserFormTrait;  // verifyCsrfToken()
    use AdminTrait;     // requireAdmin(), flash(), popFlash(), intFromPost(), csrfOk()

    public const IMAGES = ['urbix-moto.png', 'voltrider.png', 'scootcity.png', 'glidemax.png', 'velo-vassist.png', 'ecoride.png'];
    public const FILTRES_LOCATION = ['tous' => 'Toutes les locations', 'reserve' => 'Réservées', 'encours' => 'En cours', 'termine' => 'Terminées / Annulées'];

    protected string $page  = 'admin-location';
    protected string $title = 'Location | Backoffice Smart City';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function findVehicule(int $id): ?MobiliteVehiculeModel
    {
        $stmt = $this->db->prepare('SELECT * FROM location_vehicules WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? MobiliteVehiculeModel::fromArray($row) : null;
    }

    private function findLocation(int $id): ?MobiliteLocationModel
    {
        $stmt = $this->db->prepare('SELECT * FROM location_locations WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? MobiliteLocationModel::fromArray($row) : null;
    }

    /** Toutes les actions : POST + admin + CSRF, puis retour à la page (en gardant le filtre). */
    private function debutAction(): void
    {
        $this->requireSuperAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            self::redirect('admin-location');
        }
        if (!$this->csrfOk()) {
            $this->flash('danger', 'Session expirée, veuillez réessayer.');
            $this->retour();
        }
    }

    private function retour(): void
    {
        $filtre = $_POST['filtre'] ?? 'tous';
        self::redirect('admin-location', isset(self::FILTRES_LOCATION[$filtre]) && $filtre !== 'tous' ? ['statut' => $filtre] : []);
    }

    // index.php?page=admin-location
    public function indexAction(): void
    {
        $this->requireSuperAdmin();
        $filtre = $_GET['statut'] ?? 'tous';
        if (!is_string($filtre) || !isset(self::FILTRES_LOCATION[$filtre])) {
            $filtre = 'tous';
        }

        $vehicules = array_map([MobiliteVehiculeModel::class, 'fromArray'],
            $this->db->query('SELECT * FROM location_vehicules ORDER BY id')->fetchAll());

        $sql = "SELECT l.*, CONCAT(u.prenom, ' ', u.nom) AS client_nom
                FROM location_locations l LEFT JOIN users u ON u.id = l.user_id";
        $params = [];
        if ($filtre !== 'tous') {
            $sql .= ' WHERE l.statut = :s';
            $params['s'] = $filtre;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY l.id');
        $stmt->execute($params);
        $locations = array_map([MobiliteLocationModel::class, 'fromArray'], $stmt->fetchAll());

        $kpis = [
            'total' => count($vehicules), 'disponibles' => 0, 'actives' => 0, 'maintenance' => 0,
            'revenus' => (float) $this->db->query('SELECT COALESCE(SUM(montant), 0) FROM location_locations WHERE annulee = 0')->fetchColumn(),
        ];
        foreach ($vehicules as $v) {
            match ($v->getStatut()) {
                'disponible'         => $kpis['disponibles']++,
                'reserve', 'encours' => $kpis['actives']++,
                'maintenance'        => $kpis['maintenance']++,
                default              => null,
            };
        }

        $this->render('admin_location', [
            'vehicules' => $vehicules,
            'locations' => $locations,
            'kpis'      => $kpis,
            'filtre'    => $filtre,
            'filtres'   => self::FILTRES_LOCATION,
            'stations'  => MobiliteController::STATIONS,
            'types'     => ['moto' => 'Moto', 'trottinette' => 'Trottinette', 'velo' => 'Vélo'],
            'images'    => self::IMAGES,
            'flash'     => $this->popFlash(),
        ], 'layout_admin');
    }

    public function ajouterVehiculeAction(): void
    {
        $this->debutAction();
        $nom = trim((string) ($_POST['nom'] ?? ''));
        $type = (string) ($_POST['type'] ?? '');
        $tarif = (float) str_replace(',', '.', (string) ($_POST['tarif'] ?? '0'));
        $autonomie = trim((string) ($_POST['autonomie'] ?? ''));
        $station = (string) ($_POST['station'] ?? '');
        $image = (string) ($_POST['image'] ?? '');

        $erreurs = [];
        if ($nom === '' || mb_strlen($nom) > 100) {
            $erreurs[] = 'Nom du véhicule obligatoire (100 caractères max).';
        }
        if (!in_array($type, MobiliteController::TYPES, true)) {
            $erreurs[] = 'Catégorie invalide.';
        }
        if ($tarif <= 0 || $tarif > 1000) {
            $erreurs[] = 'Le tarif doit être compris entre 0,5 et 1000 DT/h.';
        }
        if (!preg_match('/^(100|[1-9]?[0-9])%$/', $autonomie)) {
            $erreurs[] = "L'autonomie doit être un pourcentage (ex : 85%).";
        }
        if (!in_array($station, MobiliteController::STATIONS, true) || !in_array($image, self::IMAGES, true)) {
            $erreurs[] = 'Station ou image invalide.';
        }
        if ($erreurs) {
            $this->flash('danger', implode(' ', $erreurs));
            $this->retour();
        }

        $v = new MobiliteVehiculeModel();
        $v->setNom($nom);
        $v->setType($type);
        $v->setTarifHeure($tarif);
        $v->setAutonomie($autonomie);
        $v->setStation($station);
        $v->setStatut('disponible');
        $v->setNoteMoyenne(5.0);
        $v->setAvisCount(1);
        $v->setImage($image);

        $this->db->prepare(
            'INSERT INTO location_vehicules (nom, type, tarif_heure, autonomie, station, statut, note_moyenne, avis_count, image)
             VALUES (:nom, :type, :tarif, :auto, :station, :statut, :note, :avis, :image)'
        )->execute([
            'nom' => $v->getNom(), 'type' => $v->getType(), 'tarif' => $v->getTarifHeure(), 'auto' => $v->getAutonomie(),
            'station' => $v->getStation(), 'statut' => $v->getStatut(), 'note' => $v->getNoteMoyenne(),
            'avis' => $v->getAvisCount(), 'image' => $v->getImage(),
        ]);
        $this->flash('success', 'Véhicule "' . $v->getNom() . '" ajouté.');
        $this->retour();
    }

    public function tarifAction(): void
    {
        $this->debutAction();
        $v = $this->findVehicule($this->intFromPost());
        $tarif = (float) str_replace(',', '.', (string) ($_POST['tarif'] ?? '0'));

        if ($v === null || $tarif <= 0 || $tarif > 1000) {
            $this->flash('danger', 'Tarif invalide : il doit être compris entre 0,5 et 1000 DT/h.');
        } else {
            $this->db->prepare('UPDATE location_vehicules SET tarif_heure = :t WHERE id = :id')->execute(['t' => $tarif, 'id' => $v->getId()]);
            $this->flash('success', 'Tarif de ' . $v->getNom() . ' mis à jour (' . $tarif . ' DT/h).');
        }
        $this->retour();
    }

    /** Maintenance <-> disponible */
    public function statutAction(): void
    {
        $this->debutAction();
        $v = $this->findVehicule($this->intFromPost());
        $statut = (string) ($_POST['statut'] ?? '');
        $permis = ['maintenance' => 'disponible', 'disponible' => 'maintenance']; // nouveau => statut actuel requis

        if ($v !== null && isset($permis[$statut]) && $v->getStatut() === $permis[$statut]) {
            $this->db->prepare('UPDATE location_vehicules SET statut = :s WHERE id = :id')->execute(['s' => $statut, 'id' => $v->getId()]);
            $this->flash('success', $v->getNom() . ' : ' . $statut . '.');
        } else {
            $this->flash('danger', 'Changement de statut impossible.');
        }
        $this->retour();
    }

    public function supprimerVehiculeAction(): void
    {
        $this->debutAction();
        $v = $this->findVehicule($this->intFromPost());
        if ($v === null) {
            $this->flash('danger', 'Véhicule introuvable.');
        } elseif (in_array($v->getStatut(), ['reserve', 'encours'], true)) {
            $this->flash('danger', 'Impossible de supprimer un véhicule réservé ou en cours de location.');
        } else {
            $this->db->prepare('DELETE FROM location_vehicules WHERE id = :id')->execute(['id' => $v->getId()]);
            $this->flash('success', $v->getNom() . ' supprimé.');
        }
        $this->retour();
    }

    public function cloturerAction(): void
    {
        $this->debutAction();
        $l = $this->findLocation($this->intFromPost());
        if ($l !== null) {
            $this->db->prepare("UPDATE location_locations SET statut = 'termine' WHERE id = :id")->execute(['id' => $l->getId()]);
            if ($l->getVehiculeId() !== null) {
                $this->db->prepare("UPDATE location_vehicules SET statut = 'disponible', station = :st WHERE id = :id")
                    ->execute(['st' => $l->getStationRetour(), 'id' => $l->getVehiculeId()]);
            }
            $this->flash('success', 'Location ' . $l->getReference() . ' clôturée.');
        }
        $this->retour();
    }

    public function supprimerLocationAction(): void
    {
        $this->debutAction();
        $l = $this->findLocation($this->intFromPost());
        if ($l !== null) {
            if (in_array($l->getStatut(), ['reserve', 'encours'], true) && $l->getVehiculeId() !== null) {
                $this->db->prepare("UPDATE location_vehicules SET statut = 'disponible' WHERE id = :id")->execute(['id' => $l->getVehiculeId()]);
            }
            $this->db->prepare('DELETE FROM location_locations WHERE id = :id')->execute(['id' => $l->getId()]);
            $this->flash('success', 'Location ' . $l->getReference() . ' supprimée.');
        }
        $this->retour();
    }

    public function purgerAction(): void
    {
        $this->debutAction();
        $this->db->exec("DELETE FROM location_locations WHERE statut = 'termine'");
        $this->flash('success', 'Locations terminées purgées.');
        $this->retour();
    }
}
