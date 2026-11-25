<?php
/**
 * Vue : Train — alertes et perturbations, avec filtres facultatifs (frontoffice).
 * Variables : $filtres, $alertes (TrainAlerteModel[]), $lignes, $trains
 */
$types = ['Retard' => 'Retard', 'Annulation' => 'Annulation', 'Interruption' => 'Interruption', 'Changement' => 'Changement de trajet'];
?>
<section id="main">
    <div class="container">
        <div class="row gtr-200">
            <div class="col-12">
                <section class="box">
                    <h2 class="major"><span>Consulter les alertes</span></h2>
                    <p>Retrouvez ici les dernières informations concernant la circulation des trains et les lignes urbaines.</p>
                    <form method="get" action="<?= BASE_URL ?>index.php" novalidate>
                        <input type="hidden" name="page" value="train-alertes">
                        <div class="row gtr-uniform">
                            <div class="col-4 col-12-small">
                                <label for="ta-type">Type d'alerte</label>
                                <select id="ta-type" name="type_alerte">
                                    <option value="">Toutes les alertes</option>
                                    <?php foreach ($types as $v => $lbl): ?>
                                        <option value="<?= e($v) ?>" <?= $filtres['type_alerte'] === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-12-small">
                                <label for="ta-ligne">Ligne</label>
                                <select id="ta-ligne" name="ligne">
                                    <option value="">Toutes les lignes</option>
                                    <?php foreach ($lignes as $l): ?>
                                        <option value="<?= (int) $l->getId() ?>" <?= $filtres['ligne'] === $l->getId() ? 'selected' : '' ?>><?= e($l->getNom()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4 col-12-small">
                                <label for="ta-train">Numéro du train</label>
                                <select id="ta-train" name="numero_train">
                                    <option value="">Tous les trains</option>
                                    <?php foreach ($trains as $t): ?>
                                        <option value="<?= e($t->getNumero()) ?>" <?= $filtres['numero_train'] === $t->getNumero() ? 'selected' : '' ?>>Train <?= e($t->getNumero()) ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                    <h2 class="major"><span>Liste des alertes</span></h2>
                    <?php if (!$alertes): ?>
                        <p>Aucune alerte pour le moment.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead><tr><th>Type d'alerte</th><th>Train</th><th>Ligne</th><th>Station concernée</th><th>Description</th><th>Date</th><th>Heure</th></tr></thead>
                                <tbody>
                                    <?php foreach ($alertes as $a): ?>
                                        <tr>
                                            <td><?= e($a->getType()) ?><?= $a->getDureeRetard() ? ' (' . $a->getDureeRetard() . ' min)' : '' ?><?= $a->getEtat() === 'Résolue' ? ' — résolue' : '' ?></td>
                                            <td><?= $a->getTrainNumero() !== '' ? e($a->getTrainNumero()) : '—' ?></td>
                                            <td><?= e($a->getLigneNom()) ?></td>
                                            <td><?= $a->getStation() !== null ? e($a->getStation()) : '—' ?></td>
                                            <td><?= e($a->getDescription()) ?></td>
                                            <td><?= e(date('d/m/Y', strtotime($a->getDateAlerte()))) ?></td>
                                            <td><?= $a->getHeureAlerte() !== null ? e(substr($a->getHeureAlerte(), 0, 5)) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h3>Conseils aux voyageurs</h3>
                    <ul>
                        <li>Consultez les alertes avant de prendre le train.</li>
                        <li>Vérifiez les horaires en cas de retard.</li>
                        <li>En cas d'interruption, vérifiez les autres trajets disponibles.</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</section>
