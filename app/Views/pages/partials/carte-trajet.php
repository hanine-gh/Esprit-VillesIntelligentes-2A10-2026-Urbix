<?php
/** @var array $t  trajet (avec score/badges si issu de la recherche) */
$note      = $t['note_moyenne'] !== null ? (float) $t['note_moyenne'] : null;
$score     = $t['score'] ?? null;
$meilleur  = !empty($t['meilleur']);
$timestamp = strtotime($t['date_depart'] . ' ' . $t['heure_depart']);
$duree     = (int) ($t['duree_estimee_min'] ?? 0);
$arrivee   = $t['heure_arrivee'] ?? ($duree > 0 ? date('H:i:s', strtotime($t['heure_depart']) + 60 * $duree) : null);
$bagages   = ['petit' => 'Petit bagage', 'moyen' => 'Bagage moyen', 'grand' => 'Grand bagage'];
$donnees   = [
    'id'         => (int) $t['id_trajet'],
    'conducteur' => $t['conducteur_prenom'] . ' ' . $t['conducteur_nom'],
    'initiales'  => initiales($t['conducteur_prenom'], $t['conducteur_nom']),
    'depart'     => $t['ville_depart'],
    'arrivee'    => $t['ville_arrivee'],
    'date'       => dateFr($t['date_depart']),
    'heure'      => heureFr($t['heure_depart']),
    'rdv'        => (string) $t['point_rencontre'],
    'vehicule'   => trim(($t['vehicule_marque'] ?? '') . ' ' . ($t['vehicule_modele'] ?? '')),
    'prix'       => (float) $t['prix_place'],
    'places'     => (int) $t['places_disponibles'],
];
?>
<article class="trajet-card<?= $meilleur ? ' is-best' : '' ?>"
         data-prix="<?= e($t['prix_place']) ?>"
         data-depart="<?= (int) $timestamp ?>"
         data-note="<?= e($note ?? 0) ?>"
         data-score="<?= e($score ?? 0) ?>"
         data-clim="<?= (int) $t['climatisation'] ?>"
         data-fumeur="<?= (int) $t['fumeur'] ?>">

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
                    <span><?= e(number_format($note, 1, ',', '')) ?> (<?= (int) $t['nb_avis'] ?> avis)</span>
                <?php else: ?>
                    <span>Nouveau conducteur</span>
                <?php endif; ?>
            </div>
            <?php if ($donnees['vehicule'] !== ''): ?>
                <p class="vehicule"><i class="fas fa-car-side" aria-hidden="true"></i>
                    <?= e($donnees['vehicule']) ?><?= !empty($t['vehicule_couleur']) ? ' · ' . e($t['vehicule_couleur']) : '' ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="trajet-itineraire">
        <p class="trajet-date"><i class="far fa-calendar-alt" aria-hidden="true"></i> <?= e(ucfirst(dateFr($t['date_depart']))) ?></p>
        <div class="timeline">
            <div class="etape">
                <strong><?= e(heureFr($t['heure_depart'])) ?></strong>
                <span><?= e($t['ville_depart']) ?></span>
            </div>
            <div class="ligne" aria-hidden="true">
                <?php if ($duree > 0): ?>
                    <em><?= intdiv($duree, 60) ?>h<?= str_pad((string) ($duree % 60), 2, '0', STR_PAD_LEFT) ?></em>
                <?php endif; ?>
            </div>
            <div class="etape">
                <strong><?= $arrivee ? e(heureFr($arrivee)) : '--h--' ?></strong>
                <span><?= e($t['ville_arrivee']) ?></span>
            </div>
        </div>
        <?php if (!empty($t['point_rencontre'])): ?>
            <p class="rdv"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> RDV : <?= e($t['point_rencontre']) ?></p>
        <?php endif; ?>
        <ul class="tags">
            <li><i class="fas fa-suitcase" aria-hidden="true"></i> <?= e($bagages[$t['bagages']] ?? 'Bagage') ?></li>
            <?php if ((int) $t['climatisation']): ?><li><i class="fas fa-snowflake" aria-hidden="true"></i> Climatisé</li><?php endif; ?>
            <li><i class="fas <?= (int) $t['fumeur'] ? 'fa-smoking' : 'fa-smoking-ban' ?>" aria-hidden="true"></i> <?= (int) $t['fumeur'] ? 'Fumeur' : 'Non-fumeur' ?></li>
            <?php if ((int) $t['animaux']): ?><li><i class="fas fa-paw" aria-hidden="true"></i> Animaux OK</li><?php endif; ?>
        </ul>
        <?php if (!empty($t['description'])): ?>
            <p class="trajet-description">« <?= e($t['description']) ?> »</p>
        <?php endif; ?>
        <?php if (!empty($t['badges'])): ?>
            <ul class="badges">
                <?php foreach ($t['badges'] as $badge): ?>
                    <li><?= e($badge) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="trajet-action">
        <p class="prix"><?= e(prixDT($t['prix_place'])) ?><small>par place</small></p>
        <p class="places <?= (int) $t['places_disponibles'] <= 1 ? 'is-low' : '' ?>">
            <i class="fas fa-user-friends" aria-hidden="true"></i>
            <?= (int) $t['places_disponibles'] ?> place<?= (int) $t['places_disponibles'] > 1 ? 's' : '' ?> libre<?= (int) $t['places_disponibles'] > 1 ? 's' : '' ?>
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
