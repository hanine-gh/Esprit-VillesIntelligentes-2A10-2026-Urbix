<?php
/**
 * Vue : flotte de bus (backoffice, template Mazer).
 * Variables : $buses (BusModel[]), $search, $statut, $statuts, $flash, $csrfToken
 */
$badge = ['disponible' => 'bg-light-success text-success', 'maintenance' => 'bg-light-warning text-warning', 'hors_service' => 'bg-light-danger text-danger'];
?>
<div class="page-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3>Bus</h3>
            <p class="text-subtitle text-muted mb-0">Gérer la flotte et attribuer les bus aux chauffeurs et aux trajets.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?page=admin-bus-create" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill"></i> Ajouter un bus
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
                    <input type="hidden" name="page" value="admin-buses">
                    <div class="col-12 col-md-5">
                        <input type="text" name="q" class="form-control" placeholder="Rechercher (matricule, marque, modèle)" value="<?= e($search) ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <?php foreach ($statuts as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= $statut === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-light-primary flex-fill"><i class="bi bi-funnel-fill"></i> Filtrer</button>
                        <?php if ($search !== '' || $statut !== ''): ?>
                            <a href="<?= BASE_URL ?>index.php?page=admin-buses" class="btn btn-light-secondary" title="Effacer"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <?php if (empty($buses)): ?>
                    <p class="text-muted text-center my-4">
                        <?= ($search !== '' || $statut !== '') ? 'Aucun bus ne correspond à votre recherche.' : 'Aucun bus dans la flotte pour le moment.' ?>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-lg mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Modèle</th>
                                    <th>Places</th>
                                    <th>Chauffeur attitré</th>
                                    <th>Trajets</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($buses as $b): ?>
                                    <tr>
                                        <td><span class="badge bg-dark fs-6"><?= e($b->getMatricule()) ?></span></td>
                                        <td>
                                            <p class="font-bold mb-0"><i class="bi bi-bus-front-fill text-primary"></i> <?= e($b->getMarque() . ' ' . $b->getModele()) ?></p>
                                            <small class="text-muted">Mise en service : <?= (int) $b->getAnnee() ?></small>
                                        </td>
                                        <td><?= (int) $b->getCapacite() ?></td>
                                        <td>
                                            <?php if ($b->getChauffeurId()): ?>
                                                <i class="bi bi-person-badge-fill text-primary"></i> <?= e($b->getChauffeurPrenom() . ' ' . $b->getChauffeurNom()) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-light-info text-info"><?= $b->getNbTrajets() ?></span></td>
                                        <td>
                                            <span class="badge <?= $badge[$b->getStatut()] ?? '' ?>"><?= e($statuts[$b->getStatut()] ?? $b->getStatut()) ?></span>
                                            <?php if ($b->getEtatCommentaire()): ?>
                                                <i class="bi bi-chat-left-text-fill text-warning" title="<?= e($b->getEtatCommentaire()) ?>"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="<?= BASE_URL ?>index.php?page=admin-bus-show&amp;id=<?= (int) $b->getId() ?>"
                                               class="btn btn-sm btn-light-info" title="Consulter"><i class="bi bi-eye-fill"></i></a>
                                            <a href="<?= BASE_URL ?>index.php?page=admin-bus-edit&amp;id=<?= (int) $b->getId() ?>"
                                               class="btn btn-sm btn-light-warning" title="Modifier / attribuer"><i class="bi bi-pencil-fill"></i></a>
                                            <form method="post" action="<?= BASE_URL ?>index.php?page=admin-bus-delete" class="d-inline"
                                                  data-confirm="Supprimer le bus <?= e($b->getMatricule()) ?> ? Ses <?= $b->getNbTrajets() ?> trajet(s) n'auront plus de bus.">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $b->getId() ?>">
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
