<?php
require_once __DIR__ . '/includes/layout.php';

requireLogin();

$user      = currentUser();
$estTuteur = $user['role'] === 'tuteur';

$tickets = $estTuteur ? getAllTickets() : getTicketsByUser((int)$user['id']);

// ── Filtres ─────────────────────────────────────────────────────
$CATEGORIES = ['Cours', 'TD', 'TP'];
$PRIORITES  = ['Basse', 'Moyenne', 'Haute'];
$STATUTS    = ['Ouvert', 'En cours', 'Résolu'];

$filterStatut    = getParam('statut')    ?? '';
$filterPriorite  = getParam('priorite')  ?? '';
$filterCategorie = getParam('categorie') ?? '';
$filterSearch    = getParam('q')         ?? '';

if ($filterStatut    && inAllowed($filterStatut,    $STATUTS))    $tickets = array_filter($tickets, fn($t) => $t['statut']    === $filterStatut);
if ($filterPriorite  && inAllowed($filterPriorite,  $PRIORITES))  $tickets = array_filter($tickets, fn($t) => $t['priorite']  === $filterPriorite);
if ($filterCategorie && inAllowed($filterCategorie, $CATEGORIES)) $tickets = array_filter($tickets, fn($t) => $t['categorie'] === $filterCategorie);
if ($filterSearch !== '') {
    $q = mb_strtolower($filterSearch);
    $tickets = array_filter($tickets, fn($t) =>
        str_contains(mb_strtolower($t['titre']), $q) ||
        str_contains(mb_strtolower($t['description']), $q)
    );
}

usort($tickets, fn($a, $b) => strcmp($b['date_creation'], $a['date_creation']));
$tickets = array_values($tickets);

// ── Stats tuteur ────────────────────────────────────────────────
$allTickets = getAllTickets();
$stats = [
    'total'   => count($allTickets),
    'ouvert'  => count(array_filter($allTickets, fn($t) => $t['statut'] === 'Ouvert')),
    'encours' => count(array_filter($allTickets, fn($t) => $t['statut'] === 'En cours')),
    'resolu'  => count(array_filter($allTickets, fn($t) => $t['statut'] === 'Résolu')),
];

$hasFilters = $filterStatut || $filterPriorite || $filterCategorie || $filterSearch;

renderHeader('Tableau de bord');
?>

<?php if ($estTuteur): ?>
<div class="stats-grid">
    <div class="stat-card stat-total">
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Total tickets</div>
    </div>
    <div class="stat-card stat-open">
        <div class="stat-value"><?= $stats['ouvert'] ?></div>
        <div class="stat-label">Ouverts</div>
    </div>
    <div class="stat-card stat-progress">
        <div class="stat-value"><?= $stats['encours'] ?></div>
        <div class="stat-label">En cours</div>
    </div>
    <div class="stat-card stat-resolved">
        <div class="stat-value"><?= $stats['resolu'] ?></div>
        <div class="stat-label">Résolus</div>
    </div>
</div>
<?php endif; ?>

<div class="page-header">
    <h1><?= $estTuteur ? 'Tous les tickets' : 'Mes tickets' ?></h1>
    <?php if (!$estTuteur): ?>
        <a href="<?= url('nouveau_ticket.php') ?>" class="btn btn-primary">+ Nouveau ticket</a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<form method="GET" action="<?= url('index.php') ?>">
    <div class="filters">
        <div class="form-group">
            <label>Recherche</label>
            <input type="text" name="q" placeholder="Titre ou description…" value="<?= h($filterSearch) ?>">
        </div>
        <div class="form-group">
            <label>Statut</label>
            <select name="statut">
                <option value="">Tous les statuts</option>
                <?php foreach ($STATUTS as $s): ?>
                    <option value="<?= h($s) ?>" <?= $filterStatut === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Priorité</label>
            <select name="priorite">
                <option value="">Toutes</option>
                <?php foreach ($PRIORITES as $p): ?>
                    <option value="<?= h($p) ?>" <?= $filterPriorite === $p ? 'selected' : '' ?>><?= h($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="categorie">
                <option value="">Toutes</option>
                <?php foreach ($CATEGORIES as $c): ?>
                    <option value="<?= h($c) ?>" <?= $filterCategorie === $c ? 'selected' : '' ?>><?= h($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filtrer</button>
        <?php if ($hasFilters): ?>
            <a href="<?= url('index.php') ?>" class="btn btn-secondary">✕ Réinitialiser</a>
        <?php endif; ?>
    </div>
</form>

<!-- Tableau -->
<div class="table-wrapper">
    <?php if (empty($tickets)): ?>
        <div class="table-empty">
            <?= $hasFilters ? 'Aucun ticket ne correspond aux filtres.' : 'Aucun ticket pour le moment.' ?>
        </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Titre</th>
                <?php if ($estTuteur): ?><th>Auteur</th><?php endif; ?>
                <th>Catégorie</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.85rem">#<?= (int)$t['id'] ?></td>
                <td>
                    <a href="<?= url('ticket.php?id=' . (int)$t['id']) ?>"
                       style="text-decoration:none;color:var(--text);font-weight:600">
                        <?= h($t['titre']) ?>
                    </a>
                </td>
                <?php if ($estTuteur): ?>
                    <td style="font-size:.88rem"><?= h($t['auteur_nom']) ?></td>
                <?php endif; ?>
                <td style="font-size:.88rem"><?= h($t['categorie']) ?></td>
                <td><span class="badge <?= h(prioriteBadgeClass($t['priorite'])) ?>"><?= h($t['priorite']) ?></span></td>
                <td><span class="badge <?= h(statutBadgeClass($t['statut'])) ?>"><?= h($t['statut']) ?></span></td>
                <td style="font-size:.85rem;color:var(--text-muted);white-space:nowrap"><?= h(substr($t['date_creation'], 0, 10)) ?></td>
                <td><a href="<?= url('ticket.php?id=' . (int)$t['id']) ?>" class="btn btn-secondary btn-xs">Voir →</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<p style="color:var(--text-muted);font-size:.82rem">
    <?= count($tickets) ?> ticket<?= count($tickets) > 1 ? 's' : '' ?> affiché<?= count($tickets) > 1 ? 's' : '' ?>
</p>

<?php renderFooter(); ?>
