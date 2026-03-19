<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (currentUser()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = postParam('username') ?? '';
    $password = postParam('password') ?? '';

    if ($username === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $user = authenticate($username, $password);
        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;
            redirect('index.php');
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Helpdesk</title>
    <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>
<nav>
    <span class="brand">🎓 Helpdesk</span>
</nav>

<div class="container login-wrapper">
    <div class="login-box">
        <div class="card">
            <div class="login-logo">🎓</div>
            <h1 class="login-title">Helpdesk</h1>
            <p class="login-subtitle">Connectez-vous pour accéder à l'application</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('login.php') ?>">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username"
                           value="<?= h(postParam('username') ?? '') ?>"
                           autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password"
                           autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                    Se connecter
                </button>
            </form>

            <div class="login-hint">
                <strong>Comptes de test :</strong><br>
                Étudiant : <code>etudiant1</code> / <code>password</code><br>
                Tuteur : <code>tuteur1</code> / <code>password</code>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">
    Helpdesk &copy; <?= date('Y') ?> — Mini application PHP
</footer>
</body>
</html>
