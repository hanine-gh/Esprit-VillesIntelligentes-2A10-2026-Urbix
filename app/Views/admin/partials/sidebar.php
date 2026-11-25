<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="<?= BASE_URL ?>index.php?page=admin" class="fs-4 fw-bold text-primary text-decoration-none">
                        <i class="bi bi-buildings-fill"></i> Smart City
                    </a>
                </div>
                <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                    <i class="bi bi-sun"></i>
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer">
                        <label class="form-check-label"></label>
                    </div>
                    <i class="bi bi-moon"></i>
                </div>
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?= BASE_URL ?>index.php?page=admin#users" class="sidebar-link">
                        <i class="bi bi-people-fill"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>

                <li class="sidebar-title">Bus</li>

                <li class="sidebar-item <?= in_array($currentPage ?? '', ['admin-drivers', 'admin-driver-show', 'admin-driver-create', 'admin-driver-edit'], true) ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-drivers" class="sidebar-link">
                        <i class="bi bi-bus-front-fill"></i>
                        <span>Chauffeurs de bus</span>
                    </a>
                </li>

                <li class="sidebar-item <?= in_array($currentPage ?? '', ['admin-buses', 'admin-bus-show', 'admin-bus-create', 'admin-bus-edit'], true) ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-buses" class="sidebar-link">
                        <i class="bi bi-truck-front-fill"></i>
                        <span>Bus</span>
                    </a>
                </li>

                <li class="sidebar-item <?= in_array($currentPage ?? '', ['admin-trajets', 'admin-trajet-show', 'admin-trajet-create', 'admin-trajet-edit'], true) ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-trajets" class="sidebar-link">
                        <i class="bi bi-signpost-split-fill"></i>
                        <span>Trajets &amp; stations</span>
                    </a>
                </li>

                <?php if (!empty($isSuperAdmin)): ?>
                <!-- Location, Train, Louage : visibles seulement par le super admin (adminBus ne gère que le Bus) -->
                <li class="sidebar-title">Location</li>

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-location' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-location" class="sidebar-link">
                        <i class="bi bi-bicycle"></i>
                        <span>Flotte &amp; locations</span>
                    </a>
                </li>

                <li class="sidebar-title">Train</li>

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-train' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-train" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Tableau de bord train</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-train-trains' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-train-trains" class="sidebar-link">
                        <i class="bi bi-train-front-fill"></i>
                        <span>Trains</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-train-lignes' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-train-lignes" class="sidebar-link">
                        <i class="bi bi-diagram-3-fill"></i>
                        <span>Lignes</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-train-horaires' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-train-horaires" class="sidebar-link">
                        <i class="bi bi-clock-fill"></i>
                        <span>Horaires</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-train-alertes' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-train-alertes" class="sidebar-link">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Alertes</span>
                    </a>
                </li>

                <li class="sidebar-title">Louage</li>

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Tableau de bord louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-vehicules' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-vehicules" class="sidebar-link">
                        <i class="bi bi-truck"></i>
                        <span>Louages</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-chauffeurs' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-chauffeurs" class="sidebar-link">
                        <i class="bi bi-person-badge"></i>
                        <span>Chauffeurs louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-stations' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-stations" class="sidebar-link">
                        <i class="bi bi-signpost-2"></i>
                        <span>Stations louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-trajets' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-trajets" class="sidebar-link">
                        <i class="bi bi-map"></i>
                        <span>Trajets louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-horaires' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-horaires" class="sidebar-link">
                        <i class="bi bi-clock-history"></i>
                        <span>Horaires louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-reservations' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-reservations" class="sidebar-link">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Réservations louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-prix' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-prix" class="sidebar-link">
                        <i class="bi bi-cash-coin"></i>
                        <span>Prix louage</span>
                    </a>
                </li>
                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-louage-reclamations' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-louage-reclamations" class="sidebar-link">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>Réclamations louage</span>
                    </a>
                </li>

                <?php endif; ?>

                <li class="sidebar-title">Mon compte</li>

                <li class="sidebar-item">
                    <a href="<?= BASE_URL ?>index.php?page=dashboard" class="sidebar-link">
                        <i class="bi bi-person-circle"></i>
                        <span>Mon profil</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?= BASE_URL ?>index.php?page=home" class="sidebar-link">
                        <i class="bi bi-arrow-left-circle"></i>
                        <span>Retour au site</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="<?= BASE_URL ?>index.php?page=logout" class="sidebar-link">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                        <span class="text-danger">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
