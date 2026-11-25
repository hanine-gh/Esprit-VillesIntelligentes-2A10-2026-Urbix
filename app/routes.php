<?php
use App\Controllers\HomeController;
use App\Controllers\LeftSidebarController;
use App\Controllers\RightSidebarController;
use App\Controllers\NoSidebarController;

return [
    'home'          => [HomeController::class, 'index'],
    'left-sidebar'  => [LeftSidebarController::class, 'index'],
    'right-sidebar' => [RightSidebarController::class, 'index'],
    'no-sidebar'    => [NoSidebarController::class, 'index'],
];