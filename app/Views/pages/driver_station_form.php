<?php
/**
 * Vue : ajout d'une station sur un trajet du chauffeur connecté (frontoffice).
 * Variables : $trajet (TrajetModel), $errors, $old, $csrfToken
 * data-min / data-max : horaires du trajet, utilisés par le contrôle de saisie JS.
 */
?>
<section id="main">
    <div class="container">
        <div class="auth-card wide">
            <h2 class="auth-title">Ajouter une station</h2>
            <p class="auth-subtitle">
                Trajet <strong><?= e($trajet->getCode()) ?></strong> :
                <?= e($trajet->getPointDepart()) ?> <i class="fas fa-long-arrow-alt-right"></i> <?= e($trajet->getPointArrivee()) ?>
                · <?= e($trajet->getHeureDepart()) ?> – <?= e($trajet->getHeureArrivee()) ?>
            </p>

            <?php if (!empty($errors)): ?>
                <div class="auth-alert auth-alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="station-form" method="post" novalidate
                  action="<?= BASE_URL ?>index.php?page=mes-trajets-station&amp;trajet=<?= (int) $trajet->getId() ?>"
                  data-min="<?= e($trajet->getHeureDepart()) ?>" data-max="<?= e($trajet->getHeureArrivee()) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="s-nom">Nom de la station</label>
                        <input type="text" id="s-nom" name="nom" placeholder="Place de la République" value="<?= e($old['nom'] ?? '') ?>">
                    </div>
                    <div class="auth-field">
                        <label for="s-ordre">Ordre de passage</label>
                        <input type="text" id="s-ordre" name="ordre" placeholder="1" value="<?= e($old['ordre'] ?? '') ?>">
                    </div>
                </div>

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="s-adresse">Adresse</label>
                        <input type="text" id="s-adresse" name="adresse" placeholder="12 avenue Habib Bourguiba, Tunis" value="<?= e($old['adresse'] ?? '') ?>">
                    </div>
                    <div class="auth-field">
                        <label for="s-heure">Heure de passage</label>
                        <input type="time" id="s-heure" name="heure_passage" value="<?= e($old['heure_passage'] ?? '') ?>">
                    </div>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="auth-btn auth-btn-primary"><i class="fas fa-save"></i> Ajouter la station</button>
                    <a href="<?= BASE_URL ?>index.php?page=mes-trajets" class="auth-btn auth-btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</section>
