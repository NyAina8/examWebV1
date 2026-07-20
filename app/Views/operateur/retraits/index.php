<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Retraits effectués</h1>
        <p class="text-muted mb-0"><?= number_format(count($retraits), 0, ',', ' ') ?> retrait<?= count($retraits) > 1 ? 's' : '' ?></p>
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
            <th class="text-end">Total débité</th>
            <th class="text-end">Solde après</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($retraits === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="8">Aucun retrait effectué.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($retraits as $retrait): ?>
            <tr>
                <td><?= esc($retrait['created_at']) ?></td>
                <td><?= esc($retrait['numero_telephone']) ?></td>
                <td><?= esc(trim($retrait['prenom'] . ' ' . $retrait['nom'])) ?></td>
                <td class="text-end"><?= number_format((int) $retrait['montant'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $retrait['frais'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $retrait['montant'] + (int) $retrait['frais'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $retrait['solde_source_apres'], 0, ',', ' ') ?> Ar</td>
                <td>
                    <span class="badge text-bg-<?= $retrait['statut'] === 'validee' ? 'success' : 'secondary' ?>">
                        <?= $retrait['statut'] === 'validee' ? 'Validé' : 'Annulé' ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
