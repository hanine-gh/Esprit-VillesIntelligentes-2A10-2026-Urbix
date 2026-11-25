<?php
/**
 * Messages du backoffice (modules intégrés) : message "flash" ($flash) et erreurs de saisie ($erreurs).
 */
?>
<?php if (!empty($flash)): ?>
    <div class="alert alert-light-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>
<?php if (!empty($erreurs)): ?>
    <div class="alert alert-light-danger">
        <ul class="mb-0">
            <?php foreach ($erreurs as $erreur): ?>
                <li><?= e($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
