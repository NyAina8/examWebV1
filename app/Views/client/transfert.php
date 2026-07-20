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

                    <div class="form-check mb-3">
                        <input type="hidden" name="inclure_frais_retrait" value="0">
                        <input
                            class="form-check-input"
                            id="inclure_frais_retrait"
                            name="inclure_frais_retrait"
                            type="checkbox"
                            value="1"
                            <?= old('inclure_frais_retrait') === '1' ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="inclure_frais_retrait">Inclure les frais de retrait</label>
                    </div>

                    <?php if ($apercu !== null): ?>
                        <div class="alert alert-info">
                            <h2 class="h5">Aperçu du transfert</h2>
                            <dl class="row mb-0">
                                <dt class="col-sm-6">Opérateur destinataire</dt>
                                <dd class="col-sm-6"><?= esc($apercu['operateur_destination']['nom']) ?></dd>

                                <dt class="col-sm-6">Type</dt>
                                <dd class="col-sm-6"><?= $apercu['transfert_externe'] ? 'Externe' : 'Interne' ?></dd>

                                <dt class="col-sm-6">Montant envoyé</dt>
                                <dd class="col-sm-6"><?= number_format((int) $apercu['montant'], 0, ',', ' ') ?> Ar</dd>

                                <dt class="col-sm-6">Frais de transfert</dt>
                                <dd class="col-sm-6"><?= number_format((int) $apercu['frais'], 0, ',', ' ') ?> Ar</dd>

                                <dt class="col-sm-6">Frais de retrait inclus</dt>
                                <dd class="col-sm-6"><?= number_format((int) $apercu['frais_retrait_inclus'], 0, ',', ' ') ?> Ar</dd>

                                <dt class="col-sm-6">Montant reçu</dt>
                                <dd class="col-sm-6"><?= number_format((int) $apercu['montant_recu'], 0, ',', ' ') ?> Ar</dd>

                                <dt class="col-sm-6">Commission interopérateur</dt>
                                <dd class="col-sm-6"><?= number_format((int) $apercu['commission_interoperateur'], 0, ',', ' ') ?> Ar</dd>

                                <dt class="col-sm-6">Total débité</dt>
                                <dd class="col-sm-6 fw-semibold"><?= number_format((int) $apercu['total_debit'], 0, ',', ' ') ?> Ar</dd>
                            </dl>
                        </div>
                        <input type="hidden" name="confirmer" value="1">
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><?= $apercu === null ? 'Voir le calcul' : 'Confirmer le transfert' ?></button>
                        <a class="btn btn-outline-secondary" href="/client">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
