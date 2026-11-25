<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\TrainAlerteModel;
use PDO;

/** TrainAlerteController : gestion des alertes du réseau train (admin). */
class TrainAlerteController extends Controller
{
    use UserFormTrait;
    use AdminTrait;
    use TrainTrait;

    public const TYPES = ['Retard' => 'Retard', 'Annulation' => 'Annulation', 'Interruption' => 'Interruption de ligne', 'Changement' => 'Changement de trajet'];
    public const ETATS = ['Active', 'Résolue'];

    protected string $page  = 'admin-train-alertes';
    protected string $title = 'Alertes train | Backoffice Smart City';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function valider(array $f, ?int &$trainId): array
    {
        $e = [];
        if (!isset(self::TYPES[$f['type_alerte']])) {
            $e['type_alerte'] = "Choisissez le type d'alerte.";
        }
        $trainId = null;
        if ($f['numero_train'] !== '') {
            $trainId = $this->trainIdParNumero($f['numero_train']);
            if ($trainId === null) {
                $e['numero_train'] = 'Le numéro du train doit correspondre à un train existant.';
            }
        }
        if (!$this->trainLigneExiste($f['ligne'])) {
            $e['ligne'] = 'Choisissez la ligne concernée.';
        }
        if ($f['station'] !== '' && !in_array($f['station'], self::trainStations(), true)) {
            $e['station'] = 'Station inconnue.';
        }
        if ($f['duree_retard'] !== '' && (!ctype_digit($f['duree_retard']) || (int) $f['duree_retard'] > 1440)) {
            $e['duree_retard'] = 'La durée du retard doit être un nombre de minutes entre 0 et 1440.';
        }
        if (!self::trainIsDate($f['date_alerte'])) {
            $e['date_alerte'] = "Saisissez la date de l'alerte.";
        }
        if ($f['heure_alerte'] !== '' && !self::trainIsTime($f['heure_alerte'])) {
            $e['heure_alerte'] = "Heure de l'alerte invalide.";
        }
        if (mb_strlen($f['description']) > 500) {
            $e['description'] = 'La description ne doit pas dépasser 500 caractères.';
        }
        if (!in_array($f['etat'], self::ETATS, true)) {
            $e['etat'] = "Choisissez l'état de l'alerte.";
        }
        return $e;
    }

    // index.php?page=admin-train-alertes
    public function indexAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'type_alerte' => '', 'numero_train' => '', 'ligne' => 0, 'station' => '', 'duree_retard' => '',
                 'date_alerte' => '', 'heure_alerte' => '', 'description' => '', 'etat' => 'Active'];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $form = [
                'id'           => $this->intFromPost(),
                'type_alerte'  => (string) ($_POST['type_alerte'] ?? ''),
                'numero_train' => trim((string) ($_POST['numero_train'] ?? '')),
                'ligne'        => $this->intFromPost('ligne'),
                'station'      => (string) ($_POST['station'] ?? ''),
                'duree_retard' => trim((string) ($_POST['duree_retard'] ?? '')),
                'date_alerte'  => (string) ($_POST['date_alerte'] ?? ''),
                'heure_alerte' => (string) ($_POST['heure_alerte'] ?? ''),
                'description'  => trim((string) ($_POST['description'] ?? '')),
                'etat'         => (string) ($_POST['etat'] ?? ''),
            ];
            $trainId = null;
            $erreurs = $this->csrfOk() ? $this->valider($form, $trainId) : ['csrf' => 'Session expirée, veuillez réessayer.'];

            if (!$erreurs) {
                $a = new TrainAlerteModel();
                $a->setId($form['id'] ?: null);
                $a->setType($form['type_alerte']);
                $a->setTrainId($trainId);
                $a->setLigneId($form['ligne']);
                $a->setStation($form['station'] !== '' ? $form['station'] : null);
                $a->setDureeRetard($form['duree_retard'] !== '' ? (int) $form['duree_retard'] : null);
                $a->setDateAlerte($form['date_alerte']);
                $a->setHeureAlerte($form['heure_alerte'] !== '' ? $form['heure_alerte'] : null);
                $a->setDescription($form['description']);
                $a->setEtat($form['etat']);

                $params = ['type' => $a->getType(), 'train' => $a->getTrainId(), 'ligne' => $a->getLigneId(), 'station' => $a->getStation(),
                           'duree' => $a->getDureeRetard(), 'date' => $a->getDateAlerte(), 'heure' => $a->getHeureAlerte(),
                           'descr' => $a->getDescription(), 'etat' => $a->getEtat()];
                if ($a->getId()) {
                    $this->db->prepare('UPDATE train_alertes SET type = :type, train_id = :train, ligne_id = :ligne, station = :station,
                                               duree_retard = :duree, date_alerte = :date, heure_alerte = :heure, description = :descr, etat = :etat WHERE id = :id')
                        ->execute($params + ['id' => $a->getId()]);
                    $this->flash('success', 'Alerte modifiée.');
                } else {
                    $this->db->prepare('INSERT INTO train_alertes (type, train_id, ligne_id, station, duree_retard, date_alerte, heure_alerte, description, etat)
                                        VALUES (:type, :train, :ligne, :station, :duree, :date, :heure, :descr, :etat)')
                        ->execute($params);
                    $this->flash('success', 'Alerte publiée.');
                }
                self::redirect('admin-train-alertes');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare(self::sqlTrainAlertes() . ' WHERE a.id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $a = TrainAlerteModel::fromArray($row);
                $form = ['id' => $a->getId(), 'type_alerte' => $a->getType(), 'numero_train' => $a->getTrainNumero(),
                         'ligne' => $a->getLigneId(), 'station' => (string) $a->getStation(),
                         'duree_retard' => $a->getDureeRetard() === null ? '' : (string) $a->getDureeRetard(),
                         'date_alerte' => $a->getDateAlerte(), 'heure_alerte' => substr((string) $a->getHeureAlerte(), 0, 5),
                         'description' => $a->getDescription(), 'etat' => $a->getEtat()];
            }
        }

        $this->render('admin_train_alertes', [
            'form' => $form, 'erreurs' => $erreurs, 'flash' => $this->popFlash(),
            'alertes' => array_map([TrainAlerteModel::class, 'fromArray'],
                $this->db->query(self::sqlTrainAlertes() . ' ORDER BY a.date_alerte DESC, a.heure_alerte DESC')->fetchAll()),
            'lignes' => $this->getTrainLignes(), 'trains' => $this->getTrains(),
            'types' => self::TYPES, 'etats' => self::ETATS, 'stations' => self::trainStations(),
        ], 'layout_admin');
    }

    public function deleteAction(): void
    {
        $this->requireSuperAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $this->csrfOk()) {
            $stmt = $this->db->prepare('DELETE FROM train_alertes WHERE id = :id');
            $stmt->execute(['id' => $this->intFromPost()]);
            $stmt->rowCount() ? $this->flash('success', 'Alerte supprimée.') : $this->flash('danger', 'Alerte introuvable.');
        }
        self::redirect('admin-train-alertes');
    }
}
