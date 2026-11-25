<?php
/**
 * Vue : gestion des trains (backoffice, template Mazer).
 * Variables : $form, $erreurs, $flash, $trains, $lignes, $etats, $stations, $csrfToken
 * Aucun contrôle HTML5 : JS (modules-validation.js) + PHP.
 */
$edition = $form['id'] > 0;
?>
<div class="page-heading">
    <h3>Gestion des trains</h3>
    <p class="text-subtitle text-muted">Ajouter, modifier et supprimer les trains du réseau.</p>
</div>

<div class="page-content">
    <?php require __DIR__ . '/../admin/partials/messages.php'; ?>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><?= $edition ? 'Modifier le train' : 'Ajouter un nouveau train' ?></h4></div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-trains" data-validate="modules" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tr-numero" class="form-label">Numéro du train</label>
                            <input type="text" id="tr-numero" name="numero" class="form-control" value="<?= e($form['numero']) ?>"
                                   data-v-required="Le numéro est obligatoire." data-v-pattern="^[A-Za-z0-9-]{1,10}$" data-v-msg="1 à 10 lettres, chiffres ou tirets.">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tr-depart" class="form-label">Station de début</label>
                            <select id="tr-depart" name="depart" class="form-select" data-v-required="Choisissez une station.">
                                <option value="">Choisir une station</option>
                                <?php foreach ($stations as $s): ?>
                                    <option value="<?= e($s) ?>" <?= $form['depart'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tr-capacite" class="form-label">Capacité totale</label>
                            <input type="number" id="tr-capacite" name="capacite" class="form-control" value="<?= e($form['capacite']) ?>"
                                   data-v-required="La capacité est obligatoire." data-v-int="1,5000" data-v-msg="Entre 1 et 5000.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tr-ligne" class="form-label">Ligne associée</label>
                            <select id="tr-ligne" name="ligne" class="form-select" data-v-required="Choisissez une ligne.">
                                <option value="">Choisir une ligne</option>
                                <?php foreach ($lignes as $l): ?>
                                    <option value="<?= (int) $l->getId() ?>" <?= $form['ligne'] === $l->getId() ? 'selected' : '' ?>><?= e($l->getNom()) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tr-etat" class="form-label">État du train</label>
                            <select id="tr-etat" name="etat" class="form-select" data-v-required="Choisissez un état.">
                                <option value="">Choisir un état</option>
                                <?php foreach ($etats as $et): ?>
                                    <option value="<?= e($et) ?>" <?= $form['etat'] === $et ? 'selected' : '' ?>><?= e($et) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $edition ? 'Enregistrer les modifications' : 'Ajouter le train' ?></button>
                    <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-train-trains" class="btn btn-light-secondary">Annuler</a><?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Liste des trains</h4></div>
            <div class="card-body">
                <?php if (!$trains): ?>
                    <p class="text-muted text-center my-4">Aucun train pour le moment.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead><tr><th>Numéro</th><th>Station de début</th><th>Capacité</th><th>Ligne</th><th>État</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($trains as $t): ?>
                                    <tr>
                                        <td><span class="badge bg-dark"><?= e($t->getNumero()) ?></span></td>
                                        <td><?= e($t->getStationDebut()) ?></td>
                                        <td><?= $t->getCapacite() ?></td>
                                        <td><?= e($t->getLigneNom()) ?></td>
                                        <td><?= e($t->getEtat()) ?></td>
                                        <td class="text-end text-nowrap">
                                            <a href="<?= BASE_URL ?>index.php?page=admin-train-trains&amp;edit=<?= (int) $t->getId() ?>" class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-train-delete" class="d-inline" data-confirm="Supprimer le train <?= e($t->getNumero()) ?> et ses horaires ?">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $t->getId() ?>">
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
