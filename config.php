<?php
define('BASE_URL', '/ProjetSmartCity/');
define('ASSETS_URL', BASE_URL . 'app/Views/');

// Fichiers du template d'administration (Mazer) utilisé par le backoffice
define('ADMIN_ASSETS_URL', BASE_URL . 'app/Views/admin/assets/');

// Le backoffice est intégré à l'application : index.php?page=admin
// (protégé côté serveur par AuthController::requireRole('admin'))
