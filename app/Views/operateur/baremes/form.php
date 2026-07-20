<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<?php
$isEdit = $bareme !== null;
$value = static fn (string $field, $default = '') => old($field, $bareme[$field] ?? $default);
?>

<h1 class="h3 mb-3"><?= $isEdit ? 'Modifier un barème' : 'Ajouter un barème' ?></h1>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="card shadow-sm" method="post" action="<?= $isEdit ? '/operateur/baremes/' . esc($bareme['id_bareme']) : '/operateur/baremes' ?>">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label" for="id_type_operation">Type d’opération</label>
            <select class="form-select" id="id_type_operation" name="id_type_operation" required>
                <?php foreach ($types as $type): ?>
                    <option value="<?= esc($type['id_type_operation']) ?>" <?= (string) $value('id_type_operation') === (string) $type['id_type_operation'] ? 'selected' : '' ?>>
                        <?= esc($type['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" for="montant_min">Montant minimum</label>
                <input class="form-control" id="montant_min" name="montant_min" type="number" min="0" value="<?= esc($value('montant_min')) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="montant_max">Montant maximum</label>
                <input class="form-control" id="montant_max" name="montant_max" type="number" min="0" value="<?= esc($value('montant_max')) ?>" placeholder="Illimité">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="frais">Frais</label>
                <input class="form-control" id="frais" name="frais" type="number" min="0" value="<?= esc($value('frais')) ?>" required>
            </div>
        </div>
        <div class="form-check form-switch">
            <input type="hidden" name="actif" value="0">
            <input class="form-check-input" id="actif" name="actif" type="checkbox" value="1" <?= (string) $value('actif', 1) === '1' ? 'checked' : '' ?>>
            <label class="form-check-label" for="actif">Actif</label>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a class="btn btn-outline-secondary" href="/operateur/baremes">Annuler</a>
    </div>
</form>
<?= $this->endSection() ?>
