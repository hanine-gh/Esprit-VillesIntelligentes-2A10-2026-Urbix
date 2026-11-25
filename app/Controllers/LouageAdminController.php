<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\LouageChauffeurModel;
use App\Models\LouageHoraireModel;
use App\Models\LouageReclamationModel;
use App\Models\LouageReservationModel;
use App\Models\LouageRouteModel;
use App\Models\LouageStationModel;
use App\Models\LouageVehiculeModel;
use PDO;
use PDOException;

/**
 * LouageAdminController : backoffice du module Louage (admin uniquement).
 * Tableau de bord, louages (véhicules), chauffeurs, stations, trajets, horaires,
 * réservations, prix, réclamations et notifications.
 */
class LouageAdminController extends Controller
{
    use UserFormTrait;
    use AdminTrait;
    use LouageTrait;

    public const STATUTS_VEHICULE  = ['Available', 'In service', 'Unavailable'];
    public const STATUTS_CHAUFFEUR = ['Active', 'Inactive'];

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // OUTILS
    // =====================================

    private function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /** Affiche une page du backoffice louage */
    private function afficher(string $page, string $titre, string $vue, array $data): void
    {
        $this->page  = $page;
        $this->title = $titre . ' | Backoffice Smart City';
        $this->render($vue, $data + ['flash' => $this->popFlash()], 'layout_admin');
    }

    /** Suppression commune (POST + CSRF) avec message si une clé étrangère bloque */
    private function supprimer(string $table, string $retour, string $ok, string $bloque): void
    {
        $this->requireSuperAdmin();
        if ($this->isPost() && $this->csrfOk()) {
            try {
                $stmt = $this->db->prepare("DELETE FROM $table WHERE id = :id");
                $stmt->execute(['id' => $this->intFromPost()]);
                $stmt->rowCount() ? $this->flash('success', $ok) : $this->flash('danger', 'Item not found.');
            } catch (PDOException $e) {
                $this->flash('danger', $bloque);
            }
        }
        self::redirect($retour);
    }

