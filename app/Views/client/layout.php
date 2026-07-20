<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Espace client') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/client">Mobile Money</a>
        <div class="navbar-nav ms-auto">
            <?php if (session('client_connecte')): ?>
                <a class="nav-link" href="/client">Mon compte</a>
                <a class="nav-link" href="/client/depot">Dépôt</a>
                <a class="nav-link" href="/client/retrait">Retrait</a>
                <a class="nav-link" href="/client/envoi-multiple">Transfert</a>
                <a class="nav-link" href="/client/historique">Historique</a>
                <a class="nav-link" href="/deconnexion">Déconnexion</a>
            <?php else: ?>
                <a class="nav-link" href="/connexion">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (session('success')): ?>
        <div class="alert alert-success"><?= esc(session('success')) ?></div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
