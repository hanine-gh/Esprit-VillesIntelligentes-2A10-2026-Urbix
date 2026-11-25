<?php
/**
 * Vue : tableau de bord du backoffice (template Mazer).
 * Variables : $user, $allUsers (UserModel[]), $totalUsers, $totalAdmins, $totalRegular, $totalDrivers, $typesService, $genres
 */
?>
<div class="page-heading">
    <h3>Tableau de bord</h3>
    <p class="text-subtitle text-muted">Bienvenue, <?= e($user->getPrenom()) ?>. Gestion des comptes de la plateforme Smart City.</p>
</div>

<div class="page-content">
    <!-- ===== Statistiques ===== -->
    <section class="row">
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon purple mb-2"><i class="iconly-boldProfile"></i></div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Total des comptes</h6>
                            <h6 class="font-extrabold mb-0"><?= (int) $totalUsers ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon blue mb-2"><i class="iconly-boldAdd-User"></i></div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Utilisateurs</h6>
                            <h6 class="font-extrabold mb-0"><?= (int) $totalRegular ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body px-4 py-4-5">
                    <div class="row">
                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                            <div class="stats-icon red mb-2"><i class="iconly-boldShield-Done"></i></div>
                        </div>
                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                            <h6 class="text-muted font-semibold">Administrateurs</h6>
                            <h6 class="font-extrabold mb-0"><?= (int) $totalAdmins ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <a href="<?= BASE_URL ?>index.php?page=admin-drivers" class="text-decoration-none">
                <div class="card">
                    <div class="card-body px-4 py-4-5">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                <div class="stats-icon green mb-2"><i class="iconly-boldWork"></i></div>
                            </div>
                            <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                <h6 class="text-muted font-semibold">Chauffeurs de bus</h6>
                                <h6 class="font-extrabold mb-0"><?= (int) $totalDrivers ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- ===== Liste des utilisateurs ===== -->
    <section class="row" id="users">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Tous les utilisateurs</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-lg mb-0">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Type de compte</th>
                                    <th>Rôle</th>
                                    <th>Inscrit le</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $u): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-md <?= $u->getRole() === 'admin' ? 'bg-danger' : 'bg-primary' ?> me-3">
                                                    <span class="avatar-content text-white">
                                                        <?= e(mb_strtoupper(mb_substr($u->getPrenom(), 0, 1) . mb_substr($u->getNom(), 0, 1))) ?>
                                                    </span>
                                                </div>
                                                <p class="font-bold mb-0"><?= e($u->getPrenom() . ' ' . $u->getNom()) ?></p>
                                            </div>
                                        </td>
                                        <td><?= e($u->getEmail()) ?></td>
                                        <td><?= e($u->getTelephone()) ?></td>
                                        <td><?= e($typesService[$u->getTypeService()] ?? 'Non renseigné') ?></td>
                                        <td>
                                            <?php if ($u->getRole() === 'admin'): ?>
                                                <span class="badge bg-light-danger text-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-light-primary text-primary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $u->getCreatedAt() ? e(date('d/m/Y', strtotime($u->getCreatedAt()))) : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
