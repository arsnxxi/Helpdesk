<?php
// ── Session ─────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Dossier data ─────────────────────────────────────────────────
// On essaie d'abord le dossier data/ local.
// Si le serveur n'autorise pas l'écriture (cas fréquent en hébergement fac),
// on bascule automatiquement sur /tmp qui est toujours accessible.
$_localData = __DIR__ . '/../data/';

if (is_writable($_localData)) {
    define('DATA_DIR', $_localData);
} else {
    // Nom de dossier unique basé sur le chemin du projet → pas de collision
    $_tmpDir = sys_get_temp_dir() . '/helpdesk_' . md5(__DIR__) . '/';
    if (!is_dir($_tmpDir)) {
        mkdir($_tmpDir, 0700, true);
    }
    define('DATA_DIR', $_tmpDir);

    // Copier les fichiers JSON initiaux si absents dans /tmp
    foreach (['users.json', 'tickets.json', 'commentaires.json'] as $_f) {
        $src = __DIR__ . '/../data/' . $_f;
        $dst = DATA_DIR . $_f;
        if (!file_exists($dst) && file_exists($src)) {
            copy($src, $dst);
        }
    }
}

define('USERS_FILE',        DATA_DIR . 'users.json');
define('TICKETS_FILE',      DATA_DIR . 'tickets.json');
define('COMMENTAIRES_FILE', DATA_DIR . 'commentaires.json');

// ── URL de base ──────────────────────────────────────────────────
$_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_dir      = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
define('BASE_URL', rtrim($_protocol . '://' . $_host . $_dir, '/'));
