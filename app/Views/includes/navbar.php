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
        <li>
            <a href="#">Dropdown</a>
            <ul>
                <li><a href="#">Lorem ipsum dolor</a></li>
                <li><a href="#">Magna phasellus</a></li>
                <li>
                    <a href="#">Phasellus consequat</a>
                    <ul>
                        <li><a href="#">Lorem ipsum dolor</a></li>
                        <li><a href="#">Phasellus consequat</a></li>
                        <li><a href="#">Magna phasellus</a></li>
                        <li><a href="#">Etiam dolore nisl</a></li>
                    </ul>
                </li>
                <li><a href="#">Veroeros feugiat</a></li>
            </ul>
        </li>
        <li class="<?= navActive('left-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=left-sidebar">Left Sidebar</a></li>
        <li class="<?= navActive('right-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=right-sidebar">Right Sidebar</a></li>
        <li class="<?= navActive('no-sidebar', $currentPage) ?>"><a href="<?= BASE_URL ?>index.php?page=no-sidebar">No Sidebar</a></li>
        <li><a href="<?= BACKOFFICE_URL ?>index.php?page=dashboard">Espace Admin</a></li>
        <li class="auth-nav-item">
            <a href="#auth-modal" class="auth-trigger" data-auth-open aria-label="Connexion / Inscription" title="Connexion / Inscription">
                <i class="fas fa-user" aria-hidden="true"></i>
            </a>
        </li>
    </ul>
</nav>