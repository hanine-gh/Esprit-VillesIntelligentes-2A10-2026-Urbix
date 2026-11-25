<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\StationModel;
use App\Models\TrajetModel;
use PDO;
use PDOException;

/**
 * StationController : stations d'un trajet (backoffice, admin uniquement).
 * Ajouter / modifier / supprimer une station ; la consultation se fait sur la page du trajet.
 *
 * Contrôle de saisie (identique en JS) :
 * - nom : 2 à 100 caractères ; adresse : 5 à 255 caractères
 * - ordre : entier de 1 à 99, unique sur le trajet
 * - heure de passage : HH:MM, comprise entre le départ et l'arrivée du trajet,
 *   et cohérente avec l'ordre (après la station précédente, avant la suivante)
 */
class StationController extends Controller
{
    use UserFormTrait;  // field(), verifyCsrfToken()
    use AdminTrait;     // requireAdmin(), flash(), intFromQuery(), csrfOk(), isValidTime()
    use StationFormTrait; // contrôle de saisie des stations (partagé avec l'espace chauffeur)

    protected string $title = 'Stations | Backoffice';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =====================================
    // ACCÈS AUX DONNÉES
    // =====================================

    private function findTrajet(int $id): ?TrajetModel
    {
        $stmt = $this->db->prepare('SELECT * FROM trajets WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? TrajetModel::fromArray($row) : null;
    }

    private function findStation(int $id): ?StationModel
    {
        $stmt = $this->db->prepare('SELECT * FROM stations WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? StationModel::fromArray($row) : null;
    }

    // =====================================
    // ACTIONS (appelées par le routeur)
    // =====================================

    // index.php?page=admin-station-create&trajet=...
    public function createAction(): void
    {
        $this->requireAdmin();

        $trajet = $this->findTrajet($this->intFromQuery('trajet'));
        if (!$trajet) {
            $this->flash('danger', "Trajet introuvable.");
            self::redirect('admin-trajets');
        }

        $errors = [];
        $old    = ['ordre' => (string) $this->nextOrdre((int) $trajet->getId())];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $this->readStationInput($_POST);

            if (!$this->csrfOk()) {
                $errors = ["Jeton de sécurité invalide. Veuillez réessayer."];
            } else {
                $errors = $this->validateStationInput($old, $trajet);
            }

            if (!$errors) {
                $station = new StationModel();
                $station->setTrajetId((int) $trajet->getId());
                $this->fillStation($station, $old);
                try {
                    $stmt = $this->db->prepare(
                        'INSERT INTO stations (trajet_id, nom, adresse, ordre, heure_passage)
                         VALUES (:trajet_id, :nom, :adresse, :ordre, :heure_passage)'
                    );
                    $stmt->execute([
                        'trajet_id'     => $station->getTrajetId(),
                        'nom'           => $station->getNom(),
                        'adresse'       => $station->getAdresse(),
                        'ordre'         => $station->getOrdre(),
                        'heure_passage' => $station->getHeurePassage(),
                    ]);
                    $this->flash('success', "La station « {$station->getNom()} » a été ajoutée au trajet.");
                    self::redirect('admin-trajet-show', ['id' => $trajet->getId()]);
                } catch (PDOException $e) {
                    error_log('Station create error: ' . $e->getMessage());
                    $errors = ["Une erreur est survenue lors de l'ajout de la station."];
                }
            }
        }

        $this->page = 'admin-trajets';
        $this->render('admin_station_form', [
            'mode'    => 'create',
            'trajet'  => $trajet,
            'station' => null,
            'errors'  => $errors,
            'old'     => $old,
        ], 'layout_admin');
    }

    // index.php?page=admin-station-edit&id=...
    public function editAction(): void
    {
        $this->requireAdmin();

        $station = $this->findStation($this->intFromQuery());
        $trajet  = $station ? $this->findTrajet($station->getTrajetId()) : null;
        if (!$station || !$trajet) {
            $this->flash('danger', "Station introuvable.");
            self::redirect('admin-trajets');
        }

        $errors = [];
        $old    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $this->readStationInput($_POST);

            if (!$this->csrfOk()) {
                $errors = ["Jeton de sécurité invalide. Veuillez réessayer."];
            } else {
                $errors = $this->validateStationInput($old, $trajet, (int) $station->getId());
            }

            if (!$errors) {
                $this->fillStation($station, $old);
                try {
                    $stmt = $this->db->prepare(
                        'UPDATE stations SET nom = :nom, adresse = :adresse, ordre = :ordre, heure_passage = :heure_passage
                         WHERE id = :id'
                    );
                    $stmt->execute([
                        'nom'           => $station->getNom(),
                        'adresse'       => $station->getAdresse(),
                        'ordre'         => $station->getOrdre(),
                        'heure_passage' => $station->getHeurePassage(),
                        'id'            => $station->getId(),
                    ]);
                    $this->flash('success', "La station « {$station->getNom()} » a été mise à jour.");
                    self::redirect('admin-trajet-show', ['id' => $trajet->getId()]);
                } catch (PDOException $e) {
                    error_log('Station update error: ' . $e->getMessage());
                    $errors = ["Une erreur est survenue lors de la modification de la station."];
                }
            }
        }

        $this->page = 'admin-trajets';
        $this->render('admin_station_form', [
            'mode'    => 'edit',
            'trajet'  => $trajet,
            'station' => $station,
            'errors'  => $errors,
            'old'     => $old,
        ], 'layout_admin');
    }

    // index.php?page=admin-station-delete  (POST + CSRF)
    public function deleteAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::redirect('admin-trajets');
        }

        $station = $this->findStation($this->intFromPost('id'));
        if (!$station) {
            $this->flash('danger', "Station introuvable.");
            self::redirect('admin-trajets');
        }
        if (!$this->csrfOk()) {
            $this->flash('danger', "Jeton de sécurité invalide. Veuillez réessayer.");
            self::redirect('admin-trajet-show', ['id' => $station->getTrajetId()]);
        }

        $stmt = $this->db->prepare('DELETE FROM stations WHERE id = :id');
        $stmt->execute(['id' => $station->getId()]);
        $this->flash('success', "La station « {$station->getNom()} » a été supprimée.");
        self::redirect('admin-trajet-show', ['id' => $station->getTrajetId()]);
    }
}
