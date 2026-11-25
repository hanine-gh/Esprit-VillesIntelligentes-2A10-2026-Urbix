<?php
/**
 * Vue : tous les détails d'un chauffeur de bus (backoffice, template Mazer).
 * Variables : $driver (UserModel), $flash, $trajets (TrajetModel[]), $bus (BusModel|null), $genres, $typesService, $csrfToken
 * L'id n'est jamais affiché (il sert seulement dans les liens et formulaires).
 */
$naissance = $driver->getDateNaissance();
$age       = $naissance ? (new DateTime($naissance))->diff(new DateTime('today'))->y : null;
?>
<div class="page-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3>Détails du chauffeur</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-drivers">Chauffeurs de bus</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($driver->getPrenom() . ' ' . $driver->getNom()) ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?= BASE_URL ?>index.php?page=admin-drivers" class="btn btn-light-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="page-content">
    <?php if ($flash): ?>
        <div class="alert alert-light-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <section class="row">
        <!-- Carte d'identité -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="avatar avatar-xl bg-primary mb-3">
                        <span class="avatar-content text-white fs-3">
                            <?= e(mb_strtoupper(mb_substr($driver->getPrenom(), 0, 1) . mb_substr($driver->getNom(), 0, 1))) ?>
                        </span>
                    </div>
                    <h4 class="mb-1"><?= e($driver->getPrenom() . ' ' . $driver->getNom()) ?></h4>
                    <p class="text-muted mb-2"><?= e($driver->getEmail()) ?></p>
                    <span class="badge bg-light-primary text-primary">
                        <i class="bi bi-bus-front-fill"></i> <?= e($typesService[$driver->getTypeService()] ?? 'Chauffeur bus') ?>
                    </span>

                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= BASE_URL ?>index.php?page=admin-driver-edit&amp;id=<?= (int) $driver->getId() ?>" class="btn btn-warning">
                            <i class="bi bi-pencil-fill"></i> Modifier
                        </a>
                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-driver-delete"
                              data-confirm="Supprimer définitivement le chauffeur <?= e($driver->getPrenom() . ' ' . $driver->getNom()) ?> ? Ses trajets deviendront « non attribués ».">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="id" value="<?= (int) $driver->getId() ?>">
                            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-trash-fill"></i> Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toutes les informations -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Informations personnelles</h4></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><h6 class="text-muted mb-1">Nom</h6><p class="fw-bold mb-0"><?= e($driver->getNom()) ?></p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Prénom</h6><p class="fw-bold mb-0"><?= e($driver->getPrenom()) ?></p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Email</h6><p class="fw-bold mb-0"><?= e($driver->getEmail()) ?></p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Téléphone</h6><p class="fw-bold mb-0"><?= e($driver->getTelephone()) ?></p></div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Date de naissance</h6>
                            <p class="fw-bold mb-0">
                                <?= $naissance ? e(date('d/m/Y', strtotime($naissance))) : '—' ?>
                                <?php if ($age !== null): ?><span class="text-muted fw-normal">(<?= (int) $age ?> ans)</span><?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Genre</h6><p class="fw-bold mb-0"><?= e($genres[$driver->getGenre()] ?? '—') ?></p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Type de compte</h6><p class="fw-bold mb-0"><?= e($typesService[$driver->getTypeService()] ?? '—') ?></p></div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Bus attitré</h6>
                            <?php if ($bus): ?>
                                <p class="fw-bold mb-0">
                                    <a href="<?= BASE_URL ?>index.php?page=admin-bus-show&amp;id=<?= (int) $bus->getId() ?>">
                                        <i class="bi bi-bus-front-fill"></i> <?= e($bus->getMatricule()) ?>
                                    </a>
                                    <small class="text-muted fw-normal">(<?= e($bus->getMarque() . ' ' . $bus->getModele()) ?>)</small>
                                </p>
                            <?php else: ?>
                                <p class="mb-0 text-muted">Aucun — <a href="<?= BASE_URL ?>index.php?page=admin-buses">attribuer un bus</a></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Rôle</h6><p class="mb-0"><span class="badge bg-light-primary text-primary">Utilisateur</span></p></div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Inscrit le</h6>
                            <p class="fw-bold mb-0"><?= $driver->getCreatedAt() ? e(date('d/m/Y à H:i', strtotime($driver->getCreatedAt()))) : '—' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trajets attribués à ce chauffeur -->
    <section class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        Trajets attribués <span class="badge bg-light-info text-info"><?= count($trajets) ?></span>
                    </h4>
                    <a href="<?= BASE_URL ?>index.php?page=admin-trajet-create&amp;chauffeur=<?= (int) $driver->getId() ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle-fill"></i> Attribuer un nouveau trajet
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($trajets)): ?>
                        <p class="text-muted text-center my-3">Aucun trajet attribué à ce chauffeur.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr><th>Code</th><th>Trajet</th><th>Horaires</th><th>Stations</th><th>Statut</th><th class="text-end"></th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trajets as $t): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= e($t->getCode()) ?></span></td>
                                            <td><?= e($t->getPointDepart()) ?> <i class="bi bi-arrow-right"></i> <?= e($t->getPointArrivee()) ?></td>
                                            <td class="text-nowrap"><?= e($t->getHeureDepart()) ?> – <?= e($t->getHeureArrivee()) ?></td>
                                            <td><?= $t->getNbStations() ?></td>
                                            <td>
                                                <span class="badge <?= $t->getStatut() === 'actif' ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                                                    <?= $t->getStatut() === 'actif' ? 'Actif' : 'Inactif' ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>index.php?page=admin-trajet-show&amp;id=<?= (int) $t->getId() ?>" class="btn btn-sm btn-light-info">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
