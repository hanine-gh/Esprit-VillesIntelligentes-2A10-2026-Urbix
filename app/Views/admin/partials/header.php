<header class="mb-3 d-flex align-items-center justify-content-between">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>

    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="badge bg-light-danger text-danger">BACKOFFICE</span>
        <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-md bg-primary">
                <span class="avatar-content text-white">
                    <?= e(mb_strtoupper(mb_substr($_SESSION['prenom'] ?? '', 0, 1) . mb_substr($_SESSION['nom'] ?? '', 0, 1))) ?>
                </span>
            </div>
            <div class="text-start">
                <h6 class="mb-0"><?= e(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')) ?></h6>
                <small class="text-muted">Administrateur</small>
            </div>
        </div>
    </div>
</header>
