<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Connexion client</h1>
                <p class="text-muted">Entrez votre numéro Mobile Money pour accéder à votre compte.</p>

                <form method="post" action="/connexion">
                    <div class="mb-3">
                        <label class="form-label" for="numero_telephone">Numéro de téléphone</label>
                        <input
                            class="form-control <?= isset($errors['numero_telephone']) ? 'is-invalid' : '' ?>"
                            id="numero_telephone"
                            name="numero_telephone"
                            value="<?= esc(old('numero_telephone')) ?>"
                            placeholder="0341234567"
                            inputmode="numeric"
                            autocomplete="tel"
                            required
                        >
                        <?php if (isset($errors['numero_telephone'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['numero_telephone']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
