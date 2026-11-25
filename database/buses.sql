-- =====================================================
-- Bus de la flotte + attribution à un chauffeur (bus attitré) ou à un trajet
-- À exécuter dans phpMyAdmin > base urbix_db > onglet SQL (après trajets_stations.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS buses (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    matricule    VARCHAR(20)  NOT NULL UNIQUE,            -- ex : 123 TU 4567
    marque       VARCHAR(50)  NOT NULL,                   -- ex : Mercedes
    modele       VARCHAR(50)  NOT NULL,                   -- ex : Citaro
    capacite     INT          NOT NULL,                   -- nombre de places
    annee        SMALLINT     NOT NULL,                   -- année de mise en service
    statut       ENUM('disponible', 'maintenance', 'hors_service') NOT NULL DEFAULT 'disponible',
    chauffeur_id INT          NULL UNIQUE,                -- chauffeur attitré (1 bus max par chauffeur)
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_buses_chauffeur
        FOREIGN KEY (chauffeur_id) REFERENCES users(id)
        ON DELETE SET NULL                                -- chauffeur supprimé => bus sans chauffeur attitré
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bus utilisé pour effectuer un trajet (mission = trajet + chauffeur + bus)
ALTER TABLE trajets
    ADD COLUMN IF NOT EXISTS bus_id INT NULL AFTER chauffeur_id;

ALTER TABLE trajets
    ADD CONSTRAINT fk_trajets_bus
        FOREIGN KEY IF NOT EXISTS (bus_id) REFERENCES buses(id)
        ON DELETE SET NULL;                               -- bus supprimé => trajet sans bus

-- État du bus signalé par son chauffeur attitré ("Cas de mon bus")
ALTER TABLE buses
    ADD COLUMN IF NOT EXISTS etat_commentaire VARCHAR(255) NULL AFTER statut,   -- description du problème
    ADD COLUMN IF NOT EXISTS etat_maj_at      DATETIME     NULL AFTER etat_commentaire;  -- date du dernier signalement
