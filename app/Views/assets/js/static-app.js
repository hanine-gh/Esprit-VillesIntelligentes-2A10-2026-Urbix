/*
 * Urbix – version HTML statique (index.html), sans PHP.
 * Même logique que la version PHP : recherche intelligente, acceptation d'un trajet,
 * interface utilisateur (connexion / inscription / mon espace) et pages des modules.
 * Les données sont conservées dans le navigateur (localStorage).
 */
(function () {
    'use strict';

    /* ================================================================
     * Outils
     * ============================================================== */
    var CLE_STOCKAGE = 'urbix_static_v1';
    var JOURS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    var MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    var MOIS_COURT = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    var REGEX_VILLE = /^[A-Za-zÀ-ÖØ-öø-ÿ\s'\-]{2,60}$/;
    var ROLES = {
        'user-normal': 'Utilisateur normal', 'chauffeur-bus': 'Chauffeur bus', 'chauffeur-train': 'Chauffeur train',
        'louage': 'Louage', 'covoiturage': 'Covoiturage', 'location': 'Location', 'reparation-vehicule': 'Réparation de véhicule'
    };

    function $(id) { return document.getElementById(id); }

    function e(texte) {
        return String(texte === null || texte === undefined ? '' : texte)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function isoJour(decalage) {
        var d = new Date();
        d.setDate(d.getDate() + (decalage || 0));
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function dateObj(iso, heure) {
        var p = iso.split('-');
        var h = (heure || '00:00').split(':');
        return new Date(+p[0], +p[1] - 1, +p[2], +h[0], +h[1]);
    }

    function dateFr(iso) {
        var d = dateObj(iso);
        return JOURS[d.getDay()] + ' ' + d.getDate() + ' ' + MOIS[d.getMonth()] + ' ' + d.getFullYear();
    }

    function heureFr(h) { return h.slice(0, 2) + 'h' + h.slice(3, 5); }

    function ajouterMinutes(h, minutes) {
        var t = (+h.slice(0, 2)) * 60 + (+h.slice(3, 5)) + minutes;
        t = ((t % 1440) + 1440) % 1440;
        return pad(Math.floor(t / 60)) + ':' + pad(t % 60);
    }

    function prixDT(m) { return Number(m).toFixed(3).replace('.', ',') + ' DT'; }

    function initiales(prenom, nom) { return (prenom.charAt(0) + nom.charAt(0)).toUpperCase(); }

    function normaliser(t) {
        return String(t).trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[-']/g, ' ').replace(/\s+/g, ' ');
    }

    function majuscule(t) { return t.charAt(0).toUpperCase() + t.slice(1); }

    // Empreinte simple du mot de passe (démo locale uniquement : la version PHP utilise password_hash).
    function empreinte(texte) {
        var h = 5381;
        for (var i = 0; i < texte.length; i++) h = ((h << 5) + h + texte.charCodeAt(i)) >>> 0;
        return 'h' + h.toString(16);
    }

    function toast(type, message) {
        var icones = { succes: 'fa-check-circle', erreur: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        var ancien = document.querySelector('.flash-toast');
        if (ancien) ancien.remove();
        var el = document.createElement('div');
        el.className = 'flash-toast flash-' + type;
        el.setAttribute('role', 'status');
        el.innerHTML = '<i class="fas ' + icones[type] + '" aria-hidden="true"></i><span>' + e(message) +
            '</span><button type="button" class="flash-close" aria-label="Fermer">&times;</button>';
        document.body.appendChild(el);
        var fermer = function () {
            el.classList.add('is-hidden');
            window.setTimeout(function () { el.remove(); }, 400);
        };
        el.querySelector('.flash-close').addEventListener('click', fermer);
        window.setTimeout(fermer, 7000);
    }

    /* ================================================================
     * Données (mêmes entités que database/smartcity.sql)
     * ============================================================== */
    function donneesInitiales() {
        var mdp = empreinte('Urbix2026');
        var u = function (id, nom, prenom, email, tel, role) {
            return { id: id, nom: nom, prenom: prenom, email: email, telephone: tel, role: role, mdp: mdp, inscription: isoJour(0) };
        };
        var utilisateurs = [
            u(1, 'Ben Ali', 'Karim', 'karim.benali@urbix.tn', '22 345 678', 'covoiturage'),
            u(2, 'Trabelsi', 'Amira', 'amira.trabelsi@urbix.tn', '55 123 987', 'covoiturage'),
            u(3, 'Gharbi', 'Youssef', 'youssef.gharbi@urbix.tn', '98 456 321', 'covoiturage'),
            u(4, 'Mansouri', 'Ines', 'ines.mansouri@urbix.tn', '20 789 456', 'covoiturage'),
            u(5, 'Jaziri', 'Mehdi', 'mehdi.jaziri@urbix.tn', '27 654 123', 'covoiturage'),
            u(6, 'Missaoui', 'Salma', 'salma@urbix.tn', '50 111 222', 'user-normal')
        ];
        return { jour: isoJour(0), utilisateurs: utilisateurs, trajets: trajetsDuJour(), reservations: [], session: null };
    }

    function trajetsDuJour() {
        var vehicules = {
            1: ['Volkswagen', 'Golf 7', 'Gris', 1], 2: ['Peugeot', '208', 'Blanc', 1], 3: ['Kia', 'Sportage', 'Noir', 1],
            4: ['Renault', 'Clio 5', 'Rouge', 1], 5: ['Hyundai', 'i20', 'Bleu', 0]
        };
        var conducteurs = {
            1: ['Karim', 'Ben Ali', '22 345 678', 4.5, 2], 2: ['Amira', 'Trabelsi', '55 123 987', 5.0, 1],
            3: ['Youssef', 'Gharbi', '98 456 321', 4.0, 1], 4: ['Ines', 'Mansouri', '20 789 456', 5.0, 1],
            5: ['Mehdi', 'Jaziri', '27 654 123', 3.0, 1]
        };
        var bruts = [
            [1, 1, 'Tunis', 'Sousse', 'Station métro Bab Saadoun', 1, '08:00', 120, 15, 4, 3, 'moyen', 0, 0, 'Départ ponctuel, autoroute A1.'],
            [2, 2, 'Tunis', 'Sousse', 'Les Berges du Lac', 1, '10:30', 125, 13, 3, 2, 'petit', 0, 1, 'Musique calme, animaux acceptés.'],
            [3, 3, 'Ariana', 'Sousse', 'Ennasr 2 – Carrefour', 1, '17:15', 130, 17.5, 4, 4, 'grand', 0, 0, 'Grand coffre, idéal avec valises.'],
            [4, 4, 'Tunis', 'Sfax', 'Place Pasteur', 2, '07:00', 210, 25, 3, 3, 'moyen', 0, 0, 'Pause café à El Jem.'],
            [5, 5, 'Tunis', 'Nabeul', 'Gare TGM Tunis Marine', 1, '09:00', 70, 8, 4, 2, 'petit', 1, 0, ''],
            [6, 1, 'Sousse', 'Tunis', 'Sousse Sahloul', 3, '18:00', 120, 15, 4, 4, 'moyen', 0, 0, 'Retour du week-end.'],
            [7, 2, 'Tunis', 'Bizerte', 'Bab Saadoun', 2, '12:00', 65, 7, 3, 3, 'petit', 0, 0, ''],
            [8, 3, 'Tunis', 'Hammamet', 'La Marsa – Zéphyr', 1, '14:00', 60, 9, 4, 3, 'grand', 0, 1, 'Direction plage, bonne ambiance.'],
            [9, 4, 'Sfax', 'Djerba', 'Sfax centre', 4, '06:30', 240, 22, 3, 2, 'moyen', 0, 0, ''],
            [10, 5, 'Tunis', 'Monastir', 'Montplaisir', 2, '16:45', 140, 16, 4, 4, 'moyen', 0, 0, 'Passage par Sousse possible.'],
            [11, 1, 'Tunis', 'Kairouan', 'Bab Alioua', 5, '08:30', 150, 14, 4, 3, 'moyen', 0, 0, ''],
            [12, 2, 'Ariana', 'Tunis', 'Ariana Soghra', 1, '07:45', 25, 3, 3, 3, 'petit', 0, 0, 'Trajet domicile-travail quotidien.']
        ];
        return bruts.map(function (b) {
            var c = conducteurs[b[1]];
            var v = vehicules[b[1]];
            return {
                id: b[0], idConducteur: b[1], depart: b[2], arrivee: b[3], rdv: b[4], date: isoJour(b[5]), heure: b[6],
                duree: b[7], prix: b[8], placesTotal: b[9], places: b[10], bagages: b[11], fumeur: b[12], animaux: b[13],
                description: b[14], statut: 'ouvert', prenom: c[0], nom: c[1], telephone: c[2], note: c[3], nbAvis: c[4],
                marque: v[0], modele: v[1], couleur: v[2], clim: v[3]
            };
        });
    }

    function charger() {
        var db = null;
        try { db = JSON.parse(localStorage.getItem(CLE_STOCKAGE)); } catch (err) { db = null; }
        if (!db || !db.utilisateurs) db = donneesInitiales();
        if (db.jour !== isoJour(0)) {          // nouveaux trajets chaque jour, comptes conservés
            db.jour = isoJour(0);
            db.trajets = trajetsDuJour();
            db.reservations = [];
        }
        return db;
    }

    var db = charger();

    function sauver() {
        try { localStorage.setItem(CLE_STOCKAGE, JSON.stringify(db)); } catch (err) { /* navigation privée */ }
    }

    function moi() {
        if (!db.session) return null;
        for (var i = 0; i < db.utilisateurs.length; i++) if (db.utilisateurs[i].id === db.session) return db.utilisateurs[i];
        return null;
    }

    function trajet(id) {
        for (var i = 0; i < db.trajets.length; i++) if (db.trajets[i].id === id) return db.trajets[i];
        return null;
    }

    function aVenir() {
        var maintenant = new Date();
        return db.trajets.filter(function (t) {
            return t.statut === 'ouvert' && dateObj(t.date, t.heure) >= maintenant;
        }).sort(function (a, b) { return dateObj(a.date, a.heure) - dateObj(b.date, b.heure); });
    }

    /* ================================================================
     * Recherche intelligente (même algorithme que Recommandation.php)
     * ============================================================== */
    function villeCorrespond(ville, recherche) {
        if (recherche === '') return true;
        return normaliser(ville).indexOf(normaliser(recherche)) !== -1;
    }

    function rechercher(c) {
        var u = moi();
        var tous = aVenir().filter(function (t) { return !u || t.idConducteur !== u.id; });
        var base = tous.filter(function (t) {
            return villeCorrespond(t.arrivee, c.destination) && villeCorrespond(t.depart, c.depart)
                && t.places >= c.places && (c.prixMax <= 0 || t.prix <= c.prixMax);
        });
        var type, liste;
        if (c.date === '') {
            type = base.length ? 'exact' : 'aucun';
            liste = base;
        } else {
            var exacts = base.filter(function (t) { return t.date === c.date; });
            if (exacts.length) {
                type = 'exact';
                liste = exacts;
            } else {
                var cible = dateObj(c.date).getTime();
                liste = base.filter(function (t) { return Math.abs(dateObj(t.date).getTime() - cible) <= 3 * 86400000; });
                type = liste.length ? 'dates_proches' : 'aucun';
            }
        }
        if (type === 'aucun') {
            liste = tous.filter(function (t) { return villeCorrespond(t.arrivee, c.destination) && t.places >= c.places; });
            type = liste.length ? 'similaires' : 'aucun';
        }
        return { type: type, trajets: noter(liste, c) };
    }

    function noter(liste, c) {
        if (!liste.length) return [];
        var prix = liste.map(function (t) { return t.prix; });
        var pMin = Math.min.apply(null, prix), pMax = Math.max.apply(null, prix);
        var nMax = Math.max.apply(null, liste.map(function (t) { return t.note || 0; }));
        var cible = null;
        if (/^\d{2}:\d{2}$/.test(c.heure)) cible = (+c.heure.slice(0, 2)) * 60 + (+c.heure.slice(3, 5));
        var ecarts = liste.map(function (t) {
            var m = (+t.heure.slice(0, 2)) * 60 + (+t.heure.slice(3, 5));
            return cible === null ? (dateObj(t.date, t.heure) - Date.now()) / 60000 : Math.abs(m - cible);
        });
        var eMin = Math.min.apply(null, ecarts), eMax = Math.max.apply(null, ecarts);

        var resultat = liste.map(function (t, i) {
            var sPrix = pMax > pMin ? (pMax - t.prix) / (pMax - pMin) : 1;
            var sNote = t.note ? t.note / 5 : 0.6;
            var sHoraire = eMax > eMin ? (eMax - ecarts[i]) / (eMax - eMin) : 1;
            var sConfort = (t.clim ? 0.5 : 0) + Math.min(t.places, 4) / 8;
            var badges = [];
            if (liste.length > 1 && t.prix === pMin) badges.push('Le moins cher');
            if (t.note && t.note === nMax && nMax >= 4) badges.push('Conducteur le mieux noté');
            if (cible !== null && ecarts[i] === eMin) badges.push('Horaire le plus proche');
            return Object.assign({}, t, {
                score: Math.round(100 * (0.35 * sPrix + 0.30 * sNote + 0.25 * sHoraire + 0.10 * sConfort)),
                badges: badges, meilleur: false
            });
        });
        resultat.sort(function (a, b) {
            return (b.score - a.score) || (dateObj(a.date, a.heure) - dateObj(b.date, b.heure));
        });
        resultat[0].meilleur = true;
        return resultat;
    }

    /* ================================================================
     * Affichage d'une carte trajet
     * ============================================================== */
    var BAGAGES = { petit: 'Petit bagage', moyen: 'Bagage moyen', grand: 'Grand bagage' };

    function etoiles(note) {
        var h = '';
        for (var i = 1; i <= 5; i++) {
            h += '<i class="' + (note >= i ? 'fas fa-star' : (note >= i - 0.5 ? 'fas fa-star-half-alt' : 'far fa-star')) + '" aria-hidden="true"></i>';
        }
        return h;
    }

    function carte(t) {
        var note = t.note ? t.note.toFixed(1).replace('.', ',') : null;
        var vehicule = (t.marque + ' ' + t.modele).trim();
        return '' +
            '<article class="trajet-card' + (t.meilleur ? ' is-best' : '') + '" data-id="' + t.id + '" data-prix="' + t.prix +
            '" data-depart="' + dateObj(t.date, t.heure).getTime() + '" data-note="' + (t.note || 0) + '" data-score="' + (t.score || 0) +
            '" data-clim="' + t.clim + '" data-fumeur="' + t.fumeur + '">' +
            (t.meilleur ? '<div class="trajet-ribbon"><i class="fas fa-magic" aria-hidden="true"></i> Recommandé par Urbix</div>' : '') +
            '<div class="trajet-conducteur"><div class="avatar" aria-hidden="true">' + e(initiales(t.prenom, t.nom)) + '</div><div>' +
            '<h3 class="conducteur-nom">' + e(t.prenom + ' ' + t.nom) + '</h3>' +
            '<div class="etoiles">' + (note ? etoiles(t.note) + '<span>' + note + ' (' + t.nbAvis + ' avis)</span>' : '<span>Nouveau conducteur</span>') + '</div>' +
            '<p class="vehicule"><i class="fas fa-car-side" aria-hidden="true"></i> ' + e(vehicule) + (t.couleur ? ' · ' + e(t.couleur) : '') + '</p>' +
            '</div></div>' +
            '<div class="trajet-itineraire">' +
            '<p class="trajet-date"><i class="far fa-calendar-alt" aria-hidden="true"></i> ' + e(majuscule(dateFr(t.date))) + '</p>' +
            '<div class="timeline"><div class="etape"><strong>' + heureFr(t.heure) + '</strong><span>' + e(t.depart) + '</span></div>' +
            '<div class="ligne" aria-hidden="true"><em>' + Math.floor(t.duree / 60) + 'h' + pad(t.duree % 60) + '</em></div>' +
            '<div class="etape"><strong>' + heureFr(ajouterMinutes(t.heure, t.duree)) + '</strong><span>' + e(t.arrivee) + '</span></div></div>' +
            (t.rdv ? '<p class="rdv"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> RDV : ' + e(t.rdv) + '</p>' : '') +
            '<ul class="tags"><li><i class="fas fa-suitcase" aria-hidden="true"></i> ' + BAGAGES[t.bagages] + '</li>' +
            (t.clim ? '<li><i class="fas fa-snowflake" aria-hidden="true"></i> Climatisé</li>' : '') +
            '<li><i class="fas ' + (t.fumeur ? 'fa-smoking' : 'fa-smoking-ban') + '" aria-hidden="true"></i> ' + (t.fumeur ? 'Fumeur' : 'Non-fumeur') + '</li>' +
            (t.animaux ? '<li><i class="fas fa-paw" aria-hidden="true"></i> Animaux OK</li>' : '') + '</ul>' +
            (t.description ? '<p class="trajet-description">« ' + e(t.description) + ' »</p>' : '') +
            (t.badges && t.badges.length ? '<ul class="badges">' + t.badges.map(function (b) { return '<li>' + e(b) + '</li>'; }).join('') + '</ul>' : '') +
            '</div>' +
            '<div class="trajet-action">' +
            '<p class="prix">' + prixDT(t.prix) + '<small>par place</small></p>' +
            '<p class="places' + (t.places <= 1 ? ' is-low' : '') + '"><i class="fas fa-user-friends" aria-hidden="true"></i> ' +
            t.places + ' place' + (t.places > 1 ? 's libres' : ' libre') + '</p>' +
            (t.score !== undefined ? '<p class="score" title="Score intelligent : prix, note, horaire et confort"><span style="width:' + t.score + '%"></span><em>Score ' + t.score + '/100</em></p>' : '') +
            '<button type="button" class="button js-accepter" data-id="' + t.id + '"><i class="fas fa-check" aria-hidden="true"></i> Accepter</button>' +
            '<button type="button" class="button alt small js-ignorer">Ignorer</button>' +
            '</div></article>';
    }

    /* ================================================================
     * Vue Covoiturage
     * ============================================================== */
    var form = $('form-recherche');
    var liste = $('liste-trajets');
    var criteres = null;

    $('liste-villes').innerHTML = ['Tunis', 'Ariana', 'Ben Arous', 'La Manouba', 'Bizerte', 'Nabeul', 'Hammamet', 'Sousse', 'Monastir',
        'Mahdia', 'Sfax', 'Kairouan', 'Gabès', 'Djerba', 'Gafsa', 'Tozeur', 'Béja', 'Jendouba']
        .map(function (v) { return '<option value="' + e(v) + '"></option>'; }).join('');
    form.elements.date.min = isoJour(0);

    function afficherListe(trajets) {
        liste.innerHTML = trajets.map(carte).join('');
        $('nb-resultats').textContent = trajets.length;
        $('resultats-barre').hidden = trajets.length === 0;
        $('resultats-vide').hidden = trajets.length !== 0;
        $('filtre-clim').checked = false;
        $('filtre-nonfumeur').checked = false;
        $('vide-filtre').hidden = true;
        document.querySelectorAll('[data-tri]').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-tri') === 'score'); });
    }

    function afficherCovoiturage() {
        var message = $('resultats-message');
        if (!criteres) {
            $('resultats-titre').textContent = 'Prochains départs';
            message.hidden = true;
            afficherListe(aVenir().slice(0, 6));
            return;
        }
        var r = rechercher(criteres);
        $('resultats-titre').textContent = (criteres.depart ? majuscule(criteres.depart) + ' → ' : 'Vers ') + majuscule(criteres.destination);
        var textes = {
            dates_proches: 'Aucun trajet à la date choisie : voici les propositions aux dates les plus proches.',
            similaires: 'Aucun trajet ne correspond exactement : voici d\'autres trajets vers la même destination.'
        };
        message.hidden = !textes[r.type];
        message.innerHTML = textes[r.type] ? '<i class="fas fa-lightbulb" aria-hidden="true"></i> ' + e(textes[r.type]) : '';
        afficherListe(r.trajets);
    }

    function erreurChamp(champ, message) {
        var bloc = champ.closest('.champ');
        bloc.classList.toggle('has-error', !!message);
        bloc.querySelector('.field-error').textContent = message || '';
        return !message;
    }

    $('btn-inverser').addEventListener('click', function () {
        var tmp = form.elements.depart.value;
        form.elements.depart.value = form.elements.destination.value;
        form.elements.destination.value = tmp;
        var b = this;
        b.classList.add('is-turning');
        window.setTimeout(function () { b.classList.remove('is-turning'); }, 400);
    });

    form.querySelectorAll('.raccourcis button').forEach(function (b) {
        b.addEventListener('click', function () {
            var j = b.getAttribute('data-jour');
            form.elements.date.value = j === '' ? '' : isoJour(+j);
            erreurChamp(form.elements.date, '');
        });
    });

    form.addEventListener('input', function (ev) {
        var bloc = ev.target.closest('.champ');
        if (bloc && bloc.classList.contains('has-error')) erreurChamp(ev.target, '');
    });

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var el = form.elements;
        var dest = el.destination.value.trim();
        var dep = el.depart.value.trim();
        var prixTexte = el.prix_max.value.trim().replace(',', '.');
        var ok = true;

        if (dest === '') ok = erreurChamp(el.destination, 'Veuillez saisir une destination.') && ok;
        else if (!REGEX_VILLE.test(dest)) ok = erreurChamp(el.destination, 'Uniquement des lettres (2 caractères minimum).') && ok;
        else if (dep !== '' && normaliser(dep) === normaliser(dest)) ok = erreurChamp(el.destination, 'La destination doit être différente du départ.') && ok;
        else erreurChamp(el.destination, '');

        ok = erreurChamp(el.depart, dep === '' || REGEX_VILLE.test(dep) ? '' : 'Uniquement des lettres.') && ok;
        ok = erreurChamp(el.date, el.date.value === '' || el.date.value >= isoJour(0) ? '' : 'La date ne peut pas être dans le passé.') && ok;
        ok = erreurChamp(el.heure, el.heure.value === '' || /^([01]\d|2[0-3]):[0-5]\d$/.test(el.heure.value) ? '' : 'Heure invalide.') && ok;
        ok = erreurChamp(el.prix_max, prixTexte === '' || /^\d+(\.\d{1,3})?$/.test(prixTexte) ? '' : 'Saisissez un montant valide (ex. : 15 ou 12,5).') && ok;

        if (!ok) {
            var premier = form.querySelector('.has-error input, .has-error select');
            if (premier) premier.focus();
            return;
        }
        criteres = {
            depart: dep, destination: dest, date: el.date.value, heure: el.heure.value,
            places: parseInt(el.places.value, 10) || 1, prixMax: prixTexte === '' ? 0 : parseFloat(prixTexte)
        };
        afficherCovoiturage();
        var nav = $('nav');
        window.scrollTo({ top: $('resultats').getBoundingClientRect().top + window.pageYOffset - (nav ? nav.offsetHeight + 10 : 0), behavior: 'smooth' });
    });

    // Tri, filtres, ignorer
    function cartes() { return Array.prototype.slice.call(liste.querySelectorAll('.trajet-card')); }

    function appliquerFiltres() {
        var visibles = 0;
        cartes().forEach(function (c) {
            var cache = c.classList.contains('is-ignored')
                || ($('filtre-clim').checked && c.dataset.clim !== '1')
                || ($('filtre-nonfumeur').checked && c.dataset.fumeur === '1');
            c.hidden = cache;
            if (!cache) visibles++;
        });
        $('nb-resultats').textContent = visibles;
        $('vide-filtre').hidden = visibles !== 0 || cartes().length === 0;
    }

    document.querySelectorAll('[data-tri]').forEach(function (bouton) {
        bouton.addEventListener('click', function () {
            var critere = bouton.getAttribute('data-tri');
            var desc = critere === 'score' || critere === 'note';
            cartes().sort(function (a, b) {
                var va = parseFloat(a.dataset[critere]) || 0, vb = parseFloat(b.dataset[critere]) || 0;
                return desc ? vb - va : va - vb;
            }).forEach(function (c) { liste.appendChild(c); });
            document.querySelectorAll('[data-tri]').forEach(function (b) { b.classList.remove('is-active'); });
            bouton.classList.add('is-active');
        });
    });
    $('filtre-clim').addEventListener('change', appliquerFiltres);
    $('filtre-nonfumeur').addEventListener('change', appliquerFiltres);

    liste.addEventListener('click', function (ev) {
        var ignorer = ev.target.closest('.js-ignorer');
        if (ignorer) {
            var c = ignorer.closest('.trajet-card');
            c.classList.add('is-leaving');
            window.setTimeout(function () { c.classList.add('is-ignored'); appliquerFiltres(); }, 300);
            return;
        }
        var accepter = ev.target.closest('.js-accepter');
        if (accepter) ouvrirAcceptation(+accepter.getAttribute('data-id'));
    });

    /* ================================================================
     * Fenêtre "Accepter le trajet"
     * ============================================================== */
    var modalAccept = $('accept-modal');
    var selectPlaces = $('acc-places');
    var trajetChoisi = null;

    function majTotal() {
        $('acc-total').textContent = prixDT(trajetChoisi.prix * (parseInt(selectPlaces.value, 10) || 1));
    }

    function ouvrirAcceptation(id) {
        var t = trajet(id);
        if (!t) return;
        trajetChoisi = t;
        $('acc-initiales').textContent = initiales(t.prenom, t.nom);
        $('acc-conducteur').textContent = t.prenom + ' ' + t.nom;
        $('acc-vehicule').textContent = t.marque + ' ' + t.modele;
        $('acc-trajet').textContent = t.depart + ' → ' + t.arrivee;
        $('acc-date').textContent = majuscule(dateFr(t.date)) + ' à ' + heureFr(t.heure);
        $('acc-rdv').textContent = t.rdv ? 'RDV : ' + t.rdv : 'Point de rendez-vous à convenir avec le conducteur';
        var voulu = criteres ? criteres.places : 1;
        selectPlaces.innerHTML = '';
        for (var i = 1; i <= Math.min(t.places, 8); i++) {
            var o = document.createElement('option');
            o.value = i;
            o.textContent = i + ' place' + (i > 1 ? 's' : '');
            if (i === Math.min(voulu, t.places)) o.selected = true;
            selectPlaces.appendChild(o);
        }
        majTotal();
        $('acc-valider').innerHTML = moi()
            ? '<i class="fas fa-check" aria-hidden="true"></i> Accepter ce trajet'
            : '<i class="fas fa-sign-in-alt" aria-hidden="true"></i> Se connecter pour accepter';
        modalAccept.classList.add('is-open');
    }

    function fermerAcceptation(ev) {
        if (ev) ev.preventDefault();
        modalAccept.classList.remove('is-open');
    }

    selectPlaces.addEventListener('change', majTotal);
    modalAccept.querySelectorAll('.js-fermer-accept').forEach(function (el) { el.addEventListener('click', fermerAcceptation); });

    $('form-accepter').addEventListener('submit', function (ev) {
        ev.preventDefault();
        var u = moi();
        if (!u) {
            fermerAcceptation();
            ouvrirAuth('login');
            toast('info', 'Connectez-vous ou créez un compte pour accepter ce trajet.');
            return;
        }
        var t = trajet(trajetChoisi.id);
        var n = parseInt(selectPlaces.value, 10);
        var deja = db.reservations.some(function (r) { return r.idTrajet === t.id && r.idPassager === u.id && r.statut === 'acceptee'; });
        var erreur = null;
        if (t.statut !== 'ouvert') erreur = 'Ce trajet n\'est plus disponible.';
        else if (deja) erreur = 'Vous avez déjà accepté ce trajet. Consultez « Mes réservations ».';
        else if (!(n >= 1) || n > t.places) erreur = 'Il ne reste que ' + t.places + ' place(s) sur ce trajet.';
        else if (dateObj(t.date, t.heure) < new Date()) erreur = 'Ce trajet est déjà parti.';
        if (erreur) {
            fermerAcceptation();
            toast('erreur', erreur);
            return;
        }
        t.places -= n;
        if (t.places === 0) t.statut = 'complet';
        db.reservations.push({
            id: db.reservations.length + 1, idTrajet: t.id, idPassager: u.id, nbPlaces: n,
            prixTotal: n * t.prix, statut: 'acceptee', date: new Date().toISOString()
        });
        sauver();
        fermerAcceptation();
        toast('succes', 'Trajet accepté ! ' + t.depart + ' → ' + t.arrivee + ' le ' + dateFr(t.date) + ' à ' + heureFr(t.heure) +
            ' avec ' + t.prenom + ' ' + t.nom + '. Total : ' + prixDT(n * t.prix) + '.');
        window.location.hash = '#espace';
    });

    /* ================================================================
     * Interface utilisateur : connexion / inscription
     * ============================================================== */
    var modalAuth = $('auth-modal');

    function ouvrirAuth(onglet) {
        $('auth-tab-' + (onglet === 'signin' ? 'signin' : 'login')).checked = true;
        $('auth-erreur').hidden = true;
        modalAuth.classList.add('is-open');
    }

    function fermerAuth(ev) {
        if (ev) ev.preventDefault();
        modalAuth.classList.remove('is-open');
    }

    function erreurAuth(message) {
        var p = $('auth-erreur');
        p.innerHTML = '<i class="fas fa-exclamation-circle" aria-hidden="true"></i> ' + e(message);
        p.hidden = false;
    }

    modalAuth.querySelectorAll('.auth-close, .auth-backdrop, .auth-btn-secondary').forEach(function (el) {
        el.addEventListener('click', fermerAuth);
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' || ev.key === 'Esc') {
            fermerAuth();
            fermerAcceptation();
        }
    });

    function connecter(u, message) {
        db.session = u.id;
        sauver();
        fermerAuth();
        majIconeUtilisateur();
        toast('succes', message);
        route();
    }

    // auth.js valide d'abord les champs (et annule l'envoi s'ils sont invalides).
    $('form-login').addEventListener('submit', function (ev) {
        var invalide = ev.defaultPrevented;
        ev.preventDefault();
        if (invalide) return;
        var email = this.elements.login_email.value.trim().toLowerCase();
        var mdp = this.elements.login_password.value;
        var u = db.utilisateurs.filter(function (x) { return x.email.toLowerCase() === email; })[0];
        if (!u || u.mdp !== empreinte(mdp)) {
            erreurAuth('Email ou mot de passe incorrect.');
            return;
        }
        this.reset();
        connecter(u, 'Bienvenue ' + u.prenom + ' !');
    });

    $('form-signin').addEventListener('submit', function (ev) {
        var invalide = ev.defaultPrevented;
        ev.preventDefault();
        if (invalide) return;
        var el = this.elements;
        var email = el.email.value.trim();
        if (db.utilisateurs.some(function (x) { return x.email.toLowerCase() === email.toLowerCase(); })) {
            erreurAuth('Un compte existe déjà avec cet email.');
            return;
        }
        var u = {
            id: Math.max.apply(null, db.utilisateurs.map(function (x) { return x.id; })) + 1,
            nom: el.last_name.value.trim(), prenom: el.first_name.value.trim(), email: email,
            telephone: el.phone.value.trim(), role: el.role.value, genre: el.gender.value,
            naissance: el.birthdate.value, mdp: empreinte(el.password.value), inscription: isoJour(0)
        };
        db.utilisateurs.push(u);
        this.reset();
        connecter(u, 'Compte créé avec succès. Bienvenue ' + u.prenom + ' !');
    });

    function majIconeUtilisateur() {
        var u = moi();
        var nav = $('nav-user');
        var fab = $('fab-user');
        if (u) {
            nav.classList.add('is-connected');
            nav.innerHTML = e(initiales(u.prenom, u.nom));
            nav.title = 'Mon espace (' + u.prenom + ')';
            fab.classList.add('is-connected');
            fab.innerHTML = '<i class="fas fa-user-circle" aria-hidden="true"></i>';
        } else {
            nav.classList.remove('is-connected');
            nav.innerHTML = '<i class="fas fa-user" aria-hidden="true"></i>';
            nav.title = 'Connexion / Inscription';
            fab.classList.remove('is-connected');
            fab.innerHTML = '<i class="fas fa-user" aria-hidden="true"></i>';
        }
    }

    // Icône à droite : connecté → Mon espace ; sinon → fenêtre de connexion.
    document.addEventListener('click', function (ev) {
        var lien = ev.target.closest('#nav-user, #fab-user, [data-auth-open]');
        if (lien && !moi()) {
            ev.preventDefault();
            ouvrirAuth('login');
        }
    });

    /* ================================================================
     * Vue "Mon espace"
     * ============================================================== */
    var SERVICES = [
        ['#covoiturage', 'fa-car-side', 'Covoiturage'], ['#module=bus', 'fa-bus', 'Bus urbain'],
        ['#module=bus-demande', 'fa-shuttle-van', 'Bus à la demande'], ['#module=train', 'fa-train', 'Métro & Train'],
        ['#module=louage', 'fa-taxi', 'Louage'], ['#module=location', 'fa-car', 'Location'], ['#module=reparation', 'fa-tools', 'Réparation']
    ];

    function afficherEspace() {
        var u = moi();
        var services = '<section class="box resultats"><h2 class="major"><span>Tous les services Urbix</span></h2><ul class="services-grille">' +
            SERVICES.map(function (s) {
                return '<li><a href="' + s[0] + '"><i class="fas ' + s[1] + '" aria-hidden="true"></i><span>' + e(s[2]) + '</span></a></li>';
            }).join('') + '</ul></section>';

        if (!u) {
            $('espace-contenu').innerHTML =
                '<section class="box highlight"><ul class="special"><li><a href="#espace" class="icon solid fa-user" data-auth-open><span class="label">Connexion</span></a></li></ul>' +
                '<header><h2>Mon espace Urbix</h2><p>Connectez-vous pour retrouver vos trajets et vos réservations.</p></header>' +
                '<ul class="actions special-actions"><li><a href="#espace" class="button large" data-auth-open><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Se connecter</a></li>' +
                '<li><a href="#covoiturage" class="button alt large">Chercher un trajet</a></li></ul></section>' + services;
            ouvrirAuth('login');
            return;
        }

        var maintenant = new Date();
        var mesResa = db.reservations.filter(function (r) { return r.idPassager === u.id; }).slice().reverse();
        var nbAVenir = 0, depense = 0;
        var lignes = mesResa.map(function (r) {
            var t = trajet(r.idTrajet);
            if (!t) return '';
            var depart = dateObj(t.date, t.heure);
            var futur = depart > maintenant;
            var actif = r.statut === 'acceptee' && futur;
            if (actif) nbAVenir++;
            if (r.statut !== 'annulee') depense += r.prixTotal;
            var statut = r.statut === 'annulee' ? ['Annulée', 'statut-ko'] : (futur ? ['Acceptée', 'statut-ok'] : ['Terminée', 'statut-fin']);
            return '<article class="reservation' + (r.statut === 'annulee' ? ' is-annulee' : '') + '">' +
                '<div class="reservation-date"><strong>' + pad(depart.getDate()) + '</strong><span>' + MOIS_COURT[depart.getMonth()] + '</span><em>' + heureFr(t.heure) + '</em></div>' +
                '<div class="reservation-infos"><h3>' + e(t.depart) + ' <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i> ' + e(t.arrivee) + '</h3>' +
                '<p><i class="fas fa-user" aria-hidden="true"></i> ' + e(t.prenom + ' ' + t.nom) +
                (actif ? ' · <i class="fas fa-phone" aria-hidden="true"></i> ' + e(t.telephone) : '') +
                ' · <i class="fas fa-car-side" aria-hidden="true"></i> ' + e(t.marque + ' ' + t.modele) + '</p>' +
                (t.rdv ? '<p><i class="fas fa-map-marker-alt" aria-hidden="true"></i> RDV : ' + e(t.rdv) + '</p>' : '') +
                (actif ? '<p class="rebours" data-depart-ts="' + Math.floor(depart.getTime() / 1000) + '"></p>' : '') + '</div>' +
                '<div class="reservation-action"><span class="statut ' + statut[1] + '">' + statut[0] + '</span>' +
                '<p class="prix">' + prixDT(r.prixTotal) + '<small>' + r.nbPlaces + ' place(s)</small></p>' +
                (actif ? '<button type="button" class="button alt small js-annuler" data-id="' + r.id + '"><i class="fas fa-times" aria-hidden="true"></i> Annuler</button>' : '') +
                '</div></article>';
        }).join('');

        $('espace-contenu').innerHTML =
            '<section class="profil-card"><div class="avatar avatar-xl" aria-hidden="true">' + e(initiales(u.prenom, u.nom)) + '</div>' +
            '<div class="profil-infos"><h2>' + e(u.prenom + ' ' + u.nom) + '</h2>' +
            '<p class="profil-role"><i class="fas fa-id-badge" aria-hidden="true"></i> ' + e(ROLES[u.role] || u.role) + '</p>' +
            '<ul class="profil-details"><li><i class="fas fa-envelope" aria-hidden="true"></i> ' + e(u.email) + '</li>' +
            (u.telephone ? '<li><i class="fas fa-phone" aria-hidden="true"></i> ' + e(u.telephone) + '</li>' : '') +
            '<li><i class="far fa-calendar-check" aria-hidden="true"></i> Membre depuis ' + e(dateFr(u.inscription)) + '</li></ul></div>' +
            '<div class="profil-actions"><a href="#covoiturage" class="button"><i class="fas fa-search" aria-hidden="true"></i> Nouveau trajet</a>' +
            '<button type="button" class="button alt" id="btn-deconnexion"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Déconnexion</button></div></section>' +
            '<div class="stats">' +
            '<div class="stat"><i class="fas fa-calendar-day" aria-hidden="true"></i><strong>' + nbAVenir + '</strong><span>Trajet(s) à venir</span></div>' +
            '<div class="stat"><i class="fas fa-route" aria-hidden="true"></i><strong>' + mesResa.length + '</strong><span>Réservation(s) au total</span></div>' +
            '<div class="stat"><i class="fas fa-wallet" aria-hidden="true"></i><strong>' + prixDT(depense) + '</strong><span>Dépensé en covoiturage</span></div></div>' +
            '<section class="box resultats" id="mes-reservations"><h2 class="major"><span>Mes réservations</span></h2>' +
            (lignes ? '<div class="liste-reservations">' + lignes + '</div>'
                : '<div class="vide"><i class="fas fa-ticket-alt" aria-hidden="true"></i><h3>Aucune réservation pour le moment</h3>' +
                  '<p>Recherchez une destination et acceptez la proposition d\'un conducteur.</p><a href="#covoiturage" class="button">Rechercher un trajet</a></div>') +
            '</section>' + services;
        majRebours();
    }

    function majRebours() {
        document.querySelectorAll('[data-depart-ts]').forEach(function (el) {
            var reste = parseInt(el.getAttribute('data-depart-ts'), 10) - Date.now() / 1000;
            if (reste <= 0) { el.textContent = 'Trajet effectué'; return; }
            var j = Math.floor(reste / 86400), h = Math.floor((reste % 86400) / 3600), m = Math.floor((reste % 3600) / 60);
            el.textContent = 'Départ dans ' + (j > 0 ? j + ' j ' : '') + h + ' h ' + pad(m) + ' min';
        });
    }
    window.setInterval(majRebours, 30000);

    $('espace-contenu').addEventListener('click', function (ev) {
        if (ev.target.closest('#btn-deconnexion')) {
            db.session = null;
            sauver();
            majIconeUtilisateur();
            toast('info', 'Vous êtes déconnecté(e).');
            window.location.hash = '#accueil';
            return;
        }
        var annuler = ev.target.closest('.js-annuler');
        if (annuler && window.confirm('Annuler cette réservation ?')) {
            var r = db.reservations.filter(function (x) { return x.id === +annuler.getAttribute('data-id'); })[0];
            var u = moi();
            if (r && u && r.idPassager === u.id && r.statut === 'acceptee') {
                r.statut = 'annulee';
                var t = trajet(r.idTrajet);
                if (t) { t.places += r.nbPlaces; t.statut = 'ouvert'; }
                sauver();
                toast('succes', 'Réservation annulée, les places ont été libérées.');
                afficherEspace();
            }
        }
    });

    /* ================================================================
     * Pages des modules des autres membres (entités)
     * ============================================================== */
    var MODULES = {
        'bus': ['Bus urbain', 'fa-bus', 'Lignes, horaires et suivi en temps réel des bus traditionnels de la ville.', {
            Station: ['id_station', 'nom', 'ville', 'adresse', 'latitude', 'longitude', 'type'],
            LigneBus: ['id_ligne', 'numero', 'nom', 'id_station_depart', 'id_station_arrivee'],
            Bus: ['id_bus', 'id_vehicule', 'id_ligne', 'id_chauffeur', 'a_la_demande', 'etat', 'latitude', 'longitude'],
            HoraireBus: ['id_horaire', 'id_ligne', 'id_station', 'heure_passage']
        }],
        'bus-demande': ['Bus à la demande', 'fa-shuttle-van', 'Réservation d\'un bus intelligent avec prise en charge à domicile.', {
            DemandePassager: ['id_demande', 'id_utilisateur', 'id_bus', 'adresse_depart', 'destination', 'date_heure_souhaitee', 'nb_passagers', 'statut'],
            Bus: ['id_bus', 'id_vehicule', 'a_la_demande', 'etat']
        }],
        'train': ['Métro & Train', 'fa-train', 'Horaires, gares et billets du métro et des trains.', {
            Train: ['id_train', 'numero', 'type', 'capacite', 'id_chauffeur', 'etat'],
            TrajetTrain: ['id_trajet_train', 'id_train', 'id_gare_depart', 'id_gare_arrivee', 'date_heure_depart', 'date_heure_arrivee', 'prix'],
            BilletTrain: ['id_billet', 'id_trajet_train', 'id_utilisateur', 'classe', 'prix', 'date_achat']
        }],
        'louage': ['Louage', 'fa-taxi', 'Places disponibles dans les louages inter-villes depuis les stations.', {
            Louage: ['id_louage', 'id_chauffeur', 'id_vehicule', 'id_station_depart', 'ville_arrivee', 'prix_place', 'places_disponibles', 'date_heure_depart']
        }],
        'location': ['Location de véhicules', 'fa-car', 'Louer une voiture à la journée ou à la semaine.', {
            Vehicule: ['id_vehicule', 'id_proprietaire', 'type', 'marque', 'modele', 'couleur', 'immatriculation', 'nb_places', 'climatisation'],
            Location: ['id_location', 'id_vehicule', 'id_client', 'date_debut', 'date_fin', 'prix_jour', 'prix_total', 'statut']
        }],
        'reparation': ['Réparation de véhicules', 'fa-tools', 'Trouver un garage et suivre la réparation de son véhicule.', {
            Garage: ['id_garage', 'id_responsable', 'nom', 'adresse', 'ville', 'telephone', 'specialite'],
            Reparation: ['id_reparation', 'id_client', 'id_vehicule', 'id_garage', 'description_panne', 'date_demande', 'date_rdv', 'cout_estime', 'statut']
        }],
        'missions': ['Missions', 'fa-clipboard-list', 'Espace chauffeur : missions planifiées et en cours.', {
            Mission: ['id_mission', 'id_chauffeur', 'id_bus', 'date_mission', 'description', 'statut']
        }],
        'demandes-passagers': ['Demande de passagers', 'fa-users', 'Espace chauffeur : demandes de prise en charge des passagers.', {
            DemandePassager: ['id_demande', 'id_utilisateur', 'id_bus', 'adresse_depart', 'destination', 'date_heure_souhaitee', 'nb_passagers', 'statut']
        }],
        'cas-bus': ['Cas de mon bus', 'fa-bus-alt', 'Espace chauffeur : signaler une panne, un accident ou un retard.', {
            IncidentBus: ['id_incident', 'id_bus', 'id_chauffeur', 'type_incident', 'description', 'date_incident', 'statut']
        }],
        'station': ['Station', 'fa-map-marker-alt', 'Espace chauffeur : stations et arrêts du réseau.', {
            Station: ['id_station', 'nom', 'ville', 'adresse', 'latitude', 'longitude', 'type']
        }]
    };

    function afficherModule(cle) {
        var m = MODULES[cle];
        var entites = Object.keys(m[3]).map(function (nom) {
            return '<article class="entite"><h3><i class="fas fa-table" aria-hidden="true"></i> ' + e(nom) + '</h3><ul>' +
                m[3][nom].map(function (a, i) {
                    var cls = i === 0 ? 'cle-primaire' : (a.indexOf('id_') === 0 ? 'cle-etrangere' : '');
                    var ico = i === 0 ? '<i class="fas fa-key" aria-hidden="true"></i>' : (a.indexOf('id_') === 0 ? '<i class="fas fa-link" aria-hidden="true"></i>' : '');
                    return '<li class="' + cls + '">' + ico + ' ' + e(a) + '</li>';
                }).join('') + '</ul></article>';
        }).join('');
        $('module-contenu').innerHTML =
            '<section class="box highlight"><ul class="special"><li><span class="icon solid ' + m[1] + '"><span class="label">' + e(m[0]) + '</span></span></li></ul>' +
            '<header><h2>' + e(m[0]) + '</h2><p>' + e(m[2]) + '</p></header>' +
            '<p class="notice notice-info module-notice"><i class="fas fa-hard-hat" aria-hidden="true"></i> Ce module est réalisé par un autre membre de l\'équipe Urbix. ' +
            'Les entités ci-dessous sont déjà créées dans la base de données commune (<code>database/smartcity.sql</code>).</p></section>' +
            '<section class="box resultats"><h2 class="major"><span>Entités du module</span></h2><div class="entites">' + entites + '</div>' +
            '<ul class="actions centre"><li><a href="#covoiturage" class="button"><i class="fas fa-car-side" aria-hidden="true"></i> Essayer le covoiturage</a></li>' +
            '<li><a href="#accueil" class="button alt">Retour à l\'accueil</a></li></ul></section>';
        document.title = m[0] + ' | Urbix Smart City';
    }

    /* ================================================================
     * Routeur (#accueil, #covoiturage, #espace, #module=xxx)
     * ============================================================== */
    var TITRES = { accueil: 'Accueil', covoiturage: 'Covoiturage', espace: 'Mon espace' };

    function route() {
        var hash = window.location.hash.replace('#', '');
        var vue = 'covoiturage', cleNav = 'covoiturage';
        if (hash === 'accueil' || hash === 'espace') {
            vue = cleNav = hash;
        } else if (hash.indexOf('module=') === 0 && MODULES[hash.slice(7)]) {
            vue = 'module';
            cleNav = 'module-' + hash.slice(7);
        } else if (hash === 'mes-reservations') {
            vue = cleNav = 'espace';
        }

        document.querySelectorAll('[data-view]').forEach(function (el) {
            el.hidden = el.getAttribute('data-view') !== vue;
        });
        document.querySelectorAll('#nav > ul > li[data-nav]').forEach(function (li) {
            li.classList.toggle('current', li.getAttribute('data-nav').split(' ').indexOf(cleNav) !== -1);
        });

        if (vue === 'covoiturage') afficherCovoiturage();
        if (vue === 'espace') afficherEspace();
        if (vue === 'module') afficherModule(hash.slice(7));
        else document.title = TITRES[vue] + ' | Urbix Smart City';

        fermerAcceptation();
        window.scrollTo(0, 0);
    }

    window.addEventListener('hashchange', route);
    majIconeUtilisateur();
    route();
})();
