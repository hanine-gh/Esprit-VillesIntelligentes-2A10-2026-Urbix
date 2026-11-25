<?php
use App\Controllers\HomeController;
use App\Controllers\LeftSidebarController;
use App\Controllers\RightSidebarController;
use App\Controllers\NoSidebarController;
use App\Controllers\CovoiturageController;
use App\Controllers\UtilisateurController;
use App\Controllers\ModuleController;

return [
    'home'          => [HomeController::class, 'index'],
    'left-sidebar'  => [LeftSidebarController::class, 'index'],
    'right-sidebar' => [RightSidebarController::class, 'index'],
    'no-sidebar'    => [NoSidebarController::class, 'index'],

    // Module Covoiturage
    'covoiturage'           => [CovoiturageController::class, 'index'],
    'covoiturage-accepter'  => [CovoiturageController::class, 'accepter'],
    'covoiturage-annuler'   => [CovoiturageController::class, 'annuler'],

    // Interface utilisateur (icône à droite)
    'espace-utilisateur'    => [UtilisateurController::class, 'index'],
    'connexion'             => [UtilisateurController::class, 'connexion'],
    'inscription'           => [UtilisateurController::class, 'inscription'],
    'deconnexion'           => [UtilisateurController::class, 'deconnexion'],

    // Modules des autres membres du groupe (bus, train, louage, location, réparation...)
    'module'                => [ModuleController::class, 'index'],
];
