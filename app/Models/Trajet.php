<?php
namespace App\Models;

/**
 * Entité TrajetCovoiturage : un trajet proposé par un conducteur.
 */
class Trajet extends BaseModel {

    private const SELECT = "
        SELECT t.*,
               u.nom AS conducteur_nom, u.prenom AS conducteur_prenom, u.telephone AS conducteur_telephone,
               v.marque AS vehicule_marque, v.modele AS vehicule_modele, v.couleur AS vehicule_couleur,
               COALESCE(v.climatisation, 0) AS climatisation,
               (SELECT ROUND(AVG(a.note), 1) FROM avis_covoiturage a WHERE a.id_conducteur = t.id_conducteur) AS note_moyenne,
               (SELECT COUNT(*) FROM avis_covoiturage a WHERE a.id_conducteur = t.id_conducteur) AS nb_avis
        FROM trajet_covoiturage t
        JOIN utilisateur u ON u.id_utilisateur = t.id_conducteur
        LEFT JOIN vehicule v ON v.id_vehicule = t.id_vehicule
    ";

    /** Tous les trajets ouverts à venir (date + heure non passées). */
    public static function aVenir(): array {
        $aujourdhui = date('Y-m-d');
        $maintenant = date('H:i:s');
        $pdo = self::pdo();

        if ($pdo === null) {
            $trajets = array_filter(DemoStore::data()['trajets'], function (array $t) use ($aujourdhui, $maintenant) {
                return $t['statut'] === 'ouvert'
                    && ($t['date_depart'] > $aujourdhui
                        || ($t['date_depart'] === $aujourdhui && $t['heure_depart'] >= $maintenant));
            });
            return array_values($trajets);
        }

        $stmt = $pdo->prepare(self::SELECT . "
            WHERE t.statut = 'ouvert'
              AND (t.date_depart > :j1 OR (t.date_depart = :j2 AND t.heure_depart >= :h))
            ORDER BY t.date_depart, t.heure_depart");
        $stmt->execute(['j1' => $aujourdhui, 'j2' => $aujourdhui, 'h' => $maintenant]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function trouver(int $id): ?array {
        $pdo = self::pdo();
        if ($pdo === null) {
            return DemoStore::data()['trajets'][$id] ?? null;
        }
        $stmt = $pdo->prepare(self::SELECT . ' WHERE t.id_trajet = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Liste des villes connues (pour l'autocomplétion). */
    public static function villes(): array {
        $base = ['Tunis', 'Ariana', 'Ben Arous', 'La Manouba', 'Bizerte', 'Nabeul', 'Hammamet', 'Sousse',
                 'Monastir', 'Mahdia', 'Sfax', 'Kairouan', 'Gabès', 'Djerba', 'Gafsa', 'Tozeur', 'Béja', 'Jendouba'];
        $pdo = self::pdo();
        if ($pdo === null) {
            $trajets = DemoStore::data()['trajets'];
            $villes = array_merge(array_column($trajets, 'ville_depart'), array_column($trajets, 'ville_arrivee'));
        } else {
            $villes = $pdo->query('SELECT ville_depart FROM trajet_covoiturage UNION SELECT ville_arrivee FROM trajet_covoiturage')
                          ->fetchAll(\PDO::FETCH_COLUMN);
        }
        $villes = array_values(array_unique(array_merge($base, $villes)));
        sort($villes, SORT_NATURAL | SORT_FLAG_CASE);
        return $villes;
    }
}
