-- =====================================================================
-- Gestion Covoiturage : base de données du module (dans urbix_db)
-- Tables : covoiturage_vehicules, covoiturage_trajets, covoiturage_reservations, covoiturage_avis
-- À importer dans phpMyAdmin > base urbix_db > onglet Import.
-- Les tables sont reliées à la table users (comptes communs à toutes les gestions).
-- =====================================================================

USE urbix_db;
SET NAMES utf8mb4;

-- =====================================================================
-- COVOITURAGE (conducteurs et passagers = comptes de la table users)
-- =====================================================================
CREATE TABLE IF NOT EXISTS covoiturage_vehicules (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT         NULL,                       -- propriétaire (conducteur)
    marque         VARCHAR(60) NOT NULL,
    modele         VARCHAR(60) NOT NULL,
    couleur        VARCHAR(30) NULL,
    immatriculation VARCHAR(30) NOT NULL UNIQUE,
    nb_places      INT         NOT NULL DEFAULT 4,
    climatisation  TINYINT(1)  NOT NULL DEFAULT 1,
    CONSTRAINT fk_covoit_vehicule_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS covoiturage_trajets (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    conducteur_id      INT NOT NULL,
    vehicule_id        INT NULL,
    ville_depart       VARCHAR(60)  NOT NULL,
    ville_arrivee      VARCHAR(60)  NOT NULL,
    point_rencontre    VARCHAR(150) NULL,
    date_depart        DATE NOT NULL,
    heure_depart       TIME NOT NULL,
    duree_estimee_min  INT  NULL,
    prix_place         DECIMAL(8,3) NOT NULL,
    places_total       INT NOT NULL DEFAULT 4,
    places_disponibles INT NOT NULL DEFAULT 4,
    bagages            ENUM('petit', 'moyen', 'grand') NOT NULL DEFAULT 'moyen',
    fumeur             TINYINT(1) NOT NULL DEFAULT 0,
    animaux            TINYINT(1) NOT NULL DEFAULT 0,
    description        VARCHAR(255) NULL,
    statut             ENUM('ouvert', 'complet', 'annule', 'termine') NOT NULL DEFAULT 'ouvert',
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_covoit_trajet_conducteur FOREIGN KEY (conducteur_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_covoit_trajet_vehicule   FOREIGN KEY (vehicule_id)   REFERENCES covoiturage_vehicules(id) ON DELETE SET NULL,
    CONSTRAINT chk_covoit_places CHECK (places_disponibles >= 0 AND places_disponibles <= places_total)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS covoiturage_reservations (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id        INT NOT NULL,
    passager_id      INT NOT NULL,
    nb_places        INT NOT NULL DEFAULT 1,
    prix_total       DECIMAL(10,3) NOT NULL,
    statut           ENUM('acceptee', 'annulee', 'terminee') NOT NULL DEFAULT 'acceptee',
    date_reservation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_covoit_resa_trajet   FOREIGN KEY (trajet_id)   REFERENCES covoiturage_trajets(id) ON DELETE CASCADE,
    CONSTRAINT fk_covoit_resa_passager FOREIGN KEY (passager_id) REFERENCES users(id)               ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS covoiturage_avis (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    trajet_id     INT NOT NULL,
    auteur_id     INT NOT NULL,
    conducteur_id INT NOT NULL,
    note          TINYINT NOT NULL,
    commentaire   VARCHAR(255) NULL,
    date_avis     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_covoit_avis_trajet     FOREIGN KEY (trajet_id)     REFERENCES covoiturage_trajets(id) ON DELETE CASCADE,
    CONSTRAINT fk_covoit_avis_auteur     FOREIGN KEY (auteur_id)     REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_covoit_avis_conducteur FOREIGN KEY (conducteur_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_covoit_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comptes de démonstration du covoiturage (mot de passe : Urbix2026), ajoutés à users
INSERT IGNORE INTO users (nom, prenom, email, password, date_naissance, genre, telephone, type_service, role) VALUES
('Ben Ali',  'Karim',   'karim.benali@urbix.tn',   '$2y$10$1k734JXlkkgxw1vPhdFrj.3N3bnEf6k6xo3sRTG3VOUPVbU2qEJmq', '1990-04-12', 'homme', '22345678', 'covoiturage', 'user'),
('Trabelsi', 'Amira',   'amira.trabelsi@urbix.tn', '$2y$10$1k734JXlkkgxw1vPhdFrj.3N3bnEf6k6xo3sRTG3VOUPVbU2qEJmq', '1994-09-03', 'femme', '55123987', 'covoiturage', 'user'),
('Gharbi',   'Youssef', 'youssef.gharbi@urbix.tn', '$2y$10$1k734JXlkkgxw1vPhdFrj.3N3bnEf6k6xo3sRTG3VOUPVbU2qEJmq', '1987-01-25', 'homme', '98456321', 'covoiturage', 'user'),
('Mansouri', 'Ines',    'ines.mansouri@urbix.tn',  '$2y$10$1k734JXlkkgxw1vPhdFrj.3N3bnEf6k6xo3sRTG3VOUPVbU2qEJmq', '1996-06-18', 'femme', '20789456', 'covoiturage', 'user'),
('Jaziri',   'Mehdi',   'mehdi.jaziri@urbix.tn',   '$2y$10$1k734JXlkkgxw1vPhdFrj.3N3bnEf6k6xo3sRTG3VOUPVbU2qEJmq', '1992-11-07', 'homme', '27654123', 'covoiturage', 'user'),
('Missaoui', 'Salma',   'salma@urbix.tn',          '$2y$10$1k734JXlkkgxw1vPhdFrj.3N3bnEf6k6xo3sRTG3VOUPVbU2qEJmq', '2004-02-14', 'femme', '50111222', 'utilisateur_normal', 'user');

INSERT INTO covoiturage_vehicules (user_id, marque, modele, couleur, immatriculation, nb_places, climatisation)
SELECT u.id, v.marque, v.modele, v.couleur, v.imm, v.places, v.clim
FROM (
    SELECT 'karim.benali@urbix.tn' AS email, 'Volkswagen' AS marque, 'Golf 7' AS modele, 'Gris' AS couleur, '215 TU 4521' AS imm, 4 AS places, 1 AS clim
    UNION ALL SELECT 'amira.trabelsi@urbix.tn', 'Peugeot', '208', 'Blanc', '228 TU 1187', 3, 1
    UNION ALL SELECT 'youssef.gharbi@urbix.tn', 'Kia', 'Sportage', 'Noir', '201 TU 9934', 4, 1
    UNION ALL SELECT 'ines.mansouri@urbix.tn', 'Renault', 'Clio 5', 'Rouge', '231 TU 0452', 3, 1
    UNION ALL SELECT 'mehdi.jaziri@urbix.tn', 'Hyundai', 'i20', 'Bleu', '219 TU 7763', 4, 0
) AS v
JOIN users u ON u.email = v.email
WHERE NOT EXISTS (SELECT 1 FROM covoiturage_vehicules);

INSERT INTO covoiturage_trajets
    (conducteur_id, vehicule_id, ville_depart, ville_arrivee, point_rencontre, date_depart, heure_depart,
     duree_estimee_min, prix_place, places_total, places_disponibles, bagages, fumeur, animaux, description)
SELECT u.id, v.id, t.dep, t.arr, t.rdv, CURDATE() + INTERVAL t.j DAY, t.h, t.duree, t.prix, t.tot, t.dispo, t.bag, t.fum, t.ani, t.descr
FROM (
    SELECT 'karim.benali@urbix.tn' AS email, 'Tunis' AS dep, 'Sousse' AS arr, 'Station métro Bab Saadoun' AS rdv, 1 AS j, '08:00:00' AS h, 120 AS duree, 15.000 AS prix, 4 AS tot, 3 AS dispo, 'moyen' AS bag, 0 AS fum, 0 AS ani, 'Départ ponctuel, autoroute A1.' AS descr
    UNION ALL SELECT 'amira.trabelsi@urbix.tn', 'Tunis', 'Sousse', 'Les Berges du Lac', 1, '10:30:00', 125, 13.000, 3, 2, 'petit', 0, 1, 'Musique calme, animaux acceptés.'
    UNION ALL SELECT 'youssef.gharbi@urbix.tn', 'Ariana', 'Sousse', 'Ennasr 2 – Carrefour', 1, '17:15:00', 130, 17.500, 4, 4, 'grand', 0, 0, 'Grand coffre, idéal avec valises.'
    UNION ALL SELECT 'ines.mansouri@urbix.tn', 'Tunis', 'Sfax', 'Place Pasteur', 2, '07:00:00', 210, 25.000, 3, 3, 'moyen', 0, 0, 'Pause café à El Jem.'
    UNION ALL SELECT 'mehdi.jaziri@urbix.tn', 'Tunis', 'Nabeul', 'Gare TGM Tunis Marine', 1, '09:00:00', 70, 8.000, 4, 2, 'petit', 1, 0, NULL
    UNION ALL SELECT 'karim.benali@urbix.tn', 'Sousse', 'Tunis', 'Sousse Sahloul', 3, '18:00:00', 120, 15.000, 4, 4, 'moyen', 0, 0, 'Retour du week-end.'
    UNION ALL SELECT 'amira.trabelsi@urbix.tn', 'Tunis', 'Bizerte', 'Bab Saadoun', 2, '12:00:00', 65, 7.000, 3, 3, 'petit', 0, 0, NULL
    UNION ALL SELECT 'youssef.gharbi@urbix.tn', 'Tunis', 'Hammamet', 'La Marsa – Zéphyr', 1, '14:00:00', 60, 9.000, 4, 3, 'grand', 0, 1, 'Direction plage, bonne ambiance.'
    UNION ALL SELECT 'ines.mansouri@urbix.tn', 'Sfax', 'Djerba', 'Sfax centre', 4, '06:30:00', 240, 22.000, 3, 2, 'moyen', 0, 0, NULL
    UNION ALL SELECT 'mehdi.jaziri@urbix.tn', 'Tunis', 'Monastir', 'Montplaisir', 2, '16:45:00', 140, 16.000, 4, 4, 'moyen', 0, 0, 'Passage par Sousse possible.'
    UNION ALL SELECT 'karim.benali@urbix.tn', 'Tunis', 'Kairouan', 'Bab Alioua', 5, '08:30:00', 150, 14.000, 4, 3, 'moyen', 0, 0, NULL
    UNION ALL SELECT 'amira.trabelsi@urbix.tn', 'Ariana', 'Tunis', 'Ariana Soghra', 1, '07:45:00', 25, 3.000, 3, 3, 'petit', 0, 0, 'Trajet domicile-travail quotidien.'
) AS t
JOIN users u ON u.email = t.email
LEFT JOIN covoiturage_vehicules v ON v.user_id = u.id
WHERE NOT EXISTS (SELECT 1 FROM covoiturage_trajets);

INSERT INTO covoiturage_avis (trajet_id, auteur_id, conducteur_id, note, commentaire)
SELECT t.id, s.id, t.conducteur_id, a.note, a.com
FROM (
    SELECT 'karim.benali@urbix.tn' AS email, 'Sousse' AS arr, 5 AS note, 'Très ponctuel et agréable.' AS com
    UNION ALL SELECT 'karim.benali@urbix.tn', 'Sousse', 4, 'Bonne conduite.'
    UNION ALL SELECT 'amira.trabelsi@urbix.tn', 'Sousse', 5, 'Super trajet !'
    UNION ALL SELECT 'youssef.gharbi@urbix.tn', 'Sousse', 4, 'Voiture confortable.'
    UNION ALL SELECT 'ines.mansouri@urbix.tn', 'Sfax', 5, 'Parfait.'
    UNION ALL SELECT 'mehdi.jaziri@urbix.tn', 'Nabeul', 3, 'Un peu en retard.'
) AS a
JOIN users c ON c.email = a.email
JOIN covoiturage_trajets t ON t.conducteur_id = c.id AND t.ville_arrivee = a.arr
JOIN users s ON s.email = 'salma@urbix.tn'
WHERE NOT EXISTS (SELECT 1 FROM covoiturage_avis);
