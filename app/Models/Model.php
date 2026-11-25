<?php
namespace App\Models;

abstract class Model {
    protected static function db(): \PDO {
        static $pdo = null;
        if ($pdo === null) {
            $pdo = new \PDO(
                'mysql:host=localhost;dbname=smartcity;charset=utf8mb4',
                'root', '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        }
        return $pdo;
    }
}