<?php $pageTitle = 'Gestion des équipements'; ?>

<div class="toolbar">
    <form method="get" action="<?= BASE_URL ?>equipements" class="toolbar-form" data-filter-form>
        <div class="filter-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Recherche multicritères… (nom, marque, modèle, description)" data-filter-search>
        </div>
        <select name="categorie_id">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= (int) $cat['id'] ?>" <?= (string) $filters['categorie_id'] === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="prix_min" value="<?= e($filters['prix_min']) ?>" placeholder="Prix min">
        <input type="number" name="prix_max" value="<?= e($filters['prix_max']) ?>" placeholder="Prix max">
        <select name="disponible">
            <option value="">Disponibilité</option>
            <option value="oui" <?= $filters['disponible'] === 'oui' ? 'selected' : '' ?>>Disponible</option>
            <option value="non" <?= $filters['disponible'] === 'non' ? 'selected' : '' ?>>Rupture</option>
        </select>
        <select name="alerte">
            <option value="">Seuil d'alerte</option>
            <option value="oui" <?= $filters['alerte'] === 'oui' ? 'selected' : '' ?>>Sous le seuil</option>
            <option value="non" <?= $filters['alerte'] === 'non' ? 'selected' : '' ?>>Au-dessus du seuil</option>
        </select>
        <select name="etat">
            <option value="">État</option>
            <option value="disponible" <?= $filters['etat'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
            <option value="en_location" <?= $filters['etat'] === 'en_location' ? 'selected' : '' ?>>En location</option>
            <option value="maintenance" <?= $filters['etat'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            <option value="endommage" <?= $filters['etat'] === 'endommage' ? 'selected' : '' ?>>Endommagé</option>
        </select>
        <select name="tri">
            <option value="nom_asc" <?= $filters['tri'] === 'nom_asc' ? 'selected' : '' ?>>Tri</option>
            <option value="prix_asc" <?= $filters['tri'] === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
            <option value="prix_desc" <?= $filters['tri'] === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            <option value="stock_asc" <?= $filters['tri'] === 'stock_asc' ? 'selected' : '' ?>>Stock croissant</option>
            <option value="stock_desc" <?= $filters['tri'] === 'stock_desc' ? 'selected' : '' ?>>Stock décroissant</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i></button>
        <?php if (array_filter($filters, fn($v) => $v !== '')): ?>
        <a href="<?= BASE_URL ?>equipements" class="btn btn-ghost" title="Réinitialiser"><i class="fas fa-rotate-left"></i></a>
        <?php endif; ?>
    </form>
    <a href="<?= BASE_URL ?>equipements/creer" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel équipement</a>
</div>

<?php if (!empty($alertes)): ?>
<div class="alert alert-warning reveal">
    <i class="fas fa-triangle-exclamation"></i>
    <div><b><?= count($alertes) ?> équipement(s) sous le seuil d'alerte.</b> Pensez à réapprovisionner.</div>
</div>
<?php endif; ?>

<div class="panel reveal">
    <div class="panel-head">
        <h3><i class="fas fa-gear"></i> Catalogue des équipements <span class="count-pill"><?= count($equipements) ?></span></h3>
    </div>
    <?php if (empty($equipements)): ?>
    <div class="panel-empty"><i class="fas fa-box-open"></i><p>Aucun équipement ne correspond à vos critères.</p></div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Équipement</th><th>Catégorie</th><th>Prix / jour</th>
                    <th>Stock</th><th>Disponible</th><th>Seuil</th><th>État</th><th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($equipements as $eq): ?>
                <tr class="<?= (int) $eq['stock_disponible'] <= (int) $eq['seuil_alerte'] ? 'row-alert' : '' ?>">
                    <td>
                        <div class="cell-eq">
                            <div class="avatar avatar-img"><img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt=""></div>
                            <div><b><?= e($eq['nom']) ?></b><span class="cell-sub"><?= e($eq['marque']) ?> <?= e($eq['modele']) ?></span></div>
                        </div>
                    </td>
                    <td><span class="tag"><?= e($eq['categorie_nom']) ?></span></td>
                    <td><b><?= e(montant(prix_actuel($eq))) ?></b><?= promo_badge($eq) ?></td>
                    <td><?= (int) $eq['stock_total'] ?></td>
                    <td>
                        <?php if ((int) $eq['stock_disponible'] <= (int) $eq['seuil_alerte']): ?>
                            <span class="stock-mini warn"><i class="fas fa-triangle-exclamation"></i> <?= (int) $eq['stock_disponible'] ?></span>
                        <?php else: ?>
                            <span class="stock-mini ok"><i class="fas fa-check"></i> <?= (int) $eq['stock_disponible'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $eq['seuil_alerte'] ?></td>
                    <td>
                        <?= etat_badge($eq['etat']) ?>
                    </td>
                    <td>
                        <div class="row-actions">
                            <a href="<?= BASE_URL ?>equipement/<?= (int) $eq['id'] ?>" class="btn-icon" title="Voir"><i class="fas fa-eye"></i></a>
                            <a href="<?= BASE_URL ?>equipements/modifier/<?= (int) $eq['id'] ?>" class="btn-icon" title="Modifier"><i class="fas fa-pen"></i></a>
                            <a href="<?= BASE_URL ?>equipements/supprimer/<?= (int) $eq['id'] ?>" class="btn-icon danger" title="Supprimer"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
