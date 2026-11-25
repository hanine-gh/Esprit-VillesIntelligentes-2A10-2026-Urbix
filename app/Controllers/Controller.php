<?php
namespace App\Controllers;

abstract class Controller {
    protected string $page      = '';
    protected string $title     = 'Smart City';
    protected string $bodyClass = '';

    protected function render(string $view, array $data = []): void {
        extract($data);
        $currentPage = $this->page;
        $pageTitle   = $this->title;
        $bodyClass   = $this->bodyClass;

        ob_start();
        require __DIR__ . "/../Views/pages/$view.php";
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layout.php';
    }
}