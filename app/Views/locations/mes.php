<?php $pageTitle = 'Mes locations'; ?>

<div class="section-head page-title reveal">
    <div>
        <h1>Mes <span class="grad-text">locations</span></h1>
        <p>Suivez vos demandes, téléchargez vos documents.</p>
    </div>
    <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle location</a>
</div>

<?php if (empty($locations)): ?>
<div class="panel reveal"><div class="panel-empty">
    <i class="fas fa-box-open"></i>
    <h3>Vous n'avez pas encore de location</h3>
    <p>Parcourez notre catalogue et faites votre première demande.</p>
    <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary">Explorer le catalogue</a>
</div></div>
<?php else: ?>
<div class="panel reveal">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Référence</th><th>Équipement</th><th>Période</th><th>Durée</th><th>Montant</th><th>Statut</th><th class="th-actions">Documents</th></tr>
            </thead>
            <tbody>
            <?php foreach ($locations as $l): ?>
                <tr>
                    <td><a class="mono" href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>"><?= e($l['reference']) ?></a></td>
                    <td>
                        <div class="cell-eq">
                            <div class="avatar avatar-img"><img src="<?= image_url($l['equipement_image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
                            <div><b><?= e(excerpt($l['equipement_nom'], 26)) ?></b><span class="cell-sub">× <?= (int) $l['quantite'] ?></span></div>
                        </div>
                    </td>
                    <td><?= e(date_fr($l['date_debut'])) ?><br><span class="cell-sub">→ <?= e(date_fr($l['date_fin'])) ?></span></td>
                    <td><?= (int) $l['duree'] ?> jour(s)</td>
                    <td><b><?= e(montant($l['montant_total'])) ?></b></td>
                    <td><?= statut_badge($l['statut']) ?></td>
                    <td>
                        <?php if (in_array($l['statut'], ['confirmee', 'en_cours', 'terminee'], true)): ?>
                        <div class="doc-actions">
                            <a href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>/contrat" class="btn-icon" title="Contrat"><i class="fas fa-file-contract"></i></a>
                            <a href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>/facture" class="btn-icon" title="Facture"><i class="fas fa-file-invoice"></i></a>
                            <?php if ($l['statut'] === 'terminee'): ?>
                            <a href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>/recu" class="btn-icon" title="Reçu"><i class="fas fa-receipt"></i></a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
