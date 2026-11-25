<?php
/**
 * Vue : formulaire d'ajout / de modification d'une station (backoffice, template Mazer).
 * Variables : $mode, $trajet (TrajetModel), $station (StationModel|null), $errors, $old, $csrfToken
 * data-min / data-max : horaires du trajet, utilisés par le contrôle de saisie JS.
 */
$isEdit = $mode === 'edit';
$tid    = (int) $trajet->getId();

$f = (!$isEdit || !empty($old)) ? $old : [
    'nom'           => $station->getNom(),
    'adresse'       => $station->getAdresse(),
    'ordre'         => (string) $station->getOrdre(),
    'heure_passage' => $station->getHeurePassage(),
];

$action = $isEdit
    ? BASE_URL . 'index.php?page=admin-station-edit&id=' . (int) $station->getId()
    : BASE_URL . 'index.php?page=admin-station-create&trajet=' . $tid;
?>
<div class="page-heading">
    <h3><?= $isEdit ? 'Modifier la station' : 'Ajouter une station' ?></h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-trajets">Trajets</a></li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>index.php?page=admin-trajet-show&amp;id=<?= $tid ?>"><?= e($trajet->getCode()) ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Modifier la station' : 'Nouvelle station' ?></li>
        </ol>
    </nav>
</div>

<div class="page-content">
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="bi bi-geo-alt-fill"></i> Station du trajet <?= e($trajet->getCode()) ?></h4>
                <p class="text-muted mb-0 mt-1">
                    <?= e($trajet->getPointDepart()) ?> <i class="bi bi-arrow-right"></i> <?= e($trajet->getPointArrivee()) ?>
                    · horaires <?= e($trajet->getHeureDepart()) ?> – <?= e($trajet->getHeureArrivee()) ?>
                </p>
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

                <form id="station-form" method="post" novalidate action="<?= e($action) ?>"
                      data-min="<?= e($trajet->getHeureDepart()) ?>" data-max="<?= e($trajet->getHeureArrivee()) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-8 mb-3 auth-field">
                            <label for="s-nom" class="form-label">Nom de la station</label>
                            <input type="text" id="s-nom" name="nom" class="form-control" placeholder="Place de la République"
                                   value="<?= e($f['nom'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3 auth-field">
                            <label for="s-ordre" class="form-label">Ordre de passage</label>
                            <input type="text" id="s-ordre" name="ordre" class="form-control" placeholder="1"
                                   value="<?= e($f['ordre'] ?? '') ?>">
                        </div>
                        <div class="col-md-8 mb-3 auth-field">
                            <label for="s-adresse" class="form-label">Adresse</label>
                            <input type="text" id="s-adresse" name="adresse" class="form-control" placeholder="12 avenue Habib Bourguiba, Tunis"
                                   value="<?= e($f['adresse'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3 auth-field">
                            <label for="s-heure" class="form-label">Heure de passage</label>
                            <input type="time" id="s-heure" name="heure_passage" class="form-control" value="<?= e($f['heure_passage'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="<?= BASE_URL ?>index.php?page=admin-trajet-show&amp;id=<?= $tid ?>" class="btn btn-light-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Enregistrer' : 'Ajouter la station' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
