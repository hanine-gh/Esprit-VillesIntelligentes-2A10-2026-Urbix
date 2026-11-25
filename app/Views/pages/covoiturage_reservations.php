<?php
/**
 * Vue : mes réservations de covoiturage (frontoffice).
 * Variables : $reservations (CovoiturageReservationModel[]), $nbAVenir, $depense, $flash, $csrfToken
 */
$statuts = [
    'acceptee' => ['Acceptée', 'statut-ok'],
    'annulee'  => ['Annulée', 'statut-ko'],
    'terminee' => ['Terminée', 'statut-fin'],
];
?>
<section id="main" class="espace-utilisateur">
    <div class="container">

        <?php if ($flash): ?>
            <div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : 'auth-alert-error' ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

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
                    <a href="<?= BASE_URL ?>index.php?page=covoiturage" class="button">Rechercher un trajet</a>
                </div>
            <?php else: ?>
                <div class="liste-reservations">
                    <?php foreach ($reservations as $r):
                        $ts = strtotime($r->getDateDepart() . ' ' . $r->getHeureDepart());
                        $futur = $ts > time();
                        [$libelle, $classe] = $statuts[$r->getStatut()] ?? [$r->getStatut(), ''];
                        if ($r->getStatut() === 'acceptee' && !$futur) {
                            [$libelle, $classe] = $statuts['terminee'];
                        }
                    ?>
                        <article class="reservation <?= $r->getStatut() === 'annulee' ? 'is-annulee' : '' ?>">
                            <div class="reservation-date">
                                <strong><?= e(date('d', $ts)) ?></strong>
                                <span><?= e(moisCourt($r->getDateDepart())) ?></span>
                                <em><?= e(heureFr($r->getHeureDepart())) ?></em>
                            </div>
                            <div class="reservation-infos">
                                <h3><?= e($r->getVilleDepart()) ?> <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i> <?= e($r->getVilleArrivee()) ?></h3>
                                <p>
                                    <i class="fas fa-user" aria-hidden="true"></i> <?= e($r->getConducteurPrenom() . ' ' . $r->getConducteurNom()) ?>
                                    <?php if ($r->getStatut() === 'acceptee' && $futur && $r->getConducteurTelephone()): ?>
                                        · <i class="fas fa-phone" aria-hidden="true"></i> <?= e($r->getConducteurTelephone()) ?>
                                    <?php endif; ?>
                                    <?php if ($r->getVehiculeMarque()): ?>
                                        · <i class="fas fa-car-side" aria-hidden="true"></i> <?= e($r->getVehiculeMarque() . ' ' . $r->getVehiculeModele()) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($r->getPointRencontre()): ?>
                                    <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i> RDV : <?= e($r->getPointRencontre()) ?></p>
                                <?php endif; ?>
                                <?php if ($r->getStatut() === 'acceptee' && $futur): ?>
                                    <p class="rebours" data-depart-ts="<?= (int) $ts ?>"></p>
                                <?php endif; ?>
                            </div>
                            <div class="reservation-action">
                                <span class="statut <?= e($classe) ?>"><?= e($libelle) ?></span>
                                <p class="prix"><?= e(prixDT($r->getPrixTotal())) ?><small><?= $r->getNbPlaces() ?> place(s)</small></p>
                                <?php if ($r->getStatut() === 'acceptee' && $futur): ?>
                                    <form method="post" action="<?= BASE_URL ?>index.php?page=covoiturage-annuler" class="js-confirmer"
                                          data-message="Annuler votre réservation <?= e($r->getVilleDepart() . ' → ' . $r->getVilleArrivee()) ?> ?">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="id_reservation" value="<?= (int) $r->getId() ?>">
                                        <button type="submit" class="button alt small"><i class="fas fa-times" aria-hidden="true"></i> Annuler</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</section>
