/*
 * Urbix – Interface utilisateur
 * Contrôle de saisie (JavaScript) des formulaires de connexion / inscription
 * + fermeture des messages flash.
 */
(function () {
    'use strict';

    var REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var REGEX_NOM = /^[A-Za-zÀ-ÖØ-öø-ÿ\s'\-]{2,60}$/;
    var REGEX_TEL = /^[0-9 +]{8,15}$/;

    function afficherErreur(champ, message) {
        var bloc = champ.closest('.auth-field');
        var zone = bloc ? bloc.querySelector('.field-error') : null;
        champ.classList.toggle('is-invalid', !!message);
        if (zone) zone.textContent = message || '';
        return !message;
    }

    function valeur(form, nom) {
        var el = form.elements[nom];
        return el ? String(el.value).trim() : '';
    }

    function validerLogin(form) {
        var ok = true;
        ok = afficherErreur(form.elements.login_email,
            REGEX_EMAIL.test(valeur(form, 'login_email')) ? '' : 'Saisissez une adresse email valide.') && ok;
        ok = afficherErreur(form.elements.login_password,
            valeur(form, 'login_password') !== '' ? '' : 'Saisissez votre mot de passe.') && ok;
        return ok;
    }

    function age(dateTexte) {
        var d = new Date(dateTexte);
        if (isNaN(d.getTime())) return -1;
        var now = new Date();
        var a = now.getFullYear() - d.getFullYear();
        var m = now.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < d.getDate())) a--;
        return a;
    }

    function validerInscription(form) {
        var ok = true;
        var mdp = form.elements.password.value;

        ok = afficherErreur(form.elements.last_name,
            REGEX_NOM.test(valeur(form, 'last_name')) ? '' : 'Nom : au moins 2 lettres.') && ok;
        ok = afficherErreur(form.elements.first_name,
            REGEX_NOM.test(valeur(form, 'first_name')) ? '' : 'Prénom : au moins 2 lettres.') && ok;
        ok = afficherErreur(form.elements.email,
            REGEX_EMAIL.test(valeur(form, 'email')) ? '' : 'Adresse email invalide.') && ok;

        var naissance = valeur(form, 'birthdate');
        ok = afficherErreur(form.elements.birthdate,
            naissance === '' || (age(naissance) >= 16 && age(naissance) <= 110) ? '' : 'Vous devez avoir au moins 16 ans.') && ok;

        ok = afficherErreur(form.elements.phone,
            REGEX_TEL.test(valeur(form, 'phone')) ? '' : 'Téléphone : 8 chiffres minimum.') && ok;
        ok = afficherErreur(form.elements.role,
            valeur(form, 'role') !== '' ? '' : 'Choisissez un type de compte.') && ok;

        var mdpOk = mdp.length >= 8 && /\d/.test(mdp) && /[A-Za-zÀ-ÿ]/.test(mdp);
        ok = afficherErreur(form.elements.password,
            mdpOk ? '' : '8 caractères min., une lettre et un chiffre.') && ok;
        ok = afficherErreur(form.elements.password_confirm,
            form.elements.password_confirm.value === mdp && mdp !== '' ? '' : 'Les mots de passe ne correspondent pas.') && ok;
        return ok;
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        var type = form.getAttribute('data-validate');
        form.addEventListener('submit', function (event) {
            var ok = type === 'login' ? validerLogin(form) : validerInscription(form);
            if (!ok) {
                event.preventDefault();
                var premier = form.querySelector('.is-invalid');
                if (premier) premier.focus();
            }
        });
        // Efface l'erreur dès que l'utilisateur corrige le champ.
        form.addEventListener('input', function (event) {
            if (event.target.classList.contains('is-invalid')) {
                afficherErreur(event.target, '');
            }
        });
    });

    // Messages flash : fermeture manuelle + disparition automatique.
    document.querySelectorAll('.flash-toast').forEach(function (toast) {
        var fermer = function () {
            toast.classList.add('is-hidden');
            window.setTimeout(function () { toast.remove(); }, 400);
        };
        var bouton = toast.querySelector('.flash-close');
        if (bouton) bouton.addEventListener('click', fermer);
        window.setTimeout(fermer, 7000);
    });
})();
