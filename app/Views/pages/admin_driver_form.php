<?php
/**
 * Vue : formulaire d'ajout / de modification d'un chauffeur de bus (backoffice, template Mazer).
 * Variables : $mode ('create'|'edit'), $driver (UserModel|null), $errors, $old, $genres, $csrfToken
 * Aucun contrôle HTML5 (pas de required/minlength/type=email) : contrôle en JS + PHP.
 */
$isEdit = $mode === 'edit';

// Valeurs : saisies (si erreur) sinon celles de la base (modification) sinon vides (création)
$f = !empty($old) ? $old : ($isEdit ? [
    'nom'            => $driver->getNom(),
    'prenom'         => $driver->getPrenom(),
    'email'          => $driver->getEmail(),
    'telephone'      => $driver->getTelephone(),
    'date_naissance' => $driver->getDateNaissance(),
    'genre'          => $driver->getGenre(),
] : []);

$action = $isEdit
    ? BASE_URL . 'index.php?page=admin-driver-edit&id=' . (int) $driver->getId()
    : BASE_URL . 'index.php?page=admin-driver-create';
?>
<div class="page-heading">
    <h3><?= $isEdit ? 'Modifier le chauffeur' : 'Ajouter un chauffeur de bus' ?></h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-drivers">Chauffeurs de bus</a></li>
            <?php if ($isEdit): ?>
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>index.php?page=admin-driver-show&amp;id=<?= (int) $driver->getId() ?>">
                        <?= e($driver->getPrenom() . ' ' . $driver->getNom()) ?>
                    </a>
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
                <h4 class="card-title mb-0"><i class="bi bi-bus-front-fill"></i> Informations du chauffeur</h4>
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

                <form id="driver-form" data-mode="<?= $isEdit ? 'edit' : 'create' ?>" method="post" novalidate action="<?= e($action) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-nom" class="form-label">Nom</label>
                            <input type="text" id="d-nom" name="nom" class="form-control" value="<?= e($f['nom'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-prenom" class="form-label">Prénom</label>
                            <input type="text" id="d-prenom" name="prenom" class="form-control" value="<?= e($f['prenom'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-email" class="form-label">Email</label>
                            <input type="text" id="d-email" name="email" class="form-control" value="<?= e($f['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-telephone" class="form-label">Téléphone</label>
                            <input type="text" id="d-telephone" name="telephone" class="form-control" placeholder="20 000 000" value="<?= e($f['telephone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-date" class="form-label">Date de naissance</label>
                            <input type="date" id="d-date" name="date_naissance" class="form-control" value="<?= e($f['date_naissance'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-genre" class="form-label">Genre</label>
                            <select id="d-genre" name="genre" class="form-select">
                                <option value="">Choisir...</option>
                                <?php foreach ($genres as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= ($f['genre'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">
                        <i class="bi bi-lock-fill"></i> Mot de passe
                        <?php if ($isEdit): ?><small class="text-muted fw-normal">(laisser vide pour le conserver)</small><?php endif; ?>
                    </h6>

                    <div class="row">
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-password" class="form-label"><?= $isEdit ? 'Nouveau mot de passe' : 'Mot de passe' ?> (min. 8 caractères)</label>
                            <input type="password" id="d-password" name="password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-md-6 mb-3 auth-field">
                            <label for="d-confirm" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" id="d-confirm" name="password_confirm" class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <a href="<?= BASE_URL ?>index.php?page=<?= $isEdit ? 'admin-driver-show&amp;id=' . (int) $driver->getId() : 'admin-drivers' ?>"
                           class="btn btn-light-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save-fill"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le chauffeur' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
