<?php
/**
 * Vue : trajets de bus disponibles pour les voyageurs (frontoffice).
 * Variables : $trajets (TrajetModel[]), $stations ([trajetId => StationModel[]]), $search, $flash
 */
?>
<section id="main">
    <div class="container">
        <h2 class="major"><span>Trajets disponibles</span></h2>

        <?php if ($flash): ?>
            <div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : 'auth-alert-error' ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <form method="get" action="<?= BASE_URL ?>index.php" class="voyageur-search">
            <input type="hidden" name="page" value="trajets-disponibles">
            <input type="text" name="q" placeholder="Rechercher une ligne, un lieu ou une station..." value="<?= e($search) ?>">
            <button type="submit" class="auth-btn auth-btn-primary"><i class="fas fa-search"></i> Rechercher</button>
            <?php if ($search !== ''): ?>
                <a href="<?= BASE_URL ?>index.php?page=trajets-disponibles" class="auth-btn auth-btn-secondary">Effacer</a>
            <?php endif; ?>
        </form>

        <?php if (empty($trajets)): ?>
            <div class="trajet-empty">
                <i class="fas fa-route"></i>
                <p><?= $search !== '' ? 'Aucun trajet ne correspond à votre recherche.' : 'Aucun trajet disponible pour le moment.' ?></p>
            </div>
        <?php else: ?>
            <div class="trajet-list">
                <?php foreach ($trajets as $t): ?>
                    <?php $list = $stations[(int) $t->getId()] ?? []; ?>
                    <article class="trajet-card">
                        <header class="trajet-card-header">
                            <span class="trajet-code"><?= e($t->getCode()) ?></span>
                            <div class="trajet-title">
                                <h3><?= e($t->getNom()) ?></h3>
                                <p><?= e($t->getPointDepart()) ?> <i class="fas fa-long-arrow-alt-right"></i> <?= e($t->getPointArrivee()) ?></p>
                            </div>
                            <div class="trajet-meta">
                                <span><i class="far fa-clock"></i> <?= e($t->getHeureDepart()) ?> – <?= e($t->getHeureArrivee()) ?></span>
                            </div>
                        </header>

                        <div class="trajet-card-body">
                            <h4><i class="fas fa-map-marker-alt"></i> Stations (<?= count($list) ?>)</h4>

                            <?php if (empty($list)): ?>
                                <p class="trajet-no-station">Les stations de ce trajet ne sont pas encore définies.</p>
                            <?php else: ?>
                                <ol class="station-timeline">
                                    <?php foreach ($list as $s): ?>
                                        <li>
                                            <span class="station-time"><?= e($s->getHeurePassage()) ?></span>
                                            <span class="station-dot"><?= $s->getOrdre() ?></span>
                                            <div class="station-info">
                                                <strong><?= e($s->getNom()) ?></strong>
                                                <small><?= e($s->getAdresse()) ?></small>
                                                <a class="station-stop-link"
                                                   href="<?= BASE_URL ?>index.php?page=demande-stop&amp;trajet=<?= (int) $t->getId() ?>&amp;station=<?= (int) $s->getId() ?>">
                                                    <i class="fas fa-hand-paper"></i> Demander un STOP ici
                                                </a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>

                                <div class="profile-actions">
                                    <a href="<?= BASE_URL ?>index.php?page=demande-stop&amp;trajet=<?= (int) $t->getId() ?>" class="auth-btn auth-btn-primary">
                                        <i class="fas fa-hand-paper"></i> Demander un STOP
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
