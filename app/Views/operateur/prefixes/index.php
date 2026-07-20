<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Préfixes téléphoniques</h1>
    <a class="btn btn-primary" href="/operateur/prefixes/nouveau">Ajouter</a>
</div>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover mb-0">
        <thead>
        <tr>
            <th>Préfixe</th>
            <th>Opérateur</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($prefixes as $prefixe): ?>
            <tr>
                <td><?= esc($prefixe['prefixe']) ?></td>
                <td><?= esc($prefixe['operateur']) ?></td>
                <td>
                    <span class="badge text-bg-<?= $prefixe['actif'] ? 'success' : 'secondary' ?>">
                        <?= $prefixe['actif'] ? 'Actif' : 'Inactif' ?>
                    </span>
                </td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="/operateur/prefixes/<?= esc($prefixe['id_prefixe']) ?>/modifier">Modifier</a>
                    <form class="d-inline" method="post" action="/operateur/prefixes/<?= esc($prefixe['id_prefixe']) ?>/supprimer" onsubmit="return confirm('Supprimer ce préfixe ?')">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
