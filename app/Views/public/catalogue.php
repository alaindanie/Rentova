<?php $pageTitle = 'Catalogue'; ?>

<section class="page-hero page-hero-sm">
    <div class="container">
        <span class="hero-eyebrow"><i class="fas fa-boxes-stacked"></i> Catalogue complet</span>
        <h1>Nos équipements <span class="grad-text">disponibles</span></h1>
        <p>Tous les équipements professionnels, prêts à être loués.</p>
    </div>
</section>

<section class="section section-top">
    <div class="container">
        <form method="get" action="<?= BASE_URL ?>catalogue" class="filter-panel reveal" data-filter-form>
            <div class="filter-row">
                <div class="filter-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Rechercher un équipement, une marque…" data-filter-search>
                </div>
                <div class="filter-field">
                    <select name="categorie_id">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= (string) $filters['categorie_id'] === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-field">
                    <select name="tri">
                        <option value="nom_asc" <?= $filters['tri'] === 'nom_asc' ? 'selected' : '' ?>>Nom A → Z</option>
                        <option value="nom_desc" <?= $filters['tri'] === 'nom_desc' ? 'selected' : '' ?>>Nom Z → A</option>
                        <option value="prix_asc" <?= $filters['tri'] === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="prix_desc" <?= $filters['tri'] === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                        <option value="stock_desc" <?= $filters['tri'] === 'stock_desc' ? 'selected' : '' ?>>Stock le plus élevé</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-sliders"></i> Filtrer</button>
            </div>
            <div class="filter-row filter-row-advanced">
                <div class="filter-field"><label>Prix min</label><input type="number" name="prix_min" min="0" value="<?= e($filters['prix_min']) ?>" placeholder="0 €"></div>
                <div class="filter-field"><label>Prix max</label><input type="number" name="prix_max" min="0" value="<?= e($filters['prix_max']) ?>" placeholder="250 €"></div>
                <div class="filter-field"><label>Disponibilité</label>
                    <select name="disponible">
                        <option value="">Tous</option>
                        <option value="oui" <?= $filters['disponible'] === 'oui' ? 'selected' : '' ?>>Disponibles uniquement</option>
                        <option value="non" <?= $filters['disponible'] === 'non' ? 'selected' : '' ?>>En rupture</option>
                    </select>
                </div>
                <?php if (!empty($filters['q']) || $filters['categorie_id'] || $filters['prix_min'] !== '' || $filters['prix_max'] !== '' || $filters['disponible'] !== ''): ?>
                <a href="<?= BASE_URL ?>catalogue" class="filter-reset"><i class="fas fa-rotate-left"></i> Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="catalog-meta reveal">
            <p><b><?= $total ?></b> équipement(s) trouvé(s)</p>
            <div class="catalog-views" data-view-toggle>
                <button type="button" class="active" data-view="grid"><i class="fas fa-grip"></i></button>
                <button type="button" data-view="list"><i class="fas fa-list"></i></button>
            </div>
        </div>

        <?php if (empty($equipements)): ?>
        <div class="empty-state reveal">
            <i class="fas fa-box-open"></i>
            <h3>Aucun équipement trouvé</h3>
            <p>Essayez d'élargir vos critères de recherche.</p>
            <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary">Réinitialiser les filtres</a>
        </div>
        <?php else: ?>
        <div class="equip-grid view-grid" data-grid>
            <?php foreach ($equipements as $eq): ?>
            <article class="equip-card reveal">
                <div class="equip-card-img">
                    <img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($eq['nom']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= IMG ?>equipements/eq_perceuse.jpg'">
                    <span class="equip-cat"><?= e($eq['categorie_nom']) ?></span>
                    <?= dispo_badge($eq) ?>
                </div>
                <div class="equip-card-body">
                    <h3><?= e($eq['nom']) ?></h3>
                    <p class="equip-meta"><?= e($eq['marque']) ?> <?= e($eq['modele']) ?></p>
                    <div class="equip-card-foot">
                        <div class="price"><b><?= e(number_format($eq['prix_jour'], 2, ',', ' ')) ?> €</b><span>/ jour</span></div>
                        <a href="<?= BASE_URL ?>equipement/<?= (int) $eq['id'] ?>" class="btn btn-primary btn-sm">Louer</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
