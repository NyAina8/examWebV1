<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Situation gain via les différents frais</h1>
        <p class="text-muted mb-0">Les frais sont les gains de l’opérateur source ; les commissions externes sont les gains de l’opérateur destinataire.</p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur">Retour</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Transferts internes</h2>
                <p class="h4 mb-0"><?= number_format((int) $nombreTransfertsInternes, 0, ',', ' ') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Transferts externes</h2>
                <p class="h4 mb-0"><?= number_format((int) $nombreTransfertsExternes, 0, ',', ' ') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Montants transférés</h2>
                <p class="h4 mb-0"><?= number_format((int) $montantsTransferes, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Commissions opérateurs</h2>
                <p class="h4 mb-0"><?= number_format((int) $commissionsExternes, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Frais retrait</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainRetrait, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Gain total</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainTotal, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Frais retrait gagnés</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainRetrait, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Frais transfert gagnés</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainTransfert, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Gains internes</h2>
                <p class="h4 mb-0"><?= number_format((int) $gainsInternes, 0, ',', ' ') ?> Ar</p>
            </div>
        </div>
    </div>
</div>

<form class="card shadow-sm mb-4" method="get" action="/operateur/gains">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label" for="operateur_id">Opérateur</label>
            <select class="form-select" id="operateur_id" name="operateur_id" onchange="this.form.submit()">
                <option value="0">Tous les opérateurs</option>
                <?php foreach ($operateursListe as $operateur): ?>
                    <option value="<?= esc($operateur['id_operateur']) ?>" <?= (int) $operateurSelectionneId === (int) $operateur['id_operateur'] ? 'selected' : '' ?>>
                        <?= esc($operateur['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100" type="submit">Afficher</button>
        </div>
    </div>
</form>

<?php if ($operateurSelectionne !== null): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted">Frais transfert</h2>
                    <p class="h4 mb-0"><?= number_format((int) $operateurSelectionne['frais_transfert_gagnes'], 0, ',', ' ') ?> Ar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted">Frais retrait</h2>
                    <p class="h4 mb-0"><?= number_format((int) $operateurSelectionne['frais_retrait_gagnes'], 0, ',', ' ') ?> Ar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted">Commission gagnée</h2>
                    <p class="h4 mb-0"><?= number_format((int) $operateurSelectionne['commissions_gagnees'], 0, ',', ' ') ?> Ar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-muted">Gain total</h2>
                    <p class="h4 mb-0"><?= number_format((int) $operateurSelectionne['gain_total'], 0, ',', ' ') ?> Ar</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Opérateur</th>
            <th>Préfixes</th>
            <th class="text-end">Envoyés</th>
            <th class="text-end">Reçus externes</th>
            <th class="text-end">Montant envoyé</th>
            <th class="text-end">Montant reçu externe</th>
            <th class="text-end">Commission %</th>
            <th class="text-end">Frais transfert</th>
            <th class="text-end">Frais retrait</th>
            <th class="text-end">Commission gagnée</th>
            <th class="text-end">Gain total</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($operateurs === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="11">Aucun gain par opérateur enregistré.</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($operateurs as $operateur): ?>
            <tr>
                <td><?= esc($operateur['nom']) ?></td>
                <td><?= esc($operateur['prefixes'] ?? '-') ?></td>
                <td class="text-end"><?= number_format((int) $operateur['transferts_envoyes'], 0, ',', ' ') ?></td>
                <td class="text-end"><?= number_format((int) $operateur['transferts_recus_externes'], 0, ',', ' ') ?></td>
                <td class="text-end"><?= number_format((int) $operateur['montant_envoye'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operateur['montant_recu_externe'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((float) $operateur['commission_transfert_externe'], 2, ',', ' ') ?> %</td>
                <td class="text-end"><?= number_format((int) $operateur['frais_transfert_gagnes'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operateur['frais_retrait_gagnes'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operateur['commissions_gagnees'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end fw-semibold"><?= number_format((int) $operateur['gain_total'], 0, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <?php if ($operateurs !== []): ?>
            <tfoot>
            <tr>
                <th>Total</th>
                <th></th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['transferts_envoyes'], $operateurs)), 0, ',', ' ') ?></th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['transferts_recus_externes'], $operateurs)), 0, ',', ' ') ?></th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['montant_envoye'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['montant_recu_externe'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th></th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['frais_transfert_gagnes'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['frais_retrait_gagnes'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['commissions_gagnees'], $operateurs)), 0, ',', ' ') ?> Ar</th>
                <th class="text-end"><?= number_format(array_sum(array_map(static fn ($op) => (int) $op['gain_total'], $operateurs)), 0, ',', ' ') ?> Ar</th>
            </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>
<?= $this->endSection() ?>
