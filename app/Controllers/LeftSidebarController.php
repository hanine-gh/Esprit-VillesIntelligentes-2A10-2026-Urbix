<?php
namespace App\Controllers;

class LeftSidebarController extends Controller {
    protected string $page      = 'left-sidebar';
    protected string $title     = 'Left Sidebar | Smart City';
    protected string $bodyClass = 'left-sidebar is-preload';

    public function index(): void {
        $this->render('left-sidebar');
    }
}