<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Évolution du solde</h1>
        <p class="text-muted mb-0"><?= esc($compte['numero_telephone']) ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/client">Retour au compte</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h6 text-muted">Solde actuel</h2>
        <p class="display-6 mb-0"><?= number_format((int) $solde, 0, ',', ' ') ?> Ar</p>
    </div>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Date</th>
            <th>Opération</th>
            <th>Numéro concerné</th>
            <th class="text-end">Solde avant</th>
            <th class="text-end">Variation</th>
            <th class="text-end">Solde après</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($evolution === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="6">Aucune opération pour le moment.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($evolution as $operation): ?>
            <?php $variation = (int) $operation['variation']; ?>
            <tr>
                <td><?= esc($operation['created_at']) ?></td>
                <td><?= esc($operation['libelle_historique']) ?></td>
                <td><?= esc($operation['numero_affiche']) ?></td>
                <td class="text-end"><?= number_format((int) $operation['solde_avant'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end <?= $variation >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $variation >= 0 ? '+' : '' ?><?= number_format($variation, 0, ',', ' ') ?> Ar
                </td>
                <td class="text-end fw-semibold"><?= number_format((int) $operation['solde_apres'], 0, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
