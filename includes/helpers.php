<?php

/**
 * Échappe une chaîne pour l'affichage HTML (protection XSS)
 */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Construit une URL absolue à partir du BASE_URL défini dans config.php
 * url('index.php')          → http://serveur.univ.fr/~user/helpdesk/index.php
 * url('ticket.php?id=3')    → http://serveur.univ.fr/~user/helpdesk/ticket.php?id=3
 */
function url(string $path = ''): string {
    return BASE_URL . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

/**
 * Redirige vers une page du projet et stoppe l'exécution
 */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

/**
 * Retourne un paramètre GET nettoyé ou null
 */
function getParam(string $key): ?string {
    return isset($_GET[$key]) && $_GET[$key] !== '' ? trim($_GET[$key]) : null;
}

/**
 * Retourne un paramètre POST nettoyé ou null
 */
function postParam(string $key): ?string {
    return isset($_POST[$key]) && $_POST[$key] !== '' ? trim($_POST[$key]) : null;
}

/**
 * Valide qu'une valeur appartient à une liste autorisée
 */
function inAllowed(string $value, array $allowed): bool {
    return in_array($value, $allowed, true);
}

/**
 * Classe CSS du badge selon le statut
 */
function statutBadgeClass(string $statut): string {
    return match($statut) {
        'Ouvert'   => 'badge-open',
        'En cours' => 'badge-progress',
        'Résolu'   => 'badge-resolved',
        default    => ''
    };
}

/**
 * Classe CSS du badge selon la priorité
 */
function prioriteBadgeClass(string $priorite): string {
    return match($priorite) {
        'Haute'   => 'badge-high',
        'Moyenne' => 'badge-medium',
        'Basse'   => 'badge-low',
        default   => ''
    };
}

/**
 * Stocke un message flash en session
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retourne et supprime le message flash courant
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Formate une date Y-m-d H:i:s en format lisible
 */
function formatDate(string $date): string {
    $ts = strtotime($date);
    return $ts ? date('d/m/Y à H\hi', $ts) : $date;
}
