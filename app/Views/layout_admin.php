<?php
/**
 * Layout du backoffice (rôle admin) basé sur le template Mazer.
 * Visuellement différent du frontoffice : barre latérale, cartes, étiquette BACKOFFICE.
 */
require __DIR__ . '/admin/partials/head.php';
?>
<div id="app">
    <?php require __DIR__ . '/admin/partials/sidebar.php'; ?>
    <div id="main">
        <?php require __DIR__ . '/admin/partials/header.php'; ?>
        <?= $content ?>
        <?php require __DIR__ . '/admin/partials/footer.php'; ?>
    </div>
</div>
<?php require __DIR__ . '/admin/partials/scripts.php'; ?>
</body>
</html>
