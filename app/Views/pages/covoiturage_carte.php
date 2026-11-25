<?php
/**
 * Vue partielle : carte d'une proposition de covoiturage.
 * Variable : $t (CovoiturageTrajetModel, avec score/badges si issu de la recherche)
 */
$note      = $t->getNoteMoyenne();
$score     = $t->getScore();
$meilleur  = (bool) $t->getMeilleur();
$timestamp = strtotime($t->getDateDepart() . ' ' . $t->getHeureDepart());
$duree     = (int) $t->getDureeEstimeeMin();
$arrivee   = $t->getHeureArrivee();
$bagages   = ['petit' => 'Petit bagage', 'moyen' => 'Bagage moyen', 'grand' => 'Grand bagage'];
$vehicule  = trim($t->getVehiculeMarque() . ' ' . $t->getVehiculeModele());
$donnees   = [
    'id'         => (int) $t->getId(),
    'conducteur' => $t->getConducteurPrenom() . ' ' . $t->getConducteurNom(),
    'initiales'  => initiales($t->getConducteurPrenom(), $t->getConducteurNom()),
    'depart'     => $t->getVilleDepart(),
    'arrivee'    => $t->getVilleArrivee(),
    'date'       => dateFr($t->getDateDepart()),
    'heure'      => heureFr($t->getHeureDepart()),
    'rdv'        => (string) $t->getPointRencontre(),
    'vehicule'   => $vehicule,
    'prix'       => $t->getPrixPlace(),
    'places'     => $t->getPlacesDisponibles(),
];
?>
<article class="covoit-card<?= $meilleur ? ' is-best' : '' ?>"
         data-prix="<?= e((string) $t->getPrixPlace()) ?>"
         data-depart="<?= (int) $timestamp ?>"
         data-note="<?= e((string) ($note ?? 0)) ?>"
         data-score="<?= e((string) ($score ?? 0)) ?>"
         data-clim="<?= (int) $t->getClimatisation() ?>"
         data-fumeur="<?= (int) $t->getFumeur() ?>">

    <?php if ($meilleur): ?>
        <div class="trajet-ribbon"><i class="fas fa-magic" aria-hidden="true"></i> Recommandé par Urbix</div>
    <?php endif; ?>

    <div class="trajet-conducteur">
        <div class="avatar" aria-hidden="true"><?= e($donnees['initiales']) ?></div>
        <div>
            <h3 class="conducteur-nom"><?= e($donnees['conducteur']) ?></h3>
            <div class="etoiles" title="<?= $note !== null ? e(number_format($note, 1, ',', '')) . ' / 5' : 'Nouveau conducteur' ?>">
                <?php if ($note !== null): ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="<?= $note >= $i ? 'fas fa-star' : ($note >= $i - 0.5 ? 'fas fa-star-half-alt' : 'far fa-star') ?>" aria-hidden="true"></i>
                    <?php endfor; ?>
                    <span><?= e(number_format($note, 1, ',', '')) ?> (<?= $t->getNbAvis() ?> avis)</span>
                <?php else: ?>
                    <span>Nouveau conducteur</span>
                <?php endif; ?>
            </div>
            <?php if ($vehicule !== ''): ?>
                <p class="vehicule"><i class="fas fa-car-side" aria-hidden="true"></i>
                    <?= e($vehicule) ?><?= $t->getVehiculeCouleur() ? ' · ' . e($t->getVehiculeCouleur()) : '' ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="trajet-itineraire">
        <p class="trajet-date"><i class="far fa-calendar-alt" aria-hidden="true"></i> <?= e(ucfirst(dateFr($t->getDateDepart()))) ?></p>
        <div class="timeline">
            <div class="etape">
                <strong><?= e(heureFr($t->getHeureDepart())) ?></strong>
                <span><?= e($t->getVilleDepart()) ?></span>
            </div>
            <div class="ligne" aria-hidden="true">
                <?php if ($duree > 0): ?>
                    <em><?= intdiv($duree, 60) ?>h<?= str_pad((string) ($duree % 60), 2, '0', STR_PAD_LEFT) ?></em>
                <?php endif; ?>
            </div>
            <div class="etape">
                <strong><?= $arrivee ? e(heureFr($arrivee)) : '--h--' ?></strong>
                <span><?= e($t->getVilleArrivee()) ?></span>
            </div>
        </div>
        <?php if ($t->getPointRencontre()): ?>
            <p class="rdv"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> RDV : <?= e($t->getPointRencontre()) ?></p>
        <?php endif; ?>
        <ul class="tags">
            <li><i class="fas fa-suitcase" aria-hidden="true"></i> <?= e($bagages[$t->getBagages()] ?? 'Bagage') ?></li>
            <?php if ($t->getClimatisation()): ?><li><i class="fas fa-snowflake" aria-hidden="true"></i> Climatisé</li><?php endif; ?>
            <li><i class="fas <?= $t->getFumeur() ? 'fa-smoking' : 'fa-smoking-ban' ?>" aria-hidden="true"></i> <?= $t->getFumeur() ? 'Fumeur' : 'Non-fumeur' ?></li>
            <?php if ($t->getAnimaux()): ?><li><i class="fas fa-paw" aria-hidden="true"></i> Animaux OK</li><?php endif; ?>
        </ul>
        <?php if ($t->getDescription()): ?>
            <p class="trajet-description">« <?= e($t->getDescription()) ?> »</p>
        <?php endif; ?>
        <?php if ($t->getBadges()): ?>
            <ul class="badges">
                <?php foreach ($t->getBadges() as $badge): ?>
                    <li><?= e($badge) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="trajet-action">
        <p class="prix"><?= e(prixDT($t->getPrixPlace())) ?><small>par place</small></p>
        <p class="places <?= $t->getPlacesDisponibles() <= 1 ? 'is-low' : '' ?>">
            <i class="fas fa-user-friends" aria-hidden="true"></i>
            <?= $t->getPlacesDisponibles() ?> place<?= $t->getPlacesDisponibles() > 1 ? 's' : '' ?> libre<?= $t->getPlacesDisponibles() > 1 ? 's' : '' ?>
        </p>
        <?php if ($score !== null): ?>
            <p class="score" title="Score intelligent : prix, note, horaire et confort"><span style="width: <?= (int) $score ?>%"></span><em>Score <?= (int) $score ?>/100</em></p>
        <?php endif; ?>
        <button type="button" class="button js-accepter" data-trajet="<?= e(json_encode($donnees, JSON_UNESCAPED_UNICODE)) ?>">
            <i class="fas fa-check" aria-hidden="true"></i> Accepter
        </button>
        <button type="button" class="button alt small js-ignorer">Ignorer</button>
    </div>
</article>
