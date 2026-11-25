<?php
namespace App\Models;

/**
 * Base des modèles du module Covoiturage.
 *
 * Utilise la connexion PDO commune (Model::db()). Si la base "smartcity"
 * n'est pas encore importée ou si MySQL est arrêté, le module passe
 * automatiquement en "mode démo" (données en session) au lieu d'afficher
 * une erreur fatale.
 */
abstract class BaseModel extends Model {

    private static ?bool $dbDisponible = null;

    protected static function pdo(): ?\PDO {
        if (self::$dbDisponible === false) {
            return null;
        }
        try {
            $pdo = static::db();
            if (self::$dbDisponible === null) {
                $stmt = $pdo->query("SHOW TABLES LIKE 'trajet_covoiturage'");
                self::$dbDisponible = $stmt !== false && $stmt->fetchColumn() !== false;
            }
            return self::$dbDisponible ? $pdo : null;
        } catch (\Throwable $e) {
            error_log('[Urbix] Base de données indisponible : ' . $e->getMessage());
            self::$dbDisponible = false;
            return null;
        }
    }

    /** true si les données viennent de la session (base non connectée). */
    public static function modeDemo(): bool {
        return static::pdo() === null;
    }
}
