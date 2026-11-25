<?php
function navActive(string $page, string $currentPage): string {
    return $page === $currentPage ? 'current' : '';
}

/**
 * Échappe une valeur avant de l'afficher dans le HTML (protection XSS).
 */
function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// =====================================================================
// Affichage : modules intégrés (louage, covoiturage)
// =====================================================================

/** Date et heure lisibles, ex. 26/11/2026 09:30 */
function dateHeure(?string $dt): string {
    return $dt ? date('d/m/Y H:i', strtotime($dt)) : '—';
}

/** Valeur pour un champ datetime-local (2026-11-26T09:30) */
function dateTimeLocal(?string $dt): string {
    return $dt ? date('Y-m-d\TH:i', strtotime($dt)) : '';
}

/** Prix en TND, ou un tiret s'il n'est pas encore fixé */
function prixTnd(?float $prix): string {
    return $prix === null ? '—' : rtrim(rtrim(number_format($prix, 3, '.', ''), '0'), '.') . ' TND';
}

/** Prix en dinars tunisiens : 15,000 DT */
function prixDT($montant): string {
    return number_format((float) $montant, 3, ',', ' ') . ' DT';
}

/** Date en français : "mercredi 26 novembre 2026" */
function dateFr(string $date): string {
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $ts = strtotime($date);
    if ($ts === false) {
        return $date;
    }
    return $jours[(int) date('w', $ts)] . ' ' . (int) date('j', $ts) . ' ' . $mois[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Mois abrégé : "2026-11-26" -> "nov." */
function moisCourt(string $date): string {
    $mois = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $ts = strtotime($date);
    return $ts === false ? '' : $mois[(int) date('n', $ts)];
}

/** Heure "08:00:00" -> "08h00" */
function heureFr(string $heure): string {
    return substr($heure, 0, 2) . 'h' . substr($heure, 3, 2);
}

/** Initiales pour l'avatar : "Karim", "Ben Ali" -> "KB" */
function initiales(string $prenom, string $nom): string {
    return mb_strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
}

/** ' selected' si les deux valeurs sont égales (listes déroulantes) */
function selected($valeur, $choix): string {
    return (string) $valeur === (string) $choix ? ' selected' : '';
}
