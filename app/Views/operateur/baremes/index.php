<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<?php
$baremesParType = [
    'retrait' => [],
    'transfert' => [],
];

foreach ($baremes as $bareme) {
    if (isset($baremesParType[$bareme['code']])) {
        $baremesParType[$bareme['code']][] = $bareme;
    }
}

$renderBaremes = static function (array $items): void {
    ?>
    <div class="table-responsive bg-white shadow-sm">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Montant min</th>
                <th>Montant max</th>
                <th>Frais</th>
                <th>Statut</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($items === []): ?>
                <tr>
                    <td class="text-center text-muted py-4" colspan="5">Aucun barème.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($items as $bareme): ?>
                <tr>
                    <td><?= number_format((int) $bareme['montant_min'], 0, ',', ' ') ?></td>
                    <td><?= $bareme['montant_max'] === null ? 'Illimité' : number_format((int) $bareme['montant_max'], 0, ',', ' ') ?></td>
                    <td><?= number_format((int) $bareme['frais'], 0, ',', ' ') ?></td>
                    <td>
                        <span class="badge text-bg-<?= $bareme['actif'] ? 'success' : 'secondary' ?>">
                            <?= $bareme['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="/operateur/baremes/<?= esc($bareme['id_bareme']) ?>/modifier">Modifier</a>
                        <form class="d-inline" method="post" action="/operateur/baremes/<?= esc($bareme['id_bareme']) ?>/supprimer" onsubmit="return confirm('Supprimer ce barème ?')">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
};
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Barèmes de frais</h1>
    <a class="btn btn-primary" href="/operateur/baremes/nouveau">Ajouter</a>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <h2 class="h4 mb-3">Retrait</h2>
        <?php $renderBaremes($baremesParType['retrait']); ?>
    </div>
    <div class="col-xl-6">
        <h2 class="h4 mb-3">Transfert</h2>
        <?php $renderBaremes($baremesParType['transfert']); ?>
    </div>
</div>
<?= $this->endSection() ?>