    private static function dateTimeValide(string $v): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d\TH:i', $v);
        return $d !== false && $d->format('Y-m-d\TH:i') === $v;
    }

    // =====================================
    // TABLEAU DE BORD
    // =====================================

    // index.php?page=admin-louage
    public function dashboardAction(): void
    {
        $this->requireSuperAdmin();
        $compter = fn(string $sql): int => (int) $this->db->query($sql)->fetchColumn();
        $this->afficher('admin-louage', 'Louage', 'admin_louage', [
            'nbLouages'      => $compter('SELECT COUNT(*) FROM louage_vehicules'),
            'nbChauffeurs'   => $compter('SELECT COUNT(*) FROM louage_chauffeurs'),
            'nbReservations' => $compter('SELECT COUNT(*) FROM louage_reservations'),
            'nbStations'     => $compter('SELECT COUNT(*) FROM louage_stations'),
        ]);
    }

    // =====================================
    // LOUAGES (véhicules)
    // =====================================

    public function vehiculesAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'registration' => '', 'model' => '', 'capacity' => '', 'driver' => '', 'status' => 'Available'];

        if ($this->isPost()) {
            $form = [
                'id'           => $this->intFromPost(),
                'registration' => strtoupper(trim((string) ($_POST['registration'] ?? ''))),
                'model'        => trim((string) ($_POST['model'] ?? '')),
                'capacity'     => trim((string) ($_POST['capacity'] ?? '')),
                'driver'       => (string) ($_POST['driver'] ?? ''),
                'status'       => (string) ($_POST['status'] ?? ''),
            ];
            if (!$this->csrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if (!preg_match('/^[0-9A-Z ]{4,20}$/', $form['registration'])) {
                $erreurs[] = 'Registration number: 4 to 20 letters, digits or spaces (e.g. 123 TU 4567).';
            } else {
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM louage_vehicules WHERE immatriculation = :i AND id <> :id');
                $stmt->execute(['i' => $form['registration'], 'id' => $form['id']]);
                if ((int) $stmt->fetchColumn() > 0) {
                    $erreurs[] = 'This registration number already exists.';
                }
            }
            if ($form['model'] === '' || mb_strlen($form['model']) > 60) {
                $erreurs[] = 'Vehicle model is required (60 characters max).';
            }
            if (!ctype_digit($form['capacity']) || (int) $form['capacity'] < 1 || (int) $form['capacity'] > 20) {
                $erreurs[] = 'Capacity: 1 to 20 seats.';
            }
            if ($form['driver'] !== '') {
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM louage_chauffeurs WHERE id = :id');
                $stmt->execute(['id' => (int) $form['driver']]);
                if ((int) $stmt->fetchColumn() === 0) {
                    $erreurs[] = 'Unknown driver.';
                }
            }
            if (!in_array($form['status'], self::STATUTS_VEHICULE, true)) {
                $erreurs[] = 'Please choose a status.';
            }

            if (!$erreurs) {
                $v = new LouageVehiculeModel();
                $v->setId($form['id'] ?: null);
                $v->setImmatriculation($form['registration']);
                $v->setModele($form['model']);
                $v->setCapacite((int) $form['capacity']);
                $v->setChauffeurId($form['driver'] !== '' ? (int) $form['driver'] : null);
                $v->setStatut($form['status']);

                $params = ['i' => $v->getImmatriculation(), 'm' => $v->getModele(), 'c' => $v->getCapacite(),
                           'ch' => $v->getChauffeurId(), 's' => $v->getStatut()];
                if ($v->getId()) {
                    $this->db->prepare('UPDATE louage_vehicules SET immatriculation = :i, modele = :m, capacite = :c, chauffeur_id = :ch, statut = :s WHERE id = :id')
                        ->execute($params + ['id' => $v->getId()]);
                    $this->flash('success', 'Louage ' . $v->getImmatriculation() . ' updated.');
                } else {
                    $this->db->prepare('INSERT INTO louage_vehicules (immatriculation, modele, capacite, chauffeur_id, statut) VALUES (:i, :m, :c, :ch, :s)')
                        ->execute($params);
                    $this->flash('success', 'Louage ' . $v->getImmatriculation() . ' added.');
                }
                self::redirect('admin-louage-vehicules');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare('SELECT * FROM louage_vehicules WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $v = LouageVehiculeModel::fromArray($row);
                $form = ['id' => $v->getId(), 'registration' => $v->getImmatriculation(), 'model' => $v->getModele(),
                         'capacity' => (string) $v->getCapacite(), 'driver' => (string) $v->getChauffeurId(), 'status' => $v->getStatut()];
            }
        }

        $this->afficher('admin-louage-vehicules', 'Manage Louages', 'admin_louage_vehicules', [
            'form' => $form, 'erreurs' => $erreurs, 'vehicules' => $this->getLouageVehicules(),
            'chauffeurs' => $this->db->query('SELECT id, nom FROM louage_chauffeurs ORDER BY nom')->fetchAll(),
            'statuts' => self::STATUTS_VEHICULE,
        ]);
    }

    public function vehiculeDeleteAction(): void
    {
        $this->supprimer('louage_vehicules', 'admin-louage-vehicules', 'Louage deleted.', 'This louage cannot be deleted.');
    }

    // =====================================
    // CHAUFFEURS
    // =====================================

    public function chauffeursAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'name' => '', 'phone' => '', 'license' => '', 'status' => 'Active'];

        if ($this->isPost()) {
            $form = [
                'id'      => $this->intFromPost(),
                'name'    => trim((string) ($_POST['name'] ?? '')),
                'phone'   => trim((string) ($_POST['phone'] ?? '')),
                'license' => strtoupper(trim((string) ($_POST['license'] ?? ''))),
                'status'  => (string) ($_POST['status'] ?? ''),
            ];
            if (!$this->csrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if (!self::louageNomValide($form['name'])) {
                $erreurs[] = 'Full name: 2 to 80 letters.';
            }
            if ($form['phone'] !== '' && !self::louageTelephoneValide($form['phone'])) {
                $erreurs[] = 'Phone: 8 to 15 digits.';
            }
            if ($form['license'] !== '') {
                if (!preg_match('/^[0-9A-Z-]{4,30}$/', $form['license'])) {
                    $erreurs[] = 'Driving license number: 4 to 30 letters, digits or dashes.';
                } else {
                    $stmt = $this->db->prepare('SELECT COUNT(*) FROM louage_chauffeurs WHERE permis = :p AND id <> :id');
                    $stmt->execute(['p' => $form['license'], 'id' => $form['id']]);
                    if ((int) $stmt->fetchColumn() > 0) {
                        $erreurs[] = 'This driving license number already exists.';
                    }
                }
            }
            if (!in_array($form['status'], self::STATUTS_CHAUFFEUR, true)) {
                $erreurs[] = 'Please choose a status.';
            }

            if (!$erreurs) {
                $c = new LouageChauffeurModel();
                $c->setId($form['id'] ?: null);
                $c->setNom($form['name']);
                $c->setTelephone($form['phone'] !== '' ? $form['phone'] : null);
                $c->setPermis($form['license'] !== '' ? $form['license'] : null);
                $c->setStatut($form['status']);

                $params = ['n' => $c->getNom(), 't' => $c->getTelephone(), 'p' => $c->getPermis(), 's' => $c->getStatut()];
                if ($c->getId()) {
                    $this->db->prepare('UPDATE louage_chauffeurs SET nom = :n, telephone = :t, permis = :p, statut = :s WHERE id = :id')
                        ->execute($params + ['id' => $c->getId()]);
                    $this->flash('success', 'Driver ' . $c->getNom() . ' updated.');
                } else {
                    $this->db->prepare('INSERT INTO louage_chauffeurs (nom, telephone, permis, statut) VALUES (:n, :t, :p, :s)')->execute($params);
                    $this->flash('success', 'Driver ' . $c->getNom() . ' added.');
                }
                self::redirect('admin-louage-chauffeurs');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare('SELECT * FROM louage_chauffeurs WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $c = LouageChauffeurModel::fromArray($row);
                $form = ['id' => $c->getId(), 'name' => $c->getNom(), 'phone' => (string) $c->getTelephone(),
                         'license' => (string) $c->getPermis(), 'status' => $c->getStatut()];
            }
        }

        $this->afficher('admin-louage-chauffeurs', 'Manage Drivers', 'admin_louage_chauffeurs', [
            'form' => $form, 'erreurs' => $erreurs, 'statuts' => self::STATUTS_CHAUFFEUR,
            'chauffeurs' => array_map([LouageChauffeurModel::class, 'fromArray'],
                $this->db->query('SELECT * FROM louage_chauffeurs ORDER BY nom')->fetchAll()),
        ]);
    }

    /** Ses louages restent, sans chauffeur (ON DELETE SET NULL) */
    public function chauffeurDeleteAction(): void
    {
        $this->supprimer('louage_chauffeurs', 'admin-louage-chauffeurs', 'Driver deleted.', 'This driver cannot be deleted.');
    }

    // =====================================
    // STATIONS
    // =====================================

    public function stationsAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'name' => '', 'city' => '', 'address' => '', 'latitude' => '', 'longitude' => ''];

        if ($this->isPost()) {
            $form = [
                'id'        => $this->intFromPost(),
                'name'      => trim((string) ($_POST['name'] ?? '')),
                'city'      => trim((string) ($_POST['city'] ?? '')),
                'address'   => trim((string) ($_POST['address'] ?? '')),
                'latitude'  => trim((string) ($_POST['latitude'] ?? '')),
                'longitude' => trim((string) ($_POST['longitude'] ?? '')),
            ];
            if (!$this->csrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if ($form['name'] === '' || mb_strlen($form['name']) > 80) {
                $erreurs[] = 'Station name is required (80 characters max).';
            }
            if (!self::louageNomValide($form['city']) || mb_strlen($form['city']) > 60) {
                $erreurs[] = 'City: letters only (60 characters max).';
            }
            if (mb_strlen($form['address']) > 150) {
                $erreurs[] = 'Address: 150 characters max.';
            }
            if ($form['latitude'] !== '' && (!is_numeric($form['latitude']) || abs((float) $form['latitude']) > 90)) {
                $erreurs[] = 'Latitude must be a number between -90 and 90.';
            }
            if ($form['longitude'] !== '' && (!is_numeric($form['longitude']) || abs((float) $form['longitude']) > 180)) {
                $erreurs[] = 'Longitude must be a number between -180 and 180.';
            }
            if (!$erreurs) {
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM louage_stations WHERE nom = :n AND ville = :v AND id <> :id');
                $stmt->execute(['n' => $form['name'], 'v' => $form['city'], 'id' => $form['id']]);
                if ((int) $stmt->fetchColumn() > 0) {
                    $erreurs[] = 'This station already exists in this city.';
                }
            }

            if (!$erreurs) {
                $s = new LouageStationModel();
                $s->setId($form['id'] ?: null);
                $s->setNom($form['name']);
                $s->setVille($form['city']);
                $s->setAdresse($form['address'] !== '' ? $form['address'] : null);
                $s->setLatitude($form['latitude'] !== '' ? (float) $form['latitude'] : null);
                $s->setLongitude($form['longitude'] !== '' ? (float) $form['longitude'] : null);

                $params = ['n' => $s->getNom(), 'v' => $s->getVille(), 'a' => $s->getAdresse(), 'la' => $s->getLatitude(), 'lo' => $s->getLongitude()];
                if ($s->getId()) {
                    $this->db->prepare('UPDATE louage_stations SET nom = :n, ville = :v, adresse = :a, latitude = :la, longitude = :lo WHERE id = :id')
                        ->execute($params + ['id' => $s->getId()]);
                    $this->flash('success', 'Station updated.');
                } else {
                    $this->db->prepare('INSERT INTO louage_stations (nom, ville, adresse, latitude, longitude) VALUES (:n, :v, :a, :la, :lo)')->execute($params);
                    $this->flash('success', 'Station added.');
                }
                self::redirect('admin-louage-stations');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare('SELECT * FROM louage_stations WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $s = LouageStationModel::fromArray($row);
                $form = ['id' => $s->getId(), 'name' => $s->getNom(), 'city' => $s->getVille(), 'address' => (string) $s->getAdresse(),
                         'latitude' => (string) $s->getLatitude(), 'longitude' => (string) $s->getLongitude()];
            }
        }

        $this->afficher('admin-louage-stations', 'Manage Stations', 'admin_louage_stations', [
            'form' => $form, 'erreurs' => $erreurs, 'stations' => $this->getLouageStations(),
        ]);
    }

    public function stationDeleteAction(): void
    {
        $this->supprimer('louage_stations', 'admin-louage-stations', 'Station deleted.', 'This station is used by a route and cannot be deleted.');
    }

    // =====================================
    // TRAJETS (routes)
    // =====================================

    public function routesAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'departure' => 0, 'arrival' => 0];

        if ($this->isPost()) {
            $form = ['id' => $this->intFromPost(), 'departure' => $this->intFromPost('departure'), 'arrival' => $this->intFromPost('arrival')];
            $ids = array_map(fn($s) => $s->getId(), $this->getLouageStations());
            if (!$this->csrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if (!in_array($form['departure'], $ids, true) || !in_array($form['arrival'], $ids, true)) {
                $erreurs[] = 'Please choose the departure and arrival stations.';
            } elseif ($form['departure'] === $form['arrival']) {
                $erreurs[] = 'The arrival station must be different from the departure station.';
            } else {
                $stmt = $this->db->prepare('SELECT COUNT(*) FROM louage_routes WHERE station_depart_id = :d AND station_arrivee_id = :a AND id <> :id');
                $stmt->execute(['d' => $form['departure'], 'a' => $form['arrival'], 'id' => $form['id']]);
                if ((int) $stmt->fetchColumn() > 0) {
                    $erreurs[] = 'This route already exists.';
                }
            }

            if (!$erreurs) {
                $r = new LouageRouteModel();
                $r->setId($form['id'] ?: null);
                $r->setStationDepartId($form['departure']);
                $r->setStationArriveeId($form['arrival']);
                $params = ['d' => $r->getStationDepartId(), 'a' => $r->getStationArriveeId()];
                if ($r->getId()) {
                    $this->db->prepare('UPDATE louage_routes SET station_depart_id = :d, station_arrivee_id = :a WHERE id = :id')
                        ->execute($params + ['id' => $r->getId()]);
                    $this->flash('success', 'Route updated.');
                } else {
                    $this->db->prepare('INSERT INTO louage_routes (station_depart_id, station_arrivee_id) VALUES (:d, :a)')->execute($params);
                    $this->flash('success', 'Route added. You can now set its price.');
                }
                self::redirect('admin-louage-trajets');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare('SELECT * FROM louage_routes WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $r = LouageRouteModel::fromArray($row);
                $form = ['id' => $r->getId(), 'departure' => $r->getStationDepartId(), 'arrival' => $r->getStationArriveeId()];
            }
        }

        $this->afficher('admin-louage-trajets', 'Manage Routes', 'admin_louage_trajets', [
            'form' => $form, 'erreurs' => $erreurs, 'routes' => $this->getLouageRoutes(), 'stations' => $this->getLouageStations(),
        ]);
    }

    public function routeDeleteAction(): void
    {
        $this->supprimer('louage_routes', 'admin-louage-trajets', 'Route deleted.', 'This route has schedules and cannot be deleted.');
    }

    // =====================================
    // HORAIRES (schedules)
    // =====================================

    public function horairesAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'route' => 0, 'louage' => '', 'departure_time' => '', 'arrival_time' => ''];

        if ($this->isPost()) {
            $form = [
                'id'             => $this->intFromPost(),
                'route'          => $this->intFromPost('route'),
                'louage'         => (string) ($_POST['louage'] ?? ''),
                'departure_time' => (string) ($_POST['departure_time'] ?? ''),
                'arrival_time'   => (string) ($_POST['arrival_time'] ?? ''),
            ];
            if (!$this->csrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if (!in_array($form['route'], array_map(fn($r) => $r->getId(), $this->getLouageRoutes()), true)) {
                $erreurs[] = 'Please choose a route.';
            }
            if ($form['louage'] !== '' && !in_array((int) $form['louage'], array_map(fn($v) => $v->getId(), $this->getLouageVehicules()), true)) {
                $erreurs[] = 'Unknown louage.';
            }
            if (!self::dateTimeValide($form['departure_time'])) {
                $erreurs[] = 'Please enter the departure time.';
            } elseif ($form['id'] === 0 && strtotime($form['departure_time']) < time()) {
                $erreurs[] = 'The departure time must be in the future.';
            }
            if ($form['arrival_time'] !== '') {
                if (!self::dateTimeValide($form['arrival_time'])) {
                    $erreurs[] = 'Estimated arrival is not a valid date.';
                } elseif (self::dateTimeValide($form['departure_time']) && strtotime($form['arrival_time']) <= strtotime($form['departure_time'])) {
                    $erreurs[] = 'Estimated arrival must be after the departure time.';
                }
            }

            if (!$erreurs) {
                $h = new LouageHoraireModel();
                $h->setId($form['id'] ?: null);
                $h->setRouteId($form['route']);
                $h->setVehiculeId($form['louage'] !== '' ? (int) $form['louage'] : null);
                $h->setDepartAt(date('Y-m-d H:i:s', strtotime($form['departure_time'])));
                $h->setArriveeAt($form['arrival_time'] !== '' ? date('Y-m-d H:i:s', strtotime($form['arrival_time'])) : null);

                $params = ['r' => $h->getRouteId(), 'v' => $h->getVehiculeId(), 'd' => $h->getDepartAt(), 'a' => $h->getArriveeAt()];
                if ($h->getId()) {
                    $this->db->prepare('UPDATE louage_horaires SET route_id = :r, vehicule_id = :v, depart_at = :d, arrivee_at = :a WHERE id = :id')
                        ->execute($params + ['id' => $h->getId()]);
                    $this->flash('success', 'Schedule updated.');
                } else {
                    $this->db->prepare('INSERT INTO louage_horaires (route_id, vehicule_id, depart_at, arrivee_at) VALUES (:r, :v, :d, :a)')->execute($params);
                    $this->flash('success', 'Schedule saved.');
                }
                self::redirect('admin-louage-horaires');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $h = $this->findLouageHoraire($id);
            if ($h !== null) {
                $form = ['id' => $h->getId(), 'route' => $h->getRouteId(), 'louage' => (string) $h->getVehiculeId(),
                         'departure_time' => dateTimeLocal($h->getDepartAt()), 'arrival_time' => dateTimeLocal($h->getArriveeAt())];
            }
        }

        $this->afficher('admin-louage-horaires', 'Manage Schedules', 'admin_louage_horaires', [
            'form' => $form, 'erreurs' => $erreurs,
            'horaires' => array_map([LouageHoraireModel::class, 'fromArray'],
                $this->db->query(self::sqlLouageHoraires() . ' ORDER BY h.depart_at DESC')->fetchAll()),
            'routes' => $this->getLouageRoutes(), 'vehicules' => $this->getLouageVehicules(),
        ]);
    }

    /** Les réservations de ce départ sont supprimées avec lui (ON DELETE CASCADE) */
    public function horaireDeleteAction(): void
    {
        $this->supprimer('louage_horaires', 'admin-louage-horaires', 'Schedule deleted.', 'This schedule cannot be deleted.');
    }

    // =====================================
    // RÉSERVATIONS
    // =====================================

    public function reservationsAction(): void
    {
        $this->requireSuperAdmin();
        $this->afficher('admin-louage-reservations', 'Manage Reservations', 'admin_louage_reservations', [
            'reservations' => array_map([LouageReservationModel::class, 'fromArray'],
                $this->db->query(self::sqlLouageReservations() . ' ORDER BY h.depart_at DESC, res.id DESC')->fetchAll()),
        ]);
    }

    /** Confirm (demande en attente) ou Cancel (toute réservation non annulée) */
    public function reservationStatutAction(): void
    {
        $this->requireSuperAdmin();
        if ($this->isPost() && $this->csrfOk()) {
            $statut = (string) ($_POST['status'] ?? '');
            $stmt = match ($statut) {
                'Confirmed' => $this->db->prepare("UPDATE louage_reservations SET statut = 'Confirmed' WHERE id = :id AND statut = 'Pending'"),
                'Cancelled' => $this->db->prepare("UPDATE louage_reservations SET statut = 'Cancelled' WHERE id = :id AND statut <> 'Cancelled'"),
                default     => null,
            };
            if ($stmt) {
                $stmt->execute(['id' => $this->intFromPost()]);
            }
            $stmt && $stmt->rowCount()
                ? $this->flash('success', 'Reservation ' . strtolower($statut) . '.')
                : $this->flash('danger', 'Nothing to change.');
        }
        self::redirect('admin-louage-reservations');
    }

    // =====================================
    // PRIX (colonne prix des trajets)
    // =====================================

    public function prixAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $routes = $this->getLouageRoutes();
        $form = ['route' => $this->intFromQuery('edit'), 'price' => ''];
        foreach ($routes as $r) {
            if ($r->getId() === $form['route'] && $r->getPrix() !== null) {
                $form['price'] = (string) $r->getPrix();
            }
        }

        if ($this->isPost()) {
            $form = ['route' => $this->intFromPost('route'), 'price' => trim(str_replace(',', '.', (string) ($_POST['price'] ?? '')))];
            if (!$this->csrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if (!in_array($form['route'], array_map(fn($r) => $r->getId(), $routes), true)) {
                $erreurs[] = 'Please choose a route.';
            }
            if (!is_numeric($form['price']) || (float) $form['price'] < 0 || (float) $form['price'] > 1000) {
                $erreurs[] = 'Price: a number between 0 and 1000 TND.';
            }
            if (!$erreurs) {
                $this->db->prepare('UPDATE louage_routes SET prix = :p WHERE id = :id')
                    ->execute(['p' => round((float) $form['price'], 3), 'id' => $form['route']]);
                $this->flash('success', 'Price saved.');
                self::redirect('admin-louage-prix');
            }
        }

        $this->afficher('admin-louage-prix', 'Manage Prices', 'admin_louage_prix', [
            'form' => $form, 'erreurs' => $erreurs, 'routes' => $routes,
            'avecPrix' => array_filter($routes, fn($r) => $r->getPrix() !== null),
        ]);
    }

    /** Supprimer un prix : le trajet reste, sans prix */
    public function prixDeleteAction(): void
    {
        $this->requireSuperAdmin();
        if ($this->isPost() && $this->csrfOk()) {
            $this->db->prepare('UPDATE louage_routes SET prix = NULL WHERE id = :id')->execute(['id' => $this->intFromPost()]);
            $this->flash('success', 'Price deleted.');
        }
        self::redirect('admin-louage-prix');
    }

    // =====================================
    // RÉCLAMATIONS & NOTIFICATIONS
    // =====================================

    public function reclamationsAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = $_SESSION['louage_notif_erreurs'] ?? [];
        $texte = $_SESSION['louage_notif_texte'] ?? '';
        unset($_SESSION['louage_notif_erreurs'], $_SESSION['louage_notif_texte']);

        $this->afficher('admin-louage-reclamations', 'Manage Complaints', 'admin_louage_reclamations', [
            'erreurs' => $erreurs, 'texte' => $texte,
            'reclamations' => array_map([LouageReclamationModel::class, 'fromArray'],
                $this->db->query("SELECT * FROM louage_reclamations ORDER BY statut = 'Resolved', created_at DESC")->fetchAll()),
        ]);
    }

    public function reclamationResoudreAction(): void
    {
        $this->requireSuperAdmin();
        if ($this->isPost() && $this->csrfOk()) {
            $stmt = $this->db->prepare("UPDATE louage_reclamations SET statut = 'Resolved' WHERE id = :id AND statut = 'Open'");
            $stmt->execute(['id' => $this->intFromPost()]);
            $stmt->rowCount() ? $this->flash('success', 'Complaint marked as resolved.') : $this->flash('danger', 'Nothing to change.');
        }
        self::redirect('admin-louage-reclamations');
    }

    public function notificationAction(): void
    {
        $this->requireSuperAdmin();
        if ($this->isPost() && $this->csrfOk()) {
            $texte = trim((string) ($_POST['message'] ?? ''));
            if (mb_strlen($texte) < 5 || mb_strlen($texte) > 1000) {
                $_SESSION['louage_notif_erreurs'] = ['Notification message: 5 to 1000 characters.'];
                $_SESSION['louage_notif_texte'] = $texte;
            } else {
                $this->db->prepare('INSERT INTO louage_notifications (message) VALUES (:m)')->execute(['m' => $texte]);
                $this->flash('success', 'Notification sent.');
            }
        }
        self::redirect('admin-louage-reclamations');
    }
}
