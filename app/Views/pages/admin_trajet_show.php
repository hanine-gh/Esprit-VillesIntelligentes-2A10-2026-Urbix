<?php
/**
 * Vue : détails d'un trajet + ses stations (backoffice, template Mazer).
 * Variables : $trajet (TrajetModel), $stations (StationModel[]), $flash, $csrfToken
 */
$tid = (int) $trajet->getId();
?>
<div class="page-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3><span class="badge bg-primary"><?= e($trajet->getCode()) ?></span> <?= e($trajet->getNom()) ?></h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-trajets">Trajets</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($trajet->getCode()) ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?= BASE_URL ?>index.php?page=admin-trajets" class="btn btn-light-secondary">
            <i class="bi bi-arrow-left"></i> Retour aux trajets
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
        <!-- Informations du trajet -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Informations</h4></div>
                <div class="card-body">
                    <h6 class="text-muted mb-1">Itinéraire</h6>
                    <p class="fw-bold">
                        <?= e($trajet->getPointDepart()) ?> <i class="bi bi-arrow-right"></i> <?= e($trajet->getPointArrivee()) ?>
                    </p>

                    <h6 class="text-muted mb-1">Horaires</h6>
                    <p class="fw-bold"><i class="bi bi-clock"></i> <?= e($trajet->getHeureDepart()) ?> – <?= e($trajet->getHeureArrivee()) ?></p>

                    <h6 class="text-muted mb-1">Statut</h6>
                    <p>
                        <span class="badge <?= $trajet->getStatut() === 'actif' ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary' ?>">
                            <?= $trajet->getStatut() === 'actif' ? 'Actif' : 'Inactif' ?>
                        </span>
                    </p>

                    <h6 class="text-muted mb-1">Chauffeur attribué</h6>
                    <?php if ($trajet->getChauffeurId()): ?>
                        <p class="fw-bold">
                            <a href="<?= BASE_URL ?>index.php?page=admin-driver-show&amp;id=<?= (int) $trajet->getChauffeurId() ?>">
                                <i class="bi bi-person-badge-fill"></i> <?= e($trajet->getChauffeurPrenom() . ' ' . $trajet->getChauffeurNom()) ?>
                            </a>
                        </p>
                    <?php else: ?>
                        <p><span class="badge bg-light-warning text-warning">Non attribué</span></p>
                    <?php endif; ?>

                    <h6 class="text-muted mb-1">Bus utilisé</h6>
                    <?php if ($trajet->getBusId()): ?>
                        <p class="fw-bold">
                            <a href="<?= BASE_URL ?>index.php?page=admin-bus-show&amp;id=<?= (int) $trajet->getBusId() ?>">
                                <i class="bi bi-bus-front-fill"></i> <?= e($trajet->getBusMatricule()) ?>
                            </a>
                            <br><small class="text-muted fw-normal"><?= e($trajet->getBusMarque() . ' ' . $trajet->getBusModele()) ?> · <?= (int) $trajet->getBusCapacite() ?> places</small>
                        </p>
                    <?php else: ?>
                        <p><span class="badge bg-light-warning text-warning">Aucun bus</span></p>
                    <?php endif; ?>

                    <h6 class="text-muted mb-1">Créé le</h6>
                    <p class="fw-bold"><?= $trajet->getCreatedAt() ? e(date('d/m/Y', strtotime($trajet->getCreatedAt()))) : '—' ?></p>

                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>index.php?page=admin-trajet-edit&amp;id=<?= $tid ?>" class="btn btn-warning">
                            <i class="bi bi-pencil-fill"></i> Modifier / attribuer
                        </a>
                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-trajet-delete"
                              data-confirm="Supprimer le trajet <?= e($trajet->getCode()) ?> et ses <?= count($stations) ?> station(s) ?">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="id" value="<?= $tid ?>">
                            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-trash-fill"></i> Supprimer le trajet</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stations du trajet -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        Stations <span class="badge bg-light-info text-info"><?= count($stations) ?></span>
                    </h4>
                    <a href="<?= BASE_URL ?>index.php?page=admin-station-create&amp;trajet=<?= $tid ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-geo-alt-fill"></i> Ajouter une station
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($stations)): ?>
                        <p class="text-muted text-center my-4">
                            Aucune station sur ce trajet.<br>
                            Ajoutez les arrêts dans l'ordre de passage du bus.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Ordre</th>
                                        <th>Station</th>
                                        <th>Passage</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stations as $s): ?>
                                        <tr>
                                            <td><span class="badge bg-light-primary text-primary fs-6"><?= $s->getOrdre() ?></span></td>
                                            <td>
                                                <p class="font-bold mb-0"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e($s->getNom()) ?></p>
                                                <small class="text-muted"><?= e($s->getAdresse()) ?></small>
                                            </td>
                                            <td class="text-nowrap"><i class="bi bi-clock"></i> <?= e($s->getHeurePassage()) ?></td>
                                            <td class="text-end text-nowrap">
                                                <a href="<?= BASE_URL ?>index.php?page=admin-station-edit&amp;id=<?= (int) $s->getId() ?>"
                                                   class="btn btn-sm btn-light-warning" title="Modifier"><i class="bi bi-pencil-fill"></i></a>
                                                <form method="post" action="<?= BASE_URL ?>index.php?page=admin-station-delete" class="d-inline"
                                                      data-confirm="Supprimer la station <?= e($s->getNom()) ?> ?">
                                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                    <input type="hidden" name="id" value="<?= (int) $s->getId() ?>">
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
        </div>
    </section>
</div>
