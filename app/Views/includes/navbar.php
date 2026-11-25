<?php $currentPage = $currentPage ?? ''; ?>
<!-- Header -->
<header id="header">
    <div class="logo container">
        <div>
            <h1><a href="<?= BASE_URL ?>index.php?page=home" id="logo">Smart City</a></h1>
            <p>Plateforme de gestion de la ville intelligente</p>
        </div>
    </div>
</header>

<!-- Nav -->
<nav id="nav">
    <ul>
        <li class="<?= navActive('home', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=home">Home</a></li>
        <?php if ($isLoggedIn && $currentTypeService === 'utilisateur_normal' && $currentRole !== 'admin'): ?>
            <!-- Menu réservé aux voyageurs (utilisateur normal) -->
            <li class="<?= in_array($currentPage, ['trajets-disponibles', 'mes-demandes'], true) ? 'current' : '' ?>">
                <a href="#">Espace voyageur</a>
                <ul>
                    <li><a href="<?= BASE_URL ?>index.php?page=trajets-disponibles"><i class="fas fa-route"></i> Trajets disponibles</a></li>
                    <li><a href="<?= BASE_URL ?>index.php?page=mes-demandes"><i class="fas fa-hand-paper"></i> Mes demandes de STOP</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($isLoggedIn && $currentTypeService === 'chauffeur_bus'): ?>
            <!-- Menu réservé aux chauffeurs de bus -->
            <li class="<?= in_array($currentPage, ['mes-trajets', 'mes-missions', 'mon-bus'], true) ? 'current' : '' ?>">
                <a href="#">Espace chauffeur</a>
                <ul>
                    <li><a href="<?= BASE_URL ?>index.php?page=mes-missions"><i class="fas fa-clipboard-list"></i> Missions</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Demande de passagers</a></li>
                    <li><a href="<?= BASE_URL ?>index.php?page=mon-bus"><i class="fas fa-bus"></i> Cas de mon bus</a></li>
                    <li><a href="<?= BASE_URL ?>index.php?page=mes-trajets"><i class="fas fa-map-marker-alt"></i> Station</a></li>
                </ul>
            </li>
        <?php endif; ?>
        <li class="<?= navActive('left-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=left-sidebar">Left Sidebar</a></li>
        <li class="<?= navActive('right-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=right-sidebar">Right Sidebar</a></li>
        <li class="<?= navActive('no-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=no-sidebar">No Sidebar</a></li>

        <?php if ($isLoggedIn): ?>
            <li class="<?= navActive('dashboard', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=dashboard">Mon espace</a></li>

            <?php if ($currentRole === 'admin'): ?>
                <!-- Lien affiché aux admins uniquement (la vraie protection est requireRole('admin') côté serveur) -->
                <li><a href="<?= BASE_URL ?>index.php?page=admin">Espace Admin</a></li>
            <?php endif; ?>

            <li class="auth-nav-item">
                <a href="<?= BASE_URL ?>index.php?page=logout" class="auth-trigger auth-logout" aria-label="Déconnexion" title="Déconnexion">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="auth-nav-item">
                <a href="#auth-modal" class="auth-trigger" data-auth-open aria-label="Connexion / Inscription" title="Connexion / Inscription">
                    <i class="fas fa-user" aria-hidden="true"></i>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
