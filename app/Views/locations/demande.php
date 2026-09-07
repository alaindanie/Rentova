<?php $pageTitle = 'Demande de location'; ?>

<div class="loc-request-grid">
    <div class="panel reveal">
        <div class="loc-equip">
            <div class="avatar avatar-lg avatar-img"><img src="<?= image_url($equipement['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
            <div>
                <h3><?= e($equipement['nom']) ?></h3>
                <p><?= e($equipement['marque']) ?> <?= e($equipement['modele']) ?></p>
                <span class="tag"><?= e($equipement['categorie_nom']) ?></span>
            </div>
        </div>
        <div class="divider"></div>
        <div class="bill">
            <div class="bill-row"><span>Prix unitaire</span><b><?= e(montant($equipement['prix_jour'])) ?> / jour</b></div>
            <div class="bill-row"><span>Stock disponible</span><b><?= (int) $equipement['stock_disponible'] ?> unité(s)</b></div>
            <div class="bill-row bill-total" data-total-row>
                <span>Estimation totale</span><b data-total-price><?= e(montant(0)) ?></b>
            </div>
        </div>
        <p class="field-hint"><i class="fas fa-circle-info"></i> Le montant final sera confirmé par notre agent après validation de la disponibilité.</p>
    </div>

    <div class="panel panel-form reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-calendar-plus"></i> Votre demande de location</h3>
        </div>
        <form method="post" action="<?= BASE_URL ?>demande/<?= (int) $equipement['id'] ?>" class="form" data-location-form novalidate>
            <?= \App\Core\Csrf::field() ?>

            <?php if (!empty($clients)): ?>
            <div class="field">
                <label for="client_id">Client concerné *</label>
                <div class="input-wrap"><i class="fas fa-user"></i>
                    <select name="client_id" id="client_id" data-client-select>
                        <option value="">— Sélectionner un client —</option>
                        <?php foreach ($clients as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (isset($old['client_id']) && (int) $old['client_id'] === (int) $c['id']) ? 'selected' : '' ?>><?= e($c['prenom']) ?> <?= e($c['nom']) ?> — <?= e($c['email']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <span class="field-error"></span>
            </div>
            <?php endif; ?>

            <div class="form-grid-2">
                <div class="field">
                    <label for="date_debut">Date de début *</label>
                    <div class="input-wrap"><i class="fas fa-calendar-day"></i>
                        <input type="date" id="date_debut" name="date_debut"
                               value="<?= e($old['date_debut'] ?? date('Y-m-d', strtotime('+1 day'))) ?>"
                               data-date-debut>
                    </div>
                    <span class="field-error"></span>
                </div>
                <div class="field">
                    <label for="date_fin">Date de fin *</label>
                    <div class="input-wrap"><i class="fas fa-calendar-day"></i>
                        <input type="date" id="date_fin" name="date_fin"
                               value="<?= e($old['date_fin'] ?? date('Y-m-d', strtotime('+3 days'))) ?>"
                               data-date-fin>
                    </div>
                    <span class="field-error"></span>
                </div>
            </div>

            <div class="field">
                <label for="quantite">Quantité *</label>
                <div class="input-wrap"><i class="fas fa-boxes-stacked"></i>
                    <input type="number" id="quantite" name="quantite"
                           value="<?= e($old['quantite'] ?? 1) ?>"
                           data-quantite>
                </div>
                <span class="field-error"></span>
            </div>

            <div class="field">
                <label for="note">Remarque <small>(facultatif)</small></label>
                <textarea id="note" name="note" rows="3" placeholder="Précisez vos besoins : livraison, horaires, accessoires…"><?= e($old['note'] ?? '') ?></textarea>
                <span class="field-error"></span>
            </div>

            <div class="field">
                <label class="checkbox">
                    <input type="checkbox" id="cgv" name="cgv" value="1">
                    <span></span>
                    J'accepte les conditions générales de location
                </label>
                <span class="field-error"></span>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>equipement/<?= (int) $equipement['id'] ?>" class="btn btn-ghost">Retour</a>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane"></i> Envoyer ma demande</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var price = <?= json_encode((float) $equipement['prix_jour']) ?>;
        var start = document.querySelector('[data-date-debut]');
        var end   = document.querySelector('[data-date-fin]');
        var qty   = document.querySelector('[data-quantite]');
        var total = document.querySelector('[data-total-price]');
        var form  = document.querySelector('[data-location-form]');

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

        form.addEventListener('change', function (e) {
            if (e.target === qty) {
                var max = <?= (int) $equipement['stock_disponible'] ?>;
                if (parseInt(qty.value, 10) > max) qty.value = max;
            }
        });
    });
</script>
