/**
 * Contrôle de saisie de la popup (connexion / inscription) en JavaScript.
 * Mêmes règles que AuthController::register() / login() en PHP.
 * Le contrôle JS améliore l'expérience, mais la vraie sécurité reste
 * la vérification côté serveur (PHP), car le JS peut être désactivé ou contourné.
 */
(function () {
    'use strict';

    var NAME_REGEX  = /^\p{L}[\p{L} '\-]{1,49}$/u;
    var EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var PHONE_REGEX = /^\+?[0-9]{8,15}$/;

    // ---------- Affichage des erreurs sous chaque champ ----------
    function showError(input, message) {
        clearError(input);
        input.classList.add('is-invalid');
        var span = document.createElement('small');
        span.className = 'auth-field-error invalid-feedback d-block'; // + style Bootstrap (backoffice)
        span.textContent = message;
        input.parentNode.appendChild(span);
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        var old = input.parentNode.querySelector('.auth-field-error');
        if (old) old.remove();
    }

    function value(form, name) {
        var field = form.elements[name];
        return field ? field.value.trim() : '';
    }

    // ---------- Règles de validation ----------
    function validateLogin(form) {
        var errors = {};
        var email = value(form, 'email');

        if (email === '') errors.email = 'Veuillez saisir votre email.';
        else if (!EMAIL_REGEX.test(email)) errors.email = 'Adresse email invalide.';

        if (form.elements.password.value === '') errors.password = 'Veuillez saisir votre mot de passe.';

        return errors;
    }

    // Données personnelles : mêmes règles à la création et à la modification
    function validatePersonal(form) {
        var errors = {};

        if (!NAME_REGEX.test(value(form, 'nom'))) {
            errors.nom = 'Le nom doit contenir 2 à 50 lettres.';
        }
        if (!NAME_REGEX.test(value(form, 'prenom'))) {
            errors.prenom = 'Le prénom doit contenir 2 à 50 lettres.';
        }

        var email = value(form, 'email');
        if (email === '') errors.email = "L'email est obligatoire.";
        else if (!EMAIL_REGEX.test(email) || email.length > 150) errors.email = 'Adresse email invalide.';

        var date = value(form, 'date_naissance');
        if (date === '') {
            errors.date_naissance = 'La date de naissance est obligatoire.';
        } else {
            var d = new Date(date + 'T00:00:00');
            var today = new Date(); today.setHours(0, 0, 0, 0);
            if (isNaN(d.getTime()) || d > today || d < new Date('1900-01-01T00:00:00')) {
                errors.date_naissance = 'Date de naissance invalide.';
            }
        }

        if (value(form, 'genre') === '') errors.genre = 'Veuillez choisir un genre.';

        var phone = value(form, 'telephone').replace(/[\s.\-]/g, '');
        if (phone === '') errors.telephone = 'Le téléphone est obligatoire.';
        else if (!PHONE_REGEX.test(phone)) errors.telephone = 'Numéro invalide (8 à 15 chiffres).';

        // Le type de compte n'existe pas dans le formulaire admin (imposé : chauffeur bus)
        if (form.elements.type_service && value(form, 'type_service') === '') {
            errors.type_service = 'Veuillez choisir un type de compte.';
        }

        return errors;
    }

    function validatePasswordFields(form, errors) {
        var password = form.elements.password.value;
        var confirm  = form.elements.password_confirm.value;
        if (password.length < 8) errors.password = 'Au moins 8 caractères.';
        if (confirm === '') errors.password_confirm = 'Veuillez confirmer le mot de passe.';
        else if (password !== confirm) errors.password_confirm = 'Les mots de passe ne correspondent pas.';
        return errors;
    }

    // Inscription : données personnelles + mot de passe obligatoire
    function validateRegister(form) {
        return validatePasswordFields(form, validatePersonal(form));
    }

    // Modification du profil : données personnelles + mot de passe facultatif
    function validateProfile(form) {
        var errors = validatePersonal(form);
        var wantsChange = form.elements.password.value !== '' || form.elements.password_confirm.value !== '';
        if (wantsChange) {
            if (form.elements.current_password.value === '') {
                errors.current_password = 'Saisissez votre mot de passe actuel.';
            }
            validatePasswordFields(form, errors);
        }
        return errors;
    }

    // ---------- Branchement sur les formulaires ----------
    function attach(form, validate) {
        if (!form) return;

        form.addEventListener('submit', function (event) {
            var errors = validate(form);
            var firstInvalid = null;

            Array.prototype.forEach.call(form.elements, function (input) {
                if (!input.name || input.type === 'hidden') return;
                if (errors[input.name]) {
                    showError(input, errors[input.name]);
                    if (!firstInvalid) firstInvalid = input;
                } else {
                    clearError(input);
                }
            });

            if (firstInvalid) {
                event.preventDefault(); // le formulaire n'est pas envoyé
                firstInvalid.focus();
            }
        });

        // L'erreur d'un champ disparaît dès que l'utilisateur le corrige
        Array.prototype.forEach.call(form.elements, function (input) {
            if (!input.name || input.type === 'hidden') return;
            input.addEventListener(input.tagName === 'SELECT' ? 'change' : 'input', function () {
                clearError(input);
            });
        });
    }

    // Rendu public pour les autres formulaires (trajets, stations) : même affichage des erreurs
    window.SmartValidation = { attach: attach };

    attach(document.getElementById('auth-login-form'), validateLogin);
    attach(document.getElementById('auth-register-form'), validateRegister);
    attach(document.getElementById('profile-form'), validateProfile);

    // Backoffice : ajout (mot de passe obligatoire) / modification (mot de passe facultatif) d'un chauffeur
    var driverForm = document.getElementById('driver-form');
    attach(driverForm, function (form) {
        var errors = validatePersonal(form);
        var isEdit = form.getAttribute('data-mode') === 'edit';
        var wantsChange = form.elements.password.value !== '' || form.elements.password_confirm.value !== '';
        if (!isEdit || wantsChange) {
            validatePasswordFields(form, errors);
        }
        return errors;
    });
})();
