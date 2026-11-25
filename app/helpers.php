<?php
function navActive(string $page, string $currentPage): string {
    return $page === $currentPage ? 'current' : '';
}

/** Échappe une valeur pour l'affichage HTML. */
function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Construit une URL interne : url('covoiturage', ['destination' => 'Sousse']). */
function url(string $page, array $params = []): string {
    $query = http_build_query(array_merge(['page' => $page], $params));
    return BASE_URL . 'index.php?' . $query;
}

/** Utilisateur connecté (ou null). */
function utilisateurConnecte(): ?array {
    return $_SESSION['utilisateur'] ?? null;
}

/** Message flash : flash('succes', '...') pour enregistrer, flash() pour lire et vider. */
function flash(?string $type = null, ?string $message = null): ?array {
    if ($type !== null) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Jeton CSRF pour les formulaires POST. */
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrfValide(?string $token): bool {
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/** Formate un prix en dinars tunisiens : 15,000 DT */
function prixDT($montant): string {
    return number_format((float) $montant, 3, ',', ' ') . ' DT';
}

/** Date en français : "mercredi 26 novembre 2026" */
function dateFr(string $date): string {
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois  = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
              'août', 'septembre', 'octobre', 'novembre', 'décembre'];
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

/** Initiales pour l'avatar : "Karim Ben Ali" -> "KB" */
function initiales(string $prenom, string $nom): string {
    $p = function_exists('mb_substr') ? mb_substr($prenom, 0, 1) : substr($prenom, 0, 1);
    $n = function_exists('mb_substr') ? mb_substr($nom, 0, 1) : substr($nom, 0, 1);
    return strtoupper($p . $n);
}
