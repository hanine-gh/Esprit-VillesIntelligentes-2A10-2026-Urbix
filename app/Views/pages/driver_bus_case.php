<?php
/**
 * Vue : "Cas de mon bus" — le chauffeur signale l'état de son bus attitré (frontoffice).
 * Variables : $bus (BusModel|null), $errors, $old, $success, $statuts, $csrfToken
 * Aucun contrôle HTML5 : contrôle de saisie en JS (trajet-validation.js) + PHP.
 */
?>
<section id="main">
    <div class="container">
        <div class="auth-card wide">
            <h2 class="auth-title">Cas de mon bus</h2>

            <?php if (!$bus): ?>
                <div class="trajet-empty">
                    <i class="fas fa-bus"></i>
                    <p>Aucun bus ne vous est attribué pour le moment.<br>
                       Votre administrateur vous attribuera un bus attitré.</p>
                </div>
            <?php else: ?>
                <p class="auth-subtitle">
                    <strong><?= e($bus->getMatricule()) ?></strong> — <?= e($bus->getMarque() . ' ' . $bus->getModele()) ?>,
                    <?= (int) $bus->getCapacite() ?> places
                    <?php if ($bus->getEtatMajAt()): ?>
                        <br><small>Dernier signalement : <?= e(date('d/m/Y à H:i', strtotime($bus->getEtatMajAt()))) ?></small>
                    <?php endif; ?>
                </p>

                <?php if ($success): ?>
                    <div class="auth-alert auth-alert-success"><?= e($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="auth-alert auth-alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="bus-case-form" method="post" novalidate action="<?= BASE_URL ?>index.php?page=mon-bus">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="auth-field">
                        <label for="bc-statut">État du bus</label>
                        <select id="bc-statut" name="statut">
                            <option value="">Choisir...</option>
                            <?php foreach ($statuts as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= ($old['statut'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="auth-field">
                        <label for="bc-commentaire">Description du problème <small>(obligatoire si le bus n'est pas disponible)</small></label>
                        <textarea id="bc-commentaire" name="etat_commentaire" rows="4"
                                  placeholder="Ex : pneu avant droit crevé, climatisation en panne..."><?= e($old['etat_commentaire'] ?? '') ?></textarea>
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="auth-btn auth-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                        <a href="<?= BASE_URL ?>index.php?page=mes-missions" class="auth-btn auth-btn-secondary">Retour aux missions</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
