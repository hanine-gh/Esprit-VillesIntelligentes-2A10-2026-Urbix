<?php
declare(strict_types=1);

namespace App\Controllers;

/**
 * FrontTrait : outils communs aux pages "client" des modules intégrés
 * (location, train, louage, covoiturage). Le contrôleur doit avoir une propriété $db (PDO)
 * et utiliser UserFormTrait (verifyCsrfToken).
 */
trait FrontTrait
{
    /** Exige un utilisateur connecté (popup de connexion sinon) et retourne son id. */
    private function requireUserId(): int
    {
        AuthController::requireAuth();
        return (int) $_SESSION['user_id'];
    }

    /** Nom affiché de l'utilisateur connecté et son téléphone (pour pré-remplir les formulaires). */
    private function currentUserInfo(): array
    {
        $stmt = $this->db->prepare('SELECT nom, prenom, telephone FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) ($_SESSION['user_id'] ?? 0)]);
        $u = $stmt->fetch() ?: ['nom' => '', 'prenom' => '', 'telephone' => ''];
        return ['nom' => trim($u['prenom'] . ' ' . $u['nom']), 'telephone' => (string) $u['telephone']];
    }

    /** Message affiché une seule fois sur la page suivante (type : success | error). */
    private function frontFlash(string $type, string $message): void
    {
        $_SESSION['module_flash'] = ['type' => $type, 'message' => $message];
    }

    private function popFrontFlash(): ?array
    {
        $f = $_SESSION['module_flash'] ?? null;
        unset($_SESSION['module_flash']);
        return $f;
    }

    /** Jeton CSRF du formulaire POST valide ? */
    private function frontCsrfOk(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        return $this->verifyCsrfToken(is_string($token) ? $token : null);
    }

    private function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    private static function isDate(string $v): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $v);
        return $d !== false && $d->format('Y-m-d') === $v;
    }
}
