<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\TrainHoraireModel;
use PDO;

/** TrainHoraireController : gestion des horaires de trains (admin). */
class TrainHoraireController extends Controller
{
    use UserFormTrait;
    use AdminTrait;
    use TrainTrait;

    public const ETATS = ["A l'heure" => "À l'heure", 'En retard' => 'En retard', 'Annulé' => 'Annulé'];

    protected string $page  = 'admin-train-horaires';
    protected string $title = 'Horaires des trains | Backoffice Smart City';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function valider(array $f, ?int &$trainId): array
    {
        $e = [];
        $trainId = $f['numero_train'] !== '' ? $this->trainIdParNumero($f['numero_train']) : null;
        if ($trainId === null) {
            $e['numero_train'] = 'Le numéro du train doit correspondre à un train existant.';
        }
        if (!$this->trainLigneExiste($f['ligne'])) {
            $e['ligne'] = 'Choisissez une ligne.';
        }
        if (!in_array($f['station_depart'], self::trainStations(), true)) {
            $e['station_depart'] = 'Choisissez la station de départ.';
        }
        if (!in_array($f['station_arrivee'], self::trainStations(), true)) {
            $e['station_arrivee'] = "Choisissez la station d'arrivée.";
        } elseif ($f['station_depart'] === $f['station_arrivee']) {
            $e['station_arrivee'] = "La station d'arrivée doit être différente de la station de départ.";
        }
        if (!self::trainIsTime($f['heure_depart'])) {
            $e['heure_depart'] = "Saisissez l'heure de départ.";
        }
        if (!self::trainIsTime($f['heure_arrivee'])) {
            $e['heure_arrivee'] = "Saisissez l'heure d'arrivée.";
        } elseif (self::trainIsTime($f['heure_depart']) && strtotime($f['heure_arrivee']) <= strtotime($f['heure_depart'])) {
            $e['heure_arrivee'] = "L'heure d'arrivée doit être après l'heure de départ.";
        }
        if (!self::trainIsDate($f['date'])) {
            $e['date'] = 'Saisissez une date valide.';
        }
        if (!isset(self::ETATS[$f['etat']])) {
            $e['etat'] = "Choisissez l'état du trajet.";
        }
        if ($f['prix'] !== '' && (!is_numeric($f['prix']) || (float) $f['prix'] < 0 || (float) $f['prix'] > 1000)) {
            $e['prix'] = 'Le prix doit être un nombre entre 0 et 1000 DT.';
        }
        return $e;
    }

    // index.php?page=admin-train-horaires
    public function indexAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'numero_train' => '', 'ligne' => 0, 'station_depart' => '', 'heure_depart' => '',
                 'station_arrivee' => '', 'heure_arrivee' => '', 'date' => '', 'etat' => "A l'heure", 'prix' => ''];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $form = [
                'id'              => $this->intFromPost(),
                'numero_train'    => trim((string) ($_POST['numero_train'] ?? '')),
                'ligne'           => $this->intFromPost('ligne'),
                'station_depart'  => (string) ($_POST['station_depart'] ?? ''),
                'heure_depart'    => (string) ($_POST['heure_depart'] ?? ''),
                'station_arrivee' => (string) ($_POST['station_arrivee'] ?? ''),
                'heure_arrivee'   => (string) ($_POST['heure_arrivee'] ?? ''),
                'date'            => (string) ($_POST['date'] ?? ''),
                'etat'            => (string) ($_POST['etat'] ?? ''),
                'prix'            => trim(str_replace(',', '.', (string) ($_POST['prix'] ?? ''))),
            ];
            $trainId = null;
            $erreurs = $this->csrfOk() ? $this->valider($form, $trainId) : ['csrf' => 'Session expirée, veuillez réessayer.'];

            if (!$erreurs) {
                $h = new TrainHoraireModel();
                $h->setId($form['id'] ?: null);
                $h->setTrainId($trainId);
                $h->setLigneId($form['ligne']);
                $h->setStationDepart($form['station_depart']);
                $h->setHeureDepart($form['heure_depart']);
                $h->setStationArrivee($form['station_arrivee']);
                $h->setHeureArrivee($form['heure_arrivee']);
                $h->setDateTrajet($form['date']);
                $h->setEtat($form['etat']);
                $h->setPrix($form['prix'] === '' ? null : round((float) $form['prix'], 3));

                $params = ['train' => $h->getTrainId(), 'ligne' => $h->getLigneId(), 'sd' => $h->getStationDepart(),
                           'hd' => $h->getHeureDepart(), 'sa' => $h->getStationArrivee(), 'ha' => $h->getHeureArrivee(),
                           'date' => $h->getDateTrajet(), 'etat' => $h->getEtat(), 'prix' => $h->getPrix()];
                if ($h->getId()) {
                    $this->db->prepare('UPDATE train_horaires SET train_id = :train, ligne_id = :ligne, station_depart = :sd, heure_depart = :hd,
                                               station_arrivee = :sa, heure_arrivee = :ha, date_trajet = :date, etat = :etat, prix = :prix WHERE id = :id')
                        ->execute($params + ['id' => $h->getId()]);
                    $this->flash('success', 'Horaire modifié.');
                } else {
                    $this->db->prepare('INSERT INTO train_horaires (train_id, ligne_id, station_depart, heure_depart, station_arrivee, heure_arrivee, date_trajet, etat, prix)
                                        VALUES (:train, :ligne, :sd, :hd, :sa, :ha, :date, :etat, :prix)')
                        ->execute($params);
                    $this->flash('success', 'Horaire ajouté.');
                }
                self::redirect('admin-train-horaires');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare(self::sqlTrainHoraires() . ' WHERE h.id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $h = TrainHoraireModel::fromArray($row);
                $form = ['id' => $h->getId(), 'numero_train' => $h->getTrainNumero(), 'ligne' => $h->getLigneId(),
                         'station_depart' => $h->getStationDepart(), 'heure_depart' => substr($h->getHeureDepart(), 0, 5),
                         'station_arrivee' => $h->getStationArrivee(), 'heure_arrivee' => substr($h->getHeureArrivee(), 0, 5),
                         'date' => $h->getDateTrajet(), 'etat' => $h->getEtat(), 'prix' => $h->getPrix() === null ? '' : (string) $h->getPrix()];
            }
        }

        $this->render('admin_train_horaires', [
            'form' => $form, 'erreurs' => $erreurs, 'flash' => $this->popFlash(),
            'horaires' => array_map([TrainHoraireModel::class, 'fromArray'],
                $this->db->query(self::sqlTrainHoraires() . ' ORDER BY h.date_trajet DESC, h.heure_depart')->fetchAll()),
            'lignes' => $this->getTrainLignes(), 'trains' => $this->getTrains(),
            'etats' => self::ETATS, 'stations' => self::trainStations(),
        ], 'layout_admin');
    }

    public function deleteAction(): void
    {
        $this->requireSuperAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $this->csrfOk()) {
            $stmt = $this->db->prepare('DELETE FROM train_horaires WHERE id = :id');
            $stmt->execute(['id' => $this->intFromPost()]);
            $stmt->rowCount() ? $this->flash('success', 'Horaire supprimé.') : $this->flash('danger', 'Horaire introuvable.');
        }
        self::redirect('admin-train-horaires');
    }
}
