<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * AdminTrait : outils communs aux contrôleurs du backoffice
 * (chauffeurs, trajets, stations). Le contrôleur doit avoir une propriété $db (PDO).
 */
trait AdminTrait
{
    /** Autorise uniquement un administrateur dont le rôle est confirmé en base. */
    private function requireAdmin(): void
    {
        AuthController::requireRole('admin');

        $stmt = $this->db->prepare('SELECT role, admin_scope FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) $_SESSION['user_id']]);
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        $role = $row['role'] ?? null;
        $_SESSION['admin_scope'] = $row['admin_scope'] ?? 'tout';

        if ($role !== 'admin') {
            $_SESSION['role'] = $role;
            http_response_code(403);
            exit("Accès refusé : vous n'avez pas les permissions nécessaires.");
        }
    }

    /**
     * Gestions Location, Train et Louage : réservées au super administrateur
     * (admin_scope = 'tout'). adminBus (admin_scope = 'bus') reçoit un 403.
     */
    private function requireSuperAdmin(): void
    {
        $this->requireAdmin();

        if ($_SESSION['admin_scope'] !== 'tout') {
            http_response_code(403);
            exit("Accès refusé : cet administrateur gère uniquement la partie Bus.");
        }
    }

    /** Message "flash" affiché une seule fois sur la page suivante. */
    private function flash(string $type, string $message): void
    {
        $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
    }

    /** Lit puis supprime le message "flash". */
    private function popFlash(): ?array
    {
        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        return $flash;
    }

    /** Entier positif lu dans l'URL (ex : ?id=3), sinon 0. */
    private function intFromQuery(string $key = 'id'): int
    {
        $v = $_GET[$key] ?? '';
        return (is_string($v) && ctype_digit($v)) ? (int) $v : 0;
    }

    /** Entier positif lu dans un formulaire POST, sinon 0. */
    private function intFromPost(string $key = 'id'): int
    {
        $v = $_POST[$key] ?? '';
        return (is_string($v) && ctype_digit($v)) ? (int) $v : 0;
    }

    /** Vérifie le jeton CSRF d'un formulaire POST. */
    private function csrfOk(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        return $this->verifyCsrfToken(is_string($token) ? $token : null);
    }

    /** Heure au format HH:MM (00:00 à 23:59). */
    private function isValidTime(string $t): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
    }
}
