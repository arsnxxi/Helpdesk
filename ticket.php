<?php
require_once __DIR__ . '/includes/layout.php';

requireLogin();
$user = currentUser();

// ── Validation paramètre id ──────────────────────────────────────
$idParam = getParam('id');
if ($idParam === null || !ctype_digit($idParam)) {
    http_response_code(400);
    renderHeader('Erreur');
    echo '<div class="alert alert-error">Identifiant de ticket invalide.</div>';
    echo '<a href="' . url('index.php') . '" class="btn btn-secondary">← Retour</a>';
    renderFooter(); exit;
}

$ticket = getTicketById((int)$idParam);
if ($ticket === null) {
    http_response_code(404);
    renderHeader('Introuvable');
    echo '<div class="alert alert-error">Ce ticket n\'existe pas.</div>';
    echo '<a href="' . url('index.php') . '" class="btn btn-secondary">← Retour</a>';
    renderFooter(); exit;
}

// Un étudiant ne peut voir que ses propres tickets
if ($user['role'] === 'etudiant' && (int)$ticket['auteur_id'] !== (int)$user['id']) {
    http_response_code(403);
    renderHeader('Accès refusé');
    echo '<div class="alert alert-error">Vous n\'êtes pas autorisé à consulter ce ticket.</div>';
    echo '<a href="' . url('index.php') . '" class="btn btn-secondary">← Retour</a>';
    renderFooter(); exit;
}

$STATUTS = ['Ouvert', 'En cours', 'Résolu'];
$errors  = [];

// ── Mise à jour statut (tuteur) ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('action') === 'statut') {
    if ($user['role'] !== 'tuteur') { http_response_code(403); die('Accès refusé.'); }
    $statut = postParam('statut') ?? '';
    if (!inAllowed($statut, $STATUTS)) {
        $errors[] = 'Statut invalide.';
    } else {
        updateTicketStatut((int)$ticket['id'], $statut);
        setFlash('success', 'Statut mis à jour : ' . $statut);
        redirect('ticket.php?id=' . (int)$ticket['id']);
    }
}

// ── Ajout commentaire ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && postParam('action') === 'commentaire') {
    $message = postParam('message') ?? '';
    if (mb_strlen($message) < 2) {
        $errors[] = 'Le commentaire ne peut pas être vide.';
    } else {
        addCommentaire([
            'ticket_id' => (int)$ticket['id'],
            'auteur'    => $user['nom'],
            'message'   => $message,
        ]);
        setFlash('success', 'Commentaire ajouté.');
        redirect('ticket.php?id=' . (int)$ticket['id'] . '#commentaires');
    }
}

$ticket       = getTicketById((int)$ticket['id']);
$commentaires = getCommentairesByTicket((int)$ticket['id']);

function initiale(string $nom): string {
    return mb_strtoupper(mb_substr(explode(' ', $nom)[0], 0, 1));
}

renderHeader('Ticket #' . $ticket['id']);
?>

<div class="breadcrumb">
    <a href="<?= url('index.php') ?>"><?= $user['role'] === 'tuteur' ? 'Tous les tickets' : 'Mes tickets' ?></a>
    <span class="sep">›</span>
    <span>Ticket #<?= (int)$ticket['id'] ?></span>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><?= h(implode(' — ', $errors)) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h1 style="margin-bottom:0"><?= h($ticket['titre']) ?></h1>
        <div style="display:flex;gap:.5rem;align-items:center">
            <span class="badge <?= h(prioriteBadgeClass($ticket['priorite'])) ?>"><?= h($ticket['priorite']) ?></span>
            <span class="badge <?= h(statutBadgeClass($ticket['statut'])) ?>"><?= h($ticket['statut']) ?></span>
        </div>
    </div>

    <div class="ticket-meta">
        <div class="meta-item">👤 <strong><?= h($ticket['auteur_nom']) ?></strong></div>
        <div class="meta-item">📂 <strong><?= h($ticket['categorie']) ?></strong></div>
        <div class="meta-item">🕐 <strong><?= h(formatDate($ticket['date_creation'])) ?></strong></div>
    </div>

    <p class="ticket-description"><?= nl2br(h($ticket['description'])) ?></p>

    <?php if ($user['role'] === 'tuteur'): ?>
    <div style="margin-top:1.2rem;padding-top:1.2rem;border-top:1px solid var(--border)">
        <form method="POST" action="<?= url('ticket.php?id=' . (int)$ticket['id']) ?>" class="statut-form">
            <input type="hidden" name="action" value="statut">
            <label style="margin:0;white-space:nowrap;font-size:.88rem">Changer le statut :</label>
            <select name="statut">
                <?php foreach ($STATUTS as $s): ?>
                    <option value="<?= h($s) ?>" <?= $ticket['statut'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="card" id="commentaires">
    <h2>💬 Commentaires (<?= count($commentaires) ?>)</h2>

    <div class="comments-list">
        <?php if (empty($commentaires)): ?>
            <p style="color:var(--text-muted);font-size:.9rem">Aucun commentaire pour l'instant.</p>
        <?php else: ?>
            <?php foreach ($commentaires as $c): ?>
            <div class="comment">
                <div class="comment-avatar"><?= h(initiale($c['auteur'])) ?></div>
                <div class="comment-body">
                    <div class="comment-meta">
                        <strong><?= h($c['auteur']) ?></strong>
                        <span><?= h(formatDate($c['date'])) ?></span>
                    </div>
                    <p><?= nl2br(h($c['message'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="comment-form">
        <h3>Ajouter un commentaire</h3>
        <form method="POST" action="<?= url('ticket.php?id=' . (int)$ticket['id']) ?>#commentaires">
            <input type="hidden" name="action" value="commentaire">
            <div class="form-group">
                <textarea name="message" rows="3"
                          placeholder="Votre réponse, précision ou question…" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Envoyer le commentaire</button>
        </form>
    </div>
</div>

<?php renderFooter(); ?>
