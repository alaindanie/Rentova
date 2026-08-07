<?php $pageTitle = 'Enregistrer un retour'; ?>

<div class="loc-grid">
    <div class="panel reveal">
        <div class="loc-equip">
            <div class="avatar avatar-lg avatar-img"><img src="<?= image_url($location['equipement_image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
            <div>
                <h3><?= e($location['equipement_nom']) ?></h3>
                <p>Réf. <span class="mono"><?= e($location['reference']) ?></span> · <?= e($location['client_prenom']) ?> <?= e($location['client_nom']) ?></p>
            </div>
        </div>
        <div class="divider"></div>
        <div class="info-grid">
            <div><span>Début</span><b><?= e(date_fr($location['date_debut'])) ?></b></div>
            <div><span>Fin prévue</span><b><?= e(date_fr($location['date_fin'])) ?></b></div>
            <div><span>Durée</span><b><?= (int) $location['duree'] ?> jour(s)</b></div>
            <div><span>Quantité</span><b>× <?= (int) $location['quantite'] ?></b></div>
            <div><span>Montant base</span><b><?= e(montant($location['montant_base'])) ?></b></div>
            <div><span>Retour attendu</span><b><?= date('Y-m-d') > $location['date_fin'] ? '<span class="text-danger">EN RETARD</span>' : 'Dans les temps' ?></b></div>
        </div>
        <div class="alert alert-info"><i class="fas fa-circle-info"></i>
            <div>Un retour après la date de fin génère automatiquement des frais de <?= RETARD_PAR_JOUR ?> € par jour de retard et par unité. Le stock sera réapprovisionné après validation.</div>
        </div>
    </div>

    <div class="panel panel-form reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-undo"></i> Contrôle du retour</h3>
        </div>
        <form method="post" action="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/retour" class="form" novalidate>
            <?= \App\Core\Csrf::field() ?>

            <div class="field">
                <label for="date_retour">Date effective du retour</label>
                <div class="input-wrap"><i class="fas fa-calendar-day"></i>
                    <input type="date" id="date_retour" name="date_retour" value="<?= date('Y-m-d') ?>" data-date-retour>
                </div>
                <span class="field-error"></span>
            </div>

            <div class="field">
                <label for="condition_retour">Condition de l'équipement *</label>
                <div class="input-wrap"><i class="fas fa-clipboard-check"></i>
                    <select id="condition_retour" name="condition_retour" data-validate="required">
                        <option value="">— Évaluer la condition —</option>
                        <option value="neuf">Comme neuf</option>
                        <option value="tres_bon">Très bon état</option>
                        <option value="bon">Bon état</option>
                        <option value="use">Usé (usure normale)</option>
                        <option value="endommage">Endommagé / dommage</option>
                    </select>
                </div>
                <span class="field-error"></span>
            </div>

            <div class="field">
                <label for="montant_frais">Frais additionnels (€) <small>(réparation, nettoyage…)</small></label>
                <div class="input-wrap"><i class="fas fa-euro-sign"></i>
                    <input type="number" id="montant_frais" name="montant_frais" step="0.01" min="0" value="0" data-validate="numeric|min:0" data-montant-frais>
                </div>
                <span class="field-error"></span>
            </div>

            <div class="field">
                <label for="commentaire">Commentaire de retour</label>
                <textarea id="commentaire" name="commentaire" rows="3" placeholder="État constaté, remarques particulières…"></textarea>
                <span class="field-error"></span>
            </div>

            <div class="bill" data-retour-summary>
                <div class="bill-row"><span>Montant de base</span><b><?= e(montant($location['montant_base'])) ?></b></div>
                <div class="bill-row bill-warn" data-retard-row style="display:none"><span>Retard (<span data-retard-jours>0</span> j × <?= RETARD_PAR_JOUR ?> €)</span><b data-retard-montant>+ 0,00 €</b></div>
                <div class="bill-row bill-warn" data-frais-row style="display:none"><span>Frais additionnels</span><b data-frais-montant>+ 0,00 €</b></div>
                <div class="bill-row bill-total"><span>Total à facturer</span><b data-retour-total><?= e(montant($location['montant_base'])) ?></b></div>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>" class="btn btn-ghost">Annuler</a>
                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check"></i> Valider le retour</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var base = <?= json_encode((float) $location['montant_base']) ?>;
        var qty  = <?= (int) $location['quantite'] ?>;
        var finPrevue = new Date('<?= $location['date_fin'] ?>T00:00:00');
        var dateRetour = document.querySelector('[data-date-retour]');
        var fraisInput = document.querySelector('[data-montant-frais]');

        function compute() {
            var d = new Date(dateRetour.value + 'T00:00:00');
            var days = Math.max(0, Math.round((d - finPrevue) / 86400000));
            var retardMontant = days * <?= RETARD_PAR_JOUR ?> * qty;
            var retardRow = document.querySelector('[data-retard-row]');
            var frais = parseFloat(fraisInput.value) || 0;

            if (days > 0) {
                retardRow.style.display = 'flex';
                document.querySelector('[data-retard-jours]').textContent = days;
                document.querySelector('[data-retard-montant]').textContent = '+' + retardMontant.toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' €';
            } else {
                retardRow.style.display = 'none';
            }
            var fraisRow = document.querySelector('[data-frais-row]');
            if (frais > 0) {
                fraisRow.style.display = 'flex';
                document.querySelector('[data-frais-montant]').textContent = '+' + frais.toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' €';
            } else {
                fraisRow.style.display = 'none';
            }
            var total = base + retardMontant + frais;
            document.querySelector('[data-retour-total]').textContent = total.toLocaleString('fr-FR', {minimumFractionDigits: 2}) + ' €';
        }
        dateRetour.addEventListener('change', compute);
        fraisInput.addEventListener('input', compute);
        compute();
    });
</script>
