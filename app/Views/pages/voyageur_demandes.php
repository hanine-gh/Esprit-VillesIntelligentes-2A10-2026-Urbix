<?php
/**
 * Vue : demandes de STOP du voyageur connecté (frontoffice).
 * Variables : $demandes (DemandeStopModel[]), $statuts, $flash, $csrfToken
 */
$badge = ['en_attente' => 'auth-badge-user', 'acceptee' => 'auth-badge-service', 'refusee' => 'auth-badge-admin'];
?>
<section id="main">
    <div class="container">
        <h2 class="major"><span>Mes demandes de STOP</span></h2>

        <?php if ($flash): ?>
            <div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : 'auth-alert-error' ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (empty($demandes)): ?>
            <div class="trajet-empty">
                <i class="fas fa-hand-paper"></i>
                <p>Vous n'avez fait aucune demande de STOP.</p>
                <a href="<?= BASE_URL ?>index.php?page=trajets-disponibles" class="auth-btn auth-btn-primary">Voir les trajets disponibles</a>
            </div>
        <?php else: ?>
            <div class="demande-list">
                <?php foreach ($demandes as $d): ?>
                    <article class="demande-card">
                        <div class="demande-main">
                            <span class="trajet-code"><?= e($d->getTrajetCode()) ?></span>
                            <div>
                                <strong><i class="fas fa-map-marker-alt"></i> <?= e($d->getStationNom()) ?></strong>
                                <small><?= e($d->getStationAdresse()) ?></small>
                                <span class="demande-info">
                                    <i class="far fa-calendar-alt"></i> <?= e(date('d/m/Y', strtotime($d->getDateTrajet()))) ?>
                                    · <i class="far fa-clock"></i> passage à <?= e($d->getHeurePassage()) ?>
                                    · <i class="fas fa-user-friends"></i> <?= $d->getNbPersonnes() ?> pers.
                                </span>
                                <?php if ($d->getMessage()): ?>
                                    <em class="demande-message">« <?= e($d->getMessage()) ?> »</em>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="demande-side">
                            <span class="auth-badge <?= $badge[$d->getStatut()] ?? '' ?>"><?= e($statuts[$d->getStatut()] ?? $d->getStatut()) ?></span>
                            <?php if ($d->getStatut() === 'en_attente'): ?>
                                <form method="post" action="<?= BASE_URL ?>index.php?page=demande-stop-annuler" data-confirm="Annuler cette demande de STOP ?">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $d->getId() ?>">
                                    <button type="submit" class="auth-btn auth-btn-secondary demande-cancel">Annuler</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
