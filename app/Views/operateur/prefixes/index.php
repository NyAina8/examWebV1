<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Préfixes téléphoniques</h1>
    <a class="btn btn-primary" href="/operateur/prefixes/nouveau">Ajouter</a>
</div>

<?php $operateurCourant = null; ?>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover mb-0 align-middle">
        <thead>
        <tr>
            <th>Opérateur</th>
            <th>Préfixe</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($prefixes as $prefixe): ?>
            <tr>
                <td>
                    <?php if ($operateurCourant !== ($prefixe['nom_operateur'] ?? $prefixe['operateur'])): ?>
                        <?php $operateurCourant = $prefixe['nom_operateur'] ?? $prefixe['operateur']; ?>
                        <span class="fw-semibold"><?= esc($operateurCourant) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= esc($prefixe['prefixe']) ?></td>
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
