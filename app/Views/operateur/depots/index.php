<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Dépôts effectués</h1>
        <p class="text-muted mb-0"><?= number_format(count($depots), 0, ',', ' ') ?> dépôt<?= count($depots) > 1 ? 's' : '' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur">Retour</a>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Date</th>
            <th>Numéro</th>
            <th>Client</th>
            <th class="text-end">Montant</th>
            <th class="text-end">Frais</th>
            <th class="text-end">Solde après</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($depots === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="7">Aucun dépôt effectué.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($depots as $depot): ?>
            <tr>
                <td><?= esc($depot['created_at']) ?></td>
                <td><?= esc($depot['numero_telephone']) ?></td>
                <td><?= esc(trim($depot['prenom'] . ' ' . $depot['nom'])) ?></td>
                <td class="text-end"><?= number_format((int) $depot['montant'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $depot['frais'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $depot['solde_destination_apres'], 0, ',', ' ') ?> Ar</td>
                <td>
                    <span class="badge text-bg-<?= $depot['statut'] === 'validee' ? 'success' : 'secondary' ?>">
                        <?= $depot['statut'] === 'validee' ? 'Validé' : 'Annulé' ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
