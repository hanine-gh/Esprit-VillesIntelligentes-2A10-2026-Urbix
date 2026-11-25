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

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-drivers' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-drivers" class="sidebar-link">
                        <i class="bi bi-bus-front-fill"></i>
                        <span>Chauffeurs de bus</span>
                    </a>
                </li>

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-buses' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-buses" class="sidebar-link">
                        <i class="bi bi-truck-front-fill"></i>
                        <span>Bus</span>
                    </a>
                </li>

                <li class="sidebar-item <?= ($currentPage ?? '') === 'admin-trajets' ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>index.php?page=admin-trajets" class="sidebar-link">
                        <i class="bi bi-signpost-split-fill"></i>
                        <span>Trajets &amp; stations</span>
                    </a>
                </li>

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
