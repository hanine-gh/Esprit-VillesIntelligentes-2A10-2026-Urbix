<?php
/**
 * Interface Covoiturage (client) :
 * le client saisit sa destination, Urbix propose les trajets des conducteurs
 * (nom, prix, date, heure...) classés intelligemment ; le client accepte celui qui lui convient.
 *
 * @var array      $criteres
 * @var array      $erreurs
 * @var array|null $resultat
 * @var bool       $rechercheLancee
 * @var array      $villes
 * @var array      $populaires
 * @var bool       $modeDemo
 */
$moi = utilisateurConnecte();
$aujourdhui = date('Y-m-d');
$demain = date('Y-m-d', strtotime('+1 day'));
$messages = [
    'dates_proches' => 'Aucun trajet à la date choisie : voici les propositions aux dates les plus proches.',
    'similaires'    => 'Aucun trajet ne correspond exactement : voici d\'autres trajets vers la même destination.',
];
$erreurChamp = function (string $champ) use ($erreurs): string {
    return isset($erreurs[$champ]) ? '<small class="field-error">' . e($erreurs[$champ]) . '</small>' : '<small class="field-error"></small>';
};
?>
<!-- Bannière + recherche -->
<section id="covoit-hero">
    <div class="container">
        <div class="hero-titre">
            <h2><i class="fas fa-car-side" aria-hidden="true"></i> Covoiturage intelligent</h2>
            <p>Où allez-vous ? Urbix trouve et classe pour vous les meilleurs conducteurs.</p>
        </div>

        <form id="form-recherche" class="recherche-card" method="get" action="<?= BASE_URL ?>index.php" novalidate>
            <input type="hidden" name="page" value="covoiturage">

            <div class="recherche-villes">
                <div class="champ <?= isset($erreurs['depart']) ? 'has-error' : '' ?>">
                    <label for="depart"><i class="fas fa-dot-circle" aria-hidden="true"></i> Départ</label>
                    <input type="text" id="depart" name="depart" list="liste-villes" placeholder="Ex. : Tunis (facultatif)"
                           value="<?= e($criteres['depart']) ?>" autocomplete="off">
                    <?= $erreurChamp('depart') ?>
                </div>

                <button type="button" class="btn-inverser" id="btn-inverser" title="Inverser départ et destination" aria-label="Inverser départ et destination">
                    <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                </button>

                <div class="champ <?= isset($erreurs['destination']) ? 'has-error' : '' ?>">
                    <label for="destination"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Destination *</label>
                    <input type="text" id="destination" name="destination" list="liste-villes" placeholder="Ex. : Sousse"
                           value="<?= e($criteres['destination']) ?>" autocomplete="off">
                    <?= $erreurChamp('destination') ?>
                </div>
            </div>

            <div class="recherche-options">
                <div class="champ <?= isset($erreurs['date']) ? 'has-error' : '' ?>">
                    <label for="date"><i class="far fa-calendar-alt" aria-hidden="true"></i> Date</label>
                    <input type="date" id="date" name="date" min="<?= $aujourdhui ?>" value="<?= e($criteres['date']) ?>">
                    <div class="raccourcis">
                        <button type="button" data-date="<?= $aujourdhui ?>">Aujourd'hui</button>
                        <button type="button" data-date="<?= $demain ?>">Demain</button>
                        <button type="button" data-date="">Peu importe</button>
                    </div>
                    <?= $erreurChamp('date') ?>
                </div>
                <div class="champ <?= isset($erreurs['heure']) ? 'has-error' : '' ?>">
                    <label for="heure"><i class="far fa-clock" aria-hidden="true"></i> Heure souhaitée</label>
                    <input type="time" id="heure" name="heure" value="<?= e($criteres['heure']) ?>">
                    <?= $erreurChamp('heure') ?>
                </div>
                <div class="champ <?= isset($erreurs['places']) ? 'has-error' : '' ?>">
                    <label for="places"><i class="fas fa-user-friends" aria-hidden="true"></i> Passagers</label>
                    <select id="places" name="places">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>" <?= $criteres['places'] === $i ? 'selected' : '' ?>><?= $i ?> passager<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                    <?= $erreurChamp('places') ?>
                </div>
                <div class="champ <?= isset($erreurs['prix_max']) ? 'has-error' : '' ?>">
                    <label for="prix_max"><i class="fas fa-coins" aria-hidden="true"></i> Prix max (DT)</label>
                    <input type="text" id="prix_max" name="prix_max" inputmode="decimal" placeholder="Illimité"
                           value="<?= $criteres['prix_max'] > 0 ? e($criteres['prix_max']) : '' ?>">
                    <?= $erreurChamp('prix_max') ?>
                </div>
            </div>

            <button type="submit" class="button large btn-rechercher"><i class="fas fa-search" aria-hidden="true"></i> Rechercher</button>
        </form>

        <datalist id="liste-villes">
            <?php foreach ($villes as $ville): ?>
                <option value="<?= e($ville) ?>"></option>
            <?php endforeach; ?>
        </datalist>
    </div>
