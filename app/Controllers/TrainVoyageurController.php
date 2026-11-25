<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\TrainAlerteModel;
use App\Models\TrainHoraireModel;
use App\Models\TrainLigneModel;
use App\Models\TrainModel;
use PDO;

/**
 * TrainVoyageurController : module Train, côté utilisateur connecté (frontoffice).
 * Trains disponibles (recherche de trajets), suivi d'un train, horaires d'une station, alertes.
 */
class TrainVoyageurController extends Controller
{
    use TrainTrait;

    protected string $bodyClass = 'no-sidebar is-preload';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // index.php?page=train-disponibles
    public function disponiblesAction(): void
    {
        AuthController::requireAuth();
        $this->page  = 'train-disponibles';
        $this->title = 'Trains disponibles | Smart City';

        $recherche = [
            'depart'  => (string) ($_GET['depart'] ?? ''),
            'arrivee' => (string) ($_GET['arrivee'] ?? ''),
            'date'    => (string) ($_GET['date'] ?? ''),
        ];
        $resultats = null;   // null = pas encore de recherche
        $erreurs = [];

        if (isset($_GET['depart'])) {
            if (!in_array($recherche['depart'], self::trainStations(), true) || !in_array($recherche['arrivee'], self::trainStations(), true)) {
                $erreurs[] = "Choisissez la station de départ et la station d'arrivée.";
            } elseif ($recherche['depart'] === $recherche['arrivee']) {
                $erreurs[] = "La station d'arrivée doit être différente de la station de départ.";
            }
            if (!self::trainIsDate($recherche['date'])) {
                $erreurs[] = 'Saisissez une date valide.';
            }
            if (!$erreurs) {
                $stmt = $this->db->prepare(self::sqlTrainHoraires() .
                    ' WHERE h.station_depart = :d AND h.station_arrivee = :a AND h.date_trajet = :date ORDER BY h.heure_depart');
                $stmt->execute(['d' => $recherche['depart'], 'a' => $recherche['arrivee'], 'date' => $recherche['date']]);
                $resultats = array_map([TrainHoraireModel::class, 'fromArray'], $stmt->fetchAll());
            }
        }

        $this->render('train_disponibles', [
            'recherche' => $recherche, 'resultats' => $resultats, 'erreurs' => $erreurs, 'stations' => self::trainStations(),
        ]);
    }

    // index.php?page=train-suivi
    public function suiviAction(): void
    {
        AuthController::requireAuth();
        $this->page  = 'train-suivi';
        $this->title = 'Suivi des trains | Smart City';

        $numero = (string) ($_GET['numero_train'] ?? '');
        $suivi = null;

        if ($numero !== '') {
            $stmt = $this->db->prepare(
                'SELECT t.*, l.nom AS ligne_nom FROM train_trains t JOIN train_lignes l ON l.id = t.ligne_id WHERE t.numero = :n'
            );
            $stmt->execute(['n' => $numero]);
            if ($row = $stmt->fetch()) {
                $train = TrainModel::fromArray($row);

                // Station suivante : celle qui suit la station actuelle sur l'itinéraire de la ligne
                $stmt = $this->db->prepare('SELECT * FROM train_lignes WHERE id = :id');
                $stmt->execute(['id' => $train->getLigneId()]);
                $itineraire = TrainLigneModel::fromArray($stmt->fetch())->getItineraire();
                $pos = array_search($train->getStationDebut(), $itineraire, true);
                $suivante = ($pos !== false && isset($itineraire[$pos + 1])) ? $itineraire[$pos + 1] : 'Terminus';

                // Prochain trajet prévu (aujourd'hui ou plus tard, non annulé)
                $stmt = $this->db->prepare(
                    "SELECT date_trajet, heure_arrivee FROM train_horaires
                     WHERE train_id = :id AND etat <> 'Annulé'
                       AND (date_trajet > CURDATE() OR (date_trajet = CURDATE() AND heure_depart >= CURTIME()))
                     ORDER BY date_trajet, heure_depart LIMIT 1"
                );
                $stmt->execute(['id' => $train->getId()]);
                $prochain = $stmt->fetch();
                $heurePrevue = '—';
                if ($prochain) {
                    $heurePrevue = substr($prochain['heure_arrivee'], 0, 5);
                    if ($prochain['date_trajet'] !== date('Y-m-d')) {
                        $heurePrevue .= ' (' . date('d/m/Y', strtotime($prochain['date_trajet'])) . ')';
                    }
                }

                // Retard : total des alertes "Retard" actives sur ce train
                $stmt = $this->db->prepare(
                    "SELECT COALESCE(SUM(duree_retard), 0) FROM train_alertes WHERE train_id = :id AND type = 'Retard' AND etat = 'Active'"
                );
                $stmt->execute(['id' => $train->getId()]);
                $retard = (int) $stmt->fetchColumn();

                $suivi = ['train' => $train, 'suivante' => $suivante, 'heurePrevue' => $heurePrevue,
                          'retard' => $retard > 0 ? $retard . ' min' : 'Aucun'];
            }
        }

        $this->render('train_suivi', ['numero' => $numero, 'trains' => $this->getTrains(), 'suivi' => $suivi]);
    }

