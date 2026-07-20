<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="h3 mb-1">Transfert</h1>
                        <p class="text-muted mb-0"><?= esc($compte['numero_telephone']) ?></p>
                    </div>
                    <span class="badge text-bg-success">Solde : <?= number_format((int) $compte['solde'], 0, ',', ' ') ?> Ar</span>
                </div>

                <form method="post" action="/client/transfert">
                    <div class="mb-3">
                        <label class="form-label" for="numero_destinataire">Numéro du destinataire</label>
                        <input
                            class="form-control <?= isset($errors['numero_destinataire']) ? 'is-invalid' : '' ?>"
                            id="numero_destinataire"
                            name="numero_destinataire"
                            inputmode="numeric"
                            value="<?= esc(old('numero_destinataire')) ?>"
                            placeholder="0341234567"
                            required
                        >
                        <?php if (isset($errors['numero_destinataire'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['numero_destinataire']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="montant">Montant</label>
                        <div class="input-group">
                            <input
                                class="form-control <?= isset($errors['montant']) ? 'is-invalid' : '' ?>"
                                id="montant"
                                name="montant"
                                type="number"
                                min="1"
                                step="1"
                                value="<?= esc(old('montant')) ?>"
                                required
                            >
                            <span class="input-group-text">Ar</span>
                            <?php if (isset($errors['montant'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['montant']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Valider le transfert</button>
                        <a class="btn btn-outline-secondary" href="/client">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
