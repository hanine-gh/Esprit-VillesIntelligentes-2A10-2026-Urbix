/**
 * Contrôle de saisie JavaScript des formulaires des modules intégrés
 * (location, train, louage, covoiturage), frontoffice et backoffice.
 * Les règles sont écrites sur chaque champ avec des attributs data-v-* ;
 * le serveur (PHP) revérifie toujours tout. Même affichage des erreurs que
 * le reste du site (SmartValidation.attach de auth-validation.js).
 *
 *   data-v-required="message"        champ obligatoire
 *   data-v-int="min,max"             entier entre min et max
 *   data-v-number="min,max"          nombre (décimal) entre min et max
 *   data-v-pattern="regex"           expression régulière (avec data-v-msg)
 *   data-v-maxlength="n"             longueur maximale
 *   data-v-date                      date AAAA-MM-JJ valide
 *   data-v-time                      heure HH:MM valide
 *   data-v-after="autreChamp"        valeur strictement après celle d'un autre champ (heures, dates)
 *   data-v-different="autreChamp"    valeur différente d'un autre champ
 *   data-v-msg="message"             message d'erreur des règles ci-dessus
 */
(function () {
    'use strict';
    if (!window.SmartValidation) return;

    function validate(form) {
        var errors = {};

        Array.prototype.forEach.call(form.elements, function (input) {
            if (!input.name || input.type === 'hidden' || errors[input.name]) return;
            var v = (input.value || '').trim();
            var msg = input.getAttribute('data-v-msg') || 'Valeur invalide.';
            var req = input.getAttribute('data-v-required');

            if (v === '') {
                if (req !== null) errors[input.name] = req || 'Ce champ est obligatoire.';
                return; // champ facultatif vide : rien d'autre à vérifier
            }

            var range = input.getAttribute('data-v-int') || input.getAttribute('data-v-number');
            if (range !== null) {
                var isInt = input.hasAttribute('data-v-int');
                var parts = range.split(',');
                var n = Number(v.replace(',', '.'));
                var okType = isInt ? /^-?\d+$/.test(v) : !isNaN(n);
                if (!okType || n < Number(parts[0]) || n > Number(parts[1])) {
                    errors[input.name] = msg;
                    return;
                }
            }

            var pattern = input.getAttribute('data-v-pattern');
            if (pattern && !new RegExp(pattern, 'u').test(v)) {
                errors[input.name] = msg;
                return;
            }

            var maxLen = input.getAttribute('data-v-maxlength');
            if (maxLen && v.length > Number(maxLen)) {
                errors[input.name] = msg;
                return;
            }

            if (input.hasAttribute('data-v-date') && !/^\d{4}-\d{2}-\d{2}$/.test(v)) {
                errors[input.name] = msg;
                return;
            }

            if (input.hasAttribute('data-v-time') && !/^([01]\d|2[0-3]):[0-5]\d$/.test(v)) {
                errors[input.name] = msg;
                return;
            }

            var after = input.getAttribute('data-v-after');
            if (after && form.elements[after] && form.elements[after].value !== '' && v <= form.elements[after].value) {
                errors[input.name] = input.getAttribute('data-v-after-msg') || msg;
                return;
            }

            var diff = input.getAttribute('data-v-different');
            if (diff && form.elements[diff] && v === form.elements[diff].value.trim()) {
                errors[input.name] = input.getAttribute('data-v-different-msg') || msg;
            }
        });

        return errors;
    }

    document.querySelectorAll('form[data-validate="modules"]').forEach(function (form) {
        window.SmartValidation.attach(form, validate);
    });
})();
