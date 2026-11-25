<?php
/**
 * Vue : tableau de bord du module Train (backoffice, template Mazer).
 * Variables : $total, $nbLignes, $enCirculation, $indisponibles, $etatGeneral, $nbRetards, $lignesInterrompues, $derniersTrains
 */
?>
<div class="page-heading">
    <h3>Train : tableau de bord</h3>
    <p class="text-subtitle text-muted">Bienvenue dans l'espace d'administration du réseau ferroviaire.</p>
</div>

<div class="page-content">
    <section class="row">
        <?php foreach ([[$total, 'Nombre total de trains', 'bi-train-front-fill', 'purple'], [$nbLignes, 'Nombre de lignes', 'bi-diagram-3-fill', 'blue'],
                        [$enCirculation, 'Trains en circulation', 'bi-check-circle-fill', 'green'], [$indisponibles, 'Trains indisponibles', 'bi-x-circle-fill', 'red']] as [$val, $lbl, $icone, $couleur]): ?>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stats-icon <?= $couleur ?>"><i class="bi <?= $icone ?>"></i></div>
                            <div>
                                <h6 class="text-muted font-semibold mb-1"><?= e($lbl) ?></h6>
                                <h6 class="font-extrabold mb-0"><?= (int) $val ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">État du réseau ferroviaire</h4></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><h6 class="text-muted">État général</h6><p class="font-bold"><?= e($etatGeneral) ?></p></div>
                    <div class="col-md-4"><h6 class="text-muted">Retards signalés</h6><p class="font-bold"><?= $nbRetards ?> train<?= $nbRetards > 1 ? 's' : '' ?> en retard</p></div>
                    <div class="col-md-4"><h6 class="text-muted">Lignes interrompues</h6><p class="font-bold"><?= $lignesInterrompues ? e(implode(', ', $lignesInterrompues)) : 'Aucune interruption signalée' ?></p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Liste des derniers trains</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Numéro du train</th><th>Ligne</th><th>Station actuelle</th><th>État</th></tr></thead>
                        <tbody>
                            <?php foreach ($derniersTrains as $t): ?>
                                <tr>
                                    <td><span class="badge bg-dark"><?= e($t->getNumero()) ?></span></td>
                                    <td><?= e($t->getLigneNom()) ?></td>
                                    <td><?= e($t->getStationDebut()) ?></td>
                                    <td><?= e($t->getEtat()) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
