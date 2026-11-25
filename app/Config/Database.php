<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database : centralise la connexion à la base de données (patron Singleton).
 * Une seule connexion PDO est créée pour toute la durée de la requête.
 * Utilisation : Database::getConnection()
 */
final class Database
{
    private const HOST     = 'localhost';
    private const DB_NAME  = 'urbix_db';
    private const USER     = 'root';
    private const PASSWORD = '';

    private static ?Database $instance = null;
    private PDO $pdo;

    // Constructeur privé : impossible de faire "new Database()" depuis l'extérieur
    private function __construct()
    {
        $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';

        try {
            $this->pdo = new PDO($dsn, self::USER, self::PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs SQL = exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                  // vraies requêtes préparées
                // Mode strict : une valeur invalide (ex : hors ENUM) déclenche une erreur
                // au lieu d'être enregistrée silencieusement comme une chaîne vide
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ENGINE_SUBSTITUTION'",
            ]);
        } catch (PDOException $e) {
            // On n'affiche jamais le détail technique à l'utilisateur
            error_log('Database connection error: ' . $e->getMessage());
            http_response_code(500);
            exit('Erreur de connexion à la base de données.');
        }
    }

    // Empêche la copie de l'instance
    private function __clone() {}

    // Empêche la désérialisation de l'instance
    public function __wakeup()
    {
        throw new \LogicException('Cannot unserialize a singleton.');
    }

    /**
     * Retourne l'unique connexion PDO (créée au premier appel).
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
