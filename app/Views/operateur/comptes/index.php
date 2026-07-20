<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Numéros clients</h1>
        <p class="text-muted mb-0"><?= number_format(count($comptes), 0, ',', ' ') ?> compte<?= count($comptes) > 1 ? 's' : '' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur">Retour</a>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Numéro</th>
            <th>Client</th>
            <th>Opérateur</th>
            <th class="text-end">Solde</th>
            <th>État</th>
            <th>Créé le</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($comptes === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="6">Aucun numéro client.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($comptes as $compte): ?>
            <tr>
                <td><?= esc($compte['numero_telephone']) ?></td>
                <td><?= esc(trim($compte['prenom'] . ' ' . $compte['nom'])) ?></td>
                <td><?= esc($compte['operateur']) ?></td>
                <td class="text-end"><?= number_format((int) $compte['solde'], 0, ',', ' ') ?> Ar</td>
                <td>
                    <span class="badge text-bg-<?= $compte['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                        <?= $compte['statut'] === 'actif' ? 'Actif' : 'Désactivé' ?>
                    </span>
                </td>
                <td><?= esc($compte['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
