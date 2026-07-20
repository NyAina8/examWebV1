<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Tableau de bord opérateur</h1>
        <p class="text-muted mb-0">Configuration des préfixes, types d’opérations et barèmes de frais.</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Préfixes téléphoniques</h2>
                <p class="display-6 mb-3"><?= esc($prefixesCount) ?></p>
                <a class="btn btn-primary" href="/operateur/prefixes">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Types d’opérations</h2>
                <p class="display-6 mb-3"><?= esc($activeTypesCount) ?> / <?= esc($typesCount) ?></p>
                <a class="btn btn-primary" href="/operateur/types">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Barèmes de frais</h2>
                <p class="display-6 mb-3"><?= esc($baremesCount) ?></p>
                <a class="btn btn-primary" href="/operateur/baremes">Gérer</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
