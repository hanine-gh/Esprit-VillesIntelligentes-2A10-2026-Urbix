<?php
/**
 * Vue : formulaire d'ajout / de modification d'un bus (backoffice, template Mazer).
 * Variables : $mode, $bus (BusModel|null), $errors, $old, $statuts, $driverOptions, $csrfToken
 * Aucun contrôle HTML5 : contrôle de saisie en JS (trajet-validation.js) + PHP.
 */
$isEdit = $mode === 'edit';

$f = (!$isEdit || !empty($old)) ? $old : [
    'matricule'    => $bus->getMatricule(),
    'marque'       => $bus->getMarque(),
    'modele'       => $bus->getModele(),
    'capacite'     => (string) $bus->getCapacite(),
    'annee'        => (string) $bus->getAnnee(),
    'statut'       => $bus->getStatut(),
    'chauffeur_id' => (string) ($bus->getChauffeurId() ?? ''),
];

$action = $isEdit
    ? BASE_URL . 'index.php?page=admin-bus-edit&id=' . (int) $bus->getId()
    : BASE_URL . 'index.php?page=admin-bus-create';
?>
<div class="page-heading">
    <h3><?= $isEdit ? 'Modifier le bus' : 'Ajouter un bus' ?></h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-buses">Bus</a></li>
            <?php if ($isEdit): ?>
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>index.php?page=admin-bus-show&amp;id=<?= (int) $bus->getId() ?>"><?= e($bus->getMatricule()) ?></a>
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
                <h4 class="card-title mb-0"><i class="bi bi-bus-front-fill"></i> Informations du bus</h4>
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

                <form id="bus-form" method="post" novalidate action="<?= e($action) ?>" data-max-year="<?= (int) date('Y') + 1 ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-4 mb-3 auth-field">
                            <label for="b-matricule" class="form-label">Matricule</label>
                            <input type="text" id="b-matricule" name="matricule" class="form-control text-uppercase" placeholder="123 TU 4567"
                                   value="<?= e($f['matricule'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3 auth-field">
                            <label for="b-marque" class="form-label">Marque</label>
                            <input type="text" id="b-marque" name="marque" class="form-control" placeholder="Mercedes" value="<?= e($f['marque'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3 auth-field">
                            <label for="b-modele" class="form-label">Modèle</label>
                            <input type="text" id="b-modele" name="modele" class="form-control" placeholder="Citaro" value="<?= e($f['modele'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="b-capacite" class="form-label">Capacité (places)</label>
                            <input type="text" id="b-capacite" name="capacite" class="form-control" placeholder="50" value="<?= e($f['capacite'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="b-annee" class="form-label">Année de mise en service</label>
                            <input type="text" id="b-annee" name="annee" class="form-control" placeholder="<?= date('Y') ?>" value="<?= e($f['annee'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="b-statut" class="form-label">Statut</label>
                            <select id="b-statut" name="statut" class="form-select">
                                <option value="">Choisir...</option>
                                <?php foreach ($statuts as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= ($f['statut'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 auth-field">
                            <label for="b-chauffeur" class="form-label">Chauffeur attitré</label>
                            <select id="b-chauffeur" name="chauffeur_id" class="form-select">
                                <option value="">— Aucun —</option>
                                <?php foreach ($driverOptions as $id => $name): ?>
                                    <option value="<?= (int) $id ?>" <?= ($f['chauffeur_id'] ?? '') === (string) $id ? 'selected' : '' ?>><?= e($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Seuls les chauffeurs sans bus attitré sont proposés.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="<?= BASE_URL ?>index.php?page=<?= $isEdit ? 'admin-bus-show&amp;id=' . (int) $bus->getId() : 'admin-buses' ?>"
                           class="btn btn-light-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le bus' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
