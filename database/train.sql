-- =====================================================================
-- Gestion Train : base de données du module (dans urbix_db)
-- Tables : train_lignes, train_trains, train_horaires, train_alertes
-- À importer dans phpMyAdmin > base urbix_db > onglet Import.
-- Les tables sont reliées à la table users (comptes communs à toutes les gestions).
-- =====================================================================

USE urbix_db;
SET NAMES utf8mb4;

-- =====================================================================
-- TRAIN
-- =====================================================================
CREATE TABLE IF NOT EXISTS train_lignes (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    nom                     VARCHAR(50)  NOT NULL UNIQUE,
    station_depart          VARCHAR(60)  NOT NULL,
    station_arrivee         VARCHAR(60)  NOT NULL,
    stations_intermediaires VARCHAR(255) NOT NULL DEFAULT '',
    distance                DECIMAL(6,1) NULL,
    etat                    ENUM('En service', 'Perturbée', 'Interrompue') NOT NULL DEFAULT 'En service'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS train_trains (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    numero        VARCHAR(10) NOT NULL UNIQUE,
    station_debut VARCHAR(60) NOT NULL,
    capacite      INT         NOT NULL,
    ligne_id      INT         NOT NULL,
    etat          ENUM('En circulation', 'En maintenance', 'Indisponible') NOT NULL DEFAULT 'En circulation',
    CONSTRAINT fk_train_ligne FOREIGN KEY (ligne_id) REFERENCES train_lignes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS train_horaires (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    train_id        INT          NOT NULL,
    ligne_id        INT          NOT NULL,
    station_depart  VARCHAR(60)  NOT NULL,
    heure_depart    TIME         NOT NULL,
    station_arrivee VARCHAR(60)  NOT NULL,
    heure_arrivee   TIME         NOT NULL,
    date_trajet     DATE         NOT NULL,
    etat            ENUM('A l''heure', 'En retard', 'Annulé') NOT NULL DEFAULT 'A l''heure',
    prix            DECIMAL(7,3) NULL,
    CONSTRAINT fk_horaire_train FOREIGN KEY (train_id) REFERENCES train_trains(id) ON DELETE CASCADE,
    CONSTRAINT fk_horaire_ligne FOREIGN KEY (ligne_id) REFERENCES train_lignes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS train_alertes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    type         ENUM('Retard', 'Annulation', 'Interruption', 'Changement') NOT NULL,
    train_id     INT          NULL,
    ligne_id     INT          NOT NULL,
    station      VARCHAR(60)  NULL,
    duree_retard INT          NULL,
    date_alerte  DATE         NOT NULL,
    heure_alerte TIME         NULL,
    description  VARCHAR(500) NOT NULL DEFAULT '',
    etat         ENUM('Active', 'Résolue') NOT NULL DEFAULT 'Active',
    CONSTRAINT fk_alerte_train FOREIGN KEY (train_id) REFERENCES train_trains(id) ON DELETE SET NULL,
    CONSTRAINT fk_alerte_ligne FOREIGN KEY (ligne_id) REFERENCES train_lignes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO train_lignes (nom, station_depart, station_arrivee, stations_intermediaires, distance, etat)
SELECT * FROM (
    SELECT 'Ligne A' AS nom, 'Ariana' AS d, 'Centre Ville' AS a, 'Cité El Khadra' AS s, 9.5 AS km, 'En service' AS e
    UNION ALL SELECT 'Ligne B', 'Centre Ville', 'Le Bardo', 'Bab Saadoun', 7.0, 'En service'
    UNION ALL SELECT 'Ligne C', 'Bab Saadoun', 'Cité El Khadra', '', 5.5, 'En service'
) AS depart
WHERE NOT EXISTS (SELECT 1 FROM train_lignes);

INSERT INTO train_trains (numero, station_debut, capacite, ligne_id, etat)
SELECT t.numero, t.station, t.capacite, l.id, t.etat
FROM (
    SELECT '102' AS numero, 'Ariana' AS station, 300 AS capacite, 'Ligne A' AS ligne, 'En circulation' AS etat
    UNION ALL SELECT '105', 'Centre Ville', 250, 'Ligne B', 'En circulation'
    UNION ALL SELECT '110', 'Le Bardo', 300, 'Ligne C', 'En circulation'
    UNION ALL SELECT '115', 'Bab Saadoun', 200, 'Ligne A', 'Indisponible'
) AS t
JOIN train_lignes l ON l.nom = t.ligne
WHERE NOT EXISTS (SELECT 1 FROM train_trains)
ORDER BY t.numero;

INSERT INTO train_horaires (train_id, ligne_id, station_depart, heure_depart, station_arrivee, heure_arrivee, date_trajet, etat, prix)
SELECT tr.id, tr.ligne_id, h.dep, h.hd, h.arr, h.ha, CURDATE(), h.etat, h.prix
FROM (
    SELECT '102' AS numero, 'Ariana' AS dep, '08:00:00' AS hd, 'Centre Ville' AS arr, '08:25:00' AS ha, 'A l''heure' AS etat, 1.200 AS prix
    UNION ALL SELECT '102', 'Centre Ville', '17:30:00', 'Ariana', '17:55:00', 'A l''heure', 1.200
    UNION ALL SELECT '105', 'Centre Ville', '09:10:00', 'Le Bardo', '09:30:00', 'En retard', 0.900
    UNION ALL SELECT '110', 'Bab Saadoun', '10:00:00', 'Cité El Khadra', '10:15:00', 'A l''heure', 0.700
) AS h
JOIN train_trains tr ON tr.numero = h.numero
WHERE NOT EXISTS (SELECT 1 FROM train_horaires);

INSERT INTO train_alertes (type, train_id, ligne_id, station, duree_retard, date_alerte, heure_alerte, description, etat)
SELECT 'Retard', tr.id, tr.ligne_id, 'Centre Ville', 10, CURDATE(), '09:05:00', 'Retard dû à un incident technique.', 'Active'
FROM train_trains tr
WHERE tr.numero = '105' AND NOT EXISTS (SELECT 1 FROM train_alertes);
