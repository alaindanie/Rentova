<?php $pageTitle = 'Modifier la demande ' . $location['reference']; ?>

<div class="loc-request-grid">
    <div class="panel reveal">
        <div class="loc-equip">
            <div class="avatar avatar-lg avatar-img"><img src="<?= image_url($location['equipement_image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
            <div>
                <h3><?= e($location['equipement_nom']) ?></h3>
                <p><?= e($location['marque']) ?> <?= e($location['modele']) ?></p>
                <span class="tag"><?= e($location['categorie_nom']) ?></span>
            </div>
        </div>
        <div class="divider"></div>
        <div class="bill">
            <div class="bill-row"><span>Prix unitaire</span><b><?= e(montant($location['prix_jour'])) ?> / jour</b></div>
            <div class="bill-row"><span>Client</span><b><?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?></b></div>
            <div class="bill-row bill-total" data-total-row>
                <span>Estimation totale</span><b data-total-price><?= e(montant(0)) ?></b>
            </div>
        </div>
        <p class="field-hint"><i class="fas fa-circle-info"></i> Le montant est recalculé automatiquement lors de la mise à jour.</p>
    </div>

    <div class="panel panel-form reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-pen-to-square"></i> Modifier la demande <span class="mono"><?= e($location['reference']) ?></span></h3>
        </div>
        <form method="post" action="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/modifier" class="form" data-location-form novalidate>
            <?= \App\Core\Csrf::field() ?>

            <div class="form-grid-2">
                <div class="field">
                    <label for="date_debut">Date de début *</label>
                    <div class="input-wrap"><i class="fas fa-calendar-day"></i>
                        <input type="date" id="date_debut" name="date_debut"
                               value="<?= e($old['date_debut'] ?? $location['date_debut']) ?>"
                               data-date-debut>
                    </div>
                    <span class="field-error"></span>
                </div>
                <div class="field">
                    <label for="date_fin">Date de fin *</label>
                    <div class="input-wrap"><i class="fas fa-calendar-day"></i>
                        <input type="date" id="date_fin" name="date_fin"
                               value="<?= e($old['date_fin'] ?? $location['date_fin']) ?>"
                               data-date-fin>
                    </div>
                    <span class="field-error"></span>
                </div>
            </div>

            <div class="field">
                <label for="quantite">Quantité *</label>
                <div class="input-wrap"><i class="fas fa-boxes-stacked"></i>
                    <input type="number" id="quantite" name="quantite"
                           value="<?= e($old['quantite'] ?? $location['quantite']) ?>"
                           data-quantite>
                </div>
                <span class="field-error"></span>
            </div>

            <div class="field">
                <label for="note">Remarque <small>(facultatif)</small></label>
                <textarea id="note" name="note" rows="3" placeholder="Précisez vos besoins : livraison, horaires, accessoires…"><?= e($old['note'] ?? $location['note'] ?? '') ?></textarea>
                <span class="field-error"></span>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var price = <?= json_encode((float) $location['prix_jour']) ?>;
        var start = document.querySelector('[data-date-debut]');
        var end   = document.querySelector('[data-date-fin]');
        var qty   = document.querySelector('[data-quantite]');
        var total = document.querySelector('[data-total-price]');

        function compute() {
            var d1 = new Date(start.value + 'T00:00:00');
            var d2 = new Date(end.value + 'T00:00:00');
            var days = Math.max(1, Math.round((d2 - d1) / 86400000));
            var q = Math.max(1, parseInt(qty.value, 10) || 1);
            var t = days * q * price;
            total.textContent = (t).toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' €';
        }
        [start, end, qty].forEach(function (el) {
            el.addEventListener('change', compute);
            el.addEventListener('input', compute);
        });
        compute();
    });
</script>