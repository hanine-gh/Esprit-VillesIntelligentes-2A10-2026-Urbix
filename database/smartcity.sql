-- =====================================================================
--  Urbix – Plateforme de mobilité urbaine intelligente
--  Base de données : smartcity  (MySQL / MariaDB – phpMyAdmin / XAMPP)
--
--  Import : phpMyAdmin > Importer > choisir ce fichier > Exécuter
--  Contient les entités de TOUS les modules du groupe :
--    - Utilisateurs & véhicules        (commun)
--    - Bus urbain / Bus à la demande   (stations, lignes, horaires, missions, incidents)
--    - Métro & Train
--    - Louage
--    - Location de véhicules
--    - Réparation de véhicules
--    - Covoiturage                     (trajets, réservations, avis)
-- =====================================================================

DROP DATABASE IF EXISTS smartcity;
CREATE DATABASE smartcity CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartcity;

-- ---------------------------------------------------------------------
-- 1. COMMUN : utilisateur & véhicule
-- ---------------------------------------------------------------------
CREATE TABLE utilisateur (
    id_utilisateur   INT AUTO_INCREMENT PRIMARY KEY,
    nom              VARCHAR(60)  NOT NULL,
    prenom           VARCHAR(60)  NOT NULL,
    email            VARCHAR(120) NOT NULL UNIQUE,
    mot_de_passe     VARCHAR(255) NOT NULL,
    date_naissance   DATE         NULL,
    genre            ENUM('homme','femme','autre') NULL,
    telephone        VARCHAR(20)  NULL,
    role             ENUM('user-normal','chauffeur-bus','chauffeur-train','louage','covoiturage','location','reparation-vehicule','admin')
                     NOT NULL DEFAULT 'user-normal',
    date_inscription DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vehicule (
    id_vehicule      INT AUTO_INCREMENT PRIMARY KEY,
    id_proprietaire  INT          NULL,
    type             ENUM('voiture','bus','train','louage','location') NOT NULL DEFAULT 'voiture',
    marque           VARCHAR(60)  NOT NULL,
    modele           VARCHAR(60)  NOT NULL,
    couleur          VARCHAR(30)  NULL,
    immatriculation  VARCHAR(30)  NOT NULL UNIQUE,
    nb_places        INT          NOT NULL DEFAULT 4,
    climatisation    TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_vehicule_proprio FOREIGN KEY (id_proprietaire)
        REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. BUS URBAIN & BUS À LA DEMANDE
-- ---------------------------------------------------------------------
CREATE TABLE station (
    id_station  INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(100) NOT NULL,
    ville       VARCHAR(60)  NOT NULL,
    adresse     VARCHAR(150) NULL,
    latitude    DECIMAL(10,7) NULL,
    longitude   DECIMAL(10,7) NULL,
    type        ENUM('bus','metro','train','louage') NOT NULL DEFAULT 'bus'
) ENGINE=InnoDB;

CREATE TABLE ligne_bus (
    id_ligne          INT AUTO_INCREMENT PRIMARY KEY,
    numero            VARCHAR(10)  NOT NULL,
    nom               VARCHAR(100) NOT NULL,
    id_station_depart INT NOT NULL,
    id_station_arrivee INT NOT NULL,
    CONSTRAINT fk_ligne_dep FOREIGN KEY (id_station_depart)  REFERENCES station(id_station),
    CONSTRAINT fk_ligne_arr FOREIGN KEY (id_station_arrivee) REFERENCES station(id_station)
) ENGINE=InnoDB;

CREATE TABLE bus (
    id_bus       INT AUTO_INCREMENT PRIMARY KEY,
    id_vehicule  INT NOT NULL,
    id_ligne     INT NULL,
    id_chauffeur INT NULL,
    a_la_demande TINYINT(1) NOT NULL DEFAULT 0,
    etat         ENUM('en_service','en_panne','maintenance','hors_service') NOT NULL DEFAULT 'en_service',
    latitude     DECIMAL(10,7) NULL,
    longitude    DECIMAL(10,7) NULL,
    CONSTRAINT fk_bus_vehicule  FOREIGN KEY (id_vehicule)  REFERENCES vehicule(id_vehicule) ON DELETE CASCADE,
    CONSTRAINT fk_bus_ligne     FOREIGN KEY (id_ligne)     REFERENCES ligne_bus(id_ligne) ON DELETE SET NULL,
    CONSTRAINT fk_bus_chauffeur FOREIGN KEY (id_chauffeur) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE horaire_bus (
    id_horaire     INT AUTO_INCREMENT PRIMARY KEY,
    id_ligne       INT  NOT NULL,
    id_station     INT  NOT NULL,
    heure_passage  TIME NOT NULL,
    CONSTRAINT fk_horaire_ligne   FOREIGN KEY (id_ligne)   REFERENCES ligne_bus(id_ligne) ON DELETE CASCADE,
    CONSTRAINT fk_horaire_station FOREIGN KEY (id_station) REFERENCES station(id_station) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Espace chauffeur : missions
CREATE TABLE mission (
    id_mission   INT AUTO_INCREMENT PRIMARY KEY,
    id_chauffeur INT  NOT NULL,
    id_bus       INT  NOT NULL,
    date_mission DATE NOT NULL,
    description  TEXT NULL,
    statut       ENUM('planifiee','en_cours','terminee','annulee') NOT NULL DEFAULT 'planifiee',
    CONSTRAINT fk_mission_chauffeur FOREIGN KEY (id_chauffeur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_mission_bus       FOREIGN KEY (id_bus)       REFERENCES bus(id_bus) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Espace chauffeur : demandes de passagers (bus à la demande / prise en charge à domicile)
CREATE TABLE demande_passager (
    id_demande          INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur      INT NOT NULL,
    id_bus              INT NULL,
    adresse_depart      VARCHAR(150) NOT NULL,
    destination         VARCHAR(150) NOT NULL,
    date_heure_souhaitee DATETIME    NOT NULL,
    nb_passagers        INT NOT NULL DEFAULT 1,
    statut              ENUM('en_attente','acceptee','refusee','terminee') NOT NULL DEFAULT 'en_attente',
    CONSTRAINT fk_demande_user FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_demande_bus  FOREIGN KEY (id_bus)         REFERENCES bus(id_bus) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Espace chauffeur : cas de mon bus (incidents)
CREATE TABLE incident_bus (
    id_incident   INT AUTO_INCREMENT PRIMARY KEY,
    id_bus        INT NOT NULL,
    id_chauffeur  INT NOT NULL,
    type_incident ENUM('panne','accident','retard','autre') NOT NULL DEFAULT 'autre',
    description   TEXT NULL,
    date_incident DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut        ENUM('signale','en_traitement','resolu') NOT NULL DEFAULT 'signale',
    CONSTRAINT fk_incident_bus       FOREIGN KEY (id_bus)       REFERENCES bus(id_bus) ON DELETE CASCADE,
    CONSTRAINT fk_incident_chauffeur FOREIGN KEY (id_chauffeur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. MÉTRO & TRAIN
-- ---------------------------------------------------------------------
CREATE TABLE train (
    id_train     INT AUTO_INCREMENT PRIMARY KEY,
    numero       VARCHAR(20) NOT NULL,
    type         ENUM('metro','train') NOT NULL DEFAULT 'train',
    capacite     INT NOT NULL,
    id_chauffeur INT NULL,
    etat         ENUM('en_service','maintenance','hors_service') NOT NULL DEFAULT 'en_service',
    CONSTRAINT fk_train_chauffeur FOREIGN KEY (id_chauffeur) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE trajet_train (
    id_trajet_train   INT AUTO_INCREMENT PRIMARY KEY,
    id_train          INT NOT NULL,
    id_gare_depart    INT NOT NULL,
    id_gare_arrivee   INT NOT NULL,
    date_heure_depart DATETIME NOT NULL,
    date_heure_arrivee DATETIME NOT NULL,
    prix              DECIMAL(8,3) NOT NULL,
    CONSTRAINT fk_tt_train FOREIGN KEY (id_train)        REFERENCES train(id_train) ON DELETE CASCADE,
    CONSTRAINT fk_tt_dep   FOREIGN KEY (id_gare_depart)  REFERENCES station(id_station),
    CONSTRAINT fk_tt_arr   FOREIGN KEY (id_gare_arrivee) REFERENCES station(id_station)
) ENGINE=InnoDB;

CREATE TABLE billet_train (
    id_billet       INT AUTO_INCREMENT PRIMARY KEY,
    id_trajet_train INT NOT NULL,
    id_utilisateur  INT NOT NULL,
    classe          ENUM('1ere','2eme','confort') NOT NULL DEFAULT '2eme',
    prix            DECIMAL(8,3) NOT NULL,
    date_achat      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_billet_trajet FOREIGN KEY (id_trajet_train) REFERENCES trajet_train(id_trajet_train) ON DELETE CASCADE,
    CONSTRAINT fk_billet_user   FOREIGN KEY (id_utilisateur)  REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. LOUAGE
-- ---------------------------------------------------------------------
CREATE TABLE louage (
    id_louage          INT AUTO_INCREMENT PRIMARY KEY,
    id_chauffeur       INT NOT NULL,
    id_vehicule        INT NOT NULL,
    id_station_depart  INT NOT NULL,
    ville_arrivee      VARCHAR(60) NOT NULL,
    prix_place         DECIMAL(8,3) NOT NULL,
    places_disponibles INT NOT NULL DEFAULT 8,
    date_heure_depart  DATETIME NULL,
    CONSTRAINT fk_louage_chauffeur FOREIGN KEY (id_chauffeur)      REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_louage_vehicule  FOREIGN KEY (id_vehicule)       REFERENCES vehicule(id_vehicule) ON DELETE CASCADE,
    CONSTRAINT fk_louage_station   FOREIGN KEY (id_station_depart) REFERENCES station(id_station)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. LOCATION DE VÉHICULES
-- ---------------------------------------------------------------------
CREATE TABLE location (
    id_location    INT AUTO_INCREMENT PRIMARY KEY,
    id_vehicule    INT NOT NULL,
    id_client      INT NOT NULL,
    date_debut     DATE NOT NULL,
    date_fin       DATE NOT NULL,
    prix_jour      DECIMAL(8,3) NOT NULL,
    prix_total     DECIMAL(10,3) NOT NULL,
    statut         ENUM('en_attente','confirmee','en_cours','terminee','annulee') NOT NULL DEFAULT 'en_attente',
    CONSTRAINT fk_location_vehicule FOREIGN KEY (id_vehicule) REFERENCES vehicule(id_vehicule) ON DELETE CASCADE,
    CONSTRAINT fk_location_client   FOREIGN KEY (id_client)   REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. RÉPARATION DE VÉHICULES
-- ---------------------------------------------------------------------
CREATE TABLE garage (
    id_garage      INT AUTO_INCREMENT PRIMARY KEY,
    id_responsable INT NULL,
    nom            VARCHAR(100) NOT NULL,
    adresse        VARCHAR(150) NOT NULL,
    ville          VARCHAR(60)  NOT NULL,
    telephone      VARCHAR(20)  NULL,
    specialite     VARCHAR(100) NULL,
    CONSTRAINT fk_garage_resp FOREIGN KEY (id_responsable) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE reparation (
    id_reparation   INT AUTO_INCREMENT PRIMARY KEY,
    id_client       INT NOT NULL,
    id_vehicule     INT NOT NULL,
    id_garage       INT NOT NULL,
    description_panne TEXT NOT NULL,
    date_demande    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_rdv        DATETIME NULL,
    cout_estime     DECIMAL(10,3) NULL,
    statut          ENUM('en_attente','diagnostic','en_reparation','terminee','annulee') NOT NULL DEFAULT 'en_attente',
    CONSTRAINT fk_rep_client   FOREIGN KEY (id_client)   REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_rep_vehicule FOREIGN KEY (id_vehicule) REFERENCES vehicule(id_vehicule) ON DELETE CASCADE,
    CONSTRAINT fk_rep_garage   FOREIGN KEY (id_garage)   REFERENCES garage(id_garage) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. COVOITURAGE  (module de cette interface)
-- ---------------------------------------------------------------------
CREATE TABLE trajet_covoiturage (
    id_trajet          INT AUTO_INCREMENT PRIMARY KEY,
    id_conducteur      INT NOT NULL,
    id_vehicule        INT NULL,
    ville_depart       VARCHAR(60)  NOT NULL,
    ville_arrivee      VARCHAR(60)  NOT NULL,
    point_rencontre    VARCHAR(150) NULL,
    date_depart        DATE NOT NULL,
    heure_depart       TIME NOT NULL,
    duree_estimee_min  INT  NULL,
    prix_place         DECIMAL(8,3) NOT NULL,
    places_total       INT NOT NULL DEFAULT 4,
    places_disponibles INT NOT NULL DEFAULT 4,
    bagages            ENUM('petit','moyen','grand') NOT NULL DEFAULT 'moyen',
    fumeur             TINYINT(1) NOT NULL DEFAULT 0,
    animaux            TINYINT(1) NOT NULL DEFAULT 0,
    description        VARCHAR(255) NULL,
    statut             ENUM('ouvert','complet','annule','termine') NOT NULL DEFAULT 'ouvert',
    date_creation      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tc_conducteur FOREIGN KEY (id_conducteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_tc_vehicule   FOREIGN KEY (id_vehicule)   REFERENCES vehicule(id_vehicule) ON DELETE SET NULL,
    CONSTRAINT chk_tc_places CHECK (places_disponibles >= 0 AND places_disponibles <= places_total)
) ENGINE=InnoDB;

CREATE TABLE reservation_covoiturage (
    id_reservation   INT AUTO_INCREMENT PRIMARY KEY,
    id_trajet        INT NOT NULL,
    id_passager      INT NOT NULL,
    nb_places        INT NOT NULL DEFAULT 1,
    prix_total       DECIMAL(10,3) NOT NULL,
    statut           ENUM('acceptee','annulee','terminee') NOT NULL DEFAULT 'acceptee',
    date_reservation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rc_trajet   FOREIGN KEY (id_trajet)   REFERENCES trajet_covoiturage(id_trajet) ON DELETE CASCADE,
    CONSTRAINT fk_rc_passager FOREIGN KEY (id_passager) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avis_covoiturage (
    id_avis      INT AUTO_INCREMENT PRIMARY KEY,
    id_trajet    INT NOT NULL,
    id_auteur    INT NOT NULL,
    id_conducteur INT NOT NULL,
    note         TINYINT NOT NULL,
    commentaire  VARCHAR(255) NULL,
    date_avis    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_avis_trajet     FOREIGN KEY (id_trajet)     REFERENCES trajet_covoiturage(id_trajet) ON DELETE CASCADE,
    CONSTRAINT fk_avis_auteur     FOREIGN KEY (id_auteur)     REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_avis_conducteur FOREIGN KEY (id_conducteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- =====================================================================
-- DONNÉES DE DÉMONSTRATION
-- Mot de passe de tous les comptes de démo : Urbix2026
-- (stocké en clair ici : il est automatiquement re-haché avec password_hash()
--  à la première connexion ; les nouveaux comptes sont toujours hachés)
-- =====================================================================
SET @mdp = 'Urbix2026';

INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, date_naissance, genre, telephone, role) VALUES
('Ben Ali',   'Karim',  'karim.benali@urbix.tn',  @mdp, '1990-04-12', 'homme', '22 345 678', 'covoiturage'),
('Trabelsi',  'Amira',  'amira.trabelsi@urbix.tn', @mdp, '1994-09-03', 'femme', '55 123 987', 'covoiturage'),
('Gharbi',    'Youssef','youssef.gharbi@urbix.tn', @mdp, '1987-01-25', 'homme', '98 456 321', 'covoiturage'),
('Mansouri',  'Ines',   'ines.mansouri@urbix.tn',  @mdp, '1996-06-18', 'femme', '20 789 456', 'covoiturage'),
('Jaziri',    'Mehdi',  'mehdi.jaziri@urbix.tn',   @mdp, '1992-11-07', 'homme', '27 654 123', 'covoiturage'),
('Missaoui',  'Salma',  'salma@urbix.tn',          @mdp, '2004-02-14', 'femme', '50 111 222', 'user-normal'),
('Hamdi',     'Sami',   'sami.hamdi@urbix.tn',     @mdp, '1985-03-30', 'homme', '23 000 111', 'chauffeur-bus'),
('Kefi',      'Nour',   'nour.kefi@urbix.tn',      @mdp, '1989-07-21', 'femme', '24 000 222', 'chauffeur-train'),
('Admin',     'Urbix',  'admin@urbix.tn',          @mdp, '1980-01-01', 'autre', '71 000 000', 'admin');

INSERT INTO vehicule (id_proprietaire, type, marque, modele, couleur, immatriculation, nb_places, climatisation) VALUES
(1, 'voiture', 'Volkswagen', 'Golf 7',   'Gris',  '215 TU 4521', 4, 1),
(2, 'voiture', 'Peugeot',    '208',      'Blanc', '228 TU 1187', 3, 1),
(3, 'voiture', 'Kia',        'Sportage', 'Noir',  '201 TU 9934', 4, 1),
(4, 'voiture', 'Renault',    'Clio 5',   'Rouge', '231 TU 0452', 3, 1),
(5, 'voiture', 'Hyundai',    'i20',      'Bleu',  '219 TU 7763', 4, 0),
(7, 'bus',     'Iveco',      'Crossway', 'Jaune', '180 TU 1000', 60, 1),
(NULL, 'louage','Toyota',    'Hiace',    'Blanc', '205 TU 3030', 8, 1),
(NULL, 'location','Dacia',   'Logan',    'Gris',  '226 TU 5050', 5, 1);

INSERT INTO station (nom, ville, adresse, latitude, longitude, type) VALUES
('Station Barcelone',   'Tunis',  'Place de Barcelone',     36.7955, 10.1817, 'bus'),
('Station Ariana Centre','Ariana','Avenue Habib Bourguiba', 36.8625, 10.1956, 'bus'),
('Gare de Tunis',       'Tunis',  'Place Barcelone',        36.7950, 10.1810, 'train'),
('Gare de Sousse',      'Sousse', 'Boulevard Hassouna Ayachi',35.8278,10.6380, 'train'),
('Station Louage Moncef Bey','Tunis','Rue Moncef Bey',      36.7890, 10.1850, 'louage');

INSERT INTO ligne_bus (numero, nom, id_station_depart, id_station_arrivee) VALUES ('20', 'Barcelone – Ariana', 1, 2);
INSERT INTO bus (id_vehicule, id_ligne, id_chauffeur, a_la_demande, etat) VALUES (6, 1, 7, 0, 'en_service');
INSERT INTO horaire_bus (id_ligne, id_station, heure_passage) VALUES (1, 1, '07:30:00'), (1, 2, '08:05:00');
INSERT INTO mission (id_chauffeur, id_bus, date_mission, description) VALUES (7, 1, CURDATE(), 'Service ligne 20 – matin');
INSERT INTO train (numero, type, capacite, id_chauffeur) VALUES ('TR-501', 'train', 400, 8);
INSERT INTO trajet_train (id_train, id_gare_depart, id_gare_arrivee, date_heure_depart, date_heure_arrivee, prix)
VALUES (1, 3, 4, CONCAT(DATE_ADD(CURDATE(), INTERVAL 1 DAY), ' 08:00:00'), CONCAT(DATE_ADD(CURDATE(), INTERVAL 1 DAY), ' 10:20:00'), 11.500);
INSERT INTO louage (id_chauffeur, id_vehicule, id_station_depart, ville_arrivee, prix_place, places_disponibles)
VALUES (5, 7, 5, 'Sousse', 12.000, 8);
INSERT INTO garage (nom, adresse, ville, telephone, specialite) VALUES ('Garage Ennour', 'Rue de Marseille 12', 'Tunis', '71 222 333', 'Mécanique générale');

-- Trajets de covoiturage (dates relatives à aujourd'hui pour toujours avoir des propositions)
INSERT INTO trajet_covoiturage
(id_conducteur, id_vehicule, ville_depart, ville_arrivee, point_rencontre, date_depart, heure_depart, duree_estimee_min, prix_place, places_total, places_disponibles, bagages, fumeur, animaux, description) VALUES
(1, 1, 'Tunis',  'Sousse',   'Station métro Bab Saadoun',   DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', 120, 15.000, 4, 3, 'moyen', 0, 0, 'Départ ponctuel, autoroute A1.'),
(2, 2, 'Tunis',  'Sousse',   'Les Berges du Lac',           DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:30:00', 125, 13.000, 3, 2, 'petit', 0, 1, 'Musique calme, animaux acceptés.'),
(3, 3, 'Ariana', 'Sousse',   'Ennasr 2 – Carrefour',        DATE_ADD(CURDATE(), INTERVAL 1 DAY), '17:15:00', 130, 17.500, 4, 4, 'grand', 0, 0, 'Grand coffre, idéal avec valises.'),
(4, 4, 'Tunis',  'Sfax',     'Place Pasteur',               DATE_ADD(CURDATE(), INTERVAL 2 DAY), '07:00:00', 210, 25.000, 3, 3, 'moyen', 0, 0, 'Pause café à El Jem.'),
(5, 5, 'Tunis',  'Nabeul',   'Gare TGM Tunis Marine',       DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00',  70,  8.000, 4, 2, 'petit', 1, 0, NULL),
(1, 1, 'Sousse', 'Tunis',    'Sousse Sahloul',              DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00', 120, 15.000, 4, 4, 'moyen', 0, 0, 'Retour du week-end.'),
(2, 2, 'Tunis',  'Bizerte',  'Bab Saadoun',                 DATE_ADD(CURDATE(), INTERVAL 2 DAY), '12:00:00',  65,  7.000, 3, 3, 'petit', 0, 0, NULL),
(3, 3, 'Tunis',  'Hammamet', 'La Marsa – Zéphyr',           DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00',  60,  9.000, 4, 3, 'grand', 0, 1, 'Direction plage, bonne ambiance.'),
(4, 4, 'Sfax',   'Djerba',   'Sfax centre',                 DATE_ADD(CURDATE(), INTERVAL 4 DAY), '06:30:00', 240, 22.000, 3, 2, 'moyen', 0, 0, NULL),
(5, 5, 'Tunis',  'Monastir', 'Montplaisir',                 DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:45:00', 140, 16.000, 4, 4, 'moyen', 0, 0, 'Passage par Sousse possible.'),
(1, 1, 'Tunis',  'Kairouan', 'Bab Alioua',                  DATE_ADD(CURDATE(), INTERVAL 5 DAY), '08:30:00', 150, 14.000, 4, 3, 'moyen', 0, 0, NULL),
(2, 2, 'Ariana', 'Tunis',    'Ariana Soghra',               DATE_ADD(CURDATE(), INTERVAL 1 DAY), '07:45:00',  25,  3.000, 3, 3, 'petit', 0, 0, 'Trajet domicile-travail quotidien.');

INSERT INTO avis_covoiturage (id_trajet, id_auteur, id_conducteur, note, commentaire) VALUES
(1, 6, 1, 5, 'Très ponctuel et agréable.'),
(1, 6, 1, 4, 'Bonne conduite.'),
(2, 6, 2, 5, 'Super trajet !'),
(3, 6, 3, 4, 'Voiture confortable.'),
(4, 6, 4, 5, 'Parfait.'),
(5, 6, 5, 3, 'Un peu en retard.');
