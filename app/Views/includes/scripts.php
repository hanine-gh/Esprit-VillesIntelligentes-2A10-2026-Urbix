<script src="<?= ASSETS_URL ?>assets/js/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/jquery.dropotron.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/jquery.scrolly.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/browser.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/breakpoints.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/util.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/main.js"></script>
<script>
(function () {
    var modal = document.getElementById('auth-modal');
    if (!modal) return;
    function open(event) {
        if (event) event.preventDefault();
        modal.classList.add('is-open');
    }
    function close(event) {
        if (event) event.preventDefault();
        modal.classList.remove('is-open');
    }
    window.urbixOuvrirAuth = open;
    // Délégation : fonctionne aussi pour les liens du menu mobile (#navPanel) créés dynamiquement.
    document.addEventListener('click', function (event) {
        var cible = event.target.closest ? event.target.closest('[data-auth-open]') : null;
        if (cible) open(event);
    });
    modal.querySelectorAll('.auth-close, .auth-backdrop, .auth-btn-secondary').forEach(function (el) {
        el.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.key === 'Esc') close();
    });
})();
</script>
<script src="<?= ASSETS_URL ?>assets/js/auth.js?v=1"></script>
<?php foreach (($extraJs ?? []) as $script): ?>
<script src="<?= ASSETS_URL . $script ?>?v=1"></script>
<?php endforeach; ?>
