-- =====================================================
-- Compte administrateur fixe (backoffice)
--   Email        : adminBus@gmail.com
--   Mot de passe : admin123   (stocké uniquement sous forme hachée, bcrypt / password_hash)
-- À exécuter dans phpMyAdmin > base urbix_db > onglet SQL.
-- Si le compte existe déjà, il est remis à jour (rôle admin + mot de passe).
-- =====================================================
INSERT INTO users (nom, prenom, email, password, date_naissance, genre, telephone, type_service, role)
VALUES ('Admin', 'Bus', 'adminBus@gmail.com',
        '$2y$10$byY/TPtZ4hUyOqfx6wZmIuLzFVl5SGHrHiIAQFwaOoOm8Dzl.E10.',
        '2000-01-01', 'homme', '00000000', 'utilisateur_normal', 'admin')
ON DUPLICATE KEY UPDATE
    password = VALUES(password),
    role     = 'admin';
