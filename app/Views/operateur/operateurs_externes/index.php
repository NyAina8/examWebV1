<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Opérateurs Mobile Money</h1>
        <p class="text-muted mb-0">Gestion des opérateurs et préfixes associés.</p>
    </div>
    <a class="btn btn-primary" href="/operateur/operateurs-externes/nouveau">Ajouter</a>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Code</th>
            <th>Nom</th>
            <th>Préfixes</th>
            <th class="text-end">Commission externe</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($operateurs as $operateur): ?>
            <tr>
                <td><?= esc($operateur['code'] ?? '-') ?></td>
                <td>
                    <?= esc($operateur['nom']) ?>
                    <?php if ((int) $operateur['principal'] === 1): ?>
                        <span class="badge text-bg-primary ms-1">Principal</span>
                    <?php endif; ?>
                </td>
                <td><?= esc($operateur['prefixes'] ?? '-') ?></td>
                <td class="text-end"><?= number_format((float) $operateur['commission_transfert_externe'], 2, ',', ' ') ?> %</td>
                <td>
                    <span class="badge text-bg-<?= $operateur['actif'] ? 'success' : 'secondary' ?>">
                        <?= $operateur['actif'] ? 'Actif' : 'Inactif' ?>
                    </span>
                </td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="/operateur/operateurs-externes/<?= esc($operateur['id_operateur']) ?>/modifier">Modifier</a>
                    <form class="d-inline" method="post" action="/operateur/operateurs-externes/<?= esc($operateur['id_operateur']) ?>/basculer">
                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?= $operateur['actif'] ? 'Désactiver' : 'Activer' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
