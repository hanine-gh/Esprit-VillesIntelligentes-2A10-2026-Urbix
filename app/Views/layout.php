<!DOCTYPE HTML>
<html lang="fr">
<head>
    <?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <div id="page-wrapper">

        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <?= $content ?>

        <?php require __DIR__ . '/includes/footer.php'; ?>

    </div>

    <?php if (!$isLoggedIn): ?>
        <?php require __DIR__ . '/includes/auth-modal.php'; ?>

        <button type="button" class="auth-fab" data-auth-open aria-label="Connexion / Inscription" title="Connexion / Inscription">
            <i class="fas fa-user" aria-hidden="true"></i>
        </button>
    <?php endif; ?>

    <?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
