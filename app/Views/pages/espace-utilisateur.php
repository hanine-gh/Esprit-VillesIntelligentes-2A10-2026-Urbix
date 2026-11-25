<?php
/**
 * Interface utilisateur (ouverte par l'icône à droite).
 *
 * @var array|null $utilisateur
 * @var array      $reservations
 * @var int        $nbAVenir
 * @var float      $depense
 * @var array      $roles
 * @var bool       $modeDemo
 */
$statuts = [
    'acceptee' => ['Acceptée', 'statut-ok'],
    'annulee'  => ['Annulée', 'statut-ko'],
    'terminee' => ['Terminée', 'statut-fin'],
];
$services = [
    ['covoiturage', [], 'fa-car-side', 'Covoiturage'],
    ['module', ['nom' => 'bus'], 'fa-bus', 'Bus urbain'],
    ['module', ['nom' => 'bus-demande'], 'fa-shuttle-van', 'Bus à la demande'],
    ['module', ['nom' => 'train'], 'fa-train', 'Métro & Train'],
    ['module', ['nom' => 'louage'], 'fa-taxi', 'Louage'],
    ['module', ['nom' => 'location'], 'fa-car', 'Location'],
    ['module', ['nom' => 'reparation'], 'fa-tools', 'Réparation'],
];
?>
<section id="main" class="espace-utilisateur">
    <div class="container">

        <?php if ($modeDemo): ?>
            <p class="notice notice-info">
                <i class="fas fa-database" aria-hidden="true"></i>
                Mode démo : base <strong>smartcity</strong> non connectée. Compte de test : <code>salma@urbix.tn</code> / <code>Urbix2026</code>.
            </p>
        <?php endif; ?>

        <?php if ($utilisateur === null): ?>
            <section class="box highlight">
                <ul class="special">
                    <li><a href="#auth-modal" class="icon solid fa-user" data-auth-open><span class="label">Connexion</span></a></li>
                </ul>
                <header>
                    <h2>Mon espace Urbix</h2>
                    <p>Connectez-vous pour retrouver vos trajets et vos réservations.</p>
                </header>
                <ul class="actions special-actions">
                    <li><a href="#auth-modal" class="button large" data-auth-open><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Se connecter</a></li>
                    <li><a href="<?= url('covoiturage') ?>" class="button alt large">Chercher un trajet</a></li>
                </ul>
            </section>
        <?php else: ?>

            <!-- Profil -->
            <section class="profil-card">
                <div class="avatar avatar-xl" aria-hidden="true"><?= e(initiales($utilisateur['prenom'], $utilisateur['nom'])) ?></div>
                <div class="profil-infos">
                    <h2><?= e($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></h2>
                    <p class="profil-role"><i class="fas fa-id-badge" aria-hidden="true"></i> <?= e($roles[$utilisateur['role']] ?? $utilisateur['role']) ?></p>
                    <ul class="profil-details">
                        <li><i class="fas fa-envelope" aria-hidden="true"></i> <?= e($utilisateur['email']) ?></li>
                        <?php if (!empty($utilisateur['telephone'])): ?>
                            <li><i class="fas fa-phone" aria-hidden="true"></i> <?= e($utilisateur['telephone']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($utilisateur['date_inscription'])): ?>
                            <li><i class="far fa-calendar-check" aria-hidden="true"></i> Membre depuis <?= e(dateFr(substr($utilisateur['date_inscription'], 0, 10))) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="profil-actions">
                    <a href="<?= url('covoiturage') ?>" class="button"><i class="fas fa-search" aria-hidden="true"></i> Nouveau trajet</a>
                    <form method="post" action="<?= url('deconnexion') ?>">
                        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                        <button type="submit" class="button alt"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Déconnexion</button>
                    </form>
                </div>
            </section>

            <!-- Statistiques -->
            <div class="stats">
                <div class="stat"><i class="fas fa-calendar-day" aria-hidden="true"></i><strong><?= (int) $nbAVenir ?></strong><span>Trajet(s) à venir</span></div>
                <div class="stat"><i class="fas fa-route" aria-hidden="true"></i><strong><?= count($reservations) ?></strong><span>Réservation(s) au total</span></div>
                <div class="stat"><i class="fas fa-wallet" aria-hidden="true"></i><strong><?= e(prixDT($depense)) ?></strong><span>Dépensé en covoiturage</span></div>
            </div>

            <!-- Mes réservations -->
            <section class="box resultats" id="mes-reservations">
                <h2 class="major"><span>Mes réservations</span></h2>

                <?php if (!$reservations): ?>
                    <div class="vide">
                        <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                        <h3>Aucune réservation pour le moment</h3>
                        <p>Recherchez une destination et acceptez la proposition d'un conducteur.</p>
                        <a href="<?= url('covoiturage') ?>" class="button">Rechercher un trajet</a>
                    </div>
                <?php else: ?>
                    <div class="liste-reservations">
                        <?php foreach ($reservations as $r):
                            $ts = strtotime($r['date_depart'] . ' ' . $r['heure_depart']);
                            $futur = $ts > time();
                            [$libelle, $classe] = $statuts[$r['statut']] ?? [$r['statut'], ''];
                            if ($r['statut'] === 'acceptee' && !$futur) {
                                [$libelle, $classe] = $statuts['terminee'];
                            }
                        ?>
                            <article class="reservation <?= $r['statut'] === 'annulee' ? 'is-annulee' : '' ?>">
                                <div class="reservation-date">
                                    <strong><?= e(date('d', $ts)) ?></strong>
                                    <span><?= e(moisCourt($r['date_depart'])) ?></span>
                                    <em><?= e(heureFr($r['heure_depart'])) ?></em>
                                </div>
                                <div class="reservation-infos">
                                    <h3><?= e($r['ville_depart']) ?> <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i> <?= e($r['ville_arrivee']) ?></h3>
                                    <p>
                                        <i class="fas fa-user" aria-hidden="true"></i> <?= e($r['conducteur_prenom'] . ' ' . $r['conducteur_nom']) ?>
                                        <?php if ($r['statut'] === 'acceptee' && $futur && !empty($r['conducteur_telephone'])): ?>
                                            · <i class="fas fa-phone" aria-hidden="true"></i> <?= e($r['conducteur_telephone']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($r['vehicule_marque'])): ?>
                                            · <i class="fas fa-car-side" aria-hidden="true"></i> <?= e($r['vehicule_marque'] . ' ' . $r['vehicule_modele']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($r['point_rencontre'])): ?>
                                        <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i> RDV : <?= e($r['point_rencontre']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($r['statut'] === 'acceptee' && $futur): ?>
                                        <p class="rebours" data-depart-ts="<?= (int) $ts ?>"></p>
                                    <?php endif; ?>
                                </div>
                                <div class="reservation-action">
                                    <span class="statut <?= e($classe) ?>"><?= e($libelle) ?></span>
                                    <p class="prix"><?= e(prixDT($r['prix_total'])) ?><small><?= (int) $r['nb_places'] ?> place(s)</small></p>
                                    <?php if ($r['statut'] === 'acceptee' && $futur): ?>
                                        <form method="post" action="<?= url('covoiturage-annuler') ?>" class="js-confirmer"
                                              data-message="Annuler votre réservation <?= e($r['ville_depart'] . ' → ' . $r['ville_arrivee']) ?> ?">
                                            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="id_reservation" value="<?= (int) $r['id_reservation'] ?>">
                                            <button type="submit" class="button alt small"><i class="fas fa-times" aria-hidden="true"></i> Annuler</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <!-- Autres services Urbix -->
        <section class="box resultats">
            <h2 class="major"><span>Tous les services Urbix</span></h2>
            <ul class="services-grille">
                <?php foreach ($services as [$page, $params, $icone, $libelle]): ?>
                    <li><a href="<?= url($page, $params) ?>"><i class="fas <?= $icone ?>" aria-hidden="true"></i><span><?= e($libelle) ?></span></a></li>
                <?php endforeach; ?>
            </ul>
        </section>

    </div>
</section>
