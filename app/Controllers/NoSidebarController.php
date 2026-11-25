<?php
namespace App\Controllers;

class NoSidebarController extends Controller {
    protected string $page      = 'no-sidebar';
    protected string $title     = 'No Sidebar | Smart City';
    protected string $bodyClass = 'no-sidebar is-preload';

    public function index(): void {
        $this->render('no-sidebar');
    }
}