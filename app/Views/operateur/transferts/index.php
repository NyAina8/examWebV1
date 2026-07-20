<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Transferts effectués</h1>
        <p class="text-muted mb-0"><?= number_format(count($transferts), 0, ',', ' ') ?> transfert<?= count($transferts) > 1 ? 's' : '' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur">Retour</a>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Date</th>
            <th>Expéditeur</th>
            <th>Destinataire</th>
            <th class="text-end">Montant</th>
            <th class="text-end">Montant reçu</th>
            <th class="text-end">Frais</th>
            <th class="text-end">Commission</th>
            <th class="text-end">Total débité</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($transferts === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="9">Aucun transfert effectué.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($transferts as $transfert): ?>
            <tr>
                <td><?= esc($transfert['created_at']) ?></td>
                <td>
                    <div><?= esc($transfert['numero_source']) ?></div>
                    <small class="text-muted"><?= esc(trim($transfert['prenom_source'] . ' ' . $transfert['nom_source'])) ?></small>
                </td>
                <td>
                    <div><?= esc($transfert['numero_destination'] ?? 'Compte externe') ?></div>
                    <small class="text-muted"><?= esc(trim(($transfert['prenom_destination'] ?? '') . ' ' . ($transfert['nom_destination'] ?? '')) ?: ($transfert['nom_operateur_destination'] ?? '-')) ?></small>
                </td>
                <td class="text-end"><?= number_format((int) $transfert['montant'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) (($transfert['montant_recu'] ?? 0) > 0 ? $transfert['montant_recu'] : (int) $transfert['montant'] + (int) ($transfert['frais_retrait_inclus'] ?? 0)), 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $transfert['frais'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) ($transfert['commission_interoperateur'] ?? 0), 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) (($transfert['total_debite'] ?? 0) > 0 ? $transfert['total_debite'] : (int) $transfert['montant'] + (int) $transfert['frais'] + (int) ($transfert['frais_retrait_inclus'] ?? 0) + (int) ($transfert['commission_interoperateur'] ?? 0)), 0, ',', ' ') ?> Ar</td>
                <td>
                    <span class="badge text-bg-<?= $transfert['statut'] === 'validee' ? 'success' : 'secondary' ?>">
                        <?= $transfert['statut'] === 'validee' ? 'Validé' : 'Annulé' ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
