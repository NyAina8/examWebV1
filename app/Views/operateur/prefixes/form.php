<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<?php
$isEdit = $prefixe !== null;
$value = static fn (string $field, $default = '') => old($field, $prefixe[$field] ?? $default);
?>

<h1 class="h3 mb-3"><?= $isEdit ? 'Modifier un préfixe' : 'Ajouter un préfixe' ?></h1>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="card shadow-sm" method="post" action="<?= $isEdit ? '/operateur/prefixes/' . esc($prefixe['id_prefixe']) : '/operateur/prefixes' ?>">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label" for="prefixe">Préfixe</label>
            <input class="form-control" id="prefixe" name="prefixe" value="<?= esc($value('prefixe')) ?>" placeholder="032" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="id_operateur">Opérateur</label>
            <select class="form-select" id="id_operateur" name="id_operateur" required>
                <option value="">Choisir un opérateur</option>
                <?php foreach ($operateurs as $operateur): ?>
                    <option value="<?= esc($operateur['id_operateur']) ?>" <?= (string) $value('id_operateur') === (string) $operateur['id_operateur'] ? 'selected' : '' ?>>
                        <?= esc($operateur['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="actif" value="0">
            <input class="form-check-input" id="actif" name="actif" type="checkbox" value="1" <?= (string) $value('actif', 1) === '1' ? 'checked' : '' ?>>
            <label class="form-check-label" for="actif">Actif</label>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a class="btn btn-outline-secondary" href="/operateur/prefixes">Annuler</a>
    </div>
</form>
<?= $this->endSection() ?>
