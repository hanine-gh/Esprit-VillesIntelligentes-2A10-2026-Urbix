<?php
/**
 * Vue : backoffice du module Location (template Mazer).
 * Variables : $vehicules, $locations, $kpis, $filtre, $filtres, $stations, $types, $images, $flash, $csrfToken
 */
$badge = [
    'disponible'  => ['Disponible', 'bg-light-success text-success'],
    'reserve'     => ['Réservé', 'bg-light-warning text-warning'],
    'encours'     => ['En cours', 'bg-light-info text-info'],
    'maintenance' => ['Maintenance', 'bg-light-danger text-danger'],
    'termine'     => ['Terminé', 'bg-light-secondary text-secondary'],
];
$b = fn(string $s): string => '<span class="badge ' . ($badge[$s][1] ?? '') . '">' . e($badge[$s][0] ?? $s) . '</span>';
$f = e($filtre);
?>
<div class="page-heading">
    <h3>Location : flotte &amp; locations</h3>
    <p class="text-subtitle text-muted">Ajouter, modifier et superviser les véhicules (moto, trottinette, vélo) et les locations.</p>
</div>

<div class="page-content">
    <?php if ($flash): ?>
        <div class="alert alert-light-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <section class="row">
        <?php foreach ([['total', 'Total véhicules', 'bi-collection-fill', 'purple'], ['disponibles', 'Disponibles', 'bi-check-circle-fill', 'green'],
                        ['actives', 'En location', 'bi-key-fill', 'blue'], ['maintenance', 'Maintenance', 'bi-tools', 'red']] as [$cle, $lbl, $icone, $couleur]): ?>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stats-icon <?= $couleur ?>"><i class="bi <?= $icone ?>"></i></div>
                            <div>
                                <h6 class="text-muted font-semibold mb-1"><?= e($lbl) ?></h6>
                                <h6 class="font-extrabold mb-0"><?= (int) $kpis[$cle] ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-12">
            <div class="card"><div class="card-body py-3">
                <i class="bi bi-cash-stack text-success"></i> Revenus : <strong><?= number_format($kpis['revenus'], 2, '.', '') ?> DT</strong>
            </div></div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Ajouter un véhicule</h4></div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-vehicule-ajouter" data-validate="modules" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="filtre" value="<?= $f ?>">
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label for="newNom" class="form-label">Nom du véhicule</label>
                            <input type="text" id="newNom" name="nom" class="form-control" placeholder="Ex : Moto Urbix R300"
                                   data-v-required="Le nom est obligatoire." data-v-maxlength="100" data-v-msg="100 caractères au maximum.">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="newType" class="form-label">Catégorie</label>
                            <select id="newType" name="type" class="form-select">
                                <?php foreach ($types as $val => $lbl): ?><option value="<?= e($val) ?>"><?= e($lbl) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="newTarif" class="form-label">Tarif (DT/h)</label>
                            <input type="text" id="newTarif" name="tarif" class="form-control" value="10" inputmode="decimal"
                                   data-v-required="Le tarif est obligatoire." data-v-number="0.5,1000" data-v-msg="Tarif entre 0,5 et 1000 DT/h.">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="newAutonomie" class="form-label">Batterie / Autonomie</label>
                            <input type="text" id="newAutonomie" name="autonomie" class="form-control" value="100%"
                                   data-v-required="L'autonomie est obligatoire." data-v-pattern="^(100|[1-9]?[0-9])%$" data-v-msg="Pourcentage, ex : 85%.">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="newStation" class="form-label">Station</label>
                            <select id="newStation" name="station" class="form-select">
                                <?php foreach ($stations as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label for="newImage" class="form-label">Image</label>
                            <select id="newImage" name="image" class="form-select">
                                <?php foreach ($images as $img): ?><option value="<?= e($img) ?>"><?= e($img) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Ajouter le véhicule</button>
                    <a href="<?= BASE_URL ?>index.php?page=location" class="btn btn-light-secondary">Voir le site</a>
                </form>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Liste des véhicules</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-lg mb-0 align-middle">
                        <thead>
                            <tr><th>Véhicule</th><th>Type</th><th>Station</th><th>Statut</th><th>Tarif (DT/h)</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicules as $v): ?>
                                <tr>
                                    <td class="font-bold"><?= e($v->getNom()) ?></td>
                                    <td><?= strtoupper(e($v->getType())) ?></td>
                                    <td><?= e($v->getStation()) ?></td>
                                    <td><?= $b($v->getStatut()) ?></td>
                                    <td>
                                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-vehicule-tarif" class="d-flex gap-1" data-validate="modules" novalidate>
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="filtre" value="<?= $f ?>">
                                            <input type="hidden" name="id" value="<?= (int) $v->getId() ?>">
                                            <div>
                                                <input type="text" name="tarif" class="form-control form-control-sm" style="width:80px" inputmode="decimal"
                                                       value="<?= e((string) $v->getTarifHeure()) ?>" data-v-required="Tarif obligatoire." data-v-number="0.5,1000" data-v-msg="0,5 à 1000.">
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-light-warning" title="Modifier le tarif"><i class="bi bi-pencil-fill"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <?php if (in_array($v->getStatut(), ['disponible', 'maintenance'], true)): ?>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-vehicule-statut" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="filtre" value="<?= $f ?>">
                                                <input type="hidden" name="id" value="<?= (int) $v->getId() ?>">
                                                <?php if ($v->getStatut() === 'disponible'): ?>
                                                    <input type="hidden" name="statut" value="maintenance">
                                                    <button type="submit" class="btn btn-sm btn-light-warning" title="Mettre en maintenance"><i class="bi bi-tools"></i></button>
                                                <?php else: ?>
                                                    <input type="hidden" name="statut" value="disponible">
                                                    <button type="submit" class="btn btn-sm btn-light-success" title="Remettre disponible"><i class="bi bi-check-lg"></i></button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-vehicule-supprimer" class="d-inline"
                                              data-confirm="Supprimer le véhicule <?= e($v->getNom()) ?> ?">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="filtre" value="<?= $f ?>">
                                            <input type="hidden" name="id" value="<?= (int) $v->getId() ?>">
                                            <button type="submit" class="btn btn-sm btn-light-danger" title="Supprimer"><i class="bi bi-trash-fill"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h4 class="card-title mb-0">Supervision des locations</h4>
                    <div class="d-flex gap-2">
                        <form method="get" action="<?= BASE_URL ?>index.php" class="d-flex gap-2">
                            <input type="hidden" name="page" value="admin-location">
                            <select name="statut" class="form-select">
                                <?php foreach ($filtres as $val => $lbl): ?>
                                    <option value="<?= e($val) ?>" <?= $filtre === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-light-primary"><i class="bi bi-funnel-fill"></i></button>
                        </form>
                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-purger" data-confirm="Supprimer toutes les locations terminées ?">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="filtre" value="<?= $f ?>">
                            <button type="submit" class="btn btn-light-secondary">Purger terminées</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($locations)): ?>
                    <p class="text-muted text-center my-4">Aucune location enregistrée.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-lg mb-0 align-middle">
                            <thead>
                                <tr><th>Réf</th><th>Client</th><th>Véhicule</th><th>Trajet</th><th>Durée / Total</th><th>Statut</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($locations as $l): ?>
                                    <tr>
                                        <td><span class="badge bg-dark"><?= e($l->getReference()) ?></span></td>
                                        <td><?= $l->getClientNom() !== '' ? e($l->getClientNom()) : '<span class="text-muted">—</span>' ?></td>
                                        <td><?= e($l->getVehiculeNom()) ?></td>
                                        <td><?= e($l->getStationDepart()) ?> → <?= e($l->getStationRetour()) ?></td>
                                        <td><?= $l->getDureeHeures() ?>h — <strong><?= number_format($l->getMontant(), 2, '.', '') ?> DT</strong></td>
                                        <td><?= $b($l->getStatut()) ?><?= $l->getAnnulee() ? ' <small class="text-muted">(annulée)</small>' : '' ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php if (in_array($l->getStatut(), ['reserve', 'encours'], true)): ?>
                                                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-cloturer" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                    <input type="hidden" name="filtre" value="<?= $f ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $l->getId() ?>">
                                                    <button type="submit" class="btn btn-sm btn-light-success" title="Clôturer"><i class="bi bi-flag-fill"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-location-supprimer" class="d-inline"
                                                  data-confirm="Supprimer la location <?= e($l->getReference()) ?> ?">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="filtre" value="<?= $f ?>">
                                                <input type="hidden" name="id" value="<?= (int) $l->getId() ?>">
                                                <button type="submit" class="btn btn-sm btn-light-danger" title="Supprimer"><i class="bi bi-trash-fill"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
