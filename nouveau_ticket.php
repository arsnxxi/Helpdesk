<?php
require_once __DIR__ . '/includes/layout.php';

requireRole('etudiant');
$user = currentUser();

$CATEGORIES = ['Cours', 'TD', 'TP'];
$PRIORITES  = ['Basse', 'Moyenne', 'Haute'];
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre       = postParam('titre')       ?? '';
    $description = postParam('description') ?? '';
    $categorie   = postParam('categorie')   ?? '';
    $priorite    = postParam('priorite')    ?? '';

    if (mb_strlen($titre) < 3)               $errors[] = 'Le titre doit faire au moins 3 caractères.';
    if (mb_strlen($titre) > 120)             $errors[] = 'Le titre ne peut dépasser 120 caractères.';
    if (mb_strlen($description) < 10)        $errors[] = 'La description doit faire au moins 10 caractères.';
    if (!inAllowed($categorie, $CATEGORIES)) $errors[] = 'Catégorie invalide.';
    if (!inAllowed($priorite, $PRIORITES))   $errors[] = 'Priorité invalide.';

    if (empty($errors)) {
        $id = createTicket([
            'auteur_id'   => $user['id'],
            'auteur_nom'  => $user['nom'],
            'titre'       => $titre,
            'description' => $description,
            'categorie'   => $categorie,
            'priorite'    => $priorite,
        ]);

        if ($id === false) {
            // Échec d'écriture → probablement permissions sur data/
            $errors[] = 'Impossible d\'enregistrer le ticket : le dossier "data/" n\'est pas accessible en écriture sur le serveur. 
Faites un clic droit sur le dossier "data" dans votre gestionnaire de fichiers FTP/cPanel et donnez-lui les permissions 755 (ou 777 si 755 ne fonctionne pas). Faites de même pour les fichiers tickets.json, commentaires.json et users.json.';
        } else {
            setFlash('success', 'Ticket #' . $id . ' créé avec succès.');
            redirect('ticket.php?id=' . $id);
        }
    }
}

renderHeader('Nouveau ticket');
?>

<div class="breadcrumb">
    <a href="<?= url('index.php') ?>">Mes tickets</a>
    <span class="sep">›</span>
    <span>Nouveau ticket</span>
</div>

<h1>Créer un ticket</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $e): ?>
            <p style="margin-bottom:.3rem"><?= nl2br(h($e)) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="<?= url('nouveau_ticket.php') ?>">

        <div class="form-group">
            <label for="titre">Titre <span style="color:#c92a2a">*</span></label>
            <input type="text" id="titre" name="titre" maxlength="120"
                   value="<?= h(postParam('titre') ?? '') ?>"
                   placeholder="Décrivez votre problème en une phrase…" required autofocus>
        </div>

        <div class="form-group">
            <label for="description">Description <span style="color:#c92a2a">*</span></label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Donnez le plus de détails possibles (erreur rencontrée, étapes pour reproduire…)" required><?= h(postParam('description') ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="categorie">Catégorie <span style="color:#c92a2a">*</span></label>
                <select id="categorie" name="categorie" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($CATEGORIES as $c): ?>
                        <option value="<?= h($c) ?>" <?= (postParam('categorie') === $c) ? 'selected' : '' ?>><?= h($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="priorite">Priorité <span style="color:#c92a2a">*</span></label>
                <select id="priorite" name="priorite" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($PRIORITES as $p): ?>
                        <option value="<?= h($p) ?>" <?= (postParam('priorite') === $p) ? 'selected' : '' ?>><?= h($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:flex;gap:.8rem;margin-top:.5rem">
            <button type="submit" class="btn btn-primary">Créer le ticket</button>
            <a href="<?= url('index.php') ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php renderFooter(); ?>
