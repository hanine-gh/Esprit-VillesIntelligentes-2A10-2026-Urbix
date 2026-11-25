<?php
/**
 * Vue : liste des trajets (backoffice, template Mazer).
 * Variables : $trajets (TrajetModel[]), $search, $chauffeurId, $driverOptions, $flash, $csrfToken
 */
?>
<div class="page-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3>Trajets</h3>
            <p class="text-subtitle text-muted mb-0">Créer les trajets de bus, les attribuer aux chauffeurs et gérer leurs stations.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?page=admin-trajet-create" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill"></i> Ajouter un trajet
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
            <div class="card-header">
                <form method="get" action="<?= BASE_URL ?>index.php" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="admin-trajets">
                    <div class="col-12 col-md-5">
                        <input type="text" name="q" class="form-control" placeholder="Rechercher (code, nom, départ, arrivée)"
                               value="<?= e($search) ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="chauffeur" class="form-select">
                            <option value="">Tous les chauffeurs</option>
                            <?php foreach ($driverOptions as $id => $name): ?>
                                <option value="<?= (int) $id ?>" <?= $chauffeurId === (int) $id ? 'selected' : '' ?>><?= e($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-light-primary flex-fill"><i class="bi bi-funnel-fill"></i> Filtrer</button>
                        <?php if ($search !== '' || $chauffeurId > 0): ?>
                            <a href="<?= BASE_URL ?>index.php?page=admin-trajets" class="btn btn-light-secondary" title="Effacer"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <?php if (empty($trajets)): ?>
                    <p class="text-muted text-center my-4">
                        <?= ($search !== '' || $chauffeurId > 0) ? 'Aucun trajet ne correspond à votre recherche.' : 'Aucun trajet pour le moment. Commencez par en ajouter un.' ?>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-lg mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Trajet</th>
                                    <th>Horaires</th>
                                    <th>Chauffeur</th>
                                    <th>Bus</th>
                                    <th>Stations</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trajets as $t): ?>
                                    <tr>
                                        <td><span class="badge bg-primary fs-6"><?= e($t->getCode()) ?></span></td>
                                        <td>
                                            <p class="font-bold mb-0"><?= e($t->getNom()) ?></p>
                                            <small class="text-muted">
                                                <?= e($t->getPointDepart()) ?> <i class="bi bi-arrow-right"></i> <?= e($t->getPointArrivee()) ?>
                                            </small>
                                        </td>
                                        <td class="text-nowrap"><?= e($t->getHeureDepart()) ?> – <?= e($t->getHeureArrivee()) ?></td>
                                        <td>
                                            <?php if ($t->getChauffeurId()): ?>
                                                <i class="bi bi-person-badge-fill text-primary"></i>
                                                <?= e($t->getChauffeurPrenom() . ' ' . $t->getChauffeurNom()) ?>
                                            <?php else: ?>
                                                <span class="badge bg-light-warning text-warning">Non attribué</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <?php if ($t->getBusId()): ?>
                                                <i class="bi bi-bus-front-fill text-primary"></i> <?= e($t->getBusMatricule()) ?>
                                            <?php else: ?>
                                                <span class="badge bg-light-warning text-warning">Aucun</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-light-info text-info"><?= $t->getNbStations() ?></span></td>
                                        <td>
                                            <span class="badge <?= $t->getStatut() === 'actif' ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                                                <?= $t->getStatut() === 'actif' ? 'Actif' : 'Inactif' ?>
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="<?= BASE_URL ?>index.php?page=admin-trajet-show&amp;id=<?= (int) $t->getId() ?>"
                                               class="btn btn-sm btn-light-info" title="Consulter / stations"><i class="bi bi-eye-fill"></i></a>
                                            <a href="<?= BASE_URL ?>index.php?page=admin-trajet-edit&amp;id=<?= (int) $t->getId() ?>"
                                               class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-trajet-delete" class="d-inline"
                                                  data-confirm="Supprimer le trajet <?= e($t->getCode()) ?> et ses <?= $t->getNbStations() ?> station(s) ?">
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
