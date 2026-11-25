<?php
namespace App\Models;

/**
 * Entité ReservationCovoiturage : un passager accepte la proposition d'un conducteur.
 */
class Reservation extends BaseModel {

    /**
     * Crée la réservation et décrémente les places disponibles du trajet.
     * Retourne l'id de la réservation, ou lève une \RuntimeException avec un message clair.
     */
    public static function creer(int $idTrajet, int $idPassager, int $nbPlaces): int {
        $pdo = self::pdo();

        if ($pdo === null) {
            $db = &DemoStore::data();
            if (!isset($db['trajets'][$idTrajet])) {
                throw new \RuntimeException('Ce trajet n\'existe plus.');
            }
            $t = &$db['trajets'][$idTrajet];
            self::verifier($t, $idPassager, $nbPlaces, self::dejaReserveDemo($db, $idTrajet, $idPassager));
            $t['places_disponibles'] -= $nbPlaces;
            if ($t['places_disponibles'] === 0) {
                $t['statut'] = 'complet';
            }
            $id = count($db['reservations']) + 1;
            $db['reservations'][$id] = [
                'id_reservation' => $id, 'id_trajet' => $idTrajet, 'id_passager' => $idPassager,
                'nb_places' => $nbPlaces, 'prix_total' => $nbPlaces * (float) $t['prix_place'],
                'statut' => 'acceptee', 'date_reservation' => date('Y-m-d H:i:s'),
            ];
            return $id;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM trajet_covoiturage WHERE id_trajet = :id FOR UPDATE');
            $stmt->execute(['id' => $idTrajet]);
            $t = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$t) {
                throw new \RuntimeException('Ce trajet n\'existe plus.');
            }
            $dej = $pdo->prepare("SELECT COUNT(*) FROM reservation_covoiturage
                                  WHERE id_trajet = :t AND id_passager = :p AND statut = 'acceptee'");
            $dej->execute(['t' => $idTrajet, 'p' => $idPassager]);
            self::verifier($t, $idPassager, $nbPlaces, (int) $dej->fetchColumn() > 0);

            $ins = $pdo->prepare('INSERT INTO reservation_covoiturage (id_trajet, id_passager, nb_places, prix_total, statut)
                                  VALUES (:t, :p, :n, :prix, \'acceptee\')');
            $ins->execute([
                't' => $idTrajet, 'p' => $idPassager, 'n' => $nbPlaces,
                'prix' => $nbPlaces * (float) $t['prix_place'],
            ]);
            $idReservation = (int) $pdo->lastInsertId();

            $upd = $pdo->prepare("UPDATE trajet_covoiturage
                                  SET places_disponibles = places_disponibles - :n,
                                      statut = IF(places_disponibles = 0, 'complet', statut)
                                  WHERE id_trajet = :id");
            $upd->execute(['n' => $nbPlaces, 'id' => $idTrajet]);

            $pdo->commit();
            return $idReservation;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Réservations d'un passager avec le détail du trajet et du conducteur. */
    public static function parPassager(int $idPassager): array {
        $pdo = self::pdo();
        if ($pdo === null) {
            $db = DemoStore::data();
            $resultat = [];
            foreach ($db['reservations'] as $r) {
                if ((int) $r['id_passager'] === $idPassager && isset($db['trajets'][$r['id_trajet']])) {
                    $resultat[] = array_merge($db['trajets'][$r['id_trajet']], $r);
                }
            }
            usort($resultat, fn($a, $b) => strcmp($b['date_reservation'], $a['date_reservation']));
            return $resultat;
        }

        $stmt = $pdo->prepare("
            SELECT t.*, r.id_reservation, r.nb_places, r.prix_total, r.statut, r.date_reservation,
                   u.nom AS conducteur_nom, u.prenom AS conducteur_prenom, u.telephone AS conducteur_telephone,
                   v.marque AS vehicule_marque, v.modele AS vehicule_modele, v.couleur AS vehicule_couleur
            FROM reservation_covoiturage r
            JOIN trajet_covoiturage t ON t.id_trajet = r.id_trajet
            JOIN utilisateur u ON u.id_utilisateur = t.id_conducteur
            LEFT JOIN vehicule v ON v.id_vehicule = t.id_vehicule
            WHERE r.id_passager = :p
            ORDER BY r.date_reservation DESC");
        $stmt->execute(['p' => $idPassager]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Annule une réservation du passager et libère les places. */
    public static function annuler(int $idReservation, int $idPassager): bool {
        $pdo = self::pdo();
        if ($pdo === null) {
            $db = &DemoStore::data();
            $r = $db['reservations'][$idReservation] ?? null;
            if (!$r || (int) $r['id_passager'] !== $idPassager || $r['statut'] !== 'acceptee') {
                return false;
            }
            $db['reservations'][$idReservation]['statut'] = 'annulee';
            if (isset($db['trajets'][$r['id_trajet']])) {
                $db['trajets'][$r['id_trajet']]['places_disponibles'] += (int) $r['nb_places'];
                $db['trajets'][$r['id_trajet']]['statut'] = 'ouvert';
            }
            return true;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM reservation_covoiturage
                                   WHERE id_reservation = :r AND id_passager = :p AND statut = 'acceptee' FOR UPDATE");
            $stmt->execute(['r' => $idReservation, 'p' => $idPassager]);
            $r = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$r) {
                $pdo->rollBack();
                return false;
            }
            $pdo->prepare("UPDATE reservation_covoiturage SET statut = 'annulee' WHERE id_reservation = :r")
                ->execute(['r' => $idReservation]);
            $pdo->prepare("UPDATE trajet_covoiturage
                           SET places_disponibles = places_disponibles + :n, statut = 'ouvert'
                           WHERE id_trajet = :t")
                ->execute(['n' => (int) $r['nb_places'], 't' => (int) $r['id_trajet']]);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function verifier(array $t, int $idPassager, int $nbPlaces, bool $dejaReserve): void {
        if ($t['statut'] !== 'ouvert') {
            throw new \RuntimeException('Ce trajet n\'est plus disponible.');
        }
        if ((int) $t['id_conducteur'] === $idPassager) {
            throw new \RuntimeException('Vous ne pouvez pas réserver votre propre trajet.');
        }
        if ($dejaReserve) {
            throw new \RuntimeException('Vous avez déjà accepté ce trajet. Consultez « Mes réservations ».');
        }
        if ($nbPlaces < 1 || $nbPlaces > (int) $t['places_disponibles']) {
            throw new \RuntimeException('Il ne reste que ' . (int) $t['places_disponibles'] . ' place(s) sur ce trajet.');
        }
        $depart = strtotime($t['date_depart'] . ' ' . $t['heure_depart']);
        if ($depart !== false && $depart < time()) {
            throw new \RuntimeException('Ce trajet est déjà parti.');
        }
    }

    private static function dejaReserveDemo(array $db, int $idTrajet, int $idPassager): bool {
        foreach ($db['reservations'] as $r) {
            if ((int) $r['id_trajet'] === $idTrajet && (int) $r['id_passager'] === $idPassager && $r['statut'] === 'acceptee') {
                return true;
            }
        }
        return false;
    }
}
