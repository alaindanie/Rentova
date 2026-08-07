<?php $pageTitle = 'Tableau de bord'; ?>

<div class="kpi-grid">
    <div class="kpi-card reveal" style="--d:0s">
        <div class="kpi-icon kpi-blue"><i class="fas fa-gear"></i></div>
        <div><span>Équipements</span><b class="counter" data-count="<?= (int) $stats['equipements'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.05s">
        <div class="kpi-icon kpi-green"><i class="fas fa-check-circle"></i></div>
        <div><span>Disponibles</span><b class="counter" data-count="<?= (int) $stats['equipementsDispo'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.1s">
        <div class="kpi-icon kpi-violet"><i class="fas fa-tags"></i></div>
        <div><span>Catégories</span><b class="counter" data-count="<?= (int) $stats['categories'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.15s">
        <div class="kpi-icon kpi-orange"><i class="fas fa-users"></i></div>
        <div><span>Clients</span><b class="counter" data-count="<?= (int) $stats['clients'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.2s">
        <div class="kpi-icon kpi-blue"><i class="fas fa-truck-ramp-box"></i></div>
        <div><span>Locations</span><b class="counter" data-count="<?= (int) $stats['locations'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.25s">
        <div class="kpi-icon kpi-green"><i class="fas fa-spinner"></i></div>
        <div><span>En cours</span><b class="counter" data-count="<?= (int) $stats['locationsEnCours'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.3s">
        <div class="kpi-icon kpi-orange"><i class="fas fa-clock"></i></div>
        <div><span>En attente</span><b class="counter" data-count="<?= (int) $stats['enAttente'] ?>">0</b></div>
    </div>
    <div class="kpi-card kpi-amount reveal" style="--d:.35s">
        <div class="kpi-icon kpi-violet"><i class="fas fa-euro-sign"></i></div>
        <div><span>Chiffre d'affaires</span><b><?= e(montant($stats['ca'])) ?></b></div>
    </div>
</div>

<?php if (!empty($enAlertes) && $role === 'responsable'): ?>
<div class="alert alert-warning reveal">
    <i class="fas fa-triangle-exclamation"></i>
    <div>
        <b>Alertes de stock :</b> <?= count($enAlertes) ?> équipement(s) sous le seuil d'alerte.
        <a href="<?= BASE_URL ?>equipements?alerte=oui" class="link-more-inline">Voir &rarr;</a>
    </div>
</div>
<?php endif; ?>

<?php if ($role !== 'client' && $enRetard > 0): ?>
<div class="alert alert-danger reveal">
    <i class="fas fa-clock"></i>
    <div><b><?= $enRetard ?> location(s)</b> en retard de retour — pensez à contacter les clients.</div>
</div>
<?php endif; ?>

