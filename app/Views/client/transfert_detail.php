<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="h3 mb-1">Confirmation du transfert</h1>
                        <p class="text-muted mb-0">Référence <?= esc($operation['reference']) ?></p>
                    </div>
                    <span class="badge text-bg-success">Validée</span>
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-5">Expéditeur</dt>
                    <dd class="col-sm-7"><?= esc($operation['numero_source']) ?></dd>

                    <dt class="col-sm-5">Destinataire</dt>
                    <dd class="col-sm-7"><?= esc($operation['numero_destination'] ?? 'Compte externe') ?></dd>

                    <dt class="col-sm-5">Opérateur source</dt>
                    <dd class="col-sm-7"><?= esc($operation['operateur_source'] ?? '-') ?></dd>

                    <dt class="col-sm-5">Opérateur destinataire</dt>
                    <dd class="col-sm-7"><?= esc($operation['operateur_destination'] ?? '-') ?></dd>

                    <dt class="col-sm-5">Date</dt>
                    <dd class="col-sm-7"><?= esc($operation['created_at']) ?></dd>

                    <dt class="col-sm-5">Montant transféré</dt>
                    <dd class="col-sm-7"><?= number_format((int) $operation['montant'], 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Frais de transfert</dt>
                    <dd class="col-sm-7"><?= number_format((int) $operation['frais'], 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Frais de retrait inclus</dt>
                    <dd class="col-sm-7"><?= number_format((int) ($operation['frais_retrait_inclus'] ?? 0), 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Commission interopérateur</dt>
                    <dd class="col-sm-7"><?= number_format((int) ($operation['commission_interoperateur'] ?? 0), 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Montant à reverser</dt>
                    <dd class="col-sm-7"><?= number_format((int) ($operation['montant_reverser'] ?? 0), 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Total débité</dt>
                    <dd class="col-sm-7"><?= number_format((int) $operation['montant'] + (int) $operation['frais'] + (int) ($operation['frais_retrait_inclus'] ?? 0), 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Solde expéditeur après</dt>
                    <dd class="col-sm-7 fw-semibold"><?= number_format((int) $operation['solde_source_apres'], 0, ',', ' ') ?> Ar</dd>

                    <dt class="col-sm-5">Solde destinataire après</dt>
                    <dd class="col-sm-7"><?= $operation['solde_destination_apres'] === null ? '-' : number_format((int) $operation['solde_destination_apres'], 0, ',', ' ') . ' Ar' ?></dd>
                </dl>
            </div>
            <div class="card-footer d-flex gap-2">
                <a class="btn btn-primary" href="/client/transfert">Nouveau transfert</a>
                <a class="btn btn-outline-secondary" href="/client">Retour au compte</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
