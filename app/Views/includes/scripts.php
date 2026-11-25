<script src="<?= ASSETS_URL ?>assets/js/jquery.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/jquery.dropotron.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/jquery.scrolly.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/browser.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/breakpoints.min.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/util.js"></script>
<script src="<?= ASSETS_URL ?>assets/js/main.js"></script>
<!-- Script de la popup dans un fichier externe : le Content-Security-Policy bloque les scripts écrits dans la page -->
<script src="<?= ASSETS_URL ?>assets/js/auth-modal.js"></script>
<!-- Contrôle de saisie côté client (JavaScript) ; le serveur (PHP) revérifie tout -->
<script src="<?= ASSETS_URL ?>assets/js/auth-validation.js"></script>
<!-- Mêmes règles que le backoffice pour le formulaire "station" (espace chauffeur) -->
<script src="<?= ADMIN_ASSETS_URL ?>js/trajet-validation.js"></script>
<!-- Confirmation avant les actions sensibles (ex : annuler une demande de STOP) -->
<script src="<?= ADMIN_ASSETS_URL ?>js/admin.js"></script>
