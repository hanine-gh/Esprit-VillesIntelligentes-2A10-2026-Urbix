<?php
/**
 * Vue : module Location — véhicules (filtrés par type) + mes locations (frontoffice).
 * Variables : $filtreActuel, $vehiculesFiltres (MobiliteVehiculeModel[]), $locations (MobiliteLocationModel[]),
 *             $flash, $isLoggedIn
 */
?>
<section id="main">
	<div class="container">
		<div class="row gtr-200">

			<div class="col-12">
				<?php if ($flash): ?>
					<div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : 'auth-alert-error' ?>"><?= e($flash['message']) ?></div>
				<?php endif; ?>

				<section class="box">
					<h2 class="major"><span>Choisir un type et une location</span></h2>

					<div class="choix-type-bar">
						<a href="<?= BASE_URL ?>index.php?page=location&amp;type=moto" class="button small <?= $filtreActuel === 'moto' ? '' : 'alt' ?>">Moto</a>
						<a href="<?= BASE_URL ?>index.php?page=location&amp;type=trottinette" class="button small <?= $filtreActuel === 'trottinette' ? '' : 'alt' ?>">Trottinette</a>
						<a href="<?= BASE_URL ?>index.php?page=location&amp;type=velo" class="button small <?= $filtreActuel === 'velo' ? '' : 'alt' ?>">Vélo</a>
						<a href="<?= BASE_URL ?>index.php?page=location&amp;type=tous" class="button small <?= $filtreActuel === 'tous' ? '' : 'alt' ?>">Tous</a>
					</div>

					<?php foreach ($vehiculesFiltres as $v): ?>
						<?php $statut = e($v->getStatut()); ?>
						<div class="carte-location-box">
							<div class="row gtr-50">
								<div class="col-4 col-12-small">
									<span class="image fit" style="margin:0;">
										<img src="<?= ASSETS_URL ?>images/<?= e($v->getImage()) ?>" alt="<?= e($v->getNom()) ?>" />
									</span>
								</div>
								<div class="col-8 col-12-small">
									<h3 style="margin-bottom:0.25em;"><?= e($v->getNom()) ?></h3>
									<p style="margin-bottom:0.4em;">
										<span class="badge-statut statut-<?= $statut ?>"><?= $statut ?></span>
										— <strong><?= e((string) $v->getTarifHeure()) ?> DT / heure</strong>
									</p>
									<ul class="vehicule-meta">
										<li><strong>Type :</strong> <?= strtoupper(e($v->getType())) ?></li>
										<li><strong>Station :</strong> <?= e($v->getStation()) ?></li>
										<li><strong>Batterie :</strong> <?= e($v->getAutonomie()) ?> — <strong>Note :</strong> <?= number_format($v->getNoteMoyenne(), 1) ?>/5 (<?= $v->getAvisCount() ?> avis)</li>
									</ul>
									<?php if ($v->getStatut() === 'disponible'): ?>
										<a href="<?= BASE_URL ?>index.php?page=location-reserver&amp;vehicule=<?= (int) $v->getId() ?>" class="button small">Choisir et réserver</a>
									<?php else: ?>
										<button type="button" class="button alt small" disabled>Indisponible</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</section>
			</div>

			<div class="col-12">
				<section class="box">
					<h2 class="major"><span>Mes locations</span></h2>
					<?php if (!$isLoggedIn): ?>
						<p><a href="#auth-modal" data-auth-open>Connectez-vous</a> pour voir et gérer vos locations.</p>
					<?php elseif (empty($locations)): ?>
						<p>Aucune location enregistrée pour le moment.</p>
					<?php else: ?>
						<?php foreach ($locations as $l): ?>
							<div class="historique-item">
								<div>
									<strong><?= e($l->getReference()) ?></strong> — <strong><?= e($l->getVehiculeNom()) ?></strong>
									<span class="badge-statut statut-<?= e($l->getStatut()) ?>"><?= e($l->getStatut()) ?></span>
								</div>
								<p style="margin:0.4em 0;">
									<?= e($l->getStationDepart()) ?> → <?= e($l->getStationRetour()) ?> |
									Durée : <strong><?= $l->getDureeHeures() ?>h</strong> |
									Total : <strong><?= number_format($l->getMontant(), 2) ?> DT</strong> |
									PIN : <strong><?= e($l->getCodePin()) ?></strong>
								</p>
								<div class="actions-ligne">
									<a href="<?= BASE_URL ?>index.php?page=location-demarrer&amp;loc=<?= (int) $l->getId() ?>" class="button small">Déverrouiller</a>
									<a href="<?= BASE_URL ?>index.php?page=location-prolonger&amp;loc=<?= (int) $l->getId() ?>" class="button small">Prolonger</a>
									<a href="<?= BASE_URL ?>index.php?page=location-incident&amp;loc=<?= (int) $l->getId() ?>" class="button alt small">Incident</a>
									<a href="<?= BASE_URL ?>index.php?page=location-terminer&amp;loc=<?= (int) $l->getId() ?>" class="button small">Terminer</a>
									<a href="<?= BASE_URL ?>index.php?page=location-annuler&amp;loc=<?= (int) $l->getId() ?>" class="button alt small">Annuler</a>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</section>
			</div>

		</div>
	</div>
</section>
