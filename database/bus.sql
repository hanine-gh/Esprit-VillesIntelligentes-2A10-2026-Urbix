-- =====================================================================
-- Gestion Bus : base de données du module (dans urbix_db)
-- Tables : trajets, stations, buses, demandes_stop (ordre respecté : trajets/stations, puis buses, puis demandes_stop)
-- À importer dans phpMyAdmin > base urbix_db > onglet Import.
-- Les tables sont reliées à la table users (comptes communs à toutes les gestions).
-- =====================================================================

USE urbix_db;
SET NAMES utf8mb4;

-- ---------- trajets_stations ----------
-- Un trajet = une ligne de bus avec ses horaires, attribuée à un chauffeur
CREATE TABLE IF NOT EXISTS trajets (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(20)  NOT NULL UNIQUE,          -- ex : L12
    nom           VARCHAR(100) NOT NULL,                 -- ex : Ligne Centre - Aéroport
    point_depart  VARCHAR(100) NOT NULL,
    point_arrivee VARCHAR(100) NOT NULL,
    heure_depart  TIME         NOT NULL,
    heure_arrivee TIME         NOT NULL,
    statut        ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif',
    chauffeur_id  INT          NULL,                     -- NULL = non attribué
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trajets_chauffeur
        FOREIGN KEY (chauffeur_id) REFERENCES users(id)
        ON DELETE SET NULL                               -- chauffeur supprimé => trajet non attribué
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Une station appartient à un trajet ; "ordre" = position sur le trajet
CREATE TABLE IF NOT EXISTS stations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id     INT          NOT NULL,
    nom           VARCHAR(100) NOT NULL,
    adresse       VARCHAR(255) NOT NULL,
    ordre         INT          NOT NULL,
    heure_passage TIME         NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_stations_trajet_ordre UNIQUE (trajet_id, ordre),
    CONSTRAINT fk_stations_trajet
        FOREIGN KEY (trajet_id) REFERENCES trajets(id)
        ON DELETE CASCADE                                -- trajet supprimé => ses stations aussi
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- buses ----------
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

-- ---------- demandes_stop ----------
CREATE TABLE IF NOT EXISTS demandes_stop (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,                  -- le voyageur qui demande
    trajet_id    INT          NOT NULL,                  -- le trajet de bus
    station_id   INT          NOT NULL,                  -- la station où le bus doit s'arrêter
    date_trajet  DATE         NOT NULL,                  -- jour du voyage
    nb_personnes TINYINT      NOT NULL DEFAULT 1,
    message      VARCHAR(255) NULL,
    statut       ENUM('en_attente', 'acceptee', 'refusee') NOT NULL DEFAULT 'en_attente',
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_demandes_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_demandes_trajet  FOREIGN KEY (trajet_id)  REFERENCES trajets(id)  ON DELETE CASCADE,
    CONSTRAINT fk_demandes_station FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
