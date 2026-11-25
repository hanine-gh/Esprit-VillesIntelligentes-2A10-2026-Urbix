<?php
/**
 * Vue : missions du chauffeur connecté (frontoffice).
 * Une mission = un trajet attribué + le bus à conduire.
 * Variables : $missions (TrajetModel[]), $busAttitre (BusModel|null), $busStatuts
 */
?>
<section id="main">
    <div class="container">
        <h2 class="major"><span>Mes missions</span></h2>

        <?php if ($busAttitre): ?>
            <div class="mission-bus-attitre">
                <i class="fas fa-bus"></i>
                <div>
                    <span class="label">Mon bus attitré</span>
                    <strong><?= e($busAttitre->getMatricule()) ?></strong>
                    — <?= e($busAttitre->getMarque() . ' ' . $busAttitre->getModele()) ?>, <?= (int) $busAttitre->getCapacite() ?> places
                    <?php if ($busAttitre->getStatut() !== 'disponible'): ?>
                        <span class="auth-badge auth-badge-admin"><?= e($busStatuts[$busAttitre->getStatut()] ?? $busAttitre->getStatut()) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($missions)): ?>
            <div class="trajet-empty">
                <i class="fas fa-clipboard-list"></i>
                <p>Aucune mission ne vous est attribuée pour le moment.<br>
                   Votre administrateur vous attribuera vos trajets et vos bus.</p>
            </div>
        <?php else: ?>
            <div class="trajet-list">
                <?php foreach ($missions as $m): ?>
                    <article class="trajet-card mission-card <?= $m->getStatut() !== 'actif' ? 'is-inactive' : '' ?>">
                        <header class="trajet-card-header">
                            <span class="trajet-code"><?= e($m->getCode()) ?></span>
                            <div class="trajet-title">
                                <h3><?= e($m->getNom()) ?></h3>
                                <p><?= e($m->getPointDepart()) ?> <i class="fas fa-long-arrow-alt-right"></i> <?= e($m->getPointArrivee()) ?></p>
                            </div>
                            <div class="trajet-meta">
                                <span class="auth-badge <?= $m->getStatut() === 'actif' ? 'auth-badge-service' : 'auth-badge-user' ?>">
                                    <?= $m->getStatut() === 'actif' ? 'Mission active' : 'Inactive' ?>
                                </span>
                            </div>
                        </header>

                        <div class="mission-grid">
                            <div class="mission-block">
                                <span class="label"><i class="far fa-clock"></i> Horaires</span>
                                <strong>Départ <?= e($m->getHeureDepart()) ?></strong>
                                <span>Arrivée <?= e($m->getHeureArrivee()) ?></span>
                            </div>

                            <div class="mission-block">
                                <span class="label"><i class="fas fa-map-marker-alt"></i> Stations</span>
                                <strong><?= $m->getNbStations() ?> arrêt(s)</strong>
                                <a href="<?= BASE_URL ?>index.php?page=mes-trajets#trajet-<?= (int) $m->getId() ?>">Voir les stations</a>
                            </div>

                            <div class="mission-block mission-bus <?= $m->getBusId() ? '' : 'is-missing' ?>">
                                <span class="label"><i class="fas fa-bus"></i> Bus à conduire</span>
                                <?php if ($m->getBusId()): ?>
                                    <strong><?= e($m->getBusMatricule()) ?></strong>
                                    <span><?= e($m->getBusMarque() . ' ' . $m->getBusModele()) ?> · <?= (int) $m->getBusCapacite() ?> places</span>
                                    <?php if ($m->getBusStatut() !== 'disponible'): ?>
                                        <span class="auth-badge auth-badge-admin"><?= e($busStatuts[$m->getBusStatut()] ?? $m->getBusStatut()) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <strong>Aucun bus attribué</strong>
                                    <span>Contactez votre administrateur.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
