/*
 * Urbix – Module Covoiturage
 *  - contrôle de saisie du formulaire de recherche
 *  - tri / filtres des propositions de conducteurs
 *  - fenêtre de confirmation "Accepter le trajet" (calcul du prix total)
 *  - compte à rebours des réservations dans l'espace utilisateur
 */
(function () {
    'use strict';

    var REGEX_VILLE = /^[A-Za-zÀ-ÖØ-öø-ÿ\s'\-]{2,60}$/;

    function formatDT(montant) {
        return montant.toFixed(3).replace('.', ',') + ' DT';
    }

    function normaliser(texte) {
        return String(texte).trim().toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    /* ---------------------------------------------------------------
     * 1. Formulaire de recherche
     * ------------------------------------------------------------- */
    var form = document.getElementById('form-recherche');
    if (form) {
        var depart = form.elements.depart;
        var destination = form.elements.destination;
        var date = form.elements.date;
        var heure = form.elements.heure;
        var prixMax = form.elements.prix_max;

        var erreur = function (champ, message) {
            var bloc = champ.closest('.champ');
            if (!bloc) return !message;
            bloc.classList.toggle('has-error', !!message);
            var zone = bloc.querySelector('.field-error');
            if (zone) zone.textContent = message || '';
            return !message;
        };

        document.getElementById('btn-inverser').addEventListener('click', function () {
            var tmp = depart.value;
            depart.value = destination.value;
            destination.value = tmp;
            this.classList.add('is-turning');
            var bouton = this;
            window.setTimeout(function () { bouton.classList.remove('is-turning'); }, 400);
        });

        form.querySelectorAll('.raccourcis button').forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                date.value = bouton.getAttribute('data-date');
                erreur(date, '');
            });
        });

        form.addEventListener('submit', function (event) {
            var ok = true;
            var vDest = destination.value.trim();
            var vDep = depart.value.trim();

            if (vDest === '') {
                ok = erreur(destination, 'Veuillez saisir une destination.') && ok;
            } else if (!REGEX_VILLE.test(vDest)) {
                ok = erreur(destination, 'Uniquement des lettres (2 caractères minimum).') && ok;
            } else if (vDep !== '' && normaliser(vDep) === normaliser(vDest)) {
                ok = erreur(destination, 'La destination doit être différente du départ.') && ok;
            } else {
                erreur(destination, '');
            }

            ok = erreur(depart, vDep === '' || REGEX_VILLE.test(vDep) ? '' : 'Uniquement des lettres.') && ok;

            if (date.value !== '') {
                var aujourdhui = new Date();
                aujourdhui.setHours(0, 0, 0, 0);
                var choisie = new Date(date.value + 'T00:00:00');
                ok = erreur(date, isNaN(choisie.getTime()) ? 'Date invalide.'
                    : (choisie < aujourdhui ? 'La date ne peut pas être dans le passé.' : '')) && ok;
            } else {
                erreur(date, '');
            }

            ok = erreur(heure, heure.value === '' || /^([01]\d|2[0-3]):[0-5]\d$/.test(heure.value) ? '' : 'Heure invalide.') && ok;

            var vPrix = prixMax.value.trim().replace(',', '.');
            ok = erreur(prixMax, vPrix === '' || (/^\d+(\.\d{1,3})?$/.test(vPrix) && parseFloat(vPrix) >= 0)
                ? '' : 'Saisissez un montant valide (ex. : 15 ou 12,5).') && ok;
            if (ok) prixMax.value = vPrix;

            if (!ok) {
                event.preventDefault();
                var premier = form.querySelector('.has-error input, .has-error select');
                if (premier) premier.focus();
            }
        });

        form.addEventListener('input', function (event) {
            var bloc = event.target.closest('.champ');
            if (bloc && bloc.classList.contains('has-error')) erreur(event.target, '');
        });
    }

    /* ---------------------------------------------------------------
     * 2. Tri, filtres et "Ignorer"
     * ------------------------------------------------------------- */
    var liste = document.getElementById('liste-trajets');
    if (liste) {
        var cartes = Array.prototype.slice.call(liste.querySelectorAll('.covoit-card'));
        var compteur = document.getElementById('nb-resultats');
        var videFiltre = document.getElementById('vide-filtre');
        var filtreClim = document.getElementById('filtre-clim');
        var filtreNonFumeur = document.getElementById('filtre-nonfumeur');

        var majAffichage = function () {
            var visibles = 0;
            cartes.forEach(function (carte) {
                var cache = carte.classList.contains('is-ignored')
                    || (filtreClim && filtreClim.checked && carte.dataset.clim !== '1')
                    || (filtreNonFumeur && filtreNonFumeur.checked && carte.dataset.fumeur === '1');
                carte.hidden = cache;
                if (!cache) visibles++;
            });
            if (compteur) compteur.textContent = visibles;
            if (videFiltre) videFiltre.hidden = visibles !== 0;
        };

        document.querySelectorAll('[data-tri]').forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                var critere = bouton.getAttribute('data-tri');
                var decroissant = critere === 'score' || critere === 'note';
                cartes.sort(function (a, b) {
                    var va = parseFloat(a.dataset[critere]) || 0;
                    var vb = parseFloat(b.dataset[critere]) || 0;
                    return decroissant ? vb - va : va - vb;
                });
                cartes.forEach(function (carte) { liste.appendChild(carte); });
                document.querySelectorAll('[data-tri]').forEach(function (b) { b.classList.remove('is-active'); });
                bouton.classList.add('is-active');
            });
        });

        [filtreClim, filtreNonFumeur].forEach(function (filtre) {
            if (filtre) filtre.addEventListener('change', majAffichage);
        });

        liste.addEventListener('click', function (event) {
            var bouton = event.target.closest('.js-ignorer');
            if (!bouton) return;
            var carte = bouton.closest('.covoit-card');
            carte.classList.add('is-leaving');
            window.setTimeout(function () {
                carte.classList.add('is-ignored');
                majAffichage();
            }, 300);
        });

        var resultats = document.getElementById('resultats');
        if (resultats && window.location.search.indexOf('destination=') !== -1) {
            window.setTimeout(function () {
                var nav = document.getElementById('nav');
                var decalage = nav ? nav.offsetHeight + 10 : 0;
                window.scrollTo({ top: resultats.getBoundingClientRect().top + window.pageYOffset - decalage, behavior: 'smooth' });
            }, 300);
        }
    }

    /* ---------------------------------------------------------------
     * 3. Fenêtre "Accepter le trajet"
     * ------------------------------------------------------------- */
    var modal = document.getElementById('accept-modal');
    if (modal) {
        var selectPlaces = document.getElementById('acc-places');
        var total = document.getElementById('acc-total');
        var prixPlace = 0;
        var passagersRecherches = form ? parseInt(form.elements.places.value, 10) || 1 : 1;

        var texte = function (id, valeur) {
            document.getElementById(id).textContent = valeur;
        };
        var majTotal = function () {
            total.textContent = formatDT(prixPlace * (parseInt(selectPlaces.value, 10) || 1));
        };
        var fermer = function (event) {
            if (event) event.preventDefault();
            modal.classList.remove('is-open');
        };

        document.addEventListener('click', function (event) {
            var bouton = event.target.closest('.js-accepter');
            if (!bouton) return;
            var t;
            try {
                t = JSON.parse(bouton.getAttribute('data-trajet'));
            } catch (e) {
                return;
            }
            prixPlace = Number(t.prix) || 0;
            document.getElementById('acc-id').value = t.id;
            texte('acc-initiales', t.initiales);
            texte('acc-conducteur', t.conducteur);
            texte('acc-vehicule', t.vehicule || 'Véhicule non précisé');
            texte('acc-trajet', t.depart + ' → ' + t.arrivee);
            texte('acc-date', t.date.charAt(0).toUpperCase() + t.date.slice(1) + ' à ' + t.heure);
            texte('acc-rdv', t.rdv ? 'RDV : ' + t.rdv : 'Point de rendez-vous à convenir avec le conducteur');

            selectPlaces.innerHTML = '';
            for (var i = 1; i <= Math.min(t.places, 8); i++) {
                var option = document.createElement('option');
                option.value = i;
                option.textContent = i + ' place' + (i > 1 ? 's' : '');
                if (i === Math.min(passagersRecherches, t.places)) option.selected = true;
                selectPlaces.appendChild(option);
            }
            majTotal();
            modal.classList.add('is-open');
        });

        selectPlaces.addEventListener('change', majTotal);
        modal.querySelectorAll('.js-fermer-accept').forEach(function (el) {
            el.addEventListener('click', fermer);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' || event.key === 'Esc') fermer();
        });

        document.getElementById('form-accepter').addEventListener('submit', function (event) {
            var n = parseInt(selectPlaces.value, 10);
            var zone = selectPlaces.parentNode.querySelector('.field-error');
            if (!document.getElementById('acc-id').value || !(n >= 1)) {
                event.preventDefault();
                if (zone) zone.textContent = 'Choisissez le nombre de places.';
            }
        });

        // Visiteur non connecté : on ouvre l'interface utilisateur (connexion / inscription).
        var connexion = modal.querySelector('.js-connexion-requise');
        if (connexion) {
            connexion.addEventListener('click', function () {
                fermer();
                if (typeof window.urbixOuvrirAuth === 'function') window.urbixOuvrirAuth();
            });
        }
    }

    /* ---------------------------------------------------------------
     * 4. Espace utilisateur : compte à rebours + confirmation d'annulation
     * ------------------------------------------------------------- */
    var rebours = document.querySelectorAll('[data-depart-ts]');
    if (rebours.length) {
        var maj = function () {
            var maintenant = Date.now() / 1000;
            rebours.forEach(function (el) {
                var reste = parseInt(el.getAttribute('data-depart-ts'), 10) - maintenant;
                if (reste <= 0) {
                    el.textContent = 'Trajet effectué';
                    return;
                }
                var j = Math.floor(reste / 86400);
                var h = Math.floor((reste % 86400) / 3600);
                var m = Math.floor((reste % 3600) / 60);
                el.textContent = 'Départ dans ' + (j > 0 ? j + ' j ' : '') + h + ' h ' + (m < 10 ? '0' : '') + m + ' min';
            });
        };
        maj();
        window.setInterval(maj, 30000);
    }

    document.querySelectorAll('form.js-confirmer').forEach(function (f) {
        f.addEventListener('submit', function (event) {
            if (!window.confirm(f.getAttribute('data-message') || 'Confirmer ?')) event.preventDefault();
        });
    });
})();
