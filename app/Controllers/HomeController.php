<?php
namespace App\Controllers;

class HomeController extends Controller {
    protected string $page      = 'home';
    protected string $title     = 'Home | Smart City';
    protected string $bodyClass = 'homepage is-preload';

    public function index(): void {
        $this->render('home');
    }
}