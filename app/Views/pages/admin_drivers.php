<?php
/**
 * Vue : liste des chauffeurs de bus (backoffice, template Mazer).
 * Variables : $drivers (UserModel[]), $search, $flash, $genres, $csrfToken
 */
?>
<div class="page-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3>Chauffeurs de bus</h3>
            <p class="text-subtitle text-muted mb-0">Consulter, ajouter, modifier et supprimer les chauffeurs de bus.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?page=admin-driver-create" class="btn btn-primary">
            <i class="bi bi-person-plus-fill"></i> Ajouter un chauffeur
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

    <section class="section">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h4 class="card-title mb-0">
                    Liste des chauffeurs <span class="badge bg-light-primary text-primary"><?= count($drivers) ?></span>
                </h4>
                <form method="get" action="<?= BASE_URL ?>index.php" class="d-flex gap-2">
                    <input type="hidden" name="page" value="admin-drivers">
                    <input type="text" name="q" class="form-control" placeholder="Rechercher (nom, email, téléphone)"
                           value="<?= e($search) ?>">
                    <button type="submit" class="btn btn-light-primary"><i class="bi bi-search"></i></button>
                    <?php if ($search !== ''): ?>
                        <a href="<?= BASE_URL ?>index.php?page=admin-drivers" class="btn btn-light-secondary" title="Effacer">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card-body">
                <?php if (empty($drivers)): ?>
                    <p class="text-muted text-center my-4">
                        <?= $search !== '' ? 'Aucun chauffeur ne correspond à votre recherche.' : 'Aucun chauffeur de bus pour le moment.' ?>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-lg mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Chauffeur</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Date de naissance</th>
                                    <th>Genre</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drivers as $d): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-md bg-primary me-3">
                                                    <span class="avatar-content text-white">
                                                        <?= e(mb_strtoupper(mb_substr($d->getPrenom(), 0, 1) . mb_substr($d->getNom(), 0, 1))) ?>
                                                    </span>
                                                </div>
                                                <p class="font-bold mb-0"><?= e($d->getPrenom() . ' ' . $d->getNom()) ?></p>
                                            </div>
                                        </td>
                                        <td><?= e($d->getEmail()) ?></td>
                                        <td><?= e($d->getTelephone()) ?></td>
                                        <td><?= $d->getDateNaissance() ? e(date('d/m/Y', strtotime($d->getDateNaissance()))) : '—' ?></td>
                                        <td><?= e($genres[$d->getGenre()] ?? '—') ?></td>
                                        <td class="text-end text-nowrap">
                                            <a href="<?= BASE_URL ?>index.php?page=admin-driver-show&amp;id=<?= (int) $d->getId() ?>"
                                               class="btn btn-sm btn-light-info" title="Consulter"><i class="bi bi-eye-fill"></i></a>
                                            <a href="<?= BASE_URL ?>index.php?page=admin-driver-edit&amp;id=<?= (int) $d->getId() ?>"
                                               class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-driver-delete" class="d-inline"
                                                  data-confirm="Supprimer définitivement le chauffeur <?= e($d->getPrenom() . ' ' . $d->getNom()) ?> ?">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $d->getId() ?>">
                                                <button type="submit" class="btn btn-sm btn-light-danger" title="Supprimer">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
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
