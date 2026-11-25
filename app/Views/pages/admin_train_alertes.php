<?php
/**
 * Vue : gestion des alertes du réseau train (backoffice, template Mazer).
 * Variables : $form, $erreurs, $flash, $alertes, $lignes, $trains, $types, $etats, $stations, $csrfToken
 */
$edition = $form['id'] > 0;
?>
<div class="page-heading">
    <h3>Gestion des alertes</h3>
    <p class="text-subtitle text-muted">Retards, annulations et perturbations du réseau.</p>
</div>

<div class="page-content">
    <?php require __DIR__ . '/../admin/partials/messages.php'; ?>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><?= $edition ? 'Modifier une alerte' : 'Ajouter une alerte' ?></h4></div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-alertes" data-validate="modules" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="al-type" class="form-label">Type d'alerte</label>
                            <select id="al-type" name="type_alerte" class="form-select" data-v-required="Choisissez le type.">
                                <option value="">Choisir le type</option>
                                <?php foreach ($types as $val => $lbl): ?><option value="<?= e($val) ?>" <?= $form['type_alerte'] === $val ? 'selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="al-train" class="form-label">Train (facultatif)</label>
                            <select id="al-train" name="numero_train" class="form-select">
                                <option value="">Aucun train</option>
                                <?php foreach ($trains as $t): ?><option value="<?= e($t->getNumero()) ?>" <?= $form['numero_train'] === $t->getNumero() ? 'selected' : '' ?>>Train <?= e($t->getNumero()) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="al-ligne" class="form-label">Ligne concernée</label>
                            <select id="al-ligne" name="ligne" class="form-select" data-v-required="Choisissez une ligne.">
                                <option value="">Choisir une ligne</option>
                                <?php foreach ($lignes as $l): ?><option value="<?= (int) $l->getId() ?>" <?= $form['ligne'] === $l->getId() ? 'selected' : '' ?>><?= e($l->getNom()) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="al-station" class="form-label">Station concernée</label>
                            <select id="al-station" name="station" class="form-select">
                                <option value="">Aucune station</option>
                                <?php foreach ($stations as $s): ?><option value="<?= e($s) ?>" <?= $form['station'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="al-duree" class="form-label">Durée du retard (min)</label>
                            <input type="number" id="al-duree" name="duree_retard" class="form-control" value="<?= e($form['duree_retard']) ?>"
                                   data-v-int="0,1440" data-v-msg="Entre 0 et 1440 minutes.">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="al-date" class="form-label">Date de l'alerte</label>
                            <input type="date" id="al-date" name="date_alerte" class="form-control" value="<?= e($form['date_alerte']) ?>"
                                   data-v-required="La date est obligatoire." data-v-date data-v-msg="Date invalide.">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="al-heure" class="form-label">Heure de l'alerte</label>
                            <input type="time" id="al-heure" name="heure_alerte" class="form-control" value="<?= e($form['heure_alerte']) ?>"
                                   data-v-time data-v-msg="Heure invalide.">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="al-desc" class="form-label">Description</label>
                            <textarea id="al-desc" name="description" class="form-control" rows="3" placeholder="Décrire le problème..."
                                      data-v-maxlength="500" data-v-msg="500 caractères au maximum."><?= e($form['description']) ?></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="al-etat" class="form-label">État de l'alerte</label>
                            <select id="al-etat" name="etat" class="form-select">
                                <?php foreach ($etats as $et): ?><option value="<?= e($et) ?>" <?= $form['etat'] === $et ? 'selected' : '' ?>><?= e($et) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $edition ? 'Enregistrer les modifications' : "Publier l'alerte" ?></button>
                    <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-train-alertes" class="btn btn-light-secondary">Annuler</a><?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Liste des alertes</h4></div>
            <div class="card-body">
                <?php if (!$alertes): ?>
                    <p class="text-muted text-center my-4">Aucune alerte.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead><tr><th>Type</th><th>Train</th><th>Ligne</th><th>Station</th><th>Description</th><th>Date</th><th>État</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($alertes as $a): ?>
                                    <tr>
                                        <td><?= e($types[$a->getType()] ?? $a->getType()) ?><?= $a->getDureeRetard() ? ' (' . $a->getDureeRetard() . ' min)' : '' ?></td>
                                        <td><?= $a->getTrainNumero() !== '' ? e($a->getTrainNumero()) : '—' ?></td>
                                        <td><?= e($a->getLigneNom()) ?></td>
                                        <td><?= $a->getStation() !== null ? e($a->getStation()) : '—' ?></td>
                                        <td><?= e($a->getDescription()) ?></td>
                                        <td><?= e(date('d/m/Y', strtotime($a->getDateAlerte()))) ?><?= $a->getHeureAlerte() !== null ? ' ' . e(substr($a->getHeureAlerte(), 0, 5)) : '' ?></td>
                                        <td><span class="badge <?= $a->getEtat() === 'Active' ? 'bg-light-danger text-danger' : 'bg-light-success text-success' ?>"><?= e($a->getEtat()) ?></span></td>
                                        <td class="text-end text-nowrap">
                                            <a href="<?= BASE_URL ?>index.php?page=admin-train-alertes&amp;edit=<?= (int) $a->getId() ?>" class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-alerte-delete" class="d-inline" data-confirm="Supprimer cette alerte ?">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $a->getId() ?>">
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
