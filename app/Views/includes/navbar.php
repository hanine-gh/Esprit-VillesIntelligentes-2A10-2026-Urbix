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
        <!-- Gestion des bus : même menu que les autres modules ; son contenu dépend du compte connecté -->
        <?php $estChauffeurBus = $isLoggedIn && $currentTypeService === 'chauffeur_bus'; ?>
        <?php $estVoyageur = $isLoggedIn && $currentTypeService === 'utilisateur_normal' && $currentRole !== 'admin'; ?>
        <?php if (!$isLoggedIn || $estVoyageur || $estChauffeurBus || $currentRole === 'admin'): ?>
            <li class="<?= in_array($currentPage, ['trajets-disponibles', 'demande-stop', 'mes-demandes', 'mes-trajets', 'mes-missions', 'mon-bus'], true) ? 'current' : '' ?>">
                <a href="<?= BASE_URL ?>index.php?page=<?= $estChauffeurBus ? 'mes-missions' : ($currentRole === 'admin' ? 'admin-buses' : 'trajets-disponibles') ?>">Bus</a>
                <ul>
                    <?php if ($estChauffeurBus): ?>
                        <!-- Espace chauffeur de bus -->
                        <li><a href="<?= BASE_URL ?>index.php?page=mes-missions"><i class="fas fa-clipboard-list"></i> Missions</a></li>
                        <li><a href="#"><i class="fas fa-users"></i> Demande de passagers</a></li>
                        <li><a href="<?= BASE_URL ?>index.php?page=mon-bus"><i class="fas fa-bus"></i> Cas de mon bus</a></li>
                        <li><a href="<?= BASE_URL ?>index.php?page=mes-trajets"><i class="fas fa-map-marker-alt"></i> Station</a></li>
                    <?php elseif ($currentRole === 'admin'): ?>
                        <!-- Administrateur : gestion des bus dans le backoffice -->
                        <li><a href="<?= BASE_URL ?>index.php?page=admin-drivers"><i class="fas fa-id-badge"></i> Chauffeurs de bus</a></li>
                        <li><a href="<?= BASE_URL ?>index.php?page=admin-buses"><i class="fas fa-bus"></i> Bus</a></li>
                        <li><a href="<?= BASE_URL ?>index.php?page=admin-trajets"><i class="fas fa-route"></i> Trajets &amp; stations</a></li>
                    <?php else: ?>
                        <!-- Espace voyageur (un visiteur non connecté voit la popup de connexion en cliquant) -->
                        <li><a href="<?= BASE_URL ?>index.php?page=trajets-disponibles"><i class="fas fa-route"></i> Trajets disponibles</a></li>
                        <li><a href="<?= BASE_URL ?>index.php?page=mes-demandes"><i class="fas fa-hand-paper"></i> Mes demandes de STOP</a></li>
                        <?php if (!$isLoggedIn): ?>
                            <li><a href="<?= BASE_URL ?>index.php?page=mes-missions"><i class="fas fa-clipboard-list"></i> Espace chauffeur</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>

        <!-- Modules intégrés : consultation libre, actions réservées aux utilisateurs connectés -->
        <li class="<?= str_starts_with($currentPage, 'location') ? 'current' : '' ?>">
            <a href="<?= BASE_URL ?>index.php?page=location">Location</a>
            <ul>
                <li><a href="<?= BASE_URL ?>index.php?page=location"><i class="fas fa-motorcycle"></i> Tous les véhicules</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=location-reserver"><i class="fas fa-calendar-check"></i> Réserver</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=location-demarrer"><i class="fas fa-unlock"></i> Déverrouiller / Démarrer</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=location-prolonger"><i class="fas fa-clock"></i> Prolonger</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=location-incident"><i class="fas fa-exclamation-triangle"></i> Signaler un incident</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=location-terminer"><i class="fas fa-flag-checkered"></i> Terminer &amp; noter</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=location-annuler"><i class="fas fa-times-circle"></i> Annuler</a></li>
            </ul>
        </li>
        <li class="<?= str_starts_with($currentPage, 'train') ? 'current' : '' ?>">
            <a href="<?= BASE_URL ?>index.php?page=train-disponibles">Train</a>
            <ul>
                <li><a href="<?= BASE_URL ?>index.php?page=train-disponibles"><i class="fas fa-train"></i> Trains disponibles</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=train-suivi"><i class="fas fa-map-marked-alt"></i> Suivi des trains</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=train-horaires"><i class="far fa-clock"></i> Horaires des trains</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=train-alertes"><i class="fas fa-bell"></i> Alertes et perturbations</a></li>
            </ul>
        </li>
        <li class="<?= str_starts_with($currentPage, 'louage') ? 'current' : '' ?>">
            <a href="<?= BASE_URL ?>index.php?page=louage-recherche">Louage</a>
            <ul>
                <li><a href="<?= BASE_URL ?>index.php?page=louage-recherche"><i class="fas fa-search"></i> Search</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=louage-stations"><i class="fas fa-map-marker-alt"></i> Stations</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=louage-suivi"><i class="fas fa-location-arrow"></i> Tracking</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=louage-mes-reservations"><i class="fas fa-ticket-alt"></i> Reservations</a></li>
            </ul>
        </li>
        <li class="<?= str_starts_with($currentPage, 'covoiturage') ? 'current' : '' ?>">
            <a href="<?= BASE_URL ?>index.php?page=covoiturage">Covoiturage</a>
            <ul>
                <li><a href="<?= BASE_URL ?>index.php?page=covoiturage"><i class="fas fa-car-side"></i> Rechercher un trajet</a></li>
                <li><a href="<?= BASE_URL ?>index.php?page=covoiturage-reservations"><i class="fas fa-ticket-alt"></i> Mes réservations</a></li>
            </ul>
        </li>
        <li class="<?= navActive('left-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=left-sidebar">Left Sidebar</a></li>

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
