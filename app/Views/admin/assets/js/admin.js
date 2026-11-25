/**
 * Backoffice : demande une confirmation avant d'envoyer un formulaire sensible
 * (ex : suppression d'un chauffeur). Fichier externe pour respecter le
 * Content-Security-Policy (pas de onclick="..." dans le HTML).
 */
(function () {
    'use strict';
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });
})();
