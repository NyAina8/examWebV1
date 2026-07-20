<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Situation gain via les différents frais</h1>
        <p class="text-muted mb-0">Séparation des gains internes, commissions externes et montants à reverser.</p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur">Retour</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Gains internes</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainsInternes, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Frais retrait</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainRetrait, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Frais transfert</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainTransfert, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Commissions externes</h2>
                <p class="h4 mb-0"><?= number_format((int) $commissionsExternes, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">À reverser</h2>
                <p class="h4 mb-0"><?= number_format((int) $montantsReverser, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Total général</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainTotal, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Opérateur</th>
            <th class="text-end">Transferts</th>
            <th class="text-end">Montant envoyé</th>
            <th class="text-end">Frais générés</th>
            <th class="text-end">Commission conservée</th>
            <th class="text-end">Montant à reverser</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($operateurs === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="6">Aucun transfert externe enregistré.</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($operateurs as $operateur): ?>
            <tr>
                <td><?= esc($operateur['nom']) ?></td>
                <td class="text-end"><?= number_format((int) $operateur['nombre_transferts'], 0, ',', ' ') ?></td>
                <td class="text-end"><?= number_format((int) $operateur['montant_total'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operateur['frais_generes'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operateur['commission_conservee'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end fw-semibold"><?= number_format((int) $operateur['montant_reverser'], 0, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if ($operateurs !== []): ?>
            <tfoot>
            <tr>
                <th>Total</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['nombre_transferts'], $operateurs)), 0, ',', ' ') ?></th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['montant_total'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['frais_generes'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['commission_conservee'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['montant_reverser'], $operateurs)), 0, ',', ' ') ?> Ar</th>
            </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
<?= $this->endSection() ?>
