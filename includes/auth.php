<?php
require_once __DIR__ . '/config.php';

function requireLogin(): void {
    if (empty($_SESSION['user'])) {
        redirect('login.php');
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function authenticate(string $username, string $password): ?array {
    $users = jsonRead(USERS_FILE);
    foreach ($users as $user) {
        if ($user['username'] !== $username) continue;

        $stored = $user['password'];

        // Si le mot de passe stocké est un hash bcrypt → password_verify
        // Sinon (texte brut) → comparaison directe sécurisée
        $ok = str_starts_with($stored, '$2')
            ? password_verify($password, $stored)
            : hash_equals($stored, $password);

        if ($ok) {
            // Si le mot de passe était en clair, on le hashe automatiquement
            if (!str_starts_with($stored, '$2')) {
                upgradePassword((int)$user['id'], $password);
            }
            return $user;
        }
    }
    return null;
}

/**
 * Remplace le mot de passe en clair par un hash bcrypt dans users.json
 */
function upgradePassword(int $userId, string $plainPassword): void {
    $users = jsonRead(USERS_FILE);
    foreach ($users as &$u) {
        if ((int)$u['id'] === $userId) {
            $u['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);
            break;
        }
    }
    jsonWrite(USERS_FILE, $users);
}
