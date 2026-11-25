<?php
use App\Controllers\HomeController;
use App\Controllers\LeftSidebarController;
use App\Controllers\AuthController;
use App\Controllers\DriverController;
use App\Controllers\TrajetController;
use App\Controllers\StationController;
use App\Controllers\ChauffeurController;
use App\Controllers\BusController;
use App\Controllers\VoyageurController;
use App\Controllers\MobiliteController;
use App\Controllers\MobiliteAdminController;
use App\Controllers\TrainVoyageurController;
use App\Controllers\TrainAdminController;
use App\Controllers\TrainController;
use App\Controllers\TrainLigneController;
use App\Controllers\TrainHoraireController;
use App\Controllers\TrainAlerteController;
use App\Controllers\LouageController;
use App\Controllers\LouageAdminController;
use App\Controllers\CovoiturageController;

return [
    'home'          => [HomeController::class, 'index'],
    'left-sidebar'  => [LeftSidebarController::class, 'index'],

    // Authentification
    'register'      => [AuthController::class, 'registerAction'],
    'login'         => [AuthController::class, 'loginAction'],
    'logout'        => [AuthController::class, 'logoutAction'],

    // Espace utilisateur (rôle user = frontoffice)
    'dashboard'      => [AuthController::class, 'dashboardAction'],
    'profile-update' => [AuthController::class, 'profileUpdateAction'],

    // Espace voyageur (utilisateur normal) : trajets disponibles + demandes de STOP
    'trajets-disponibles'   => [VoyageurController::class, 'trajetsAction'],
    'demande-stop'          => [VoyageurController::class, 'demandeAction'],
    'mes-demandes'          => [VoyageurController::class, 'demandesAction'],
    'demande-stop-annuler'  => [VoyageurController::class, 'annulerAction'],

    // Espace chauffeur de bus (frontoffice) : ses trajets + ajout de stations
    'mes-missions'        => [ChauffeurController::class, 'missionsAction'],
    'mon-bus'             => [ChauffeurController::class, 'busCaseAction'],
    'mes-trajets'         => [ChauffeurController::class, 'trajetsAction'],
    'mes-trajets-station' => [ChauffeurController::class, 'stationCreateAction'],

    // Espace administrateur (rôle admin uniquement = backoffice)
    'admin'         => [AuthController::class, 'adminAction'],

    // Backoffice : gestion des chauffeurs de bus (CRUD, admin uniquement)
    'admin-drivers'       => [DriverController::class, 'indexAction'],
    'admin-driver-show'   => [DriverController::class, 'showAction'],
    'admin-driver-create' => [DriverController::class, 'createAction'],
    'admin-driver-edit'   => [DriverController::class, 'editAction'],
    'admin-driver-delete' => [DriverController::class, 'deleteAction'],

    // Backoffice : trajets attribués aux chauffeurs (CRUD, admin uniquement)
    'admin-trajets'       => [TrajetController::class, 'indexAction'],
    'admin-trajet-show'   => [TrajetController::class, 'showAction'],
    'admin-trajet-create' => [TrajetController::class, 'createAction'],
    'admin-trajet-edit'   => [TrajetController::class, 'editAction'],
    'admin-trajet-delete' => [TrajetController::class, 'deleteAction'],

    // Backoffice : flotte de bus (CRUD + attribution, admin uniquement)
    'admin-buses'      => [BusController::class, 'indexAction'],
    'admin-bus-show'   => [BusController::class, 'showAction'],
    'admin-bus-create' => [BusController::class, 'createAction'],
    'admin-bus-edit'   => [BusController::class, 'editAction'],
    'admin-bus-delete' => [BusController::class, 'deleteAction'],

    // Backoffice : stations d'un trajet (admin uniquement)
    'admin-station-create' => [StationController::class, 'createAction'],
    'admin-station-edit'   => [StationController::class, 'editAction'],
    'admin-station-delete' => [StationController::class, 'deleteAction'],

    // =====================================================================
    // MODULES INTÉGRÉS (frontoffice : utilisateur connecté / backoffice : admin)
    // =====================================================================

    // Location (moto, trottinette, vélo)
    'location'           => [MobiliteController::class, 'indexAction'],
    'location-reserver'  => [MobiliteController::class, 'reserverAction'],
    'location-demarrer'  => [MobiliteController::class, 'demarrerAction'],
    'location-prolonger' => [MobiliteController::class, 'prolongerAction'],
    'location-incident'  => [MobiliteController::class, 'incidentAction'],
    'location-terminer'  => [MobiliteController::class, 'terminerAction'],
    'location-annuler'   => [MobiliteController::class, 'annulerAction'],

    'admin-location'                    => [MobiliteAdminController::class, 'indexAction'],
    'admin-location-vehicule-ajouter'   => [MobiliteAdminController::class, 'ajouterVehiculeAction'],
    'admin-location-vehicule-tarif'     => [MobiliteAdminController::class, 'tarifAction'],
    'admin-location-vehicule-statut'    => [MobiliteAdminController::class, 'statutAction'],
    'admin-location-vehicule-supprimer' => [MobiliteAdminController::class, 'supprimerVehiculeAction'],
    'admin-location-cloturer'           => [MobiliteAdminController::class, 'cloturerAction'],
    'admin-location-supprimer'          => [MobiliteAdminController::class, 'supprimerLocationAction'],
    'admin-location-purger'             => [MobiliteAdminController::class, 'purgerAction'],

    // Train
    'train-disponibles' => [TrainVoyageurController::class, 'disponiblesAction'],
    'train-suivi'       => [TrainVoyageurController::class, 'suiviAction'],
    'train-horaires'    => [TrainVoyageurController::class, 'horairesAction'],
    'train-alertes'     => [TrainVoyageurController::class, 'alertesAction'],

    'admin-train'               => [TrainAdminController::class, 'indexAction'],
    'admin-train-trains'        => [TrainController::class, 'indexAction'],
    'admin-train-train-delete'  => [TrainController::class, 'deleteAction'],
    'admin-train-lignes'        => [TrainLigneController::class, 'indexAction'],
    'admin-train-ligne-delete'  => [TrainLigneController::class, 'deleteAction'],
    'admin-train-horaires'      => [TrainHoraireController::class, 'indexAction'],
    'admin-train-horaire-delete'=> [TrainHoraireController::class, 'deleteAction'],
    'admin-train-alertes'       => [TrainAlerteController::class, 'indexAction'],
    'admin-train-alerte-delete' => [TrainAlerteController::class, 'deleteAction'],

    // Louage
    'louage-recherche'           => [LouageController::class, 'rechercheAction'],
    'louage-resultats'           => [LouageController::class, 'resultatsAction'],
    'louage-details'             => [LouageController::class, 'detailsAction'],
    'louage-reservation'         => [LouageController::class, 'reservationAction'],
    'louage-stations'            => [LouageController::class, 'stationsAction'],
    'louage-suivi'               => [LouageController::class, 'suiviAction'],
    'louage-mes-reservations'    => [LouageController::class, 'mesReservationsAction'],
    'louage-reservation-annuler' => [LouageController::class, 'annulerAction'],

    'admin-louage'                     => [LouageAdminController::class, 'dashboardAction'],
    'admin-louage-vehicules'           => [LouageAdminController::class, 'vehiculesAction'],
    'admin-louage-vehicule-delete'     => [LouageAdminController::class, 'vehiculeDeleteAction'],
    'admin-louage-chauffeurs'          => [LouageAdminController::class, 'chauffeursAction'],
    'admin-louage-chauffeur-delete'    => [LouageAdminController::class, 'chauffeurDeleteAction'],
    'admin-louage-stations'            => [LouageAdminController::class, 'stationsAction'],
    'admin-louage-station-delete'      => [LouageAdminController::class, 'stationDeleteAction'],
    'admin-louage-trajets'             => [LouageAdminController::class, 'routesAction'],
    'admin-louage-trajet-delete'       => [LouageAdminController::class, 'routeDeleteAction'],
    'admin-louage-horaires'            => [LouageAdminController::class, 'horairesAction'],
    'admin-louage-horaire-delete'      => [LouageAdminController::class, 'horaireDeleteAction'],
    'admin-louage-reservations'        => [LouageAdminController::class, 'reservationsAction'],
    'admin-louage-reservation-statut'  => [LouageAdminController::class, 'reservationStatutAction'],
    'admin-louage-prix'                => [LouageAdminController::class, 'prixAction'],
    'admin-louage-prix-delete'         => [LouageAdminController::class, 'prixDeleteAction'],
    'admin-louage-reclamations'        => [LouageAdminController::class, 'reclamationsAction'],
    'admin-louage-reclamation-resoudre'=> [LouageAdminController::class, 'reclamationResoudreAction'],
    'admin-louage-notification'        => [LouageAdminController::class, 'notificationAction'],

    // Covoiturage
    'covoiturage'              => [CovoiturageController::class, 'indexAction'],
    'covoiturage-accepter'     => [CovoiturageController::class, 'accepterAction'],
    'covoiturage-annuler'      => [CovoiturageController::class, 'annulerAction'],
    'covoiturage-reservations' => [CovoiturageController::class, 'reservationsAction'],
];
