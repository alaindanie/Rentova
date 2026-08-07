<?php $pageTitle = 'Catégories d\'équipements'; ?>

<div class="toolbar">
    <p class="toolbar-count"><?= count($categories) ?> catégorie(s)</p>
    <a href="<?= BASE_URL ?>categories/creer" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle catégorie</a>
</div>

<?php if (empty($categories)): ?>
<div class="panel"><div class="panel-empty"><i class="fas fa-tags"></i><p>Aucune catégorie pour le moment.</p></div></div>
<?php else: ?>
<div class="cat-admin-grid">
    <?php foreach ($categories as $cat): ?>
    <div class="panel cat-admin reveal">
        <div class="cat-admin-img">
            <img src="<?= image_url($cat['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($cat['nom']) ?>">
            <span class="cat-count"><?= (int) $cat['nb_equipements'] ?> équipement(s)</span>
        </div>
        <div class="cat-admin-body">
            <h3><?= e($cat['nom']) ?></h3>
            <p><?= e(excerpt($cat['description'], 90)) ?></p>
            <div class="cat-admin-meta">
                <span><i class="fas fa-boxes-stacked"></i> <?= (int) $cat['stock_total_categorie'] ?> unités en stock</span>
            </div>
            <div class="row-actions">
                <a href="<?= BASE_URL ?>categories/modifier/<?= (int) $cat['id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-pen"></i> Modifier</a>
                <form method="post" action="<?= BASE_URL ?>categories/supprimer/<?= (int) $cat['id'] ?>" class="inline" data-confirm="Supprimer cette catégorie ?">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="btn btn-danger-ghost btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
