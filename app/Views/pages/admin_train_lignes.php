<?php
/**
 * Vue : gestion des lignes ferroviaires (backoffice, template Mazer).
 * Variables : $form, $erreurs, $flash, $lignes, $etats, $stations, $csrfToken
 */
$edition = $form['id'] > 0;
?>
<div class="page-heading">
    <h3>Gestion des lignes ferroviaires</h3>
    <p class="text-subtitle text-muted">Lignes, stations desservies et itinéraires.</p>
</div>

<div class="page-content">
    <?php require __DIR__ . '/../admin/partials/messages.php'; ?>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><?= $edition ? 'Modifier la ligne' : 'Ajouter une nouvelle ligne' ?></h4></div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-lignes" data-validate="modules" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="li-nom" class="form-label">Nom de la ligne</label>
                            <input type="text" id="li-nom" name="nom_ligne" class="form-control" value="<?= e($form['nom']) ?>"
                                   data-v-required="Le nom est obligatoire." data-v-maxlength="50" data-v-msg="50 caractères au maximum.">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="li-depart" class="form-label">Station de départ</label>
                            <select id="li-depart" name="depart" class="form-select" data-v-required="Choisissez la station de départ.">
                                <option value="">Choisir une station</option>
                                <?php foreach ($stations as $s): ?><option value="<?= e($s) ?>" <?= $form['depart'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="li-arrivee" class="form-label">Station d'arrivée</label>
                            <select id="li-arrivee" name="arrivee" class="form-select" data-v-required="Choisissez la station d'arrivée."
                                    data-v-different="depart" data-v-different-msg="Doit être différente de la station de départ.">
                                <option value="">Choisir une station</option>
                                <?php foreach ($stations as $s): ?><option value="<?= e($s) ?>" <?= $form['arrivee'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="li-stations" class="form-label">Stations intermédiaires</label>
                            <textarea id="li-stations" name="stations" class="form-control" rows="3" placeholder="Exemple : Station 1, Station 2, Station 3"
                                      data-v-maxlength="255" data-v-msg="255 caractères au maximum."><?= e($form['stations']) ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="li-distance" class="form-label">Distance de la ligne (km)</label>
                            <input type="text" id="li-distance" name="distance" class="form-control" inputmode="decimal" value="<?= e($form['distance']) ?>"
                                   data-v-number="0,1000" data-v-msg="Nombre entre 0 et 1000.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="li-etat" class="form-label">État de la ligne</label>
                            <select id="li-etat" name="etat" class="form-select" data-v-required="Choisissez un état.">
                                <option value="">Choisir un état</option>
                                <?php foreach ($etats as $et): ?><option value="<?= e($et) ?>" <?= $form['etat'] === $et ? 'selected' : '' ?>><?= e($et) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $edition ? 'Enregistrer les modifications' : 'Ajouter la ligne' ?></button>
                    <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-train-lignes" class="btn btn-light-secondary">Annuler</a><?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Liste des lignes disponibles</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Nom de la ligne</th><th>Itinéraire</th><th>Distance</th><th>État</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($lignes as $l): ?>
                                <tr>
                                    <td class="font-bold"><?= e($l->getNom()) ?></td>
                                    <td><?= e(implode(' → ', $l->getItineraire())) ?></td>
                                    <td><?= $l->getDistance() === null ? '—' : number_format($l->getDistance(), 1, ',', ' ') . ' km' ?></td>
                                    <td><?= e($l->getEtat()) ?></td>
                                    <td class="text-end text-nowrap">
                                        <a href="<?= BASE_URL ?>index.php?page=admin-train-lignes&amp;edit=<?= (int) $l->getId() ?>" class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-ligne-delete" class="d-inline" data-confirm="Supprimer la <?= e($l->getNom()) ?> ?">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $l->getId() ?>">
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
</div>
