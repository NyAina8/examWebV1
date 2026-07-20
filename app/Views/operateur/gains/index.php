<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Gains via les frais</h1>
        <p class="text-muted mb-0">Les frais de retrait et de transfert sont comptés comme gains.</p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur">Retour</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Gain total</h2>
                <p class="display-6 mb-0"><?= number_format((int) $gainTotal, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Retraits</h2>
                <p class="display-6 mb-0"><?= number_format((int) $gainRetrait, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Transferts</h2>
                <p class="display-6 mb-0"><?= number_format((int) $gainTransfert, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Numéro concerné</th>
            <th class="text-end">Montant</th>
            <th class="text-end">Frais / gain</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($operations === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="5">Aucun gain enregistré.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($operations as $operation): ?>
            <?php $numero = $operation['type_code'] === 'transfert' ? $operation['numero_source'] : $operation['numero_source']; ?>
            <tr>
                <td><?= esc($operation['created_at']) ?></td>
                <td><?= esc($operation['type_operation']) ?></td>
                <td><?= esc($numero ?? '-') ?></td>
                <td class="text-end"><?= number_format((int) $operation['montant'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end fw-semibold"><?= number_format((int) $operation['frais'], 0, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
