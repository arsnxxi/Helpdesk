<?php
/**
 * PAGE DE DIAGNOSTIC — À SUPPRIMER APRÈS UTILISATION
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/config.php';

function check(string $label, bool $ok, string $detail = ''): void {
    $icon  = $ok ? '✅' : '❌';
    $color = $ok ? '#2f9e44' : '#c92a2a';
    echo "<tr><td>$icon</td><td>" . htmlspecialchars($label) . "</td><td style='color:$color;font-family:monospace;font-size:.85rem'>" . htmlspecialchars($detail) . "</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Helpdesk</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        h1 { color: #3b5bdb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
        th, td { padding: .6rem .8rem; border: 1px solid #dee2e6; text-align: left; }
        th { background: #f1f3f5; }
        .ok  { background: #ebfbee; padding: 1rem; border-radius: 6px; border: 1px solid #b2f2bb; margin-top:1rem }
        .err { background: #fff0f0; padding: 1rem; border-radius: 6px; border: 1px solid #ffc9c9; margin-top:1rem }
        code { background: #f1f3f5; padding: .1rem .4rem; border-radius: 3px; }
    </style>
</head>
<body>
<h1>🔍 Diagnostic Helpdesk</h1>

<table>
    <thead><tr><th>État</th><th>Vérification</th><th>Détail</th></tr></thead>
    <tbody>
    <?php
    check('PHP version',             version_compare(PHP_VERSION, '8.0', '>='), PHP_VERSION);
    check('DATA_DIR utilisé',        true,        DATA_DIR);

    $localData = __DIR__ . '/data/';
    $usingTmp  = (DATA_DIR !== realpath($localData) . '/') && str_contains(DATA_DIR, sys_get_temp_dir());
    check('Dossier data/ local accessible en écriture', is_writable($localData), is_writable($localData) ? 'OK' : 'NON writable → /tmp utilisé à la place');
    check('DATA_DIR accessible en écriture', is_writable(DATA_DIR), is_writable(DATA_DIR) ? 'OK ✓' : 'PROBLÈME');

    foreach (['users.json', 'tickets.json', 'commentaires.json'] as $f) {
        $path = DATA_DIR . $f;
        check("$f existe",     file_exists($path),  $path);
        check("$f lisible",    is_readable($path),  is_readable($path) ? 'OK' : 'NON');
    }

    $testFile = DATA_DIR . '_test.tmp';
    $canWrite = @file_put_contents($testFile, 'ok') !== false;
    if ($canWrite) @unlink($testFile);
    check('Test écriture réel', $canWrite, $canWrite ? 'Écriture OK ✓' : 'ÉCHEC');

    check('BASE_URL', true, BASE_URL);
    check('Chemin projet', true, __DIR__);
    ?>
    </tbody>
</table>

<?php if ($canWrite): ?>
<div class="ok">
    ✅ <strong>Tout fonctionne.</strong>
    <?php if ($usingTmp): ?>
    Les données sont stockées dans <code><?= htmlspecialchars(DATA_DIR) ?></code> (dossier temporaire).
    <?php else: ?>
    Les données sont stockées dans le dossier <code>data/</code> local.
    <?php endif; ?>
</div>
<?php else: ?>
<div class="err">
    ❌ <strong>Impossible d'écrire les données.</strong> Contactez l'administrateur du serveur.
</div>
<?php endif; ?>

</body>
</html>
