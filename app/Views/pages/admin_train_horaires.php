<?php
/**
 * Vue : gestion des horaires de trains (backoffice, template Mazer).
 * Variables : $form, $erreurs, $flash, $horaires, $lignes, $trains, $etats, $stations, $csrfToken
 */
$edition = $form['id'] > 0;
?>
<div class="page-heading">
    <h3>Gestion des horaires</h3>
    <p class="text-subtitle text-muted">Heures de départ et d'arrivée des trains.</p>
</div>

<div class="page-content">
    <?php require __DIR__ . '/../admin/partials/messages.php'; ?>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title"><?= $edition ? 'Modifier un horaire' : 'Ajouter un horaire' ?></h4></div>
            <div class="card-body">
                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-horaires" data-validate="modules" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="ho-train" class="form-label">Train</label>
                            <select id="ho-train" name="numero_train" class="form-select" data-v-required="Choisissez un train.">
                                <option value="">Choisir un train</option>
                                <?php foreach ($trains as $t): ?><option value="<?= e($t->getNumero()) ?>" <?= $form['numero_train'] === $t->getNumero() ? 'selected' : '' ?>>Train <?= e($t->getNumero()) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ho-ligne" class="form-label">Ligne</label>
                            <select id="ho-ligne" name="ligne" class="form-select" data-v-required="Choisissez une ligne.">
                                <option value="">Choisir une ligne</option>
                                <?php foreach ($lignes as $l): ?><option value="<?= (int) $l->getId() ?>" <?= $form['ligne'] === $l->getId() ? 'selected' : '' ?>><?= e($l->getNom()) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="ho-date" class="form-label">Date</label>
                            <input type="date" id="ho-date" name="date" class="form-control" value="<?= e($form['date']) ?>"
                                   data-v-required="La date est obligatoire." data-v-date data-v-msg="Date invalide.">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ho-sd" class="form-label">Station de départ</label>
                            <select id="ho-sd" name="station_depart" class="form-select" data-v-required="Choisissez la station de départ.">
                                <option value="">Choisir</option>
                                <?php foreach ($stations as $s): ?><option value="<?= e($s) ?>" <?= $form['station_depart'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ho-hd" class="form-label">Heure de départ</label>
                            <input type="time" id="ho-hd" name="heure_depart" class="form-control" value="<?= e($form['heure_depart']) ?>"
                                   data-v-required="Heure de départ obligatoire." data-v-time data-v-msg="Heure invalide.">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ho-sa" class="form-label">Station d'arrivée</label>
                            <select id="ho-sa" name="station_arrivee" class="form-select" data-v-required="Choisissez la station d'arrivée."
                                    data-v-different="station_depart" data-v-different-msg="Doit être différente du départ.">
                                <option value="">Choisir</option>
                                <?php foreach ($stations as $s): ?><option value="<?= e($s) ?>" <?= $form['station_arrivee'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="ho-ha" class="form-label">Heure d'arrivée</label>
                            <input type="time" id="ho-ha" name="heure_arrivee" class="form-control" value="<?= e($form['heure_arrivee']) ?>"
                                   data-v-required="Heure d'arrivée obligatoire." data-v-time data-v-msg="Heure invalide."
                                   data-v-after="heure_depart" data-v-after-msg="L'arrivée doit être après le départ.">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ho-etat" class="form-label">État du trajet</label>
                            <select id="ho-etat" name="etat" class="form-select">
                                <?php foreach ($etats as $val => $lbl): ?><option value="<?= e($val) ?>" <?= $form['etat'] === $val ? 'selected' : '' ?>><?= e($lbl) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ho-prix" class="form-label">Prix du billet (DT)</label>
                            <input type="text" id="ho-prix" name="prix" class="form-control" inputmode="decimal" value="<?= e($form['prix']) ?>"
                                   data-v-number="0,1000" data-v-msg="Nombre entre 0 et 1000.">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $edition ? 'Enregistrer les modifications' : "Ajouter l'horaire" ?></button>
                    <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-train-horaires" class="btn btn-light-secondary">Annuler</a><?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Liste des horaires</h4></div>
            <div class="card-body">
                <?php if (!$horaires): ?>
                    <p class="text-muted text-center my-4">Aucun horaire pour le moment.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead><tr><th>Train</th><th>Ligne</th><th>Date</th><th>Départ</th><th>Arrivée</th><th>Prix (DT)</th><th>État</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($horaires as $h): ?>
                                    <tr>
                                        <td><span class="badge bg-dark"><?= e($h->getTrainNumero()) ?></span></td>
                                        <td><?= e($h->getLigneNom()) ?></td>
                                        <td><?= e(date('d/m/Y', strtotime($h->getDateTrajet()))) ?></td>
                                        <td><?= e($h->getStationDepart()) ?> <small class="text-muted"><?= e(substr($h->getHeureDepart(), 0, 5)) ?></small></td>
                                        <td><?= e($h->getStationArrivee()) ?> <small class="text-muted"><?= e(substr($h->getHeureArrivee(), 0, 5)) ?></small></td>
                                        <td><?= $h->getPrix() === null ? '—' : number_format($h->getPrix(), 3, ',', ' ') ?></td>
                                        <td><?= e($h->getEtatLibelle()) ?></td>
                                        <td class="text-end text-nowrap">
                                            <a href="<?= BASE_URL ?>index.php?page=admin-train-horaires&amp;edit=<?= (int) $h->getId() ?>" class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-train-horaire-delete" class="d-inline" data-confirm="Supprimer cet horaire ?">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $h->getId() ?>">
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
