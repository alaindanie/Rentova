<?php $pageTitle = 'Gestion des locations'; ?>

<div class="tabs reveal">
    <a href="<?= BASE_URL ?>locations" class="tab <?= $statut === '' ? 'active' : '' ?>">Toutes <span><?= array_sum($counts) ?></span></a>
    <a href="<?= BASE_URL ?>locations?statut=en_attente" class="tab <?= $statut === 'en_attente' ? 'active' : '' ?>">En attente <span class="tab-dot"><?= $counts['en_attente'] ?></span></a>
    <a href="<?= BASE_URL ?>locations?statut=confirmee" class="tab <?= $statut === 'confirmee' ? 'active' : '' ?>">Confirmées <span><?= $counts['confirmee'] ?></span></a>
    <a href="<?= BASE_URL ?>locations?statut=en_cours" class="tab <?= $statut === 'en_cours' ? 'active' : '' ?>">En cours <span><?= $counts['en_cours'] ?></span></a>
    <a href="<?= BASE_URL ?>locations?statut=terminee" class="tab <?= $statut === 'terminee' ? 'active' : '' ?>">Terminées <span><?= $counts['terminee'] ?></span></a>
    <a href="<?= BASE_URL ?>locations?statut=refusee" class="tab <?= $statut === 'refusee' ? 'active' : '' ?>">Refusées <span><?= $counts['refusee'] ?></span></a>
    <a href="<?= BASE_URL ?>locations?statut=annulee" class="tab <?= $statut === 'annulee' ? 'active' : '' ?>">Annulées <span><?= $counts['annulee'] ?></span></a>
</div>

<div class="panel reveal">
    <div class="panel-head">
        <h3><i class="fas fa-truck-ramp-box"></i> <?= $statut ? e(ucfirst(str_replace('_', ' ', $statut))) . ' — ' : '' ?>Locations <span class="count-pill"><?= count($locations) ?></span></h3>
    </div>
    <?php if (empty($locations)): ?>
    <div class="panel-empty"><i class="fas fa-inbox"></i><p>Aucune location dans cette catégorie.</p></div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Référence</th><th>Équipement</th><th>Client</th><th>Période</th><th>Montant</th><th>Statut</th><th class="th-actions">Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($locations as $l): ?>
                <tr>
                    <td><a class="mono" href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>"><?= e($l['reference']) ?></a></td>
                    <td>
                        <div class="cell-eq">
                            <div class="avatar avatar-img"><img src="<?= image_url($l['equipement_image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
                            <div><b><?= e(excerpt($l['equipement_nom'], 22)) ?></b><span class="cell-sub">× <?= (int) $l['quantite'] ?> · <?= (int) $l['duree'] ?> jour(s)</span></div>
                        </div>
                    </td>
                    <td><?= e($l['client_prenom']) ?> <?= e($l['client_nom']) ?><br><span class="cell-sub"><?= e($l['client_email']) ?></span></td>
                    <td><?= e(date_fr($l['date_debut'])) ?><br><span class="cell-sub">→ <?= e(date_fr($l['date_fin'])) ?></span></td>
                    <td><b><?= e(montant($l['montant_total'])) ?></b></td>
                    <td><?= statut_badge($l['statut']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>" class="btn btn-ghost btn-xs">Détails</a>
                            <?php if ($l['statut'] === 'en_attente'): ?>
                                <a href="<?= BASE_URL ?>location/<?= (int) $l['id'] ?>" class="btn btn-primary btn-xs">Traiter</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
