<?php
/**
 * Vue : détails d'un bus + trajets effectués avec ce bus (backoffice, template Mazer).
 * Variables : $bus (BusModel), $trajets (TrajetModel[]), $statuts, $flash, $csrfToken
 */
$badge = ['disponible' => 'bg-light-success text-success', 'maintenance' => 'bg-light-warning text-warning', 'hors_service' => 'bg-light-danger text-danger'];
$bid   = (int) $bus->getId();
?>
<div class="page-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3><span class="badge bg-dark"><?= e($bus->getMatricule()) ?></span> <?= e($bus->getMarque() . ' ' . $bus->getModele()) ?></h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php?page=admin-buses">Bus</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= e($bus->getMatricule()) ?></li>
                </ol>
            </nav>
        </div>
        <a href="<?= BASE_URL ?>index.php?page=admin-buses" class="btn btn-light-secondary"><i class="bi bi-arrow-left"></i> Retour aux bus</a>
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
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="avatar avatar-xl bg-primary mb-3">
                        <span class="avatar-content text-white fs-3"><i class="bi bi-bus-front-fill"></i></span>
                    </div>
                    <h4 class="mb-1"><?= e($bus->getMatricule()) ?></h4>
                    <p class="text-muted mb-2"><?= e($bus->getMarque() . ' ' . $bus->getModele()) ?></p>
                    <span class="badge <?= $badge[$bus->getStatut()] ?? '' ?>"><?= e($statuts[$bus->getStatut()] ?? $bus->getStatut()) ?></span>

                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= BASE_URL ?>index.php?page=admin-bus-edit&amp;id=<?= $bid ?>" class="btn btn-warning">
                            <i class="bi bi-pencil-fill"></i> Modifier / attribuer un chauffeur
                        </a>
                        <a href="<?= BASE_URL ?>index.php?page=admin-trajet-create&amp;bus=<?= $bid ?>" class="btn btn-light-primary">
                            <i class="bi bi-signpost-split-fill"></i> Nouveau trajet avec ce bus
                        </a>
                        <form method="post" action="<?= BASE_URL ?>index.php?page=admin-bus-delete"
                              data-confirm="Supprimer le bus <?= e($bus->getMatricule()) ?> ? Ses <?= count($trajets) ?> trajet(s) n'auront plus de bus.">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <input type="hidden" name="id" value="<?= $bid ?>">
                            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-trash-fill"></i> Supprimer le bus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <?php if ($bus->getEtatCommentaire() || $bus->getEtatMajAt()): ?>
                    <div class="alert alert-light-<?= $bus->getStatut() === 'disponible' ? 'info' : 'warning' ?> mb-3">
                        <h6 class="mb-1"><i class="bi bi-exclamation-triangle-fill"></i> Signalement du chauffeur
                            <?php if ($bus->getEtatMajAt()): ?>
                                <small class="fw-normal">— <?= e(date('d/m/Y à H:i', strtotime($bus->getEtatMajAt()))) ?></small>
                            <?php endif; ?>
                        </h6>
                        <p class="mb-0"><strong><?= e($statuts[$bus->getStatut()] ?? $bus->getStatut()) ?></strong>
                            <?= $bus->getEtatCommentaire() ? ' : ' . e($bus->getEtatCommentaire()) : '' ?></p>
                    </div>
                <?php endif; ?>
                <div class="card-header"><h4 class="card-title mb-0">Caractéristiques</h4></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><h6 class="text-muted mb-1">Matricule</h6><p class="fw-bold mb-0"><?= e($bus->getMatricule()) ?></p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Marque / modèle</h6><p class="fw-bold mb-0"><?= e($bus->getMarque() . ' ' . $bus->getModele()) ?></p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Capacité</h6><p class="fw-bold mb-0"><?= (int) $bus->getCapacite() ?> places</p></div>
                        <div class="col-md-6"><h6 class="text-muted mb-1">Mise en service</h6><p class="fw-bold mb-0"><?= (int) $bus->getAnnee() ?></p></div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Chauffeur attitré</h6>
                            <?php if ($bus->getChauffeurId()): ?>
                                <p class="fw-bold mb-0">
                                    <a href="<?= BASE_URL ?>index.php?page=admin-driver-show&amp;id=<?= (int) $bus->getChauffeurId() ?>">
                                        <i class="bi bi-person-badge-fill"></i> <?= e($bus->getChauffeurPrenom() . ' ' . $bus->getChauffeurNom()) ?>
                                    </a>
                                </p>
                            <?php else: ?>
                                <p class="mb-0"><span class="text-muted">Aucun</span></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Ajouté le</h6>
                            <p class="fw-bold mb-0"><?= $bus->getCreatedAt() ? e(date('d/m/Y', strtotime($bus->getCreatedAt()))) : '—' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Trajets effectués avec ce bus <span class="badge bg-light-info text-info"><?= count($trajets) ?></span></h4></div>
                <div class="card-body">
                    <?php if (empty($trajets)): ?>
                        <p class="text-muted text-center my-3">Ce bus n'est attribué à aucun trajet. Attribuez-le depuis le formulaire d'un trajet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead><tr><th>Code</th><th>Itinéraire</th><th>Horaires</th><th>Chauffeur</th><th></th></tr></thead>
                                <tbody>
                                    <?php foreach ($trajets as $t): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= e($t->getCode()) ?></span></td>
                                            <td><?= e($t->getPointDepart()) ?> <i class="bi bi-arrow-right"></i> <?= e($t->getPointArrivee()) ?></td>
                                            <td class="text-nowrap"><?= e($t->getHeureDepart()) ?> – <?= e($t->getHeureArrivee()) ?></td>
                                            <td><?= $t->getChauffeurId() ? e($t->getChauffeurPrenom() . ' ' . $t->getChauffeurNom()) : '<span class="badge bg-light-warning text-warning">Non attribué</span>' ?></td>
                                            <td class="text-end">
                                                <a href="<?= BASE_URL ?>index.php?page=admin-trajet-show&amp;id=<?= (int) $t->getId() ?>" class="btn btn-sm btn-light-info"><i class="bi bi-eye-fill"></i></a>
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
