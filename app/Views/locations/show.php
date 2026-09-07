<?php use App\Core\Auth; ?>
<?php $pageTitle = 'Location ' . $location['reference']; $role = Auth::role(); ?>

<div class="detail-head reveal">
    <div>
        <a href="<?= $role === 'client' ? BASE_URL . 'mes-locations' : BASE_URL . 'locations' ?>" class="link-more-inline"><i class="fas fa-arrow-left"></i> Retour</a>
        <h1>Location <span class="mono"><?= e($location['reference']) ?></span></h1>
        <p>Créée le <?= e(datetime_fr($location['cree_le'])) ?></p>
    </div>
    <div class="detail-head-status"><?= statut_badge($location['statut']) ?></div>
</div>

<?php
$flow = [
    'confirmee'  => ['label' => 'Location confirmée', 'icon' => 'fa-circle-check'],
    'en_cours'   => ['label' => 'Location en cours', 'icon' => 'fa-play'],
    'terminee'   => ['label' => 'Location terminée', 'icon' => 'fa-flag-checkered'],
];
$flowKeys = array_keys($flow);
if (in_array($location['statut'], ['annulee', 'refusee'], true)) {
    $flowIndex = $location['statut'] === 'refusee' ? 0 : 1;
    $flowState = 'rejected';
} else {
    $flowIndex = array_search($location['statut'], $flowKeys, true);
    $flowState = 'current';
}
$flowIndex = $flowIndex === false ? 0 : $flowIndex;
?>
<div class="panel stepper-panel reveal">
    <div class="panel-head"><h3><i class="fas fa-diagram-project"></i> Suivi de la location</h3></div>
    <div class="stepper">
        <?php foreach ($flowKeys as $k => $key): ?>
        <?php
        $cls = 'stepper-step';
        if ($k < $flowIndex) $cls .= ' done';
        elseif ($k === $flowIndex) $cls .= ' ' . $flowState;
        ?>
        <div class="<?= $cls ?>">
            <div class="s-dot"><i class="fas <?= $flow[$key]['icon'] ?>"></i></div>
            <span><?= e($flow[$key]['label']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="loc-grid">
    <div class="panel reveal">
        <div class="panel-head"><h3><i class="fas fa-box-open"></i> Équipement</h3></div>
        <div class="loc-equip">
            <div class="avatar avatar-lg avatar-img"><img src="<?= image_url($location['equipement_image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
            <div>
                <h3><?= e($location['equipement_nom']) ?></h3>
                <p><?= e($location['marque']) ?> <?= e($location['modele']) ?></p>
                <span class="tag"><?= e($location['categorie_nom']) ?></span>
            </div>
        </div>

        <div class="divider"></div>
        <h4 class="mini-title">Période &amp; quantité</h4>
        <div class="info-grid">
            <div><span>Début</span><b><?= e(date_fr($location['date_debut'])) ?></b></div>
            <div><span>Fin</span><b><?= e(date_fr($location['date_fin'])) ?></b></div>
            <div><span>Durée</span><b><?= (int) $location['duree'] ?> jour(s)</b></div>
            <div><span>Quantité</span><b>× <?= (int) $location['quantite'] ?></b></div>
        </div>

        <?php if ($location['note']): ?>
        <div class="divider"></div>
        <h4 class="mini-title">Note du client</h4>
        <p class="loc-note">"<?= e($location['note']) ?>"</p>
        <?php endif; ?>
    </div>

    <div class="panel reveal reveal-delay-1">
        <div class="panel-head"><h3><i class="fas fa-user"></i> Client</h3></div>
        <div class="loc-user">
            <div class="avatar avatar-lg"><?= e(mb_strtoupper(mb_substr($location['client_prenom'], 0, 1))) ?><?= e(mb_strtoupper(mb_substr($location['client_nom'], 0, 1))) ?></div>
            <div>
                <h3><?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?></h3>
                <p><i class="fas fa-envelope"></i> <?= e($location['client_email']) ?></p>
                <p><i class="fas fa-phone"></i> <?= e($location['client_telephone'] ?? 'Non renseigné') ?></p>
                <p><i class="fas fa-location-dot"></i> <?= e($location['client_adresse'] ?? 'Non renseignée') ?></p>
            </div>
        </div>

        <?php if ($location['agent_id']): ?>
        <div class="divider"></div>
        <h4 class="mini-title">Traitée par</h4>
        <p><i class="fas fa-user-tie"></i> <?= e($location['agent_prenom']) ?> <?= e($location['agent_nom']) ?></p>
        <?php endif; ?>

        <?php if ($location['statut'] === 'terminee' && $location['date_retour_effectif']): ?>
        <div class="divider"></div>
        <h4 class="mini-title">Retour</h4>
        <div class="info-grid">
            <div><span>Effectué le</span><b><?= e(date_fr($location['date_retour_effectif'])) ?></b></div>
            <div><span>Condition</span><b><?= e(ucfirst(str_replace('_', ' ', $location['condition_retour']))) ?></b></div>
        </div>
        <?php if ($location['commentaire_retour']): ?><p class="loc-note">"<?= e($location['commentaire_retour']) ?>"</p><?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="panel reveal">
        <div class="panel-head"><h3><i class="fas fa-file-invoice-dollar"></i> Facturation</h3></div>
        <div class="bill">
            <div class="bill-row"><span>Prix unitaire</span><b><?= e(montant($location['prix_jour'])) ?> / jour</b></div>
            <div class="bill-row"><span>Durée × quantité</span><b><?= (int) $location['duree'] ?> j × <?= (int) $location['quantite'] ?></b></div>
            <div class="bill-row"><span>Montant de base</span><b><?= e(montant($location['montant_base'])) ?></b></div>
            <?php if ((float) $location['montant_frais'] > 0): ?>
            <div class="bill-row bill-warn"><span>Frais additionnels</span><b>+ <?= e(montant($location['montant_frais'])) ?></b></div>
            <?php endif; ?>
            <div class="bill-row bill-total"><span>Total</span><b><?= e(montant($location['montant_total'])) ?></b></div>
        </div>
        <?php if (in_array($location['statut'], ['confirmee', 'en_cours', 'terminee'], true)): ?>
        <div class="doc-actions">
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/contrat" class="btn btn-ghost btn-sm"><i class="fas fa-file-contract"></i> Contrat</a>
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/facture" class="btn btn-ghost btn-sm"><i class="fas fa-file-invoice"></i> Facture</a>
            <?php if ($location['statut'] === 'terminee'): ?>
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/recu" class="btn btn-primary btn-sm"><i class="fas fa-file-arrow-down"></i> Reçu</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($role === 'agent' || $role === 'responsable'): ?>
<div class="panel workflow reveal">
    <div class="panel-head"><h3><i class="fas fa-check-double"></i> Validation finale</h3></div>
    <div class="workflow-actions">
        <?php if ($location['statut'] === 'en_attente'): ?>
            <form method="post" action="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/confirmer">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Confirmer &amp; démarrer</button>
            </form>
            <form method="post" action="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/refuser">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-danger-ghost"><i class="fas fa-xmark"></i> Refuser</button>
            </form>
        <?php elseif ($location['statut'] === 'confirmee'): ?>
            <form method="post" action="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/demarrer">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Démarrer la location</button>
            </form>
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/retour" class="btn btn-success"><i class="fas fa-undo"></i> Enregistrer le retour</a>
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/annuler" class="btn btn-danger-ghost"><i class="fas fa-ban"></i> Annuler</a>
        <?php elseif ($location['statut'] === 'en_cours'): ?>
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/retour" class="btn btn-success"><i class="fas fa-undo"></i> Enregistrer le retour</a>
            <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/annuler" class="btn btn-danger-ghost"><i class="fas fa-ban"></i> Annuler</a>
        <?php elseif ($location['statut'] === 'terminee'): ?>
            <span class="muted"><i class="fas fa-circle-check"></i> Location terminée. Merci !</span>
        <?php elseif ($location['statut'] === 'refusee'): ?>
            <span class="muted"><i class="fas fa-circle-xmark"></i> Demande refusée.</span>
        <?php else: ?>
            <span class="muted"><i class="fas fa-ban"></i> Location annulée.</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
