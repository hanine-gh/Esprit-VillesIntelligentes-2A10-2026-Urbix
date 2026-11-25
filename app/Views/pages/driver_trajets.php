<?php
/**
 * Vue : trajets attribués au chauffeur connecté + leurs stations (frontoffice).
 * Variables : $trajets (TrajetModel[]), $stations ([trajetId => StationModel[]]), $flash
 */
?>
<section id="main">
    <div class="container">
        <h2 class="major"><span>Mes trajets</span></h2>

        <?php if ($flash): ?>
            <div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : 'auth-alert-error' ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($trajets)): ?>
            <div class="trajet-empty">
                <i class="fas fa-route"></i>
                <p>Aucun trajet ne vous est attribué pour le moment.<br>
                   Votre administrateur vous attribuera vos trajets.</p>
            </div>
        <?php else: ?>
            <div class="trajet-list">
                <?php foreach ($trajets as $t): ?>
                    <?php $list = $stations[(int) $t->getId()] ?? []; ?>
                    <article class="trajet-card" id="trajet-<?= (int) $t->getId() ?>">
                        <header class="trajet-card-header">
                            <span class="trajet-code"><?= e($t->getCode()) ?></span>
                            <div class="trajet-title">
                                <h3><?= e($t->getNom()) ?></h3>
                                <p>
                                    <?= e($t->getPointDepart()) ?> <i class="fas fa-long-arrow-alt-right"></i> <?= e($t->getPointArrivee()) ?>
                                </p>
                            </div>
                            <div class="trajet-meta">
                                <span><i class="far fa-clock"></i> <?= e($t->getHeureDepart()) ?> – <?= e($t->getHeureArrivee()) ?></span>
                                <span class="auth-badge <?= $t->getStatut() === 'actif' ? 'auth-badge-service' : 'auth-badge-user' ?>">
                                    <?= $t->getStatut() === 'actif' ? 'Actif' : 'Inactif' ?>
                                </span>
                            </div>
                        </header>

                        <div class="trajet-card-body">
                            <h4><i class="fas fa-map-marker-alt"></i> Stations (<?= count($list) ?>)</h4>

                            <?php if (empty($list)): ?>
                                <p class="trajet-no-station">Aucune station sur ce trajet pour le moment.</p>
                            <?php else: ?>
                                <ol class="station-timeline">
                                    <?php foreach ($list as $s): ?>
                                        <li>
                                            <span class="station-time"><?= e($s->getHeurePassage()) ?></span>
                                            <span class="station-dot"><?= $s->getOrdre() ?></span>
                                            <div class="station-info">
                                                <strong><?= e($s->getNom()) ?></strong>
                                                <small><?= e($s->getAdresse()) ?></small>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>

                            <div class="profile-actions">
                                <a href="<?= BASE_URL ?>index.php?page=mes-trajets-station&amp;trajet=<?= (int) $t->getId() ?>"
                                   class="auth-btn auth-btn-primary">
                                    <i class="fas fa-plus"></i> Ajouter une station
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
