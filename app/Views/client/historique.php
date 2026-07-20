<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Historique</h1>
        <p class="text-muted mb-0"><?= number_format((int) $total, 0, ',', ' ') ?> opération<?= (int) $total > 1 ? 's' : '' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/client">Retour au compte</a>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Numéro</th>
            <th class="text-end">Montant</th>
            <th class="text-end">Frais</th>
            <th class="text-end">Solde après</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($operations === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="6">Aucune opération pour le moment.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($operations as $operation): ?>
            <tr>
                <td><?= esc($operation['created_at']) ?></td>
                <td>
                    <?php
                    $badge = match ($operation['type_code']) {
                        'depot' => 'success',
                        'retrait' => 'warning',
                        'transfert' => ((int) ($operation['id_compte_source'] ?? 0) === (int) session('compte_id')) ? 'primary' : 'info',
                        default => 'secondary',
                    };
                    ?>
                    <span class="badge text-bg-<?= esc($badge) ?>"><?= esc($operation['libelle_historique']) ?></span>
                </td>
                <td><?= esc($operation['numero_affiche']) ?></td>
                <td class="text-end"><?= number_format((int) $operation['montant'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operation['frais'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end fw-semibold"><?= number_format((int) $operation['solde_apres'], 0, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="mt-3" aria-label="Pagination historique">
        <ul class="pagination justify-content-end">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="/client/historique?page=<?= max(1, $page - 1) ?>">Précédent</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="/client/historique?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="/client/historique?page=<?= min($totalPages, $page + 1) ?>">Suivant</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
<?= $this->endSection() ?>
