<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$model = new MobiliteModel();
$state = $state ?? $model->getState();
$action = $action ?? ($_GET['action'] ?? 'reserver');
$vehicules = $state['vehicules'] ?? [];
$locations = $state['locations'] ?? [];

$selectedVehiculeId = $_GET['vehicule'] ?? ($state['selectedVehiculeId'] ?? 'V1');
$selectedLocationId = $_GET['loc'] ?? ($state['selectedLocationId'] ?? ($locations[0]['id'] ?? ''));

$messageErreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action_type'] ?? $action;

    if ($postAction === 'reserver') {
        $vId = $_POST['vehicule_id'] ?? '';
        $stationRetour = trim($_POST['station_retour'] ?? 'Station Centre Ville');
        $duree = max(1, (int)($_POST['duree_heures'] ?? 1));

        foreach ($vehicules as &$v) {
            if (($v['id'] ?? '') === $vId && ($v['statut'] ?? '') === 'disponible') {
                $v['statut'] = 'reserve';
                $montant = $duree * (float)($v['tarifHeure'] ?? 0);
                $locId = 'LOC-' . rand(1000, 9999);
                $pin = (string)rand(1000, 9999);

                $locations[] = [
                    'id' => $locId,
                    'vehiculeId' => $v['id'],
                    'vehiculeNom' => $v['nom'],
                    'vehiculeType' => $v['type'],
                    'stationDepart' => $v['station'],
                    'stationRetour' => $stationRetour,
                    'dureeHeures' => $duree,
                    'montant' => $montant,
                    'codePin' => $pin,
                    'statut' => 'reserve',
                    'annulee' => false,
                    'elapsedSec' => 0
                ];
                $state['selectedVehiculeId'] = $v['id'];
                $state['selectedLocationId'] = $locId;
                break;
            }
        }
        unset($v);
    } elseif ($postAction === 'demarrer') {
        $locId = $_POST['location_id'] ?? '';
        $pinSaisi = trim($_POST['code_pin'] ?? '');
        foreach ($locations as &$l) {
            if (($l['id'] ?? '') === $locId && ($l['statut'] ?? '') === 'reserve') {
                if (($l['codePin'] ?? '') !== $pinSaisi) {
                    $messageErreur = 'Code PIN incorrect.';
                    break;
                }
                $l['statut'] = 'encours';
                foreach ($vehicules as &$v) {
                    if (($v['id'] ?? '') === ($l['vehiculeId'] ?? '')) {
                        $v['statut'] = 'encours';
                        break;
                    }
                }
                unset($v);
                break;
            }
        }
        unset($l);
    } elseif ($postAction === 'prolonger') {
        $locId = $_POST['location_id'] ?? '';
        $heuresAjout = max(1, (int)($_POST['heures_ajout'] ?? 1));
        foreach ($locations as &$l) {
            if (($l['id'] ?? '') === $locId && in_array($l['statut'] ?? '', ['reserve', 'encours'], true)) {
                $l['dureeHeures'] = (int)($l['dureeHeures'] ?? 1) + $heuresAjout;
                $tarifUnitaire = 0;
                foreach ($vehicules as $v) {
                    if (($v['id'] ?? '') === ($l['vehiculeId'] ?? '')) {
                        $tarifUnitaire = (float)($v['tarifHeure'] ?? 0);
                        break;
                    }
                }
                if ($tarifUnitaire <= 0 && (int)($l['dureeHeures'] ?? 1) > $heuresAjout) {
                    $tarifUnitaire = (float)($l['montant'] ?? 0) / ((int)$l['dureeHeures'] - $heuresAjout);
                }
                $l['montant'] = (int)$l['dureeHeures'] * $tarifUnitaire;
                break;
            }
        }
        unset($l);
    } elseif ($postAction === 'incident') {
        $locId = $_POST['location_id'] ?? '';
        $desc = trim($_POST['description'] ?? 'Incident signalé');
        foreach ($locations as &$l) {
            if (($l['id'] ?? '') === $locId) {
                $l['incident'] = $desc;
                $l['statut'] = 'termine';
                foreach ($vehicules as &$v) {
                    if (($v['id'] ?? '') === ($l['vehiculeId'] ?? '')) {
                        $v['statut'] = 'maintenance';
                        break;
                    }
                }
                unset($v);
                break;
            }
        }
        unset($l);
    } elseif ($postAction === 'terminer') {
        $locId = $_POST['location_id'] ?? '';
        $note = max(1, min(5, (int)($_POST['note'] ?? 5)));
        foreach ($locations as &$l) {
            if (($l['id'] ?? '') === $locId) {
                $l['statut'] = 'termine';
                $l['noteDonnee'] = $note;
                foreach ($vehicules as &$v) {
                    if (($v['id'] ?? '') === ($l['vehiculeId'] ?? '')) {
                        $v['statut'] = 'disponible';
                        $v['station'] = $l['stationRetour'] ?? $v['station'];
                        $v['avisCount'] = (int)($v['avisCount'] ?? 0) + 1;
                        break;
                    }
                }
                unset($v);
                break;
            }
        }
        unset($l);
    } elseif ($postAction === 'annuler') {
        $locId = $_POST['location_id'] ?? '';
        foreach ($locations as &$l) {
            if (($l['id'] ?? '') === $locId) {
                $l['statut'] = 'termine';
                $l['annulee'] = true;
                foreach ($vehicules as &$v) {
                    if (($v['id'] ?? '') === ($l['vehiculeId'] ?? '') && in_array($v['statut'] ?? '', ['reserve', 'encours'], true)) {
                        $v['statut'] = 'disponible';
                        break;
                    }
                }
                unset($v);
                break;
            }
        }
        unset($l);
    }

    if ($messageErreur === '') {
        $state['vehicules'] = $vehicules;
        $state['locations'] = $locations;
        $model->saveState($state);
        header('Location: index.php?page=mobilite');
        exit;
    }
}

