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
