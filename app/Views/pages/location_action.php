<?php
/**
 * Vue : formulaire d'une action sur une location (frontoffice).
 * Variables : $action, $titrePage, $messageErreur, $vehiculesDispo, $locations, $stations,
 *             $selectedVehiculeId, $selectedLocationId, $csrfToken
 * Pas de contrôle HTML5 : JS (modules-validation.js) + PHP.
 */
$liens = ['reserver' => 'Réserver', 'demarrer' => 'Déverrouiller', 'prolonger' => 'Prolonger',
          'incident' => 'Incident', 'terminer' => 'Terminer', 'annuler' => 'Annuler'];
?>
<section id="main">
	<div class="container">
		<div class="row gtr-200">
			<div class="col-12">
				<section class="box">
					<h2 class="major"><span><?= e($titrePage) ?></span></h2>

					<div style="margin-bottom:1.2em; display:flex; gap:0.5em; flex-wrap:wrap;">
						<a href="<?= BASE_URL ?>index.php?page=location" class="button alt small">← Retour Mobilité</a>
						<?php foreach ($liens as $cle => $libelle): ?>
							<a href="<?= BASE_URL ?>index.php?page=location-<?= $cle ?>" class="button <?= $action === $cle ? '' : 'alt' ?> small"><?= e($libelle) ?></a>
						<?php endforeach; ?>
					</div>

					<?php if ($messageErreur !== ''): ?>
						<div class="auth-alert auth-alert-error"><?= e($messageErreur) ?></div>
					<?php endif; ?>

					<?php if ($action === 'reserver'): ?>
						<form method="post" action="<?= BASE_URL ?>index.php?page=location-reserver" data-validate="modules" novalidate>
							<input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
							<div class="row gtr-50">
								<div class="col-6 col-12-small">
									<label for="vehicule_id">Véhicule disponible</label>
									<select name="vehicule_id" id="vehicule_id" data-v-required="Choisissez un véhicule.">
										<?php foreach ($vehiculesDispo as $v): ?>
											<option value="<?= (int) $v->getId() ?>" <?= $v->getId() === $selectedVehiculeId ? 'selected' : '' ?>>
												<?= e($v->getNom()) ?> — <?= e((string) $v->getTarifHeure()) ?> DT/h (<?= e($v->getStation()) ?>)
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-6 col-12-small">
									<label for="station_retour">Station de retour</label>
									<select name="station_retour" id="station_retour">
										<?php foreach ($stations as $station): ?>
											<option value="<?= e($station) ?>"><?= e($station) ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-6 col-12-small">
									<label for="duree_heures">Durée (heures)</label>
									<input type="number" name="duree_heures" id="duree_heures" value="1"
									       data-v-required="Indiquez la durée." data-v-int="1,24" data-v-msg="Durée : 1 à 24 heures." />
								</div>
								<div class="col-12">
									<button type="submit" class="button">Confirmer la réservation</button>
								</div>
							</div>
						</form>
					<?php else: ?>
						<form method="post" action="<?= BASE_URL ?>index.php?page=location-<?= e($action) ?>" data-validate="modules" novalidate>
							<input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
							<div class="row gtr-50">
								<div class="col-6 col-12-small">
									<label for="location_id">Location concernée</label>
									<select name="location_id" id="location_id" data-v-required="Choisissez une location.">
										<?php foreach ($locations as $l): ?>
											<option value="<?= (int) $l->getId() ?>" <?= $l->getId() === $selectedLocationId ? 'selected' : '' ?>>
												<?= e($l->getReference()) ?> — <?= e($l->getVehiculeNom()) ?> (<?= e($l->getStatut()) ?>, PIN: <?= e($l->getCodePin()) ?>)
											</option>
										<?php endforeach; ?>
									</select>
								</div>

								<?php if ($action === 'demarrer'): ?>
									<div class="col-6 col-12-small">
										<label for="code_pin">Code PIN</label>
										<input type="text" name="code_pin" id="code_pin" placeholder="Ex: 1234"
										       data-v-required="Saisissez le code PIN." data-v-pattern="^[0-9]{4}$" data-v-msg="Le code PIN contient 4 chiffres." />
									</div>
								<?php elseif ($action === 'prolonger'): ?>
									<div class="col-6 col-12-small">
										<label for="heures_ajout">Heures supplémentaires</label>
										<input type="number" name="heures_ajout" id="heures_ajout" value="1"
										       data-v-required="Indiquez le nombre d'heures." data-v-int="1,12" data-v-msg="1 à 12 heures." />
									</div>
								<?php elseif ($action === 'incident'): ?>
									<div class="col-6 col-12-small">
										<label for="description">Description de l'incident</label>
										<input type="text" name="description" id="description" placeholder="Ex: Batterie faible / crevaison"
										       data-v-required="Décrivez l'incident." data-v-maxlength="255" data-v-msg="255 caractères au maximum." />
									</div>
								<?php elseif ($action === 'terminer'): ?>
									<div class="col-6 col-12-small">
										<label for="note">Note (1 à 5)</label>
										<select name="note" id="note">
											<option value="5">5 / 5</option>
											<option value="4">4 / 5</option>
											<option value="3">3 / 5</option>
											<option value="2">2 / 5</option>
											<option value="1">1 / 5</option>
										</select>
									</div>
								<?php endif; ?>

								<div class="col-12">
									<button type="submit" class="button">Valider l'action</button>
								</div>
							</div>
						</form>
					<?php endif; ?>
				</section>
			</div>
		</div>
	</div>
</section>
