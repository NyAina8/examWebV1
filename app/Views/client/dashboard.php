<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="mb-4">
    <div>
        <h1 class="h3 mb-1">Tableau de bord client</h1>
        <p class="text-muted mb-0">Bienvenue, <?= esc($compte['prenom'] . ' ' . $compte['nom']) ?>.</p>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn btn-primary" href="/client/depot">Faire un dépôt</a>
    <a class="btn btn-outline-primary" href="/client/retrait">Faire un retrait</a>
    <a class="btn btn-outline-primary" href="/client/transfert">Faire un transfert</a>
    <a class="btn btn-outline-primary" href="/client/envoi-multiple">Envoi multiple</a>
    <a class="btn btn-outline-secondary" href="/client/historique">Historique</a>
</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Client</h2>
                <p class="h5 mb-0"><?= esc($compte['prenom'] . ' ' . $compte['nom']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Numéro</h2>
                <p class="h5 mb-0"><?= esc($compte['numero_telephone']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Solde</h2>
                <p class="h5 mb-0"><?= number_format((int) $solde, 0, ',', ' ') ?> Ar</p>
                <a class="btn btn-sm btn-outline-primary mt-3" href="/client/solde">Détail</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">État du compte</h2>
                <span class="badge text-bg-<?= $compte['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                    <?= $compte['statut'] === 'actif' ? 'Actif' : 'Désactivé' ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
