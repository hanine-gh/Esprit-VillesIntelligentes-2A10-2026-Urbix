<?php
$currentPage = $currentPage ?? '';
$moiNav = utilisateurConnecte();
$groupeActif = function (array $pages) use ($currentPage): string {
    return in_array($currentPage, $pages, true) ? 'current' : '';
};
?>
<!-- Header -->
<header id="header">
    <div class="logo container">
        <div>
            <h1><a href="<?= url('home') ?>" id="logo">Urbix</a></h1>
            <p>Plateforme de mobilité de la ville intelligente</p>
        </div>
    </div>
</header>

<!-- Nav -->
<nav id="nav">
    <ul>
        <li class="<?= navActive('home', $currentPage) ?>"><a href="<?= url('home') ?>">Accueil</a></li>

        <li class="<?= $groupeActif(['covoiturage', 'espace-utilisateur']) ?>">
            <a href="<?= url('covoiturage') ?>">Covoiturage</a>
            <ul>
                <li><a href="<?= url('covoiturage') ?>"><i class="fas fa-search-location" aria-hidden="true"></i> Rechercher un trajet</a></li>
                <li><a href="<?= url('espace-utilisateur') ?>#mes-reservations"><i class="fas fa-ticket-alt" aria-hidden="true"></i> Mes réservations</a></li>
                <li><a href="<?= url('espace-utilisateur') ?>"><i class="fas fa-user-circle" aria-hidden="true"></i> Mon espace</a></li>
            </ul>
        </li>

        <li class="<?= $groupeActif(['module-bus', 'module-bus-demande', 'module-train', 'module-louage', 'module-location', 'module-reparation']) ?>">
            <a href="<?= url('module', ['nom' => 'bus']) ?>">Transports</a>
            <ul>
                <li><a href="<?= url('module', ['nom' => 'bus']) ?>"><i class="fas fa-bus" aria-hidden="true"></i> Bus urbain</a></li>
                <li><a href="<?= url('module', ['nom' => 'bus-demande']) ?>"><i class="fas fa-shuttle-van" aria-hidden="true"></i> Bus à la demande</a></li>
                <li><a href="<?= url('module', ['nom' => 'train']) ?>"><i class="fas fa-train" aria-hidden="true"></i> Métro &amp; Train</a></li>
                <li><a href="<?= url('module', ['nom' => 'louage']) ?>"><i class="fas fa-taxi" aria-hidden="true"></i> Louage</a></li>
                <li><a href="<?= url('module', ['nom' => 'location']) ?>"><i class="fas fa-car" aria-hidden="true"></i> Location</a></li>
                <li><a href="<?= url('module', ['nom' => 'reparation']) ?>"><i class="fas fa-tools" aria-hidden="true"></i> Réparation</a></li>
            </ul>
        </li>

        <li class="<?= $groupeActif(['module-missions', 'module-demandes-passagers', 'module-cas-bus', 'module-station']) ?>">
            <a href="<?= url('module', ['nom' => 'missions']) ?>">Espace Chauffeur</a>
            <ul>
                <li><a href="<?= url('module', ['nom' => 'missions']) ?>"><i class="fas fa-clipboard-list" aria-hidden="true"></i> Missions</a></li>
                <li><a href="<?= url('module', ['nom' => 'demandes-passagers']) ?>"><i class="fas fa-users" aria-hidden="true"></i> Demande de passagers</a></li>
                <li><a href="<?= url('module', ['nom' => 'cas-bus']) ?>"><i class="fas fa-bus-alt" aria-hidden="true"></i> Cas de mon bus</a></li>
                <li><a href="<?= url('module', ['nom' => 'station']) ?>"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Station</a></li>
            </ul>
        </li>

        <li><a href="<?= BACKOFFICE_URL ?>index.php?page=dashboard">Espace Admin</a></li>

        <li class="auth-nav-item">
            <?php if ($moiNav): ?>
                <a href="<?= url('espace-utilisateur') ?>" class="auth-trigger is-connected"
                   aria-label="Mon espace" title="Mon espace (<?= e($moiNav['prenom']) ?>)">
                    <?= e(initiales($moiNav['prenom'], $moiNav['nom'])) ?>
                </a>
            <?php else: ?>
                <a href="#auth-modal" class="auth-trigger" data-auth-open aria-label="Connexion / Inscription" title="Connexion / Inscription">
                    <i class="fas fa-user" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </li>
    </ul>
</nav>
