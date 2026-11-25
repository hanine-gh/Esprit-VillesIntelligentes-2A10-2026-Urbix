<?php
namespace App\Models;

/**
 * Données de démonstration stockées en session.
 * Utilisées uniquement si la base MySQL "smartcity" n'est pas disponible,
 * pour que l'interface reste utilisable (présentation, tests) sans erreur.
 * Ce sont les mêmes données que database/smartcity.sql.
 */
class DemoStore {

    private const CLE = 'urbix_demo_db';

    public static function &data(): array {
        if (!isset($_SESSION[self::CLE]) || ($_SESSION[self::CLE]['jour'] ?? '') !== date('Y-m-d')) {
            $_SESSION[self::CLE] = self::initialiser();
        }
        return $_SESSION[self::CLE];
    }

    private static function jour(int $plus): string {
        return date('Y-m-d', strtotime("+$plus day"));
    }

    private static function initialiser(): array {
        $mdp = password_hash('Urbix2026', PASSWORD_DEFAULT);

        $utilisateurs = [];
        $liste = [
            [1, 'Ben Ali',  'Karim',   'karim.benali@urbix.tn',   '22 345 678', 'covoiturage'],
            [2, 'Trabelsi', 'Amira',   'amira.trabelsi@urbix.tn', '55 123 987', 'covoiturage'],
            [3, 'Gharbi',   'Youssef', 'youssef.gharbi@urbix.tn', '98 456 321', 'covoiturage'],
            [4, 'Mansouri', 'Ines',    'ines.mansouri@urbix.tn',  '20 789 456', 'covoiturage'],
            [5, 'Jaziri',   'Mehdi',   'mehdi.jaziri@urbix.tn',   '27 654 123', 'covoiturage'],
            [6, 'Missaoui', 'Salma',   'salma@urbix.tn',          '50 111 222', 'user-normal'],
        ];
        foreach ($liste as [$id, $nom, $prenom, $email, $tel, $role]) {
            $utilisateurs[$id] = [
                'id_utilisateur' => $id, 'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
                'mot_de_passe' => $mdp, 'date_naissance' => null, 'genre' => null,
                'telephone' => $tel, 'role' => $role, 'date_inscription' => date('Y-m-d H:i:s'),
            ];
        }

        $vehicules = [
            1 => ['Volkswagen', 'Golf 7',   'Gris',  1],
            2 => ['Peugeot',    '208',      'Blanc', 1],
            3 => ['Kia',        'Sportage', 'Noir',  1],
            4 => ['Renault',    'Clio 5',   'Rouge', 1],
            5 => ['Hyundai',    'i20',      'Bleu',  0],
        ];
        $notes = [1 => [4.5, 2], 2 => [5.0, 1], 3 => [4.0, 1], 4 => [5.0, 1], 5 => [3.0, 1]];

        $bruts = [
            [1, 1, 'Tunis',  'Sousse',   'Station métro Bab Saadoun', 1, '08:00:00', 120, 15.0, 4, 3, 'moyen', 0, 0, 'Départ ponctuel, autoroute A1.'],
            [2, 2, 'Tunis',  'Sousse',   'Les Berges du Lac',         1, '10:30:00', 125, 13.0, 3, 2, 'petit', 0, 1, 'Musique calme, animaux acceptés.'],
            [3, 3, 'Ariana', 'Sousse',   'Ennasr 2 – Carrefour',      1, '17:15:00', 130, 17.5, 4, 4, 'grand', 0, 0, 'Grand coffre, idéal avec valises.'],
            [4, 4, 'Tunis',  'Sfax',     'Place Pasteur',             2, '07:00:00', 210, 25.0, 3, 3, 'moyen', 0, 0, 'Pause café à El Jem.'],
            [5, 5, 'Tunis',  'Nabeul',   'Gare TGM Tunis Marine',     1, '09:00:00',  70,  8.0, 4, 2, 'petit', 1, 0, null],
            [6, 1, 'Sousse', 'Tunis',    'Sousse Sahloul',            3, '18:00:00', 120, 15.0, 4, 4, 'moyen', 0, 0, 'Retour du week-end.'],
            [7, 2, 'Tunis',  'Bizerte',  'Bab Saadoun',               2, '12:00:00',  65,  7.0, 3, 3, 'petit', 0, 0, null],
            [8, 3, 'Tunis',  'Hammamet', 'La Marsa – Zéphyr',         1, '14:00:00',  60,  9.0, 4, 3, 'grand', 0, 1, 'Direction plage, bonne ambiance.'],
            [9, 4, 'Sfax',   'Djerba',   'Sfax centre',               4, '06:30:00', 240, 22.0, 3, 2, 'moyen', 0, 0, null],
            [10, 5, 'Tunis', 'Monastir', 'Montplaisir',               2, '16:45:00', 140, 16.0, 4, 4, 'moyen', 0, 0, 'Passage par Sousse possible.'],
            [11, 1, 'Tunis', 'Kairouan', 'Bab Alioua',                5, '08:30:00', 150, 14.0, 4, 3, 'moyen', 0, 0, null],
            [12, 2, 'Ariana', 'Tunis',   'Ariana Soghra',             1, '07:45:00',  25,  3.0, 3, 3, 'petit', 0, 0, 'Trajet domicile-travail quotidien.'],
        ];

        $trajets = [];
        foreach ($bruts as $t) {
            [$id, $cond, $dep, $arr, $rdv, $j, $h, $duree, $prix, $total, $dispo, $bag, $fum, $ani, $desc] = $t;
            $u = $utilisateurs[$cond];
            $v = $vehicules[$cond];
            $trajets[$id] = [
                'id_trajet' => $id, 'id_conducteur' => $cond,
                'ville_depart' => $dep, 'ville_arrivee' => $arr, 'point_rencontre' => $rdv,
                'date_depart' => self::jour($j), 'heure_depart' => $h, 'duree_estimee_min' => $duree,
                'prix_place' => $prix, 'places_total' => $total, 'places_disponibles' => $dispo,
                'bagages' => $bag, 'fumeur' => $fum, 'animaux' => $ani, 'description' => $desc,
                'statut' => 'ouvert',
                'conducteur_nom' => $u['nom'], 'conducteur_prenom' => $u['prenom'],
                'conducteur_telephone' => $u['telephone'],
                'vehicule_marque' => $v[0], 'vehicule_modele' => $v[1], 'vehicule_couleur' => $v[2],
                'climatisation' => $v[3],
                'note_moyenne' => $notes[$cond][0], 'nb_avis' => $notes[$cond][1],
            ];
        }

        return [
            'jour'         => date('Y-m-d'),
            'utilisateurs' => $utilisateurs,
            'trajets'      => $trajets,
            'reservations' => [],
        ];
    }
}
