<?php
// URL de base détectée automatiquement : le projet fonctionne quel que soit
// le nom du dossier dans htdocs (ex. /ProjetSmartCity/, /salma/, ...).
$baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$baseDir = rtrim($baseDir, '/') . '/';

define('BASE_URL', $baseDir);
define('ASSETS_URL', BASE_URL . 'app/Views/');

// Backoffice intégré dans le même dossier
define('BACKOFFICE_URL', BASE_URL . 'backoffice/');
