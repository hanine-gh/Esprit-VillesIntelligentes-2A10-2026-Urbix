-- =====================================================
-- Demandes de STOP faites par les utilisateurs (voyageurs)
-- À exécuter dans phpMyAdmin > base urbix_db > onglet SQL (après trajets_stations.sql)
-- =====================================================

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
