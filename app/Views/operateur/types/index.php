<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-3">Types d’opérations</h1>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover mb-0">
        <thead>
        <tr>
            <th>Code</th>
            <th>Libellé</th>
            <th>Statut</th>
            <th class="text-end">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($types as $type): ?>
            <tr>
                <td><?= esc($type['code']) ?></td>
                <td><?= esc($type['libelle']) ?></td>
                <td>
                    <span class="badge text-bg-<?= $type['actif'] ? 'success' : 'secondary' ?>">
                        <?= $type['actif'] ? 'Actif' : 'Inactif' ?>
                    </span>
                </td>
                <td class="text-end">
                    <form method="post" action="/operateur/types/<?= esc($type['id_type_operation']) ?>/basculer">
                        <button class="btn btn-sm btn-outline-primary" type="submit">
                            <?= $type['actif'] ? 'Désactiver' : 'Activer' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
