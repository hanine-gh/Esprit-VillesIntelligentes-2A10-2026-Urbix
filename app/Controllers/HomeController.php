<?php
namespace App\Controllers;

class HomeController extends Controller {
    protected string $page      = 'home';
    protected string $title     = 'Accueil | Urbix Smart City';
    protected string $bodyClass = 'homepage is-preload';

    public function index(): void {
        $this->render('home');
    }
}