</section>

<!-- Main -->
<section id="main">
    <div class="container">

        <?php if ($modeDemo): ?>
            <p class="notice notice-info">
                <i class="fas fa-database" aria-hidden="true"></i>
                Mode démo : la base <strong>smartcity</strong> n'est pas connectée, les données sont temporaires.
                Importez <code>database/smartcity.sql</code> dans phpMyAdmin pour utiliser MySQL.
            </p>
        <?php endif; ?>

        <?php if ($rechercheLancee && $erreurs): ?>
            <p class="notice notice-erreur"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Veuillez corriger les champs signalés dans le formulaire de recherche.</p>
        <?php endif; ?>

        <?php if ($resultat !== null): ?>
            <!-- Résultats -->
            <section class="box resultats" id="resultats">
                <h2 class="major"><span>
                    <?= $criteres['depart'] !== '' ? e(ucfirst($criteres['depart'])) . ' → ' : 'Vers ' ?><?= e(ucfirst($criteres['destination'])) ?>
                </span></h2>

                <?php if ($resultat['type'] === 'aucun'): ?>
                    <div class="vide">
                        <i class="fas fa-route" aria-hidden="true"></i>
                        <h3>Aucun conducteur ne propose ce trajet pour le moment</h3>
                        <p>Essayez une autre date, une ville voisine ou consultez les autres moyens de transport Urbix.</p>
                        <ul class="actions">
                            <li><a class="button" href="<?= url('module', ['nom' => 'louage']) ?>"><i class="fas fa-taxi" aria-hidden="true"></i> Voir les louages</a></li>
                            <li><a class="button alt" href="<?= url('module', ['nom' => 'train']) ?>"><i class="fas fa-train" aria-hidden="true"></i> Voir les trains</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <?php if (isset($messages[$resultat['type']])): ?>
                        <p class="notice notice-info"><i class="fas fa-lightbulb" aria-hidden="true"></i> <?= e($messages[$resultat['type']]) ?></p>
                    <?php endif; ?>

                    <div class="resultats-barre">
                        <p class="resultats-compte"><strong id="nb-resultats"><?= count($resultat['trajets']) ?></strong> proposition(s) de conducteurs</p>
                        <div class="tri" role="group" aria-label="Trier les propositions">
                            <span>Trier :</span>
                            <button type="button" class="is-active" data-tri="score"><i class="fas fa-magic" aria-hidden="true"></i> Recommandé</button>
                            <button type="button" data-tri="prix">Prix</button>
                            <button type="button" data-tri="depart">Heure</button>
                            <button type="button" data-tri="note">Note</button>
                        </div>
                        <div class="filtres">
                            <label><input type="checkbox" id="filtre-clim"> Climatisé</label>
                            <label><input type="checkbox" id="filtre-nonfumeur"> Non-fumeur</label>
                        </div>
                    </div>

                    <div class="liste-trajets" id="liste-trajets">
                        <?php foreach ($resultat['trajets'] as $t): ?>
                            <?php require __DIR__ . '/partials/carte-trajet.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <p class="vide-filtre" id="vide-filtre" hidden>Aucune proposition ne correspond aux filtres choisis.</p>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <!-- Comment ça marche -->
            <section class="box highlight">
                <ul class="special">
                    <li><span class="icon solid fa-search-location"><span class="label">Rechercher</span></span></li>
                    <li><span class="icon solid fa-magic"><span class="label">Comparer</span></span></li>
                    <li><span class="icon solid fa-handshake"><span class="label">Accepter</span></span></li>
                </ul>
                <header>
                    <h2>Comment ça marche ?</h2>
                    <p>1. Saisissez votre destination · 2. Comparez les conducteurs · 3. Acceptez le trajet qui vous convient</p>
                </header>
            </section>

            <?php if ($populaires): ?>
                <section class="box resultats">
                    <h2 class="major"><span>Prochains départs</span></h2>
                    <div class="liste-trajets">
                        <?php foreach ($populaires as $t): ?>
                            <?php require __DIR__ . '/partials/carte-trajet.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<!-- Fenêtre de confirmation "Accepter le trajet" -->
