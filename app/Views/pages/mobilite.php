<?php
$vehicules = $state['vehicules'] ?? [];
$locations = $state['locations'] ?? [];
$filtreActuel = $state['filtre'] ?? 'moto';
$vehiculesFiltres = array_values(array_filter($vehicules, function ($v) use ($filtreActuel) {
    return $filtreActuel === 'tous' || ($v['type'] ?? '') === $filtreActuel;
}));
?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php include __DIR__ . '/../includes/head.php'; ?>
		<title>Location Moto, Trottinette &amp; Vélo - URBIX</title>
		<style>
			.choix-type-bar { display: flex; flex-wrap: wrap; gap: 0.6em; margin-bottom: 1.5em; align-items: center; }
			.badge-statut { display: inline-block; padding: 0.25em 0.7em; border-radius: 4px; font-size: 0.8em; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
			.statut-disponible { background: #e6f7ed; color: #1e7e46; }
			.statut-reserve { background: #fff4e5; color: #b25e09; }
			.statut-encours { background: #e6f0ff; color: #1d5bbf; }
			.statut-maintenance { background: #fdecec; color: #b42318; }
			.statut-termine { background: #f0f1f3; color: #475467; }
			.vehicule-meta { list-style: none; padding: 0; margin: 0.5em 0 1em 0; font-size: 0.9em; }
			.vehicule-meta li { margin-bottom: 0.35em; }
			.carte-location-box { border: 1px solid #dfe3e6; border-radius: 6px; padding: 1.2em; margin-bottom: 1.5em; background: #ffffff; }
			.historique-item { border-bottom: 1px solid #e5e5e5; padding: 0.9em 0; }
			.historique-item:last-child { border-bottom: none; }
			.actions-ligne { display: flex; flex-wrap: wrap; gap: 0.45em; margin-top: 0.6em; }
			.actions-ligne .button.small { padding: 0.35em 0.75em; font-size: 0.75em; height: auto; line-height: 1.4; }
		</style>
	</head>
	<body class="is-preload">
		<div id="page-wrapper">

			<header id="header">
				<div class="logo container">
					<div>
						<h1><a href="index.php?page=mobilite" id="logo">URBIX</a></h1>
						<p>Mobilité urbaine — Moto, Trottinette &amp; Vélo</p>
					</div>
				</div>
			</header>

			<?php include __DIR__ . '/../includes/navbar.php'; ?>

			<section id="main">
				<div class="container">
					<div class="row gtr-200">

						<div class="col-12">
							<section class="box">
								<h2 class="major"><span>Choisir un type et une location</span></h2>

								<div class="choix-type-bar">
									<a href="index.php?page=mobilite&type=moto" class="button small <?= $filtreActuel === 'moto' ? '' : 'alt' ?>">Moto</a>
									<a href="index.php?page=mobilite&type=trottinette" class="button small <?= $filtreActuel === 'trottinette' ? '' : 'alt' ?>">Trottinette</a>
									<a href="index.php?page=mobilite&type=velo" class="button small <?= $filtreActuel === 'velo' ? '' : 'alt' ?>">Vélo</a>
									<a href="index.php?page=mobilite&type=tous" class="button small <?= $filtreActuel === 'tous' ? '' : 'alt' ?>">Tous</a>
								</div>

								<?php foreach ($vehiculesFiltres as $v): ?>
									<?php $statut = htmlspecialchars($v['statut'] ?? 'disponible'); ?>
									<div class="carte-location-box">
										<div class="row gtr-50">
											<div class="col-4 col-12-small">
												<span class="image fit" style="margin:0;">
													<img src="app/Views/images/<?= htmlspecialchars($v['image'] ?? 'urbix-moto.png') ?>" alt="<?= htmlspecialchars($v['nom'] ?? '') ?>" />
												</span>
											</div>
											<div class="col-8 col-12-small">
												<h3 style="margin-bottom:0.25em;"><?= htmlspecialchars($v['nom'] ?? '') ?></h3>
												<p style="margin-bottom:0.4em;">
													<span class="badge-statut statut-<?= $statut ?>"><?= $statut ?></span>
													— <strong><?= htmlspecialchars((string)($v['tarifHeure'] ?? 0)) ?> DT / heure</strong>
												</p>
												<ul class="vehicule-meta">
													<li><strong>Type :</strong> <?= strtoupper(htmlspecialchars($v['type'] ?? '')) ?></li>
													<li><strong>Station :</strong> <?= htmlspecialchars($v['station'] ?? '') ?></li>
													<li><strong>Batterie :</strong> <?= htmlspecialchars($v['autonomie'] ?? '100%') ?> — <strong>Note :</strong> <?= number_format((float)($v['noteMoyenne'] ?? 5), 1) ?>/5 (<?= (int)($v['avisCount'] ?? 0) ?> avis)</li>
												</ul>
												<?php if (($v['statut'] ?? '') === 'disponible'): ?>
													<a href="index.php?page=mobilite-reserver&vehicule=<?= urlencode($v['id'] ?? '') ?>" class="button small">Choisir et réserver</a>
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
								<?php if (empty($locations)): ?>
									<p>Aucune location enregistrée pour le moment.</p>
								<?php else: ?>
									<?php foreach ($locations as $l): ?>
										<div class="historique-item">
											<div>
												<strong><?= htmlspecialchars($l['id'] ?? '') ?></strong> — <strong><?= htmlspecialchars($l['vehiculeNom'] ?? '') ?></strong>
												<span class="badge-statut statut-<?= htmlspecialchars($l['statut'] ?? 'termine') ?>"><?= htmlspecialchars($l['statut'] ?? '') ?></span>
											</div>
											<p style="margin:0.4em 0;">
												<?= htmlspecialchars($l['stationDepart'] ?? '') ?> → <?= htmlspecialchars($l['stationRetour'] ?? '') ?> |
												Durée : <strong><?= htmlspecialchars((string)($l['dureeHeures'] ?? 1)) ?>h</strong> |
												Total : <strong><?= number_format((float)($l['montant'] ?? 0), 2) ?> DT</strong> |
												PIN : <strong><?= htmlspecialchars($l['codePin'] ?? '') ?></strong>
											</p>
											<div class="actions-ligne">
												<a href="index.php?page=mobilite-demarrer&loc=<?= urlencode($l['id'] ?? '') ?>" class="button small">Déverrouiller</a>
												<a href="index.php?page=mobilite-prolonger&loc=<?= urlencode($l['id'] ?? '') ?>" class="button small">Prolonger</a>
												<a href="index.php?page=mobilite-incident&loc=<?= urlencode($l['id'] ?? '') ?>" class="button alt small">Incident</a>
												<a href="index.php?page=mobilite-terminer&loc=<?= urlencode($l['id'] ?? '') ?>" class="button small">Terminer</a>
												<a href="index.php?page=mobilite-annuler&loc=<?= urlencode($l['id'] ?? '') ?>" class="button alt small">Annuler</a>
											</div>
										</div>
									<?php endforeach; ?>
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