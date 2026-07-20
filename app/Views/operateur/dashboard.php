<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Tableau de bord opérateur</h1>
        <p class="text-muted mb-0">Vue rapide de l’activité Mobile Money.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Clients</h2>
                <p class="display-6 mb-3"><?= esc($clientsCount) ?></p>
                <a class="btn btn-primary" href="/operateur/comptes">Voir</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Dépôts</h2>
                <p class="display-6 mb-3"><?= esc($depotsCount) ?></p>
                <a class="btn btn-primary" href="/operateur/depots">Voir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Retraits</h2>
                <p class="display-6 mb-3"><?= esc($retraitsCount) ?></p>
                <a class="btn btn-primary" href="/operateur/retraits">Voir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Transferts</h2>
                <p class="display-6 mb-3"><?= esc($transfertsCount) ?></p>
                <a class="btn btn-primary" href="/operateur/transferts">Voir</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
