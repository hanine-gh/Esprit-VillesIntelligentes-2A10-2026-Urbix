-- =====================================================================
-- Gestion Utilisateurs : création de la base urbix_db + table users
-- À IMPORTER EN PREMIER (avant bus.sql, location.sql, ...), car toutes
-- les gestions sont reliées à la table users.
-- phpMyAdmin > onglet Import (sans sélectionner de base) > users.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS urbix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE urbix_db;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id             INT(11)      NOT NULL AUTO_INCREMENT,
    nom            VARCHAR(50)  NOT NULL,
    prenom         VARCHAR(50)  NOT NULL,
    email          VARCHAR(150) NOT NULL,
    password       VARCHAR(255) NOT NULL,                 -- haché avec password_hash()
    date_naissance DATE         NOT NULL,
    genre          ENUM('homme','femme') NOT NULL,
    telephone      VARCHAR(20)  NOT NULL,
    type_service   ENUM('utilisateur_normal','chauffeur_bus','chauffeur_train','louage','covoiturage','location','reparation_vehicule')
                   NOT NULL DEFAULT 'utilisateur_normal',
    role           ENUM('user','admin') NOT NULL DEFAULT 'user',
    admin_scope    ENUM('tout','bus')   NOT NULL DEFAULT 'tout', -- admin : tout gérer ou seulement le bus
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Compte voyageur de démonstration (mot de passe : Urbix2026)
INSERT IGNORE INTO users (nom, prenom, email, password, date_naissance, genre, telephone, type_service, role)
VALUES ('Ben Ali', 'Salma', 'salma@urbix.tn', '$2y$10$5xvyP0TKCLW5KYwSJeULd.8e/xKmpaGbyfEQW/WAh5YuyYOHBZjle', '2002-05-14', 'femme', '22123456', 'utilisateur_normal', 'user');