    // index.php?page=train-horaires
    public function horairesAction(): void
    {
        AuthController::requireAuth();
        $this->page  = 'train-horaires';
        $this->title = 'Horaires des trains | Smart City';

        $recherche = [
            'ligne'   => (int) ($_GET['ligne'] ?? 0),
            'station' => (string) ($_GET['station'] ?? ''),
            'date'    => (string) ($_GET['date'] ?? ''),
        ];
        $passages = null;
        $erreurs = [];

        if (isset($_GET['ligne'])) {
            if (!$this->trainLigneExiste($recherche['ligne']) || !in_array($recherche['station'], self::trainStations(), true)) {
                $erreurs[] = 'Choisissez une ligne et une station.';
            }
            if (!self::trainIsDate($recherche['date'])) {
                $erreurs[] = 'Saisissez une date valide.';
            }
            if (!$erreurs) {
                $stmt = $this->db->prepare(self::sqlTrainHoraires() .
                    ' WHERE h.ligne_id = :l AND h.date_trajet = :date AND (h.station_depart = :s1 OR h.station_arrivee = :s2)');
                $stmt->execute(['l' => $recherche['ligne'], 'date' => $recherche['date'],
                                's1' => $recherche['station'], 's2' => $recherche['station']]);
                $passages = [];
                foreach ($stmt->fetchAll() as $row) {
                    $h = TrainHoraireModel::fromArray($row);
                    // Heure de passage : départ si la station est le départ, sinon arrivée
                    $heure = $h->getStationDepart() === $recherche['station'] ? $h->getHeureDepart() : $h->getHeureArrivee();
                    $passages[] = ['horaire' => $h, 'heure' => substr($heure, 0, 5)];
                }
                usort($passages, fn($a, $b) => strcmp($a['heure'], $b['heure']));
            }
        }

        $this->render('train_horaires', [
            'recherche' => $recherche, 'passages' => $passages, 'erreurs' => $erreurs,
            'lignes' => $this->getTrainLignes(), 'stations' => self::trainStations(),
        ]);
    }

    // index.php?page=train-alertes
    public function alertesAction(): void
    {
        AuthController::requireAuth();
        $this->page  = 'train-alertes';
        $this->title = 'Alertes et perturbations | Smart City';

        $filtres = [
            'type_alerte'  => (string) ($_GET['type_alerte'] ?? ''),
            'ligne'        => (int) ($_GET['ligne'] ?? 0),
            'numero_train' => (string) ($_GET['numero_train'] ?? ''),
        ];

        $sql = self::sqlTrainAlertes() . ' WHERE 1';
        $params = [];
        if (isset(TrainAlerteController::TYPES[$filtres['type_alerte']])) {
            $sql .= ' AND a.type = :type';
            $params['type'] = $filtres['type_alerte'];
        }
        if ($filtres['ligne'] > 0) {
            $sql .= ' AND a.ligne_id = :ligne';
            $params['ligne'] = $filtres['ligne'];
        }
        if ($filtres['numero_train'] !== '') {
            $sql .= ' AND t.numero = :num';
            $params['num'] = $filtres['numero_train'];
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY a.date_alerte DESC, a.heure_alerte DESC');
        $stmt->execute($params);

        $this->render('train_alertes', [
            'filtres' => $filtres,
            'alertes' => array_map([TrainAlerteModel::class, 'fromArray'], $stmt->fetchAll()),
            'lignes'  => $this->getTrainLignes(),
            'trains'  => $this->getTrains(),
        ]);
    }
}
