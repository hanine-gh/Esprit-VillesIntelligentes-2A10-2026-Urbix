<?php
/**
 * Vue : formulaire de demande de STOP (frontoffice).
 * Variables : $trajet (TrajetModel), $stations (StationModel[]), $errors, $old, $minDate, $maxDate, $csrfToken
 * Aucun contrôle HTML5 : contrôle de saisie en JS (trajet-validation.js) + PHP.
 */
?>
<section id="main">
    <div class="container">
        <div class="auth-card wide">
            <h2 class="auth-title"><i class="fas fa-hand-paper"></i> Demande de STOP</h2>
            <p class="auth-subtitle">
                Ligne <strong><?= e($trajet->getCode()) ?></strong> :
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

            <form id="stop-form" method="post" novalidate
                  action="<?= BASE_URL ?>index.php?page=demande-stop&amp;trajet=<?= (int) $trajet->getId() ?>"
                  data-min-date="<?= e($minDate) ?>" data-max-date="<?= e($maxDate) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div class="auth-field">
                    <label for="ds-station">Station où le bus doit s'arrêter</label>
                    <select id="ds-station" name="station_id">
                        <option value="">Choisir une station...</option>
                        <?php foreach ($stations as $s): ?>
                            <option value="<?= (int) $s->getId() ?>" <?= ($old['station_id'] ?? '') === (string) $s->getId() ? 'selected' : '' ?>>
                                <?= $s->getOrdre() ?>. <?= e($s->getNom()) ?> — passage à <?= e($s->getHeurePassage()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="ds-date">Date du voyage</label>
                        <input type="date" id="ds-date" name="date_trajet" value="<?= e($old['date_trajet'] ?? '') ?>">
                    </div>
                    <div class="auth-field">
                        <label for="ds-nb">Nombre de personnes</label>
                        <input type="text" id="ds-nb" name="nb_personnes" placeholder="1" value="<?= e($old['nb_personnes'] ?? '') ?>">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="ds-message">Message pour le chauffeur <small>(facultatif)</small></label>
                    <textarea id="ds-message" name="message" rows="3"
                              placeholder="Ex : je serai avec une poussette."><?= e($old['message'] ?? '') ?></textarea>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="auth-btn auth-btn-primary"><i class="fas fa-paper-plane"></i> Envoyer la demande</button>
                    <a href="<?= BASE_URL ?>index.php?page=trajets-disponibles" class="auth-btn auth-btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</section>
