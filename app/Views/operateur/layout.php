<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Administration Mobile Money') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/operateur">Admin Mobile Money</a>
        <div class="navbar-nav">
            <a class="nav-link" href="/operateur">Tableau de bord</a>
            <a class="nav-link" href="/operateur/baremes">Barèmes</a>
            <a class="nav-link" href="/operateur/comptes">Client</a>
            <a class="nav-link" href="/operateur/depots">Dépôts</a>
            <a class="nav-link" href="/operateur/retraits">Retraits</a>
            <a class="nav-link" href="/operateur/transferts">Transferts</a>
            <a class="nav-link" href="/operateur/gains">Gains</a>
            <a class="nav-link" href="/admin/deconnexion">Déconnexion</a>
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
