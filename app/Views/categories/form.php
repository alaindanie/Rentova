<?php $pageTitle = $title; $isEdit = $categorie !== null; ?>

<div class="panel panel-form reveal">
    <div class="panel-head">
        <h3><i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i> <?= e($title) ?></h3>
        <a href="<?= BASE_URL ?>categories" class="link-more-inline">Retour à la liste</a>
    </div>

    <form method="post" action="<?= BASE_URL . ($isEdit ? 'categories/modifier/' . $categorie['id'] : 'categories') ?>"
          enctype="multipart/form-data" class="form" novalidate>
        <?= \App\Core\Csrf::field() ?>

        <div class="field">
            <label for="nom">Nom de la catégorie *</label>
            <div class="input-wrap"><i class="fas fa-tag"></i>
                <input type="text" id="nom" name="nom" value="<?= e($categorie['nom'] ?? '') ?>" placeholder="Ex : Outillage Électroportatif">
            </div>
            <span class="field-error"></span>
        </div>

        <div class="field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Décrivez le type d'équipements de cette catégorie…"><?= e($categorie['description'] ?? '') ?></textarea>
            <span class="field-error"></span>
        </div>

        <div class="field">
            <label for="image">Image de couverture</label>
            <div class="upload-box">
                <div class="upload-preview" data-upload-preview>
                    <?php if ($isEdit && $categorie['image']): ?>
                        <img src="<?= image_url($categorie['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="Aperçu">
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
            <a href="<?= BASE_URL ?>categories" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> <?= $isEdit ? 'Enregistrer' : 'Créer la catégorie' ?></button>
        </div>
    </form>
</div>
