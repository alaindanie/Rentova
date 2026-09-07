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
    <?php if ($role !== 'client'): ?>
    <div class="kpi-card reveal" style="--d:.15s">
        <div class="kpi-icon kpi-orange"><i class="fas fa-users"></i></div>
        <div><span>Clients</span><b class="counter" data-count="<?= (int) $stats['clients'] ?>">0</b></div>
    </div>
    <div class="kpi-card reveal" style="--d:.175s">
        <div class="kpi-icon kpi-teal"><i class="fas fa-user-tie"></i></div>
        <div><span>Agents de location</span><b class="counter" data-count="<?= (int) $stats['agents'] ?>">0</b></div>
    </div>
    <?php endif; ?>
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
        <div class="chart" data-chart>
            <?php if (empty($mois)): ?>
            <div class="panel-empty">
                <i class="fas fa-chart-simple"></i>
                <p>Aucune activité sur les 6 derniers mois pour le moment.</p>
            </div>
            <?php else: ?>
            <?php
            $nbMois   = array_map(fn($m) => (int) $m['nb'], $mois);
            $totalN   = array_sum($nbMois);
            $r        = 70;
            $C        = 2 * M_PI * $r;
            $palette  = ['#2563eb', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#7c3aed', '#c4b5fd'];
            $acc      = -90.0;
            ?>
            <div class="chart-stage">
                <div class="donut-wrap">
                    <div class="donut">
                        <svg viewBox="0 0 160 160" class="donut-svg">
                            <circle class="donut-track" cx="80" cy="80" r="<?= $r ?>"></circle>
                            <?php foreach ($mois as $k => $m): ?>
                            <?php
                            $frac   = $totalN > 0 ? (int) $m['nb'] / $totalN : 0;
                            $segLen = $frac * $C;
                            ?>
                            <circle class="donut-seg" cx="80" cy="80" r="<?= $r ?>"
                                stroke="<?= $palette[$k % count($palette)] ?>"
                                stroke-dasharray="0 <?= round($C, 2) ?>"
                                data-len="<?= round($segLen, 2) ?>" data-c="<?= round($C, 2) ?>"
                                transform="rotate(<?= $acc ?> 80 80)" style="--d:<?= $k * 80 ?>ms"></circle>
                            <?php $acc += $frac * 360; endforeach; ?>
                        </svg>
                        <div class="donut-center">
                            <span>Locations</span>
                            <b class="counter" data-count="<?= $totalN ?>">0</b>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <?php foreach ($mois as $k => $m): ?>
                        <?php $pct = $totalN > 0 ? round((int) $m['nb'] / $totalN * 100) : 0; ?>
                        <div class="donut-item">
                            <i class="donut-dot" style="background:<?= $palette[$k % count($palette)] ?>"></i>
                            <span class="donut-month"><?= e(mois_label($m['mois'])) ?></span>
                            <b><?= (int) $m['nb'] ?></b>
                            <span class="donut-pct"><?= $pct ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($role !== 'client' && !empty($endommages)): ?>
    <div class="panel damage-panel reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-tools"></i> Équipements endommagés<span class="count-pill"><?= count($endommages) ?></span></h3>
            <a href="<?= BASE_URL ?>equipements?etat=endommage" class="link-more-inline">Voir tout &rarr;</a>
        </div>
        <div class="damage-grid">
            <?php foreach ($endommages as $eq): ?>
            <div class="damage-case">
                <div class="damage-case-head">
                    <div class="avatar avatar-img"><img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
                    <div class="damage-case-info">
                        <b><?= e($eq['nom']) ?></b>
                        <span><?= e($eq['marque']) ?> <?= e($eq['modele']) ?></span>
                    </div>
                    <?= etat_badge('endommage') ?>
                </div>
                <div class="damage-case-foot">
                    <span class="damage-case-price"><?= e(montant($eq['prix_jour'])) ?> <small>/ jour</small></span>
                    <div class="damage-actions">
                        <form method="post" action="<?= BASE_URL ?>equipements/reparer/<?= (int) $eq['id'] ?>" data-confirm="Réparer « <?= e($eq['nom']) ?> » et le remettre en location ?">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" class="btn btn-success btn-xs"><i class="fas fa-wrench"></i> Réparer</button>
                        </form>
                        <form method="post" action="<?= BASE_URL ?>equipements/remplacer/<?= (int) $eq['id'] ?>" data-confirm="Remplacer « <?= e($eq['nom']) ?> » ?">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" class="btn btn-ghost btn-xs"><i class="fas fa-rotate"></i> Remplacer</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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