<div id="accept-modal" class="auth-modal accept-modal" data-connecte="<?= $moi ? '1' : '0' ?>">
    <a href="#" class="auth-backdrop js-fermer-accept" aria-label="Fermer la fenêtre"></a>
    <div class="auth-dialog" role="dialog" aria-labelledby="accept-titre">
        <a href="#" class="auth-close js-fermer-accept" aria-label="Fermer">&times;</a>
        <h2 id="accept-titre" class="auth-title">Confirmer le trajet</h2>
        <p class="auth-subtitle">Vérifiez les informations avant d'accepter la proposition.</p>

        <div class="recap">
            <div class="avatar" id="acc-initiales" aria-hidden="true"></div>
            <div>
                <p class="recap-conducteur" id="acc-conducteur"></p>
                <p class="recap-vehicule" id="acc-vehicule"></p>
            </div>
        </div>
        <ul class="recap-liste">
            <li><i class="fas fa-route" aria-hidden="true"></i> <span id="acc-trajet"></span></li>
            <li><i class="far fa-calendar-alt" aria-hidden="true"></i> <span id="acc-date"></span></li>
            <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <span id="acc-rdv"></span></li>
        </ul>

        <form method="post" action="<?= url('covoiturage-accepter') ?>" id="form-accepter" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="retour" value="<?= e($_SERVER['REQUEST_URI'] ?? url('covoiturage')) ?>">
            <input type="hidden" name="id_trajet" id="acc-id" value="">

            <div class="auth-row">
                <div class="auth-field">
                    <label for="acc-places">Nombre de places</label>
                    <select name="nb_places" id="acc-places"></select>
                    <small class="field-error"></small>
                </div>
                <div class="auth-field">
                    <label>Total à payer</label>
                    <p class="recap-total" id="acc-total">0,000 DT</p>
                </div>
            </div>

            <p class="recap-info"><i class="fas fa-info-circle" aria-hidden="true"></i> Le paiement se fait directement au conducteur le jour du départ.</p>

            <div class="auth-actions">
                <?php if ($moi): ?>
                    <button type="submit" class="auth-btn auth-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> Accepter ce trajet</button>
                <?php else: ?>
                    <button type="button" class="auth-btn auth-btn-primary js-connexion-requise"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Se connecter pour accepter</button>
                <?php endif; ?>
                <a href="#" class="auth-btn auth-btn-secondary js-fermer-accept">Annuler</a>
            </div>
        </form>
    </div>
</div>
