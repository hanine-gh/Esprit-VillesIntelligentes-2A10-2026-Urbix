<?php
// Exemple à intégrer dans ton app/routes.php selon le routeur du projet :
// 'mobilite'            => ['MobiliteController', 'index'],
// 'backoffice-mobilite' => ['MobiliteController', 'backoffice'],
// 'mobilite-reserver'   => ['MobiliteController', 'reserver'],
// 'mobilite-demarrer'   => ['MobiliteController', 'demarrer'],
// 'mobilite-prolonger'  => ['MobiliteController', 'prolonger'],
// 'mobilite-incident'   => ['MobiliteController', 'incident'],
// 'mobilite-terminer'   => ['MobiliteController', 'terminer'],
// 'mobilite-annuler'    => ['MobiliteController', 'annuler'],

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'mobilite':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->index();
        break;
    case 'backoffice-mobilite':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->backoffice();
        break;
    case 'mobilite-reserver':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->reserver();
        break;
    case 'mobilite-demarrer':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->demarrer();
        break;
    case 'mobilite-prolonger':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->prolonger();
        break;
    case 'mobilite-incident':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->incident();
        break;
    case 'mobilite-terminer':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->terminer();
        break;
    case 'mobilite-annuler':
        require_once __DIR__ . '/Controllers/MobiliteController.php';
        (new MobiliteController())->annuler();
        break;
}