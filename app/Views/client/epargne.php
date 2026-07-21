<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h1 class="h3 mb-1">Épargne</h1>
                    <p class="text-muted mb-0"><?= esc($compte['numero_telephone']) ?></p>
                </div>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= esc($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Solde disponible</div>
                            <div class="h4 mb-0"><?= number_format((int) $compte['solde'], 0, ',', ' ') ?> Ar</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted">Solde épargne</div>
                            <div class="h4 mb-0"><?= number_format((int) ($compte['solde_epargne'] ?? 0), 0, ',', ' ') ?> Ar</div>
                        </div>
                    </div>
                </div>

                <form method="post" action="/client/epargne">
                    <div class="mb-3">
                        <label class="form-label" for="pourcentage_epargne">Pourcentage épargné sur chaque transfert reçu</label>
                        <div class="input-group">
                            <input
                                class="form-control <?= isset($errors['pourcentage_epargne']) ? 'is-invalid' : '' ?>"
                                id="pourcentage_epargne"
                                name="pourcentage_epargne"
                                type="number"
                                min="0"
                                max="100"
                                step="1"
                                value="<?= esc(old('pourcentage_epargne', $compte['pourcentage_epargne'] ?? 0)) ?>"
                                required
                            >
                            <span class="input-group-text">%</span>
                            <?php if (isset($errors['pourcentage_epargne'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['pourcentage_epargne']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                        <a class="btn btn-outline-secondary" href="/client">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
