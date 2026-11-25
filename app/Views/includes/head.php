<title><?= htmlspecialchars($pageTitle ?? 'Smart City') ?></title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
<link rel="stylesheet" href="<?= ASSETS_URL ?>assets/css/main.css?v=4" />
<link rel="stylesheet" href="<?= ASSETS_URL ?>assets/css/covoiturage.css?v=1" />
<?php foreach (($extraCss ?? []) as $feuille): ?>
<link rel="stylesheet" href="<?= ASSETS_URL . $feuille ?>?v=1" />
<?php endforeach; ?>
