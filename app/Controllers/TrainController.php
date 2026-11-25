<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\TrainModel;
use PDO;

/** TrainController : gestion des trains (admin) — ajouter, modifier, supprimer, lister. */
class TrainController extends Controller
{
    use UserFormTrait;
    use AdminTrait;
    use TrainTrait;

    public const ETATS = ['En circulation', 'En maintenance', 'Indisponible'];

    protected string $page  = 'admin-train-trains';
    protected string $title = 'Trains | Backoffice Smart City';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function valider(array $f): array
    {
        $e = [];
        if (!preg_match('/^[A-Za-z0-9-]{1,10}$/', $f['numero'])) {
            $e['numero'] = 'Le numéro du train est obligatoire (1 à 10 lettres ou chiffres).';
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM train_trains WHERE numero = :n AND id <> :id');
            $stmt->execute(['n' => $f['numero'], 'id' => $f['id']]);
            if ((int) $stmt->fetchColumn() > 0) {
                $e['numero'] = 'Ce numéro de train existe déjà.';
            }
        }
        if (!in_array($f['depart'], self::trainStations(), true)) {
            $e['depart'] = 'Choisissez une station de début.';
        }
        if (!ctype_digit($f['capacite']) || (int) $f['capacite'] < 1 || (int) $f['capacite'] > 5000) {
            $e['capacite'] = 'La capacité doit être un nombre entre 1 et 5000.';
        }
        if (!$this->trainLigneExiste($f['ligne'])) {
            $e['ligne'] = 'Choisissez une ligne.';
        }
        if (!in_array($f['etat'], self::ETATS, true)) {
            $e['etat'] = 'Choisissez un état.';
        }
        return $e;
    }

    // index.php?page=admin-train-trains (liste + formulaire d'ajout / de modification ?edit=ID)
    public function indexAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'numero' => '', 'depart' => '', 'capacite' => '', 'ligne' => 0, 'etat' => ''];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $form = [
                'id'       => $this->intFromPost(),
                'numero'   => trim((string) ($_POST['numero'] ?? '')),
                'depart'   => (string) ($_POST['depart'] ?? ''),
                'capacite' => trim((string) ($_POST['capacite'] ?? '')),
                'ligne'    => $this->intFromPost('ligne'),
                'etat'     => (string) ($_POST['etat'] ?? ''),
            ];
            $erreurs = $this->csrfOk() ? $this->valider($form) : ['csrf' => 'Session expirée, veuillez réessayer.'];

            if (!$erreurs) {
                $t = new TrainModel();
                $t->setId($form['id'] ?: null);
                $t->setNumero($form['numero']);
                $t->setStationDebut($form['depart']);
                $t->setCapacite((int) $form['capacite']);
                $t->setLigneId($form['ligne']);
                $t->setEtat($form['etat']);

                $params = ['numero' => $t->getNumero(), 'depart' => $t->getStationDebut(), 'capacite' => $t->getCapacite(),
                           'ligne' => $t->getLigneId(), 'etat' => $t->getEtat()];
                if ($t->getId()) {
                    $this->db->prepare('UPDATE train_trains SET numero = :numero, station_debut = :depart, capacite = :capacite, ligne_id = :ligne, etat = :etat WHERE id = :id')
                        ->execute($params + ['id' => $t->getId()]);
                    $this->flash('success', 'Train ' . $t->getNumero() . ' modifié.');
                } else {
                    $this->db->prepare('INSERT INTO train_trains (numero, station_debut, capacite, ligne_id, etat) VALUES (:numero, :depart, :capacite, :ligne, :etat)')
                        ->execute($params);
                    $this->flash('success', 'Train ' . $t->getNumero() . ' ajouté.');
                }
                self::redirect('admin-train-trains');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare('SELECT * FROM train_trains WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $t = TrainModel::fromArray($row);
                $form = ['id' => $t->getId(), 'numero' => $t->getNumero(), 'depart' => $t->getStationDebut(),
                         'capacite' => (string) $t->getCapacite(), 'ligne' => $t->getLigneId(), 'etat' => $t->getEtat()];
            }
        }

        $this->render('admin_train_trains', [
            'form' => $form, 'erreurs' => $erreurs, 'flash' => $this->popFlash(),
            'trains' => $this->getTrains(), 'lignes' => $this->getTrainLignes(),
            'etats' => self::ETATS, 'stations' => self::trainStations(),
        ], 'layout_admin');
    }

    public function deleteAction(): void
    {
        $this->requireSuperAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $this->csrfOk()) {
            $stmt = $this->db->prepare('DELETE FROM train_trains WHERE id = :id');
            $stmt->execute(['id' => $this->intFromPost()]);
            $stmt->rowCount() ? $this->flash('success', 'Train supprimé (ses horaires aussi).') : $this->flash('danger', 'Train introuvable.');
        }
        self::redirect('admin-train-trains');
    }
}
