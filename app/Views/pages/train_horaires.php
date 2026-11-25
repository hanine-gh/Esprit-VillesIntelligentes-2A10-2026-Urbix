<?php
/**
 * Vue : Train — horaires de passage à une station (frontoffice).
 * Variables : $recherche, $passages (null = pas de recherche), $erreurs, $lignes, $stations
 */
?>
<section id="main">
    <div class="container">
        <div class="row gtr-200">
            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Consulter les horaires</span></h2>
                    <form method="get" action="<?= BASE_URL ?>index.php" data-validate="modules" novalidate>
                        <input type="hidden" name="page" value="train-horaires">
                        <div class="row gtr-uniform">
                            <div class="col-4 col-12-small">
                                <label for="th-ligne">Choisir une ligne</label>
                                <select id="th-ligne" name="ligne" data-v-required="Choisissez une ligne.">
                                    <option value="">Choisir une ligne</option>
                                    <?php foreach ($lignes as $l): ?>
                                        <option value="<?= (int) $l->getId() ?>" <?= $recherche['ligne'] === $l->getId() ? 'selected' : '' ?>><?= e($l->getNom()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-12-small">
                                <label for="th-station">Choisir une station</label>
                                <select id="th-station" name="station" data-v-required="Choisissez une station.">
                                    <option value="">Choisir une station</option>
                                    <?php foreach ($stations as $s): ?>
                                        <option value="<?= e($s) ?>" <?= $recherche['station'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-12-small">
                                <label for="th-date">Date</label>
                                <input type="date" id="th-date" name="date" value="<?= e($recherche['date']) ?>"
                                       data-v-required="Choisissez une date." data-v-date data-v-msg="Date invalide.">
                            </div>
                            <div class="col-12">
                                <ul class="actions"><li><input type="submit" value="Afficher les horaires" class="button" /></li></ul>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Horaires des prochains trains</span></h2>
                    <?php if ($erreurs): ?>
                        <div class="auth-alert auth-alert-error"><ul><?php foreach ($erreurs as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
                    <?php elseif ($passages === null): ?>
                        <p>Choisissez une ligne, une station et une date.</p>
                    <?php elseif (!$passages): ?>
                        <p>Aucun train ne passe à cette station à cette date.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead><tr><th>Numéro du train</th><th>Ligne</th><th>Station</th><th>Heure de passage</th><th>État</th></tr></thead>
                                <tbody>
                                    <?php foreach ($passages as $p): ?>
                                        <tr>
                                            <td><?= e($p['horaire']->getTrainNumero()) ?></td>
                                            <td><?= e($p['horaire']->getLigneNom()) ?></td>
                                            <td><?= e($recherche['station']) ?></td>
                                            <td><?= e($p['heure']) ?></td>
                                            <td><?= e($p['horaire']->getEtatLibelle()) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h3>Informations pour les voyageurs</h3>
                    <ul>
                        <li>Consultez les horaires avant votre déplacement.</li>
                        <li>Vérifiez les retards éventuels.</li>
                        <li>Consultez la page des alertes en cas de perturbation.</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</section>
