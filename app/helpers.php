<?php
function navActive(string $page, string $currentPage): string {
    return $page === $currentPage ? 'current' : '';
}
