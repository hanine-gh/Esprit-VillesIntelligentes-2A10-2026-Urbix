<?php
use App\Controllers\HomeController;
use App\Controllers\LeftSidebarController;
use App\Controllers\RightSidebarController;
use App\Controllers\NoSidebarController;
use App\Controllers\AuthController;
use App\Controllers\DriverController;
use App\Controllers\TrajetController;
use App\Controllers\StationController;
use App\Controllers\ChauffeurController;
use App\Controllers\BusController;
use App\Controllers\VoyageurController;

return [
    'home'          => [HomeController::class, 'index'],
    'left-sidebar'  => [LeftSidebarController::class, 'index'],
    'right-sidebar' => [RightSidebarController::class, 'index'],
    'no-sidebar'    => [NoSidebarController::class, 'index'],

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
];
