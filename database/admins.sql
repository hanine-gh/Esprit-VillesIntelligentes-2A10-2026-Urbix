-- =====================================================================
-- Deux administrateurs :
--   adminadmin@gmail.com / admin123 : gère tout (Bus, Location, Train, Louage)
--   adminBus@gmail.com   / admin123 : gère uniquement la partie Bus
-- La colonne admin_scope dit ce qu'un admin peut gérer ('tout' ou 'bus').
-- À exécuter une fois dans phpMyAdmin > base urbix_db > onglet SQL (ou Import).
-- =====================================================================
USE urbix_db;
SET NAMES utf8mb4;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS admin_scope ENUM('tout','bus') NOT NULL DEFAULT 'tout' AFTER role;

-- adminBus : uniquement la gestion des bus (créé s'il n'existe pas, mot de passe admin123)
INSERT INTO users (nom, prenom, email, password, date_naissance, genre, telephone, type_service, role, admin_scope)
VALUES ('Admin', 'Bus', 'adminBus@gmail.com',
        '$2y$10$iX5uBbCcyDnyy6MENkUVWeu1/X4GL3F0jiV1jc.E.u4ejbM3e79D6',
        '2000-01-01', 'homme', '00000000', 'utilisateur_normal', 'admin', 'bus')
ON DUPLICATE KEY UPDATE
    role        = 'admin',
    admin_scope = 'bus';

-- adminadmin : super administrateur (mot de passe admin123, stocké haché)
INSERT INTO users (nom, prenom, email, password, date_naissance, genre, telephone, type_service, role, admin_scope)
VALUES ('Admin', 'Admin', 'adminadmin@gmail.com',
        '$2y$10$Y6AOJOCBlSVgWyFyLkbayOvJaRpsGkqWIb009j5ejZ0IFaeS8QNAm',
        '2000-01-01', 'homme', '00000000', 'utilisateur_normal', 'admin', 'tout')
ON DUPLICATE KEY UPDATE
    password    = VALUES(password),
    role        = 'admin',
    admin_scope = 'tout';
