<?php
namespace App\Controllers;

abstract class Controller {
    protected string $page      = '';
    protected string $title     = 'Smart City';
    protected string $bodyClass = '';

    /**
     * Affiche une vue de Views/pages dans un layout.
     * $layout : 'layout' (frontoffice) ou 'layout_admin' (backoffice).
     */
    protected function render(string $view, array $data = [], string $layout = 'layout'): void {
        extract($data);
        $currentPage = $this->page;
        $pageTitle   = $this->title;
        $bodyClass   = $this->bodyClass;

        // Données communes à toutes les pages (navbar, popup de connexion)
        $isLoggedIn   = AuthController::isAuthenticated();
        $currentRole  = $_SESSION['role'] ?? null;
        $currentTypeService = $_SESSION['type_service'] ?? null;
        // Super admin = admin qui gère tout ; adminBus (admin_scope 'bus') ne voit que la gestion des bus
        $isSuperAdmin = $currentRole === 'admin' && ($_SESSION['admin_scope'] ?? 'tout') === 'tout';
        $csrfToken    = AuthController::getCsrfToken();
        $genres       = AuthController::GENRES;
        $typesService = AuthController::TYPES_SERVICE;

        // Message "flash" de la popup (erreurs, succès...) : lu une seule fois puis supprimé
        $authPopup = $_SESSION['auth_popup'] ?? null;
        unset($_SESSION['auth_popup']);

        ob_start();
        require __DIR__ . "/../Views/pages/$view.php";
        $content = ob_get_clean();

        require __DIR__ . "/../Views/$layout.php";
    }

    /**
     * Redirige vers index.php?page=... puis arrête le script.
     */
    protected static function redirect(string $page, array $query = []): void {
        header('Location: ' . BASE_URL . 'index.php?' . http_build_query(['page' => $page] + $query));
        exit;
    }
}
