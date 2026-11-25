-- =====================================================================
-- Gestion Location : base de données du module (dans urbix_db)
-- Tables : location_vehicules, location_locations
-- À importer dans phpMyAdmin > base urbix_db > onglet Import.
-- Les tables sont reliées à la table users (comptes communs à toutes les gestions).
-- =====================================================================

USE urbix_db;
SET NAMES utf8mb4;

-- =====================================================================
-- LOCATION (moto, trottinette, vélo)
-- =====================================================================
CREATE TABLE IF NOT EXISTS location_vehicules (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(100) NOT NULL,
    type         ENUM('moto', 'trottinette', 'velo') NOT NULL,
    tarif_heure  DECIMAL(6,2) NOT NULL,
    autonomie    VARCHAR(10)  NOT NULL DEFAULT '100%',
    station      VARCHAR(100) NOT NULL,
    statut       ENUM('disponible', 'reserve', 'encours', 'maintenance') NOT NULL DEFAULT 'disponible',
    note_moyenne DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    avis_count   INT          NOT NULL DEFAULT 0,
    image        VARCHAR(100) NOT NULL DEFAULT 'urbix-moto.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS location_locations (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    reference      VARCHAR(20)  NOT NULL UNIQUE,
    user_id        INT          NULL,                     -- client (table users)
    vehicule_id    INT          NULL,
    vehicule_nom   VARCHAR(100) NOT NULL,
    vehicule_type  VARCHAR(20)  NOT NULL,
    station_depart VARCHAR(100) NOT NULL,
    station_retour VARCHAR(100) NOT NULL,
    duree_heures   INT          NOT NULL,
    montant        DECIMAL(8,2) NOT NULL,
    code_pin       CHAR(4)      NOT NULL,
    statut         ENUM('reserve', 'encours', 'termine') NOT NULL DEFAULT 'reserve',
    annulee        TINYINT(1)   NOT NULL DEFAULT 0,
    incident       VARCHAR(255) NULL,
    note_donnee    TINYINT      NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mobilite_loc_user     FOREIGN KEY (user_id)     REFERENCES users(id)              ON DELETE CASCADE,
    CONSTRAINT fk_mobilite_loc_vehicule FOREIGN KEY (vehicule_id) REFERENCES location_vehicules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO location_vehicules (nom, type, tarif_heure, autonomie, station, statut, note_moyenne, avis_count, image)
SELECT * FROM (
    SELECT 'Moto Urbix X125' AS nom, 'moto' AS type, 12 AS tarif_heure, '88%' AS autonomie, 'Station Centre Ville' AS station, 'disponible' AS statut, 4.8 AS note_moyenne, 14 AS avis_count, 'urbix-moto.png' AS image
    UNION ALL SELECT 'Moto VoltRoute 250', 'moto', 15, '94%', 'Station Parc Urbain', 'disponible', 4.7, 11, 'voltrider.png'
    UNION ALL SELECT 'Trottinette ScootCity Pro', 'trottinette', 4, '92%', 'Station Campus Nord', 'disponible', 4.6, 22, 'scootcity.png'
    UNION ALL SELECT 'Trottinette GlideMax', 'trottinette', 5, '76%', 'Station Gare Centrale', 'disponible', 4.5, 16, 'glidemax.png'
    UNION ALL SELECT 'Vélo Urbain V-Assist', 'velo', 5, '81%', 'Station Gare Centrale', 'disponible', 4.9, 19, 'velo-vassist.png'
    UNION ALL SELECT 'Vélo EcoRide City', 'velo', 3, '100%', 'Station Centre Ville', 'disponible', 4.8, 27, 'ecoride.png'
) AS depart
WHERE NOT EXISTS (SELECT 1 FROM location_vehicules);
