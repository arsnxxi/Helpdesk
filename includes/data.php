<?php
require_once __DIR__ . '/config.php';

function jsonRead(string $file): array {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    if ($content === false) return [];
    return json_decode($content, true) ?? [];
}

function jsonWrite(string $file, array $data): bool {
    // Créer le dossier data/ s'il n'existe pas
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Créer le fichier s'il n'existe pas
    if (!file_exists($file)) {
        touch($file);
        chmod($file, 0644);
    }

    $result = file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX  // verrou pour éviter les écritures concurrentes
    );

    return $result !== false;
}

function getAllTickets(): array {
    return jsonRead(TICKETS_FILE);
}

function getTicketById(int $id): ?array {
    foreach (getAllTickets() as $ticket) {
        if ((int)$ticket['id'] === $id) return $ticket;
    }
    return null;
}

function getTicketsByUser(int $userId): array {
    return array_values(array_filter(getAllTickets(), fn($t) => (int)$t['auteur_id'] === $userId));
}

function createTicket(array $data): int|false {
    $tickets = getAllTickets();
    $id = count($tickets) ? max(array_column($tickets, 'id')) + 1 : 1;
    $tickets[] = [
        'id'            => $id,
        'auteur_id'     => $data['auteur_id'],
        'auteur_nom'    => $data['auteur_nom'],
        'titre'         => $data['titre'],
        'description'   => $data['description'],
        'categorie'     => $data['categorie'],
        'priorite'      => $data['priorite'],
        'statut'        => 'Ouvert',
        'date_creation' => date('Y-m-d H:i:s'),
    ];

    if (!jsonWrite(TICKETS_FILE, $tickets)) {
        return false; // Échec d'écriture
    }
    return $id;
}

function updateTicketStatut(int $id, string $statut): bool {
    $tickets = getAllTickets();
    foreach ($tickets as &$ticket) {
        if ((int)$ticket['id'] === $id) {
            $ticket['statut'] = $statut;
            return jsonWrite(TICKETS_FILE, $tickets);
        }
    }
    return false;
}

function getCommentairesByTicket(int $ticketId): array {
    $all = jsonRead(COMMENTAIRES_FILE);
    return array_values(array_filter($all, fn($c) => (int)$c['ticket_id'] === $ticketId));
}

function addCommentaire(array $data): bool {
    $commentaires = jsonRead(COMMENTAIRES_FILE);
    $id = count($commentaires) ? max(array_column($commentaires, 'id')) + 1 : 1;
    $commentaires[] = [
        'id'        => $id,
        'ticket_id' => $data['ticket_id'],
        'auteur'    => $data['auteur'],
        'message'   => $data['message'],
        'date'      => date('Y-m-d H:i:s'),
    ];
    return jsonWrite(COMMENTAIRES_FILE, $commentaires);
}
