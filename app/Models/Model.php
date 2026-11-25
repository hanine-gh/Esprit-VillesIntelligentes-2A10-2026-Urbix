<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Classe de base des Models.
 * Règle MVC du projet : un Model ne contient QUE des attributs privés
 * + getters/setters. Aucune requête SQL, aucune connexion à la base
 * (la connexion est dans App\Config\Database, les requêtes dans les Controllers).
 */
abstract class Model
{
}
