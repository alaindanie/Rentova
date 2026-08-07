<?php $pageTitle = $title; $isEdit = $equipement !== null; ?>

<div class="panel panel-form reveal">
    <div class="panel-head">
        <h3><i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i> <?= e($title) ?></h3>
        <a href="<?= BASE_URL ?>equipements" class="link-more-inline">Retour à la liste</a>
    </div>

    <form method="post" action="<?= BASE_URL . ($isEdit ? 'equipements/modifier/' . $equipement['id'] : 'equipements') ?>"
          enctype="multipart/form-data" class="form" data-form novalidate>
        <?= \App\Core\Csrf::field() ?>

        <div class="form-grid-2">
            <div class="field">
                <label for="nom">Nom de l'équipement *</label>
                <div class="input-wrap"><i class="fas fa-tag"></i>
                    <input type="text" id="nom" name="nom" value="<?= e($equipement['nom'] ?? '') ?>" placeholder="Ex : Échafaudage roulant" data-validate="required|min:2">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="categorie_id">Catégorie *</label>
                <div class="input-wrap"><i class="fas fa-folder"></i>
                    <select id="categorie_id" name="categorie_id" data-validate="required">
                        <option value="">— Choisir —</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= isset($equipement) && (int) $equipement['categorie_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Caractéristiques, usage recommandé…"><?= e($equipement['description'] ?? '') ?></textarea>
            <span class="field-error"></span>
        </div>

        <div class="form-grid-2">
            <div class="field">
                <label for="marque">Marque</label>
                <div class="input-wrap"><i class="fas fa-industry"></i>
                    <input type="text" id="marque" name="marque" value="<?= e($equipement['marque'] ?? '') ?>" placeholder="Ex : BOSCH">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="modele">Modèle</label>
                <div class="input-wrap"><i class="fas fa-hashtag"></i>
                    <input type="text" id="modele" name="modele" value="<?= e($equipement['modele'] ?? '') ?>" placeholder="Ex : GSR 18V-60">
                </div>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="field">
                <label for="prix_jour">Prix de location (€ / jour) *</label>
                <div class="input-wrap"><i class="fas fa-euro-sign"></i>
                    <input type="number" id="prix_jour" name="prix_jour" step="0.01" min="0" value="<?= e($equipement['prix_jour'] ?? '') ?>" placeholder="0.00" data-validate="required|numeric|min:0">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="etat">État</label>
                <div class="input-wrap"><i class="fas fa-signal"></i>
                    <select id="etat" name="etat">
                        <option value="disponible" <?= isset($equipement) && $equipement['etat'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="maintenance" <?= isset($equipement) && $equipement['etat'] === 'maintenance' ? 'selected' : '' ?>>En maintenance</option>
                        <option value="indisponible" <?= isset($equipement) && $equipement['etat'] === 'indisponible' ? 'selected' : '' ?>>Indisponible</option>
                    </select>
                </div>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-grid-3">
            <div class="field">
                <label for="stock_total">Stock total *</label>
                <div class="input-wrap"><i class="fas fa-boxes-stacked"></i>
                    <input type="number" id="stock_total" name="stock_total" min="0" value="<?= e($equipement['stock_total'] ?? '') ?>" placeholder="0" data-validate="required|int|min:0" data-stock-total>
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="seuil_alerte">Seuil d'alerte *</label>
                <div class="input-wrap"><i class="fas fa-bell"></i>
                    <input type="number" id="seuil_alerte" name="seuil_alerte" min="0" value="<?= e($equipement['seuil_alerte'] ?? '') ?>" placeholder="0" data-validate="required|int|min:0">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label>Stock disponible</label>
                <div class="input-wrap input-readonly"><i class="fas fa-check-circle"></i>
                    <input type="text" value="<?= $isEdit ? (int) $equipement['stock_disponible'] . ' unité(s)' : 'Égal au stock total' ?>" readonly>
                </div>
                <small class="field-hint"><?= $isEdit ? 'L\'écart actuel est préservé lors de la modification.' : 'Le stock disponible est initialisé au stock total.' ?></small>
            </div>
        </div>

        <div class="field">
            <label for="image">Photo de l'équipement</label>
            <div class="upload-box">
                <div class="upload-preview" data-upload-preview>
                    <?php if ($isEdit && $equipement['image']): ?>
                        <img src="<?= image_url($equipement['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="Aperçu">
                    <?php else: ?>
                        <i class="fas fa-cloud-arrow-up"></i>
                        <p>Glissez une image ou cliquez pour parcourir</p>
                        <span>JPG, PNG ou WEBP — 5 Mo max</span>
                    <?php endif; ?>
                </div>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-upload-input>
            </div>
            <span class="field-error"></span>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>equipements" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter l\'équipement' ?></button>
        </div>
    </form>
</div>
