-- =====================================================
-- Trajets de bus (attribués à un chauffeur) et leurs stations
-- À exécuter dans phpMyAdmin > base urbix_db > onglet SQL
-- =====================================================

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