<div class="dash-grid dash-grid-main">
    <div class="panel reveal">
        <div class="panel-head">
            <h3><i class="fas fa-chart-line"></i> Activité — 6 derniers mois</h3>
            <span class="panel-tag">Locations / CA</span>
        </div>
        <div class="chart">
            <?php
            $maxN = max(1, ...array_map(fn($m) => (int) $m['nb'], $mois));
            $maxC = max(1, ...array_map(fn($m) => (float) $m['montant'], $mois));
            ?>
            <div class="chart-bars">
                <?php foreach ($mois as $m): ?>
                <div class="chart-col" title="<?= e($m['mois']) ?> — <?= (int) $m['nb'] ?> location(s), <?= e(montant($m['montant'])) ?>">
                    <div class="chart-bar chart-bar-primary" style="height: <?= round(((int) $m['nb'] / $maxN) * 100) ?>%"></div>
                    <div class="chart-bar chart-bar-accent" style="height: <?= round(((float) $m['montant'] / $maxC) * 100) ?>%"></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="chart-legend">
                <span><i class="dot dot-primary"></i> Locations</span>
                <span><i class="dot dot-accent"></i> Chiffre d'affaires</span>
            </div>
        </div>
    </div>

    <?php if ($role === 'responsable'): ?>
    <div class="panel reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-trophy"></i> Top équipements</h3>
            <a href="<?= BASE_URL ?>equipements" class="link-more-inline">Tout voir</a>
        </div>
        <div class="top-list">
            <?php foreach ($topEquip as $eq): ?>
            <div class="top-item">
                <div class="top-rank">#<?= (int) $eq['nb_locations'] ?></div>
                <div class="avatar avatar-img"><img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
                <div class="top-info">
                    <b><?= e(excerpt($eq['nom'], 28)) ?></b>
                    <span><?= (int) $eq['nb_locations'] ?> locations · <?= e(montant($eq['revenus'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($role === 'agent' || $role === 'responsable'): ?>
    <div class="panel reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-undo"></i> Retours à venir</h3>
            <a href="<?= BASE_URL ?>locations" class="link-more-inline">Tout voir</a>
        </div>
        <?php if (empty($retours)): ?>
        <div class="panel-empty"><i class="fas fa-circle-check"></i><p>Aucun retour imminent. Parfait !</p></div>
        <?php else: ?>
        <div class="top-list">
            <?php foreach ($retours as $r): ?>
            <div class="top-item">
                <div class="top-rank"><i class="fas fa-undo"></i></div>
                <div class="avatar avatar-img"><img src="<?= image_url($r['equipement_image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
                <div class="top-info">
                    <b><?= e(excerpt($r['equipement_nom'], 26)) ?></b>
                    <span>Retour le <?= e(date_fr($r['date_fin'])) ?> · <?= e($r['client_prenom']) ?> <?= e($r['client_nom']) ?></span>
                </div>
                <a href="<?= BASE_URL ?>location/<?= (int) $r['id'] ?>/retour" class="btn btn-ghost btn-xs">Retour</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="panel reveal">
        <div class="panel-head">
            <h3><i class="fas fa-clock-rotate-left"></i> Locations récentes</h3>
            <a href="<?= BASE_URL ?>locations" class="link-more-inline">Tout voir</a>
        </div>
        <div class="table-wrap">
            <table class="table table-sm">
                <thead><tr><th>Réf.</th><th>Équipement</th><th>Client</th><th>Montant</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach ($recentes as $r): ?>
                <tr>
                    <td><a class="mono" href="<?= BASE_URL ?>location/<?= (int) $r['id'] ?>"><?= e($r['reference']) ?></a></td>
                    <td><?= e(excerpt($r['equipement_nom'], 24)) ?></td>
                    <td><?= e($r['client_prenom']) ?> <?= e($r['client_nom']) ?></td>
                    <td><b><?= e(montant($r['montant_total'])) ?></b></td>
                    <td><?= statut_badge($r['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($role === 'client'): ?>
    <div class="panel reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-truck-ramp-box"></i> Mes dernières locations</h3>
            <a href="<?= BASE_URL ?>mes-locations" class="link-more-inline">Tout voir</a>
        </div>
        <?php if (empty($recentes)): ?>
        <div class="panel-empty">
            <i class="fas fa-box-open"></i>
            <p>Vous n'avez pas encore de location.</p>
            <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary btn-sm">Explorer le catalogue</a>
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table table-sm">
                <thead><tr><th>Réf.</th><th>Équipement</th><th>Période</th><th>Montant</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($recentes, 0, 6) as $r): ?>
                <tr>
                    <td><a class="mono" href="<?= BASE_URL ?>location/<?= (int) $r['id'] ?>"><?= e($r['reference']) ?></a></td>
                    <td><?= e(excerpt($r['equipement_nom'], 22)) ?></td>
                    <td><?= e(date_fr($r['date_debut'])) ?> → <?= e(date_fr($r['date_fin'])) ?></td>
                    <td><b><?= e(montant($r['montant_total'])) ?></b></td>
                    <td><?= statut_badge($r['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($role === 'responsable'): ?>
    <div class="panel reveal">
        <div class="panel-head">
            <h3><i class="fas fa-users"></i> Meilleurs clients</h3>
        </div>
        <div class="top-list">
            <?php foreach ($topClients as $c): ?>
            <div class="top-item">
                <div class="avatar avatar-img"><?= e(mb_strtoupper(mb_substr($c['prenom'], 0, 1))) ?><?= e(mb_strtoupper(mb_substr($c['nom'], 0, 1))) ?></div>
                <div class="top-info">
                    <b><?= e($c['prenom']) ?> <?= e($c['nom']) ?></b>
                    <span><?= (int) $c['nb_locations'] ?> locations · <?= e(montant($c['total_depense'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
