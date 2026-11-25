/**
 * Contrôle de saisie JavaScript des formulaires "trajet" et "station" (backoffice).
 * Mêmes règles que TrajetController / StationController en PHP.
 * Utilise SmartValidation.attach (auth-validation.js) : même affichage des erreurs partout.
 * Les règles qui demandent la base de données (code unique, chevauchement d'horaires du
 * chauffeur, ordre déjà pris...) sont vérifiées uniquement côté serveur (PHP).
 */
(function () {
    'use strict';
    if (!window.SmartValidation) return;

    var CODE_REGEX    = /^[A-Z0-9][A-Z0-9\-]{0,19}$/;
    var NAME_REGEX    = /^[\p{L}0-9][\p{L}0-9 '\-.,]{2,99}$/u;      // 3 à 100
    var PLACE_REGEX   = /^[\p{L}0-9][\p{L}0-9 '\-.,]{1,99}$/u;      // 2 à 100
    var ADDRESS_REGEX = /^[\p{L}0-9][\p{L}0-9 '\-.,°\/]{4,254}$/u;  // 5 à 255
    var TIME_REGEX    = /^([01]\d|2[0-3]):[0-5]\d$/;

    function val(form, name) {
        return form.elements[name] ? form.elements[name].value.trim() : '';
    }

    // ---------- Trajet ----------
    function validateTrajet(form) {
        var errors = {};
        var code = val(form, 'code').toUpperCase();
        form.elements.code.value = code; // le code est toujours en majuscules

        if (!CODE_REGEX.test(code)) errors.code = '1 à 20 caractères : lettres, chiffres ou tirets (ex : L12).';
        if (!NAME_REGEX.test(val(form, 'nom'))) errors.nom = 'Le nom doit contenir 3 à 100 caractères.';

        var dep = val(form, 'point_depart');
        var arr = val(form, 'point_arrivee');
        if (!PLACE_REGEX.test(dep)) errors.point_depart = 'Le point de départ doit contenir 2 à 100 caractères.';
        if (!PLACE_REGEX.test(arr)) errors.point_arrivee = "Le point d'arrivée doit contenir 2 à 100 caractères.";
        else if (dep !== '' && dep.toLowerCase() === arr.toLowerCase()) {
            errors.point_arrivee = "Le point d'arrivée doit être différent du départ.";
        }

        var hDep = val(form, 'heure_depart');
        var hArr = val(form, 'heure_arrivee');
        if (!TIME_REGEX.test(hDep)) errors.heure_depart = 'Heure de départ invalide.';
        if (!TIME_REGEX.test(hArr)) errors.heure_arrivee = "Heure d'arrivée invalide.";
        else if (TIME_REGEX.test(hDep) && hArr <= hDep) errors.heure_arrivee = "L'arrivée doit être après le départ.";

        if (val(form, 'statut') === '') errors.statut = 'Veuillez choisir un statut.';
        // chauffeur_id : facultatif (vide = non attribué)

        return errors;
    }

    // ---------- Station ----------
    function validateStation(form) {
        var errors = {};
        var min = form.getAttribute('data-min');
        var max = form.getAttribute('data-max');

        if (!PLACE_REGEX.test(val(form, 'nom'))) errors.nom = 'Le nom doit contenir 2 à 100 caractères.';
        if (!ADDRESS_REGEX.test(val(form, 'adresse'))) errors.adresse = "L'adresse doit contenir 5 à 255 caractères.";

        var ordre = val(form, 'ordre');
        if (!/^\d+$/.test(ordre) || +ordre < 1 || +ordre > 99) errors.ordre = 'Nombre entier entre 1 et 99.';

        var h = val(form, 'heure_passage');
        if (!TIME_REGEX.test(h)) errors.heure_passage = 'Heure de passage invalide.';
        else if (h < min || h > max) errors.heure_passage = 'Doit être entre ' + min + ' et ' + max + '.';

        return errors;
    }

    // ---------- Bus ----------
    var MATRICULE_REGEX = /^[A-Z0-9][A-Z0-9 \-]{2,19}$/;
    var BUS_TEXT_REGEX  = /^[\p{L}0-9][\p{L}0-9 '\-.]{1,49}$/u;   // 2 à 50

    function validateBus(form) {
        var errors = {};
        var maxYear = parseInt(form.getAttribute('data-max-year'), 10);

        var matricule = val(form, 'matricule').toUpperCase().replace(/\s+/g, ' ');
        form.elements.matricule.value = matricule; // toujours en majuscules
        if (!MATRICULE_REGEX.test(matricule)) errors.matricule = '3 à 20 caractères : majuscules, chiffres, espaces ou tirets (ex : 123 TU 4567).';

        if (!BUS_TEXT_REGEX.test(val(form, 'marque'))) errors.marque = 'La marque doit contenir 2 à 50 caractères.';
        if (!BUS_TEXT_REGEX.test(val(form, 'modele'))) errors.modele = 'Le modèle doit contenir 2 à 50 caractères.';

        var cap = val(form, 'capacite');
        if (!/^\d+$/.test(cap) || +cap < 10 || +cap > 150) errors.capacite = 'Nombre entier entre 10 et 150.';

        var annee = val(form, 'annee');
        if (!/^\d+$/.test(annee) || +annee < 1990 || +annee > maxYear) errors.annee = 'Année entre 1990 et ' + maxYear + '.';

        if (val(form, 'statut') === '') errors.statut = 'Veuillez choisir un statut.';
        // chauffeur_id : facultatif (vide = aucun chauffeur attitré)

        return errors;
    }

    // ---------- Cas de mon bus (espace chauffeur) ----------
    function validateBusCase(form) {
        var errors = {};
        var statut = val(form, 'statut');
        var texte  = val(form, 'etat_commentaire');

        if (statut === '') errors.statut = "Veuillez choisir l'état du bus.";
        if (statut !== '' && statut !== 'disponible' && texte === '') {
            errors.etat_commentaire = 'Décrivez le problème (obligatoire si le bus n\'est pas disponible).';
        } else if (texte !== '' && (texte.length < 5 || texte.length > 255)) {
            errors.etat_commentaire = 'Entre 5 et 255 caractères.';
        } else if (texte !== '' && !/\p{L}/u.test(texte)) {
            errors.etat_commentaire = 'La description doit contenir du texte.';
        }
        return errors;
    }

    // ---------- Demande de STOP (espace voyageur) ----------
    function validateStop(form) {
        var errors = {};
        var min = form.getAttribute('data-min-date');
        var max = form.getAttribute('data-max-date');

        if (val(form, 'station_id') === '') errors.station_id = 'Veuillez choisir une station.';

        var date = val(form, 'date_trajet');
        if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || date < min || date > max) {
            errors.date_trajet = 'Date entre aujourd\'hui et dans 30 jours.';
        }

        var nb = val(form, 'nb_personnes');
        if (!/^\d+$/.test(nb) || +nb < 1 || +nb > 10) errors.nb_personnes = 'Nombre entier entre 1 et 10.';

        var msg = val(form, 'message');
        if (msg !== '' && (msg.length < 5 || msg.length > 255)) errors.message = 'Entre 5 et 255 caractères (ou laissez vide).';

        return errors;
    }

    SmartValidation.attach(document.getElementById('stop-form'), validateStop);
    SmartValidation.attach(document.getElementById('bus-case-form'), validateBusCase);
    SmartValidation.attach(document.getElementById('bus-form'), validateBus);
    SmartValidation.attach(document.getElementById('trajet-form'), validateTrajet);
    SmartValidation.attach(document.getElementById('station-form'), validateStation);
})();
