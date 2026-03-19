<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/data.php';

function renderHeader(string $title = 'Helpdesk'): void {
    $user  = currentUser();
    $flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> — Helpdesk</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>

<nav>
    <a href="<?= url('index.php') ?>" class="brand">🎓 Helpdesk</a>
    <?php if ($user): ?>
    <div class="nav-links">
        <a href="<?= url('index.php') ?>"><?= $user['role'] === 'tuteur' ? 'Tous les tickets' : 'Mes tickets' ?></a>
        <?php if ($user['role'] === 'etudiant'): ?>
            <a href="<?= url('nouveau_ticket.php') ?>" class="nav-cta">+ Nouveau ticket</a>
        <?php endif; ?>
        <span class="nav-sep">|</span>
        <span class="nav-user">👤 <?= h($user['nom']) ?></span>
        <a href="<?= url('logout.php') ?>" class="nav-logout">Déconnexion</a>
    </div>
    <?php endif; ?>
</nav>

<div class="container">

<?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?>">
        <?= h($flash['message']) ?>
    </div>
<?php endif; ?>

<?php
}

function renderFooter(): void {
?>
</div><!-- /.container -->

<footer class="site-footer">
    <span>Helpdesk &copy; <?= date('Y') ?> — Mini application PHP</span>
</footer>

</body>
</html>
<?php
}
