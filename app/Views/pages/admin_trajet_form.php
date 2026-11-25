<?php
/**
 * Vue : formulaire d'ajout / de modification d'un trajet (backoffice, template Mazer).
 * Variables : $mode, $trajet (TrajetModel|null), $errors, $old, $driverOptions, $busOptions, $statuts, $csrfToken
 * Aucun contrôle HTML5 : contrôle de saisie en JS (trajet-validation.js) + PHP.
 */
$isEdit = $mode === 'edit';

$f = !empty($old) && (!$isEdit || !empty($errors)) ? $old : ($isEdit ? [
    'code'          => $trajet->getCode(),
    'nom'           => $trajet->getNom(),
    'point_depart'  => $trajet->getPointDepart(),
    'point_arrivee' => $trajet->getPointArrivee(),
    'heure_depart'  => $trajet->getHeureDepart(),
    'heure_arrivee' => $trajet->getHeureArrivee(),
    'statut'        => $trajet->getStatut(),
    'chauffeur_id'  => (string) ($trajet->getChauffeurId() ?? ''),
    'bus_id'        => (string) ($trajet->getBusId() ?? ''),
] : $old);
$busStatutLabels = ['maintenance' => ' — en maintenance', 'hors_service' => ' — hors service'];

$action = $isEdit
    ? BASE_URL . 'index.php?page=admin-trajet-edit&id=' . (int) $trajet->getId()
    : BASE_URL . 'index.php?page=admin-trajet-create';
?>
<div class="page-heading">
    <h3><?= $isEdit ? 'Modifier le trajet' : 'Ajouter un trajet' ?></h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-trajets">Trajets</a></li>
            <?php if ($isEdit): ?>
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>index.php?page=admin-trajet-show&amp;id=<?= (int) $trajet->getId() ?>"><?= e($trajet->getCode()) ?></a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Modifier' : 'Ajouter' ?></li>
        </ol>
    </nav>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="bi bi-signpost-split-fill"></i> Informations du trajet</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-light-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (empty($driverOptions)): ?>
                    <div class="alert alert-light-warning">
                        Aucun chauffeur de bus n'existe encore : le trajet sera « non attribué ».
                        <a href="<?= BASE_URL ?>index.php?page=admin-driver-create">Ajouter un chauffeur</a>
                    </div>
                <?php endif; ?>

                <form id="trajet-form" method="post" novalidate action="<?= e($action) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-4 mb-3 auth-field">
                            <label for="t-code" class="form-label">Code</label>
                            <input type="text" id="t-code" name="code" class="form-control text-uppercase" placeholder="L12"
                                   value="<?= e($f['code'] ?? '') ?>">
                        </div>
                        <div class="col-md-8 mb-3 auth-field">
                            <label for="t-nom" class="form-label">Nom du trajet</label>
                            <input type="text" id="t-nom" name="nom" class="form-control" placeholder="Ligne Centre - Aéroport"
                                   value="<?= e($f['nom'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="t-depart" class="form-label">Point de départ</label>
                            <input type="text" id="t-depart" name="point_depart" class="form-control" placeholder="Gare centrale"
                                   value="<?= e($f['point_depart'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="t-arrivee" class="form-label">Point d'arrivée</label>
                            <input type="text" id="t-arrivee" name="point_arrivee" class="form-control" placeholder="Aéroport"
                                   value="<?= e($f['point_arrivee'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="t-hdep" class="form-label">Heure de départ</label>
                            <input type="time" id="t-hdep" name="heure_depart" class="form-control" value="<?= e($f['heure_depart'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="t-harr" class="form-label">Heure d'arrivée</label>
                            <input type="time" id="t-harr" name="heure_arrivee" class="form-control" value="<?= e($f['heure_arrivee'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="t-statut" class="form-label">Statut</label>
                            <select id="t-statut" name="statut" class="form-select">
                                <option value="">Choisir...</option>
                                <?php foreach ($statuts as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= ($f['statut'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="t-chauffeur" class="form-label">Chauffeur attribué</label>
                            <select id="t-chauffeur" name="chauffeur_id" class="form-select">
                                <option value="">— Non attribué —</option>
                                <?php foreach ($driverOptions as $id => $name): ?>
                                    <option value="<?= (int) $id ?>" <?= ($f['chauffeur_id'] ?? '') === (string) $id ? 'selected' : '' ?>><?= e($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="t-bus" class="form-label">Bus utilisé</label>
                            <select id="t-bus" name="bus_id" class="form-select">
                                <option value="">— Aucun bus —</option>
                                <?php foreach ($busOptions as $id => $bus): ?>
                                    <option value="<?= (int) $id ?>" <?= ($f['bus_id'] ?? '') === (string) $id ? 'selected' : '' ?>>
                                        <?= e($bus['label'] . ($busStatutLabels[$bus['statut']] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($busOptions)): ?>
                                <small class="text-muted">Aucun bus dans la flotte. <a href="<?= BASE_URL ?>index.php?page=admin-bus-create">Ajouter un bus</a></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="<?= BASE_URL ?>index.php?page=<?= $isEdit ? 'admin-trajet-show&amp;id=' . (int) $trajet->getId() : 'admin-trajets' ?>"
                           class="btn btn-light-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le trajet' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
