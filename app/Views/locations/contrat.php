<?php
$pageTitle = 'Contrat ' . $location['reference'];
$docType   = 'contrat';
$ht    = (float) $location['montant_base'];
$frais = (float) $location['montant_frais'];
$tva   = round(($ht + $frais) * TAUX_TVA / 100, 2);
$ttc   = $ht + $frais + $tva;
?>
<div class="contract">
    <header class="contract-head">
        <span class="brand-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
        <div>
            <h1>CONTRAT DE LOCATION</h1>
            <p>N° <?= e($location['reference']) ?> — établi le <?= e(date_fr(substr($location['cree_le'], 0, 10))) ?></p>
        </div>
    </header>

    <section class="contract-section">
        <h3>1 · Les parties</h3>
        <div class="contract-parties">
            <div>
                <h4>Le Loueur</h4>
                <p>
                    <b><?= e(APP_NAME) ?></b> — 12 rue de la République, 75011 Paris<br>
                    SIRET 123 456 789 00012 — contact@rentova.fr
                </p>
            </div>
            <div>
                <h4>Le Preneur</h4>
                <p>
                    <b><?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?></b><br>
                    <?= e($location['client_adresse'] ?: '—') ?><br>
                    <?= e($location['client_email']) ?> — <?= e($location['client_telephone'] ?: '—') ?>
                </p>
            </div>
        </div>
    </section>

    <section class="contract-section">
        <h3>2 · Objet du contrat</h3>
        <p>Le présent contrat a pour objet la location du matériel suivant, dont l'état est réputé conforme à sa fiche catalogue au moment de la remise :</p>
        <table class="invoice-table">
            <tbody>
                <tr><td><b>Équipement</b></td><td><?= e($location['equipement_nom']) ?> — <?= e($location['marque']) ?> <?= e($location['modele']) ?></td></tr>
                <tr><td><b>Catégorie</b></td><td><?= e($location['categorie_nom']) ?></td></tr>
                <tr><td><b>Quantité</b></td><td><?= (int) $location['quantite'] ?> exemplaire(s)</td></tr>
                <tr><td><b>Période</b></td><td>du <?= e(date_fr($location['date_debut'])) ?> au <?= e(date_fr($location['date_fin'])) ?> inclus, soit <?= (int) $location['duree'] ?> jour(s)</td></tr>
                <tr><td><b>Tarif</b></td><td><?= e(montant($location['prix_jour'])) ?> HT / jour / unité</td></tr>
                <tr><td><b>Montant de base</b></td><td><?= e(montant($ht)) ?> HT</td></tr>
                <?php if ($frais > 0): ?><tr><td><b>Frais additionnels</b></td><td><?= e(montant($frais)) ?> HT</td></tr><?php endif; ?>
                <tr><td><b>Total</b></td><td><b><?= e(montant($ttc)) ?> TTC</b></td></tr>
            </tbody>
        </table>
    </section>

    <section class="contract-section">
        <h3>3 · Conditions d'utilisation</h3>
        <ol class="contract-list">
            <li>Le matériel reste la propriété exclusive du Loueur et ne peut être cédé, sous-loué ou déplacé hors des lieux convenus sans accord écrit.</li>
            <li>Le Preneur s'engage à utiliser le matériel conformément à sa destination, en respectant les consignes de sécurité et la réglementation en vigueur.</li>
            <li>Tout dommage causé au matériel, hors usure normale constatée, sera facturé au Preneur selon le barème de réparation du Loueur.</li>
            <li>Le matériel doit être restitué dans les délais impartis. Tout jour de retard entraîne des frais de <?= RETARD_PAR_JOUR ?> € HT par jour et par unité.</li>
            <li>Le Preneur déclare être assuré en responsabilité civile couvrant l'utilisation du matériel loué.</li>
            <li>Une caution de <?= FRAIS_CAUTION ?> € est due à la signature et restituée après contrôle du retour, déduction faite des éventuels frais.</li>
        </ol>
    </section>

    <section class="contract-section">
        <h3>4 · Retour du matériel</h3>
        <p>Le retour s'effectue au siège du Loueur aux horaires d'ouverture, sauf livraison/récupération convenue. L'état de restitution est constaté contradictoirement par un agent du Loueur ; à défaut, le matériel est réputé restitué en bon état.</p>
    </section>

    <section class="contract-section">
        <h3>5 · Signatures</h3>
        <p>Les parties déclarent avoir pris connaissance et accepter les conditions du présent contrat. Fait en deux exemplaires originaux.</p>
        <div class="contract-signs">
            <div>
                <p>Fait à Paris, le <?= e(date_fr(date('Y-m-d'))) ?></p>
                <p><b>Le Loueur</b><br><?= e(APP_NAME) ?></p>
                <div class="sign-line"></div>
                <p>Signature et cachet</p>
            </div>
            <div>
                <p>Fait le <?= e(date_fr(date('Y-m-d'))) ?></p>
                <p><b>Le Preneur</b><br><?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?></p>
                <div class="sign-line"></div>
                <p>« Lu et approuvé » — Signature précédée de la mention</p>
            </div>
        </div>
    </section>

    <footer class="invoice-foot">
        <p>Contrat généré automatiquement depuis la plateforme <?= e(APP_NAME) ?>.</p>
    </footer>
</div>
