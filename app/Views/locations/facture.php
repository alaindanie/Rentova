<?php
$pageTitle = 'Facture ' . $location['reference'];
$docType   = 'facture';
$ht     = (float) $location['montant_base'];
$frais  = (float) $location['montant_frais'];
$tva    = round(($ht + $frais) * TAUX_TVA / 100, 2);
$ttc    = $ht + $frais + $tva;
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
            <h2>FACTURE</h2>
            <p><b>N° <?= e($location['reference']) ?></b></p>
            <p>Date : <?= e(date_fr(substr($location['cree_le'], 0, 10))) ?></p>
        </div>
    </header>

    <div class="invoice-addr">
        <div>
            <h4>Émetteur</h4>
            <p>
                <?= e(APP_NAME) ?><br>
                12 rue de la République<br>
                75011 Paris — France<br>
                SIRET 123 456 789 00012<br>
                contact@rentova.fr · 01 23 45 67 89
            </p>
        </div>
        <div>
            <h4>Adressée à</h4>
            <p>
                <?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?><br>
                <?= e($location['client_adresse'] ?: '—') ?><br>
                <?= e($location['client_email']) ?><br>
                <?= e($location['client_telephone'] ?: '—') ?>
            </p>
        </div>
    </div>

    <div class="invoice-infos">
        <div><span>Location</span><b><?= e($location['reference']) ?></b></div>
        <div><span>Équipement</span><b><?= e($location['equipement_nom']) ?> (<?= e($location['marque']) ?> <?= e($location['modele']) ?>)</b></div>
        <div><span>Période</span><b><?= e(date_fr($location['date_debut'])) ?> → <?= e(date_fr($location['date_fin'])) ?></b></div>
        <div><span>Quantité</span><b>× <?= (int) $location['quantite'] ?></b></div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr><th>Désignation</th><th>Qté</th><th>Jours</th><th>P.U. HT</th><th class="right">Montant HT</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Location de <?= e($location['equipement_nom']) ?> — <?= e($location['marque']) ?> <?= e($location['modele']) ?></td>
                <td><?= (int) $location['quantite'] ?></td>
                <td><?= (int) $location['duree'] ?></td>
                <td><?= e(montant($location['prix_jour'])) ?></td>
                <td class="right"><?= e(montant($ht)) ?></td>
            </tr>
            <?php if ($frais > 0): ?>
            <tr>
                <td colspan="4">Frais additionnels (retard / dommages / nettoyage)</td>
                <td class="right"><?= e(montant($frais)) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="4" class="right">Total HT</td><td class="right"><?= e(montant($ht + $frais)) ?></td></tr>
            <tr><td colspan="4" class="right">TVA (<?= TAUX_TVA ?> %)</td><td class="right"><?= e(montant($tva)) ?></td></tr>
            <tr class="grand"><td colspan="4" class="right">TOTAL TTC</td><td class="right"><?= e(montant($ttc)) ?></td></tr>
        </tfoot>
    </table>

    <div class="invoice-payment">
        <h4>Modalités de paiement</h4>
        <p>
            Règlement à réception de la présente facture, sous 15 jours.<br>
            Paiement par virement bancaire, carte bancaire ou chèque à l'ordre de <?= e(APP_NAME) ?>.<br>
            Une caution de <?= FRAIS_CAUTION ?> € est restituée après contrôle du retour du matériel.
        </p>
    </div>

    <footer class="invoice-foot">
        <p>Merci de votre confiance. Toute réclamation doit être adressée sous 8 jours. — <?= e(APP_NAME) ?>, SIRET 123 456 789 00012, TVA FR 12 345678901.</p>
        <p>Générée le <?= e(date_fr(date('Y-m-d'))) ?>.</p>
    </footer>
</div>
