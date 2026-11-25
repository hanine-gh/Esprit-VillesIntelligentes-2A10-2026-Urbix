<?php
namespace App\Controllers;

/**
 * Pages des modules développés par les autres membres du groupe.
 * Chaque membre peut remplacer la route 'module' par son propre contrôleur ;
 * en attendant, la page présente le module et ses entités (voir database/smartcity.sql).
 */
class ModuleController extends Controller {
    protected string $page      = 'module';
    protected string $title     = 'Urbix Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    public const MODULES = [
        'bus' => [
            'titre' => 'Bus urbain', 'icone' => 'fa-bus',
            'description' => 'Lignes, horaires et suivi en temps réel des bus traditionnels de la ville.',
            'entites' => [
                'Station'    => ['id_station', 'nom', 'ville', 'adresse', 'latitude', 'longitude', 'type'],
                'LigneBus'   => ['id_ligne', 'numero', 'nom', 'id_station_depart', 'id_station_arrivee'],
                'Bus'        => ['id_bus', 'id_vehicule', 'id_ligne', 'id_chauffeur', 'a_la_demande', 'etat', 'latitude', 'longitude'],
                'HoraireBus' => ['id_horaire', 'id_ligne', 'id_station', 'heure_passage'],
            ],
        ],
        'bus-demande' => [
            'titre' => 'Bus à la demande', 'icone' => 'fa-shuttle-van',
            'description' => 'Réservation d\'un bus intelligent avec prise en charge à domicile.',
            'entites' => [
                'DemandePassager' => ['id_demande', 'id_utilisateur', 'id_bus', 'adresse_depart', 'destination', 'date_heure_souhaitee', 'nb_passagers', 'statut'],
                'Bus'             => ['id_bus', 'id_vehicule', 'a_la_demande', 'etat'],
            ],
        ],
        'train' => [
            'titre' => 'Métro & Train', 'icone' => 'fa-train',
            'description' => 'Horaires, gares et billets du métro et des trains.',
            'entites' => [
                'Train'       => ['id_train', 'numero', 'type', 'capacite', 'id_chauffeur', 'etat'],
                'TrajetTrain' => ['id_trajet_train', 'id_train', 'id_gare_depart', 'id_gare_arrivee', 'date_heure_depart', 'date_heure_arrivee', 'prix'],
                'BilletTrain' => ['id_billet', 'id_trajet_train', 'id_utilisateur', 'classe', 'prix', 'date_achat'],
            ],
        ],
        'louage' => [
            'titre' => 'Louage', 'icone' => 'fa-taxi',
            'description' => 'Places disponibles dans les louages inter-villes depuis les stations.',
            'entites' => [
                'Louage' => ['id_louage', 'id_chauffeur', 'id_vehicule', 'id_station_depart', 'ville_arrivee', 'prix_place', 'places_disponibles', 'date_heure_depart'],
            ],
        ],
        'location' => [
            'titre' => 'Location de véhicules', 'icone' => 'fa-car',
            'description' => 'Louer une voiture à la journée ou à la semaine.',
            'entites' => [
                'Vehicule' => ['id_vehicule', 'id_proprietaire', 'type', 'marque', 'modele', 'couleur', 'immatriculation', 'nb_places', 'climatisation'],
                'Location' => ['id_location', 'id_vehicule', 'id_client', 'date_debut', 'date_fin', 'prix_jour', 'prix_total', 'statut'],
            ],
        ],
        'reparation' => [
            'titre' => 'Réparation de véhicules', 'icone' => 'fa-tools',
            'description' => 'Trouver un garage et suivre la réparation de son véhicule.',
            'entites' => [
                'Garage'     => ['id_garage', 'id_responsable', 'nom', 'adresse', 'ville', 'telephone', 'specialite'],
                'Reparation' => ['id_reparation', 'id_client', 'id_vehicule', 'id_garage', 'description_panne', 'date_demande', 'date_rdv', 'cout_estime', 'statut'],
            ],
        ],
        'missions' => [
            'titre' => 'Missions', 'icone' => 'fa-clipboard-list',
            'description' => 'Espace chauffeur : missions planifiées et en cours.',
            'entites' => [
                'Mission' => ['id_mission', 'id_chauffeur', 'id_bus', 'date_mission', 'description', 'statut'],
            ],
        ],
        'demandes-passagers' => [
            'titre' => 'Demande de passagers', 'icone' => 'fa-users',
            'description' => 'Espace chauffeur : demandes de prise en charge des passagers.',
            'entites' => [
                'DemandePassager' => ['id_demande', 'id_utilisateur', 'id_bus', 'adresse_depart', 'destination', 'date_heure_souhaitee', 'nb_passagers', 'statut'],
            ],
        ],
        'cas-bus' => [
            'titre' => 'Cas de mon bus', 'icone' => 'fa-bus-alt',
            'description' => 'Espace chauffeur : signaler une panne, un accident ou un retard.',
            'entites' => [
                'IncidentBus' => ['id_incident', 'id_bus', 'id_chauffeur', 'type_incident', 'description', 'date_incident', 'statut'],
            ],
        ],
        'station' => [
            'titre' => 'Station', 'icone' => 'fa-map-marker-alt',
            'description' => 'Espace chauffeur : stations et arrêts du réseau.',
            'entites' => [
                'Station' => ['id_station', 'nom', 'ville', 'adresse', 'latitude', 'longitude', 'type'],
            ],
        ],
    ];

    public function index(): void {
        $cle = (string) ($_GET['nom'] ?? '');
        if (!isset(self::MODULES[$cle])) {
            redirect(url('home'));
        }
        $module = self::MODULES[$cle];
        $this->page  = 'module-' . $cle;
        $this->title = $module['titre'] . ' | Urbix Smart City';

        $this->render('module', [
            'module'   => $module,
            'cle'      => $cle,
        ]);
    }
}
