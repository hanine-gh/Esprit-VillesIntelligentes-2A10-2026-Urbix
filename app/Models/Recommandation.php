<?php
namespace App\Models;

/**
 * Recherche "intelligente" de covoiturage :
 *  - correspondance des villes insensible à la casse et aux accents ("sfa" → "Sfax", "gabes" → "Gabès") ;
 *  - score de recommandation (prix, note du conducteur, horaire souhaité, confort) ;
 *  - si aucun trajet ce jour-là : propositions aux dates proches (±3 jours),
 *    puis trajets similaires vers la même destination.
 */
class Recommandation {

    public static function normaliser(string $texte): string {
        $texte = trim($texte);
        $texte = function_exists('mb_strtolower') ? mb_strtolower($texte, 'UTF-8') : strtolower($texte);
        $texte = strtr($texte, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
            '-' => ' ', "'" => ' ',
        ]);
        return preg_replace('/\s+/', ' ', $texte);
    }

    private static function villeCorrespond(string $ville, string $recherche): bool {
        if ($recherche === '') {
            return true;
        }
        $v = self::normaliser($ville);
        $r = self::normaliser($recherche);
        return $v === $r || strpos($v, $r) === 0 || strpos($v, $r) !== false;
    }

    /**
     * @param array $c critères : depart, destination, date (Y-m-d|''), heure (H:i|''), places (int), prix_max (float|0)
     * @return array ['type' => 'exact'|'dates_proches'|'similaires'|'aucun', 'trajets' => [...]]
     */
    public static function rechercher(array $c, ?int $idUtilisateur = null): array {
        $tous = Trajet::aVenir();

        // On ne propose pas à un conducteur ses propres trajets.
        if ($idUtilisateur !== null) {
            $tous = array_values(array_filter($tous, fn($t) => (int) $t['id_conducteur'] !== $idUtilisateur));
        }

        $base = array_values(array_filter($tous, function ($t) use ($c) {
            return self::villeCorrespond($t['ville_arrivee'], $c['destination'])
                && self::villeCorrespond($t['ville_depart'], $c['depart'])
                && (int) $t['places_disponibles'] >= $c['places']
                && ($c['prix_max'] <= 0 || (float) $t['prix_place'] <= $c['prix_max']);
        }));

        if ($c['date'] === '') {
            $type = $base ? 'exact' : 'aucun';
            $trajets = $base;
        } else {
            $exacts = array_values(array_filter($base, fn($t) => $t['date_depart'] === $c['date']));
            if ($exacts) {
                $type = 'exact';
                $trajets = $exacts;
            } else {
                $cible = strtotime($c['date']);
                $trajets = array_values(array_filter($base, function ($t) use ($cible) {
                    return abs(strtotime($t['date_depart']) - $cible) <= 3 * 86400;
                }));
                $type = $trajets ? 'dates_proches' : 'aucun';
            }
        }

        if ($type === 'aucun') {
            // Dernier recours : même destination, n'importe quel départ / date.
            $trajets = array_values(array_filter($tous, function ($t) use ($c) {
                return self::villeCorrespond($t['ville_arrivee'], $c['destination'])
                    && (int) $t['places_disponibles'] >= $c['places'];
            }));
            $type = $trajets ? 'similaires' : 'aucun';
        }

        return ['type' => $type, 'trajets' => self::noter($trajets, $c)];
    }

    /** Calcule le score de chaque trajet, ajoute les badges et trie (meilleur en premier). */
    private static function noter(array $trajets, array $c): array {
        if (!$trajets) {
            return [];
        }
        $prix = array_map(fn($t) => (float) $t['prix_place'], $trajets);
        $pMin = min($prix);
        $pMax = max($prix);
        $notes = array_map(fn($t) => (float) ($t['note_moyenne'] ?? 0), $trajets);
        $nMax = max($notes);

        $heureCible = null;
        if ($c['heure'] !== '' && preg_match('/^(\d{2}):(\d{2})$/', $c['heure'], $m)) {
            $heureCible = (int) $m[1] * 60 + (int) $m[2];
        }
        $ecarts = [];
        foreach ($trajets as $i => $t) {
            $min = (int) substr($t['heure_depart'], 0, 2) * 60 + (int) substr($t['heure_depart'], 3, 2);
            $ecarts[$i] = $heureCible === null
                ? (strtotime($t['date_depart'] . ' ' . $t['heure_depart']) - time()) / 60   // plus tôt = mieux
                : abs($min - $heureCible);
        }
        $eMin = min($ecarts);
        $eMax = max($ecarts);

        foreach ($trajets as $i => &$t) {
            $sPrix    = $pMax > $pMin ? ($pMax - (float) $t['prix_place']) / ($pMax - $pMin) : 1;
            $sNote    = $t['note_moyenne'] !== null ? (float) $t['note_moyenne'] / 5 : 0.6;
            $sHoraire = $eMax > $eMin ? ($eMax - $ecarts[$i]) / ($eMax - $eMin) : 1;
            $sConfort = ((int) $t['climatisation'] ? 0.5 : 0) + min((int) $t['places_disponibles'], 4) / 8;

            $t['score'] = (int) round(100 * (0.35 * $sPrix + 0.30 * $sNote + 0.25 * $sHoraire + 0.10 * $sConfort));

            $badges = [];
            if (count($trajets) > 1 && (float) $t['prix_place'] === $pMin) {
                $badges[] = 'Le moins cher';
            }
            if ($t['note_moyenne'] !== null && (float) $t['note_moyenne'] === $nMax && $nMax >= 4) {
                $badges[] = 'Conducteur le mieux noté';
            }
            if ($heureCible !== null && $ecarts[$i] === $eMin) {
                $badges[] = 'Horaire le plus proche';
            }
            $t['badges'] = $badges;
            $t['meilleur'] = false;
            $t['heure_arrivee'] = $t['duree_estimee_min']
                ? date('H:i:s', strtotime($t['heure_depart']) + 60 * (int) $t['duree_estimee_min'])
                : null;
        }
        unset($t);

        usort($trajets, fn($a, $b) => $b['score'] <=> $a['score'] ?: strcmp($a['date_depart'] . $a['heure_depart'], $b['date_depart'] . $b['heure_depart']));
        $trajets[0]['meilleur'] = true;
        return $trajets;
    }
}
