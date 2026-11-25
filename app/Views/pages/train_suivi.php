<?php
/**
 * Vue : Train — position actuelle d'un train (frontoffice).
 * Variables : $numero, $trains (TrainModel[]), $suivi (null si aucun train choisi)
 */
?>
<section id="main">
    <div class="container">
        <div class="row gtr-200">
            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Suivre un train</span></h2>
                    <form method="get" action="<?= BASE_URL ?>index.php" data-validate="modules" novalidate>
                        <input type="hidden" name="page" value="train-suivi">
                        <div class="row gtr-uniform">
                            <div class="col-6 col-12-small">
                                <label for="ts-train">Numéro du train</label>
                                <select id="ts-train" name="numero_train" data-v-required="Choisissez un train.">
                                    <option value="">Choisir un train</option>
                                    <?php foreach ($trains as $t): ?>
                                        <option value="<?= e($t->getNumero()) ?>" <?= $numero === $t->getNumero() ? 'selected' : '' ?>>Train <?= e($t->getNumero()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <ul class="actions"><li><input type="submit" value="Suivre le train" class="button" /></li></ul>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Position actuelle du train</span></h2>
                    <?php if ($numero !== '' && $suivi === null): ?>
                        <div class="auth-alert auth-alert-error">Train introuvable.</div>
                    <?php elseif ($suivi === null): ?>
                        <p>Choisissez un train pour voir sa position.</p>
                    <?php else: $t = $suivi['train']; ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr><th>Numéro du train</th><th>Ligne</th><th>Station actuelle</th><th>Station suivante</th><th>Heure prévue d'arrivée</th><th>Retard</th><th>État</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= e($t->getNumero()) ?></td>
                                        <td><?= e($t->getLigneNom()) ?></td>
                                        <td><?= e($t->getStationDebut()) ?></td>
                                        <td><?= e($suivi['suivante']) ?></td>
                                        <td><?= e($suivi['heurePrevue']) ?></td>
                                        <td><?= e($suivi['retard']) ?></td>
                                        <td><?= e($t->getEtat()) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>
