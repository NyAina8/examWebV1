<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="card shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h3 mb-1">Envoi multiple confirmé</h1>
                <p class="text-muted mb-0">Groupe <?= esc($resultat['id_envoi_multiple']) ?></p>
            </div>
            <span class="badge text-bg-success">Validé</span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Destinataires</div>
                    <div class="h4 mb-0"><?= esc($resultat['nombre_destinataires']) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Montant total</div>
                    <div class="h4 mb-0"><?= number_format((int) $resultat['montant_total'], 0, ',', ' ') ?> Ar</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Frais</div>
                    <div class="h4 mb-0"><?= number_format((int) $resultat['total_frais'] + (int) $resultat['total_frais_retrait_inclus'], 0, ',', ' ') ?> Ar</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Solde après</div>
                    <div class="h4 mb-0"><?= number_format((int) $resultat['nouveau_solde_expediteur'], 0, ',', ' ') ?> Ar</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Opérateur</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Frais</th>
                    <th class="text-end">Commission</th>
                    <th class="text-end">À reverser</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resultat['transferts'] as $transfert): ?>
                    <tr>
                        <td><?= esc($transfert['numero_destinataire']) ?></td>
                        <td><?= esc($transfert['operateur_destination']['nom']) ?></td>
                        <td class="text-end"><?= number_format((int) $transfert['montant'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= number_format((int) $transfert['frais'] + (int) $transfert['frais_retrait_inclus'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= number_format((int) $transfert['commission_interoperateur'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= number_format((int) $transfert['montant_reverser'], 0, ',', ' ') ?> Ar</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex gap-2">
        <a class="btn btn-primary" href="/client/envoi-multiple">Nouvel envoi multiple</a>
        <a class="btn btn-outline-secondary" href="/client/historique">Historique</a>
    </div>
</div>
<?= $this->endSection() ?>
