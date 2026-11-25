<?php
// Ouverture automatique de la fenêtre de connexion (ex. : "Accepter" sans être connecté).
$ouvrirAuth = $_SESSION['ouvrir_auth'] ?? null;
unset($_SESSION['ouvrir_auth']);
$flashMsg = flash();
$moi = utilisateurConnecte();
?>
<!DOCTYPE HTML>
<html lang="fr">
<head>
    <?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <div id="page-wrapper">

        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <?php if ($flashMsg): ?>
            <div class="flash-toast flash-<?= e($flashMsg['type']) ?>" role="status">
                <i class="fas <?= $flashMsg['type'] === 'succes' ? 'fa-check-circle' : ($flashMsg['type'] === 'erreur' ? 'fa-exclamation-triangle' : 'fa-info-circle') ?>" aria-hidden="true"></i>
                <span><?= e($flashMsg['message']) ?></span>
                <button type="button" class="flash-close" aria-label="Fermer">&times;</button>
            </div>
        <?php endif; ?>

        <?= $content ?>

        <?php require __DIR__ . '/includes/footer.php'; ?>

    </div>

    <?php if (!$moi): ?>
        <?php require __DIR__ . '/includes/auth-modal.php'; ?>
    <?php endif; ?>

    <!-- Icône à droite : ouvre l'interface utilisateur -->
    <?php if ($moi): ?>
        <a href="<?= url('espace-utilisateur') ?>" class="auth-fab is-connected" aria-label="Mon espace" title="Mon espace">
            <i class="fas fa-user-circle" aria-hidden="true"></i>
        </a>
    <?php else: ?>
        <button type="button" class="auth-fab" data-auth-open aria-label="Connexion / Inscription" title="Connexion / Inscription">
            <i class="fas fa-user" aria-hidden="true"></i>
        </button>
    <?php endif; ?>

    <?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