$titresAction = [
    'reserver' => 'Réserver un véhicule',
    'demarrer' => 'Déverrouiller / Démarrer une location',
    'prolonger' => 'Prolonger une location',
    'incident' => 'Signaler un incident',
    'terminer' => 'Terminer et noter une location',
    'annuler' => 'Annuler une location'
];
$titrePage = $titresAction[$action] ?? 'Action Mobilité';
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include __DIR__ . '/../includes/head.php'; ?>
		<title><?= htmlspecialchars($titrePage) ?> - URBIX</title>
	</head>
	<body class="is-preload">
		<div id="page-wrapper">

			<header id="header">
				<div class="logo container">
					<div>
						<h1><a href="index.php?page=mobilite" id="logo">URBIX</a></h1>
						<p>Mobilité urbaine — <?= htmlspecialchars($titrePage) ?></p>
					</div>
				</div>
			</header>

			<?php include __DIR__ . '/../includes/navbar.php'; ?>

			<section id="main">
				<div class="container">
					<div class="row gtr-200">
						<div class="col-12">
							<section class="box">
								<h2 class="major"><span><?= htmlspecialchars($titrePage) ?></span></h2>

								<div style="margin-bottom:1.2em; display:flex; gap:0.5em; flex-wrap:wrap;">
									<a href="index.php?page=mobilite" class="button alt small">← Retour Mobilité</a>
									<a href="index.php?page=mobilite-reserver" class="button <?= $action === 'reserver' ? '' : 'alt' ?> small">Réserver</a>
									<a href="index.php?page=mobilite-demarrer" class="button <?= $action === 'demarrer' ? '' : 'alt' ?> small">Déverrouiller</a>
									<a href="index.php?page=mobilite-prolonger" class="button <?= $action === 'prolonger' ? '' : 'alt' ?> small">Prolonger</a>
									<a href="index.php?page=mobilite-incident" class="button <?= $action === 'incident' ? '' : 'alt' ?> small">Incident</a>
									<a href="index.php?page=mobilite-terminer" class="button <?= $action === 'terminer' ? '' : 'alt' ?> small">Terminer</a>
									<a href="index.php?page=mobilite-annuler" class="button <?= $action === 'annuler' ? '' : 'alt' ?> small">Annuler</a>
								</div>

								<?php if ($messageErreur !== ''): ?>
									<p style="color:#b42318; font-weight:700;"><?= htmlspecialchars($messageErreur) ?></p>
								<?php endif; ?>

								<?php if ($action === 'reserver'): ?>
									<form method="POST" action="index.php?page=mobilite-reserver">
										<input type="hidden" name="action_type" value="reserver" />
										<div class="row gtr-50">
											<div class="col-6 col-12-small">
												<label for="vehicule_id">Véhicule disponible</label>
												<select name="vehicule_id" id="vehicule_id">
													<?php foreach ($vehicules as $v): ?>
														<?php if (($v['statut'] ?? '') === 'disponible'): ?>
															<option value="<?= htmlspecialchars($v['id']) ?>" <?= ($v['id'] === $selectedVehiculeId) ? 'selected' : '' ?>>
																<?= htmlspecialchars($v['nom']) ?> — <?= htmlspecialchars((string)$v['tarifHeure']) ?> DT/h (<?= htmlspecialchars($v['station']) ?>)
															</option>
														<?php endif; ?>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="col-6 col-12-small">
												<label for="station_retour">Station de retour</label>
												<select name="station_retour" id="station_retour">
													<option value="Station Centre Ville">Station Centre Ville</option>
													<option value="Station Campus Nord">Station Campus Nord</option>
													<option value="Station Gare Centrale">Station Gare Centrale</option>
													<option value="Station Parc Urbain">Station Parc Urbain</option>
												</select>
											</div>
											<div class="col-6 col-12-small">
												<label for="duree_heures">Durée (heures)</label>
												<input type="number" name="duree_heures" id="duree_heures" min="1" max="24" value="1" required />
											</div>
											<div class="col-12">
												<button type="submit" class="button">Confirmer la réservation</button>
											</div>
										</div>
									</form>
								<?php else: ?>
									<form method="POST" action="index.php?page=mobilite-<?= htmlspecialchars($action) ?>">
										<input type="hidden" name="action_type" value="<?= htmlspecialchars($action) ?>" />
										<div class="row gtr-50">
											<div class="col-6 col-12-small">
												<label for="location_id">Location concernée</label>
												<select name="location_id" id="location_id" required>
													<?php foreach ($locations as $l): ?>
														<option value="<?= htmlspecialchars($l['id']) ?>" <?= ($l['id'] === $selectedLocationId) ? 'selected' : '' ?>>
															<?= htmlspecialchars($l['id']) ?> — <?= htmlspecialchars($l['vehiculeNom']) ?> (<?= htmlspecialchars($l['statut']) ?>, PIN: <?= htmlspecialchars($l['codePin']) ?>)
														</option>
													<?php endforeach; ?>
												</select>
											</div>

											<?php if ($action === 'demarrer'): ?>
												<div class="col-6 col-12-small">
													<label for="code_pin">Code PIN</label>
													<input type="text" name="code_pin" id="code_pin" maxlength="4" placeholder="Ex: 1234" required />
												</div>
											<?php elseif ($action === 'prolonger'): ?>
												<div class="col-6 col-12-small">
													<label for="heures_ajout">Heures supplémentaires</label>
													<input type="number" name="heures_ajout" id="heures_ajout" min="1" max="12" value="1" required />
												</div>
											<?php elseif ($action === 'incident'): ?>
												<div class="col-6 col-12-small">
													<label for="description">Description de l'incident</label>
													<input type="text" name="description" id="description" placeholder="Ex: Batterie faible / crevaison" required />
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

			<?php include __DIR__ . '/../includes/footer.php'; ?>
		</div>

		<?php include __DIR__ . '/../includes/auth-modal.php'; ?>
		<?php include __DIR__ . '/../includes/scripts.php'; ?>
	</body>
</html>