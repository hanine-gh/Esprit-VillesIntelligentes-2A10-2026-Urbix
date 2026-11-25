<?php
namespace App\Controllers;

class RightSidebarController extends Controller {
    protected string $page      = 'right-sidebar';
    protected string $title     = 'Right Sidebar | Smart City';
    protected string $bodyClass = 'right-sidebar is-preload';

    public function index(): void {
        $this->render('right-sidebar');
    }
}