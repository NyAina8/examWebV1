<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="h3 mb-1">Transfert</h1>
                        <p class="text-muted mb-0"><?= esc($compte['numero_telephone']) ?></p>
                    </div>
                    <span class="badge text-bg-success">Solde : <?= number_format((int) $compte['solde'], 0, ',', ' ') ?> Ar</span>
                </div>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= esc($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/client/envoi-multiple">
                    <div class="mb-3">
                        <label class="form-label" for="destinataires">Destinataires</label>
                        <textarea
                            class="form-control <?= isset($errors['destinataires']) ? 'is-invalid' : '' ?>"
                            id="destinataires"
                            name="destinataires"
                            rows="4"
                            placeholder="0341234567&#10;0327654321"
                            required
                        ><?= esc(old('destinataires')) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="montant_total">Montant total</label>
                        <div class="input-group">
                            <input
                                class="form-control <?= isset($errors['montant_total']) ? 'is-invalid' : '' ?>"
                                id="montant_total"
                                name="montant_total"
                                type="number"
                                min="1"
                                step="1"
                                value="<?= esc(old('montant_total')) ?>"
                                required
                            >
                            <span class="input-group-text">Ar</span>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="inclure_frais_retrait" value="0">
                        <input class="form-check-input" id="inclure_frais_retrait" name="inclure_frais_retrait" type="checkbox" value="1" <?= old('inclure_frais_retrait') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="inclure_frais_retrait">Inclure les frais de retrait</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit">Transférer</button>
                        <a class="btn btn-outline-secondary" href="/client">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
