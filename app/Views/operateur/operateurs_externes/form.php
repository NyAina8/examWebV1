<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<?php
$isEdit = $operateur !== null;
$value = static fn (string $field, $default = '') => old($field, $operateur[$field] ?? $default);
?>

<h1 class="h3 mb-3"><?= $isEdit ? 'Modifier un opérateur' : 'Ajouter un opérateur' ?></h1>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="card shadow-sm" method="post" action="<?= $isEdit ? '/operateur/operateurs-externes/' . esc($operateur['id_operateur']) : '/operateur/operateurs-externes' ?>">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" for="code">Code</label>
                <input class="form-control" id="code" name="code" value="<?= esc($value('code')) ?>" required>
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label" for="nom">Nom</label>
                <input class="form-control" id="nom" name="nom" value="<?= esc($value('nom')) ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="commission_transfert_externe">Commission transfert externe (%)</label>
            <input class="form-control" id="commission_transfert_externe" name="commission_transfert_externe" type="number" min="0" max="100" step="0.01" value="<?= esc($value('commission_transfert_externe', 0)) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="prefixes">Préfixes associés</label>
            <textarea class="form-control" id="prefixes" name="prefixes" rows="3" placeholder="034&#10;038"><?= esc(old('prefixes', $prefixes)) ?></textarea>
        </div>
        <div class="d-flex gap-4">
            <div class="form-check form-switch">
                <input type="hidden" name="actif" value="0">
                <input class="form-check-input" id="actif" name="actif" type="checkbox" value="1" <?= (string) $value('actif', 1) === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="actif">Actif</label>
            </div>
            <div class="form-check form-switch">
                <input type="hidden" name="principal" value="0">
                <input class="form-check-input" id="principal" name="principal" type="checkbox" value="1" <?= (string) $value('principal', 0) === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="principal">Opérateur principal</label>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <a class="btn btn-outline-secondary" href="/operateur/operateurs-externes">Annuler</a>
    </div>
</form>
<?= $this->endSection() ?>
