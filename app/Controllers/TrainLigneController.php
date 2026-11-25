<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\TrainLigneModel;
use PDO;
use PDOException;

/** TrainLigneController : gestion des lignes ferroviaires (admin). */
class TrainLigneController extends Controller
{
    use UserFormTrait;
    use AdminTrait;
    use TrainTrait;

    public const ETATS = ['En service', 'Perturbée', 'Interrompue'];

    protected string $page  = 'admin-train-lignes';
    protected string $title = 'Lignes ferroviaires | Backoffice Smart City';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function valider(array $f): array
    {
        $e = [];
        if ($f['nom'] === '' || mb_strlen($f['nom']) > 50) {
            $e['nom_ligne'] = 'Le nom de la ligne est obligatoire (50 caractères au maximum).';
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM train_lignes WHERE nom = :n AND id <> :id');
            $stmt->execute(['n' => $f['nom'], 'id' => $f['id']]);
            if ((int) $stmt->fetchColumn() > 0) {
                $e['nom_ligne'] = 'Une ligne porte déjà ce nom.';
            }
        }
        if (!in_array($f['depart'], self::trainStations(), true)) {
            $e['depart'] = 'Choisissez la station de départ.';
        }
        if (!in_array($f['arrivee'], self::trainStations(), true)) {
            $e['arrivee'] = "Choisissez la station d'arrivée.";
        } elseif ($f['depart'] === $f['arrivee']) {
            $e['arrivee'] = "La station d'arrivée doit être différente de la station de départ.";
        }
        if (mb_strlen($f['stations']) > 255) {
            $e['stations'] = 'Les stations intermédiaires ne doivent pas dépasser 255 caractères.';
        }
        if ($f['distance'] !== '' && (!is_numeric($f['distance']) || (float) $f['distance'] < 0 || (float) $f['distance'] > 1000)) {
            $e['distance'] = 'La distance doit être un nombre entre 0 et 1000 km.';
        }
        if (!in_array($f['etat'], self::ETATS, true)) {
            $e['etat'] = 'Choisissez un état.';
        }
        return $e;
    }

    // index.php?page=admin-train-lignes
    public function indexAction(): void
    {
        $this->requireSuperAdmin();
        $erreurs = [];
        $form = ['id' => 0, 'nom' => '', 'depart' => '', 'arrivee' => '', 'stations' => '', 'distance' => '', 'etat' => ''];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $form = [
                'id'       => $this->intFromPost(),
                'nom'      => trim((string) ($_POST['nom_ligne'] ?? '')),
                'depart'   => (string) ($_POST['depart'] ?? ''),
                'arrivee'  => (string) ($_POST['arrivee'] ?? ''),
                'stations' => trim((string) ($_POST['stations'] ?? '')),
                'distance' => trim(str_replace(',', '.', (string) ($_POST['distance'] ?? ''))),
                'etat'     => (string) ($_POST['etat'] ?? ''),
            ];
            $erreurs = $this->csrfOk() ? $this->valider($form) : ['csrf' => 'Session expirée, veuillez réessayer.'];

            if (!$erreurs) {
                $l = new TrainLigneModel();
                $l->setId($form['id'] ?: null);
                $l->setNom($form['nom']);
                $l->setStationDepart($form['depart']);
                $l->setStationArrivee($form['arrivee']);
                $l->setStationsIntermediaires($form['stations']);
                $l->setDistance($form['distance'] === '' ? null : round((float) $form['distance'], 1));
                $l->setEtat($form['etat']);

                $params = ['nom' => $l->getNom(), 'dep' => $l->getStationDepart(), 'arr' => $l->getStationArrivee(),
                           'st' => $l->getStationsIntermediaires(), 'km' => $l->getDistance(), 'etat' => $l->getEtat()];
                if ($l->getId()) {
                    $this->db->prepare('UPDATE train_lignes SET nom = :nom, station_depart = :dep, station_arrivee = :arr,
                                               stations_intermediaires = :st, distance = :km, etat = :etat WHERE id = :id')
                        ->execute($params + ['id' => $l->getId()]);
                    $this->flash('success', $l->getNom() . ' modifiée.');
                } else {
                    $this->db->prepare('INSERT INTO train_lignes (nom, station_depart, station_arrivee, stations_intermediaires, distance, etat)
                                        VALUES (:nom, :dep, :arr, :st, :km, :etat)')
                        ->execute($params);
                    $this->flash('success', $l->getNom() . ' ajoutée.');
                }
                self::redirect('admin-train-lignes');
            }
        } elseif ($id = $this->intFromQuery('edit')) {
            $stmt = $this->db->prepare('SELECT * FROM train_lignes WHERE id = :id');
            $stmt->execute(['id' => $id]);
            if ($row = $stmt->fetch()) {
                $l = TrainLigneModel::fromArray($row);
                $form = ['id' => $l->getId(), 'nom' => $l->getNom(), 'depart' => $l->getStationDepart(), 'arrivee' => $l->getStationArrivee(),
                         'stations' => $l->getStationsIntermediaires(),
                         'distance' => $l->getDistance() === null ? '' : (string) $l->getDistance(), 'etat' => $l->getEtat()];
            }
        }

        $this->render('admin_train_lignes', [
            'form' => $form, 'erreurs' => $erreurs, 'flash' => $this->popFlash(),
            'lignes' => $this->getTrainLignes(), 'etats' => self::ETATS, 'stations' => self::trainStations(),
        ], 'layout_admin');
    }

    public function deleteAction(): void
    {
        $this->requireSuperAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $this->csrfOk()) {
            try {
                $stmt = $this->db->prepare('DELETE FROM train_lignes WHERE id = :id');
                $stmt->execute(['id' => $this->intFromPost()]);
                $stmt->rowCount() ? $this->flash('success', 'Ligne supprimée.') : $this->flash('danger', 'Ligne introuvable.');
            } catch (PDOException $e) {
                // Clé étrangère : des trains, horaires ou alertes utilisent encore cette ligne
                $this->flash('danger', "Impossible de supprimer cette ligne : des trains, horaires ou alertes l'utilisent encore.");
            }
        }
        self::redirect('admin-train-lignes');
    }
}
