<?php
/**
 * Vue : Train — rechercher un train entre deux stations à une date (frontoffice).
 * Variables : $recherche, $resultats (TrainHoraireModel[]|null), $erreurs, $stations
 */
?>
<section id="main">
    <div class="container">
        <div class="row gtr-200">
            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Rechercher un train</span></h2>

                    <form method="get" action="<?= BASE_URL ?>index.php" data-validate="modules" novalidate>
                        <input type="hidden" name="page" value="train-disponibles">
                        <div class="row gtr-uniform">
                            <div class="col-4 col-12-small">
                                <label for="td-depart">Station de départ</label>
                                <select id="td-depart" name="depart" data-v-required="Choisissez la station de départ.">
                                    <option value="">Choisir une station</option>
                                    <?php foreach ($stations as $s): ?>
                                        <option value="<?= e($s) ?>" <?= $recherche['depart'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-12-small">
                                <label for="td-arrivee">Station d'arrivée</label>
                                <select id="td-arrivee" name="arrivee" data-v-required="Choisissez la station d'arrivée."
                                        data-v-different="depart" data-v-different-msg="La station d'arrivée doit être différente du départ.">
                                    <option value="">Choisir une station</option>
                                    <?php foreach ($stations as $s): ?>
                                        <option value="<?= e($s) ?>" <?= $recherche['arrivee'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-12-small">
                                <label for="td-date">Date du voyage</label>
                                <input type="date" id="td-date" name="date" value="<?= e($recherche['date']) ?>"
                                       data-v-required="Choisissez la date du voyage." data-v-date data-v-msg="Date invalide.">
                            </div>
                            <div class="col-12">
                                <ul class="actions"><li><input type="submit" value="Rechercher" class="button" /></li></ul>
                            </div>
                        </div>
                    </form>
                </section>
            </div>

            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Résultats de la recherche</span></h2>

                    <?php if ($erreurs): ?>
                        <div class="auth-alert auth-alert-error"><ul><?php foreach ($erreurs as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
                    <?php elseif ($resultats === null): ?>
                        <p>Choisissez votre trajet puis lancez la recherche.</p>
                    <?php elseif (!$resultats): ?>
                        <p>Aucun train disponible pour ce trajet à cette date.</p>
                    <?php else: ?>
                        <p>Voici les trains disponibles pour votre trajet :</p>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr><th>Numéro du train</th><th>Ligne</th><th>Départ</th><th>Heure de départ</th><th>Arrivée</th><th>Heure d'arrivée</th><th>Durée</th><th>Prix (DT)</th><th>État</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultats as $h): ?>
                                        <tr>
                                            <td><?= e($h->getTrainNumero()) ?></td>
                                            <td><?= e($h->getLigneNom()) ?></td>
                                            <td><?= e($h->getStationDepart()) ?></td>
                                            <td><?= e(substr($h->getHeureDepart(), 0, 5)) ?></td>
                                            <td><?= e($h->getStationArrivee()) ?></td>
                                            <td><?= e(substr($h->getHeureArrivee(), 0, 5)) ?></td>
                                            <td><?= e($h->getDuree()) ?></td>
                                            <td><?= $h->getPrix() === null ? '—' : number_format($h->getPrix(), 3, ',', ' ') ?></td>
                                            <td><?= e($h->getEtatLibelle()) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</section>
