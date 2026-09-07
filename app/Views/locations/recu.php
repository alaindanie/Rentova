<?php
$pageTitle = 'Reçu ' . $location['reference'];
$docType   = 'recu';
$ht    = (float) $location['montant_base'];
$frais = (float) $location['montant_frais'];
$tva   = round(($ht + $frais) * TAUX_TVA / 100, 2);
$ttc   = $ht + $frais + $tva;
?>
<div class="invoice">
    <header class="invoice-head">
        <div class="invoice-brand">
            <span class="brand-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
            <div>
                <h1><?= e(APP_NAME) ?></h1>
                <p>Location professionnelle d'équipements</p>
            </div>
        </div>
        <div class="invoice-doc">
            <h2>REÇU DE PAIEMENT</h2>
            <p><b>N° <?= e($location['reference']) ?>-R</b></p>
            <p>Date : <?= e(date_fr(date('Y-m-d'))) ?></p>
        </div>
    </header>

    <div class="receipt-stamp">
        <i class="fas fa-check-circle"></i> PAYÉ
    </div>

    <div class="invoice-addr">
        <div>
            <h4>Payé par</h4>
            <p>
                <?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?><br>
                <?= e($location['client_adresse'] ?: '—') ?><br>
                <?= e($location['client_email']) ?>
            </p>
        </div>
        <div>
            <h4>Montant encaissé</h4>
            <p class="receipt-amount"><?= e(montant($ttc)) ?></p>
            <p><small>Soit <?= e(number_format($ttc, 2, ',', ' ')) ?> € TTC, acquitté.</small></p>
        </div>
    </div>

    <div class="invoice-infos">
        <div><span>Location</span><b><?= e($location['reference']) ?></b></div>
        <div><span>Équipement</span><b><?= e($location['equipement_nom']) ?></b></div>
        <div><span>Période</span><b><?= e(date_fr($location['date_debut'])) ?> → <?= e(date_fr($location['date_fin'])) ?></b></div>
        <div><span>Retour effectif</span><b><?= e(date_fr($location['date_retour_effectif'])) ?></b></div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr><th>Désignation</th><th class="right">Montant HT</th></tr>
        </thead>
        <tbody>
            <tr><td>Location de <?= e($location['equipement_nom']) ?> (<?= (int) $location['duree'] ?> j × <?= (int) $location['quantite'] ?>)</td><td class="right"><?= e(montant($ht)) ?></td></tr>
            <?php if ($frais > 0): ?>
            <tr><td>Frais additionnels (retard / dommages / nettoyage)</td><td class="right"><?= e(montant($frais)) ?></td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr><td class="right">TVA (<?= TAUX_TVA ?> %)</td><td class="right"><?= e(montant($tva)) ?></td></tr>
            <tr class="grand"><td class="right">TOTAL TTC ENCAISSÉ</td><td class="right"><?= e(montant($ttc)) ?></td></tr>
        </tfoot>
    </table>

    <div class="invoice-payment">
        <h4>Objet du reçu</h4>
        <p>Ce document atteste du règlement intégral de la location référencée ci-dessus. La caution éventuellement versée n'est pas incluse dans ce montant et reste soumise aux conditions générales.</p>
        <p class="receipt-thanks"><i class="fas fa-handshake"></i> Merci de votre confiance — à très bientôt chez <?= e(APP_NAME) ?> !</p>
    </div>

    <footer class="invoice-foot">
        <p><?= e(APP_NAME) ?> — 12 rue de la République, 75011 Paris — SIRET 123 456 789 00012.</p>
        <p>Reçu généré le <?= e(date_fr(date('Y-m-d'))) ?>.</p>
    </footer>
</div>
