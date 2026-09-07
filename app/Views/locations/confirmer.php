<?php $pageTitle = 'Confirmer la demande ' . $location['reference']; ?>

<div class="detail-head reveal">
    <div>
        <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>" class="link-more-inline"><i class="fas fa-arrow-left"></i> Retour à la location</a>
        <h1>Confirmer la demande</h1>
        <p>Location <span class="mono"><?= e($location['reference']) ?></span> — créée le <?= e(datetime_fr($location['cree_le'])) ?></p>
    </div>
    <div class="detail-head-status"><?= statut_badge($location['statut']) ?></div>
</div>

<div class="confirm-box reveal">
    <i class="fas fa-triangle-exclamation confirm-icon"></i>
    <div>
        <h3>Vous êtes sur le point de confirmer et démarrer la location</h3>
        <p>La confirmation est définitive : le stock demandé sera réservé pour toute la période, l'équipement passera à l'état « en location » et la location sera directement marquée « en cours ». Vérifiez le récapitulatif ci-dessous avant de valider.</p>
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
    </div>

    <div class="panel reveal reveal-delay-2">
        <div class="panel-head"><h3><i class="fas fa-file-invoice-dollar"></i> Facturation</h3></div>
        <div class="bill">
            <div class="bill-row"><span>Prix unitaire</span><b><?= e(montant($location['prix_jour'])) ?> / jour</b></div>
            <div class="bill-row"><span>Durée × quantité</span><b><?= (int) $location['duree'] ?> j × <?= (int) $location['quantite'] ?></b></div>
            <div class="bill-row"><span>Montant de base</span><b><?= e(montant($location['montant_base'])) ?></b></div>
            <div class="bill-row bill-total"><span>Total à encaisser</span><b><?= e(montant($location['montant_total'])) ?></b></div>
        </div>
    </div>
</div>

<div class="panel workflow reveal">
    <div class="panel-head"><h3><i class="fas fa-check-double"></i> Validation finale</h3></div>
    <div class="workflow-actions">
        <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Retour</a>
        <form method="post" action="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/confirmer">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check"></i> Confirmer &amp; démarrer</button>
        </form>
    </div>
</div>
