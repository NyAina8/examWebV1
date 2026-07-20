<?= $this->extend('operateur/layout') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Montants à envoyer aux opérateurs</h1>
        <p class="text-muted mb-0">Période : <?= esc($periode) ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="/operateur/gains">Situation gains</a>
</div>

<form class="card shadow-sm mb-3" method="get" action="/operateur/reversements">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label" for="date_debut">Date début</label>
            <input class="form-control" id="date_debut" name="date_debut" type="date" value="<?= esc($dateDebut) ?>">
        </div>
        <div class="col-md-5">
            <label class="form-label" for="date_fin">Date fin</label>
            <input class="form-control" id="date_fin" name="date_fin" type="date" value="<?= esc($dateFin) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Filtrer</button>
        </div>
    </div>
</form>

<div class="table-responsive bg-white shadow-sm">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>Opérateur</th>
            <th>Période</th>
            <th class="text-end">Transferts</th>
            <th class="text-end">Montant transféré</th>
            <th class="text-end">Commission</th>
            <th class="text-end">Net à reverser</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($operateurs === []): ?>
            <tr>
                <td class="text-center text-muted py-4" colspan="6">Aucun montant à reverser.</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($operateurs as $operateur): ?>
            <tr>
                <td><?= esc($operateur['nom']) ?></td>
                <td><?= esc($periode) ?></td>
                <td class="text-end"><?= number_format((int) $operateur['nombre_transferts'], 0, ',', ' ') ?></td>
                <td class="text-end"><?= number_format((int) $operateur['montant_total'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end"><?= number_format((int) $operateur['commission_conservee'], 0, ',', ' ') ?> Ar</td>
                <td class="text-end fw-semibold"><?= number_format((int) $operateur['montant_reverser'], 0, ',', ' ') ?> Ar</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
