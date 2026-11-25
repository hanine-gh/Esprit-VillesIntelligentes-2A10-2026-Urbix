-- =====================================================================
-- Gestion Louage : base de données du module (dans urbix_db)
-- Tables : louage_stations, louage_chauffeurs, louage_vehicules, louage_routes, louage_horaires,
--          louage_reservations, louage_reclamations, louage_notifications
-- À importer dans phpMyAdmin > base urbix_db > onglet Import.
-- Les tables sont reliées à la table users (comptes communs à toutes les gestions).
-- =====================================================================

USE urbix_db;
SET NAMES utf8mb4;

-- =====================================================================
-- LOUAGE
-- =====================================================================
CREATE TABLE IF NOT EXISTS louage_stations (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nom       VARCHAR(80)  NOT NULL,
    ville     VARCHAR(60)  NOT NULL,
    adresse   VARCHAR(150) NULL,
    latitude  DECIMAL(9,6) NULL,
    longitude DECIMAL(9,6) NULL,
    UNIQUE KEY uq_station (nom, ville)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS louage_chauffeurs (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nom       VARCHAR(80) NOT NULL,
    telephone VARCHAR(20) NULL,
    permis    VARCHAR(30) NULL UNIQUE,
    statut    ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS louage_vehicules (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    immatriculation VARCHAR(20) NOT NULL UNIQUE,
    modele          VARCHAR(60) NOT NULL,
    capacite        INT         NOT NULL,
    chauffeur_id    INT         NULL,
    statut          ENUM('Available', 'In service', 'Unavailable') NOT NULL DEFAULT 'Available',
    CONSTRAINT fk_louage_vehicule_chauffeur FOREIGN KEY (chauffeur_id) REFERENCES louage_chauffeurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS louage_routes (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    station_depart_id  INT          NOT NULL,
    station_arrivee_id INT          NOT NULL,
    prix               DECIMAL(7,3) NULL,
    UNIQUE KEY uq_route (station_depart_id, station_arrivee_id),
    CONSTRAINT fk_louage_route_depart  FOREIGN KEY (station_depart_id)  REFERENCES louage_stations(id) ON DELETE RESTRICT,
    CONSTRAINT fk_louage_route_arrivee FOREIGN KEY (station_arrivee_id) REFERENCES louage_stations(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS louage_horaires (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    route_id    INT      NOT NULL,
    vehicule_id INT      NULL,
    depart_at   DATETIME NOT NULL,
    arrivee_at  DATETIME NULL,
    CONSTRAINT fk_louage_horaire_route    FOREIGN KEY (route_id)    REFERENCES louage_routes(id)    ON DELETE RESTRICT,
    CONSTRAINT fk_louage_horaire_vehicule FOREIGN KEY (vehicule_id) REFERENCES louage_vehicules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Le client est maintenant un compte de la table users
CREATE TABLE IF NOT EXISTS louage_reservations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    horaire_id INT         NOT NULL,
    user_id    INT         NULL,
    passager   VARCHAR(80) NOT NULL,
    telephone  VARCHAR(20) NOT NULL,
    places     INT         NOT NULL,
    statut     ENUM('Pending', 'Confirmed', 'Cancellation requested', 'Cancelled') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_louage_resa_horaire FOREIGN KEY (horaire_id) REFERENCES louage_horaires(id) ON DELETE CASCADE,
    CONSTRAINT fk_louage_resa_user    FOREIGN KEY (user_id)    REFERENCES users(id)           ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS louage_reclamations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    passager   VARCHAR(80)   NOT NULL,
    sujet      VARCHAR(100)  NOT NULL,
    message    VARCHAR(1000) NOT NULL,
    statut     ENUM('Open', 'Resolved') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS louage_notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    message    VARCHAR(1000) NOT NULL,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO louage_stations (nom, ville, adresse)
SELECT * FROM (
    SELECT 'Bab Alioua' AS nom, 'Tunis' AS ville, 'Bab Alioua, Tunis' AS adresse
    UNION ALL SELECT 'Louage Station', 'Sousse', NULL
    UNION ALL SELECT 'Louage Station', 'Sfax', NULL
    UNION ALL SELECT 'Louage Station', 'Nabeul', NULL
    UNION ALL SELECT 'Louage Station', 'Monastir', NULL
    UNION ALL SELECT 'Louage Station', 'Bizerte', NULL
    UNION ALL SELECT 'Louage Station', 'Kairouan', NULL
    UNION ALL SELECT 'Louage Station', 'Gabes', NULL
) AS s
WHERE NOT EXISTS (SELECT 1 FROM louage_stations);

INSERT INTO louage_chauffeurs (nom, telephone, permis, statut)
SELECT 'Mohamed Ali', '20123456', 'TN-458712', 'Active'
WHERE NOT EXISTS (SELECT 1 FROM louage_chauffeurs);

INSERT INTO louage_vehicules (immatriculation, modele, capacite, chauffeur_id, statut)
SELECT '123 TU 4567', 'Fiat Ducato', 8, c.id, 'Available'
FROM louage_chauffeurs c
WHERE c.nom = 'Mohamed Ali' AND NOT EXISTS (SELECT 1 FROM louage_vehicules);

INSERT INTO louage_routes (station_depart_id, station_arrivee_id, prix)
SELECT d.id, a.id, r.prix
FROM (
    SELECT 'Tunis' AS dep, 'Sousse' AS arr, 15 AS prix
    UNION ALL SELECT 'Sousse', 'Monastir', 4
    UNION ALL SELECT 'Tunis', 'Nabeul', 12
) AS r
JOIN louage_stations d ON d.ville = r.dep
JOIN louage_stations a ON a.ville = r.arr
WHERE NOT EXISTS (SELECT 1 FROM louage_routes);

INSERT INTO louage_horaires (route_id, vehicule_id, depart_at, arrivee_at)
SELECT r.id, v.id, TIMESTAMP(CURDATE() + INTERVAL 1 DAY, h.dep), TIMESTAMP(CURDATE() + INTERVAL 1 DAY, h.arr)
FROM (
    SELECT 'Tunis' AS d, 'Sousse' AS a, '09:30:00' AS dep, '11:30:00' AS arr
    UNION ALL SELECT 'Tunis', 'Nabeul', '11:00:00', '12:15:00'
) AS h
JOIN louage_stations sd ON sd.ville = h.d
JOIN louage_stations sa ON sa.ville = h.a
JOIN louage_routes r ON r.station_depart_id = sd.id AND r.station_arrivee_id = sa.id
JOIN louage_vehicules v ON v.immatriculation = '123 TU 4567'
WHERE NOT EXISTS (SELECT 1 FROM louage_horaires);

INSERT INTO louage_reclamations (passager, sujet, message, statut)
SELECT 'Example passenger', 'Delay', 'Example complaint message', 'Open'
WHERE NOT EXISTS (SELECT 1 FROM louage_reclamations);
