<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$candidats = [
    __DIR__ . '/../../../_template/mobilite_urbix/backoffice_mobilite.html',
    __DIR__ . '/../../../_template/backoffice_mobilite.html',
    __DIR__ . '/../../../backoffice/index.html',
    __DIR__ . '/../../../backoffice/backoffice_mobilite.html',
];

$ancienBackoffice = null;
foreach ($candidats as $chemin) {
    if (file_exists($chemin)) {
        $ancienBackoffice = $chemin;
        break;
    }
}

if ($ancienBackoffice !== null) {
    $html = file_get_contents($ancienBackoffice);

    $remplacements = [
        'href="../assets/'          => 'href="_template/assets/',
        'src="../assets/'           => 'src="_template/assets/',
        'src="../images/'           => 'src="_template/images/',
        'href="mobilite.html"'      => 'href="index.php?page=mobilite"',
        'href="../mobilite.html"'   => 'href="index.php?page=mobilite"',
        'href="../index.html"'      => 'href="index.php?page=home"',
    ];

    $html = str_replace(array_keys($remplacements), array_values($remplacements), $html);
    echo $html;
} else {
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
        <meta charset="utf-8" />
        <title>Back-Office introuvable - URBIX</title>
    </head>
    <body>
        <h2>Ancien fichier back-office introuvable</h2>
        <p>Vérifie l'emplacement de ton ancien fichier HTML dans <code>C:\xampp\htdocs\ProjetSmartCity\</code> :</p>
        <ul>
            <?php foreach ($candidats as $c): ?>
                <li><code><?= htmlspecialchars(realpath($c) ?: $c) ?></code></li>
            <?php endforeach; ?>
        </ul>
        <p><a href="index.php?page=mobilite">Retour à la mobilité</a></p>
    </body>
    </html>
    <?php
}