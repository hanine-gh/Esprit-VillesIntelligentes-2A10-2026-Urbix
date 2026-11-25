<?php
/**
 * Page d'un module développé par un autre membre du groupe.
 * @var array  $module
 * @var string $cle
 */
?>
<section id="main">
    <div class="container">
        <section class="box highlight">
            <ul class="special">
                <li><span class="icon solid <?= e($module['icone']) ?>"><span class="label"><?= e($module['titre']) ?></span></span></li>
            </ul>
            <header>
                <h2><?= e($module['titre']) ?></h2>
                <p><?= e($module['description']) ?></p>
            </header>
            <p class="notice notice-info module-notice">
                <i class="fas fa-hard-hat" aria-hidden="true"></i>
                Ce module est réalisé par un autre membre de l'équipe Urbix. Les entités ci-dessous sont déjà créées
                dans la base de données commune (<code>database/smartcity.sql</code>).
            </p>
        </section>

        <section class="box resultats">
            <h2 class="major"><span>Entités du module</span></h2>
            <div class="entites">
                <?php foreach ($module['entites'] as $nom => $attributs): ?>
                    <article class="entite">
                        <h3><i class="fas fa-table" aria-hidden="true"></i> <?= e($nom) ?></h3>
                        <ul>
                            <?php foreach ($attributs as $i => $attribut): ?>
                                <li class="<?= $i === 0 ? 'cle-primaire' : (strpos($attribut, 'id_') === 0 ? 'cle-etrangere' : '') ?>">
                                    <?php if ($i === 0): ?><i class="fas fa-key" aria-hidden="true"></i><?php elseif (strpos($attribut, 'id_') === 0): ?><i class="fas fa-link" aria-hidden="true"></i><?php endif; ?>
                                    <?= e($attribut) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
            <ul class="actions centre">
                <li><a href="<?= url('covoiturage') ?>" class="button"><i class="fas fa-car-side" aria-hidden="true"></i> Essayer le covoiturage</a></li>
                <li><a href="<?= url('home') ?>" class="button alt">Retour à l'accueil</a></li>
            </ul>
        </section>
    </div>
</section>
