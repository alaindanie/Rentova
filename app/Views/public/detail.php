<?php use App\Core\Auth; ?>
<?php $pageTitle = $equipement['nom']; ?>

<section class="page-hero page-hero-sm page-hero-detail" data-glow>
    <div class="hero-blobs">
        <span class="hero-blob b1"></span>
        <span class="hero-blob b2"></span>
        <span class="hero-blob b3"></span>
    </div>
    <div class="container">
        <nav class="breadcrumb breadcrumb-light">
            <a href="<?= BASE_URL ?>">Accueil</a> <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>catalogue">Catalogue</a> <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>catalogue?categorie_id=<?= (int) $equipement['categorie_id'] ?>"><?= e($equipement['categorie_nom']) ?></a>
        </nav>
    </div>
    <?php require __DIR__ . '/../partials/wave.php'; ?>
</section>

<section class="section section-detail">
    <div class="container">
        <div class="detail-grid">
            <div class="detail-gallery reveal">
                <div class="detail-img-main">
                    <img src="<?= image_url($equipement['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($equipement['nom']) ?>" onerror="this.onerror=null;this.src='<?= IMG ?>equipements/eq_perceuse.jpg'">
                    <?= dispo_badge($equipement) ?>
                </div>
            </div>

            <div class="detail-info reveal reveal-delay-1">
                <span class="detail-cat"><i class="fas fa-tag"></i> <?= e($equipement['categorie_nom']) ?></span>
                <h1><?= e($equipement['nom']) ?></h1>
                <p class="detail-brand"><?= e($equipement['marque']) ?> <?= e($equipement['modele']) ?></p>

                <div class="detail-price">
                    <?php if (est_en_promo($equipement)): ?>
                        <span class="old"><?= e(number_format($equipement['prix_jour'], 2, ',', ' ')) ?> €</span>
                        <b><?= e(number_format($equipement['prix_promo'], 2, ',', ' ')) ?> €</b>
                        <?= promo_badge($equipement) ?>
                    <?php else: ?>
                        <b><?= e(number_format($equipement['prix_jour'], 2, ',', ' ')) ?> €</b>
                    <?php endif; ?>
                    <span>/ jour</span>
                    <small>Hors frais éventuels</small>
                </div>

                <p class="detail-desc"><?= e($equipement['description']) ?></p>

                <ul class="detail-specs">
                    <li><i class="fas fa-boxes-stacked"></i><div><b>Stock disponible</b><span><?= (int) $equipement['stock_disponible'] ?> unité(s)</span></div></li>
                    <li><i class="fas fa-signal"></i><div><b>État</b><span><?= e(etat_label($equipement['etat'])) ?></span></div></li>
                    <li><i class="fas fa-shield-halved"></i><div><b>Assurance</b><span>Incluse dans la location</span></div></li>
                    <li><i class="fas fa-truck"></i><div><b>Livraison</b><span>Disponible sous 24h</span></div></li>
                </ul>

                <?php if (Auth::check() && Auth::is('client')): ?>
                    <?php if ((int) $equipement['stock_disponible'] > 0): ?>
                    <a href="<?= BASE_URL ?>demande/<?= (int) $equipement['id'] ?>" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-calendar-plus"></i> Faire une demande de location
                    </a>
                    <?php else: ?>
                    <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> Cet équipement est actuellement en rupture de stock.</div>
                    <?php endif; ?>
                <?php elseif (Auth::check()): ?>
                    <a href="<?= BASE_URL ?>dashboard" class="btn btn-primary btn-lg btn-block"><i class="fas fa-arrow-right"></i> Gérer dans mon espace</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-right-to-bracket"></i> Connectez-vous pour louer
                    </a>
                    <p class="detail-login-hint">Nouveau ici ? <a href="<?= BASE_URL ?>register">Créez un compte</a> en 1 minute.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-head reveal" style="margin-top:48px">
            <div>
                <span class="section-eyebrow">Suggestions</span>
                <h2>Dans la même <span class="grad-text">catégorie</span></h2>
            </div>
        </div>
        <div class="equip-grid">
            <?php foreach (array_slice($similaires, 0, 4) as $eq): if ((int) $eq['id'] === (int) $equipement['id']) continue; ?>
            <article class="equip-card reveal">
                <div class="equip-card-img">
                    <img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($eq['nom']) ?>" loading="lazy">
                    <span class="equip-cat"><?= e($eq['categorie_nom']) ?></span>
                    <?= promo_badge($eq) ?>
                    <?= dispo_badge($eq) ?>
                </div>
                <div class="equip-card-body">
                    <h3><?= e($eq['nom']) ?></h3>
                    <p class="equip-meta"><?= e($eq['marque']) ?> <?= e($eq['modele']) ?></p>
                    <div class="equip-card-foot">
                        <div class="price"><?= price_block($eq) ?></div>
                        <a href="<?= BASE_URL ?>equipement/<?= (int) $eq['id'] ?>" class="btn btn-primary btn-sm">Voir</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
