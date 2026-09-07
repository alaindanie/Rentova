<?php use App\Core\Auth; ?>
<?php $pageTitle = 'Accueil'; ?>

<section class="hero" data-glow>
    <div class="hero-blobs">
        <span class="hero-blob b1"></span>
        <span class="hero-blob b2"></span>
        <span class="hero-blob b3"></span>
    </div>
    <div class="container hero-grid">
        <div class="hero-content reveal">
            <span class="hero-eyebrow"><i class="fas fa-bolt"></i> Livraison en 24h partout en France</span>
            <h1>Louez le bon <span class="grad-text anim">équipement</span>, au bon moment.</h1>
            <p>Échafaudages, outillage, matériel d'événementiel, engins de chantier… <?= e(APP_NAME) ?> met à votre disposition plus de 1 200 équipements professionnels, vérifiés et entretenus.</p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary btn-lg" data-magnetic>
                    <i class="fas fa-magnifying-glass"></i> Explorer le catalogue
                </a>
                <a href="<?= BASE_URL ?>register" class="btn btn-ghost btn-lg" data-magnetic>
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <b class="counter" data-count="<?= e($countEquipements) ?>">0</b>
                    <span>Équipements</span>
                </div>
                <div class="hero-stat">
                    <b class="counter" data-count="<?= e($countCategories) ?>">0</b>
                    <span>Catégories</span>
                </div>
                <div class="hero-stat">
                    <b class="counter" data-count="<?= e($stockDisponible) ?>">0</b>
                    <span>Unités en stock</span>
                </div>
            </div>
        </div>
        <div class="hero-visual reveal reveal-delay-1">
            <?php if (!empty($plusLoues)): ?>
            <div class="hero-card main" data-hero-slider>
                <?php foreach ($plusLoues as $i => $eq): ?>
                <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>">
                    <div class="hero-card-img">
                        <img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($eq['nom']) ?>">
                        <?php if ((int) $eq['stock_disponible'] <= 0): ?>
                            <span class="hero-tag danger"><i class="fas fa-circle-xmark"></i> Rupture</span>
                        <?php else: ?>
                            <span class="hero-tag"><i class="fas fa-check-circle"></i> Disponible</span>
                        <?php endif; ?>
                    </div>
                    <div class="hero-card-body">
                        <h3><?= e($eq['nom']) ?></h3>
                        <p><?= e($eq['marque']) ?> <?= e($eq['modele']) ?></p>
                        <div class="hero-card-foot">
                            <b><?= e(number_format($eq['prix_jour'], 2, ',', ' ')) ?> € <small>/jour</small></b>
                            <a href="<?= BASE_URL ?>equipement/<?= (int) $eq['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="hero-dots">
                    <?php foreach ($plusLoues as $k => $dummy): ?>
                        <button type="button" class="hero-dot<?= $k === 0 ? ' active' : '' ?>" data-hero-dot="<?= (int) $k ?>" aria-label="Équipement <?= $k + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="hero-card main">
                <div class="hero-card-img">
                    <img src="<?= IMG ?>equipements/eq_perceuse.jpg" alt="Perceuse-visseuse" loading="lazy">
                    <span class="hero-tag"><i class="fas fa-check-circle"></i> Disponible</span>
                </div>
                <div class="hero-card-body">
                    <h3>Perceuse-visseuse sans fil</h3>
                    <p>BOSCH GSR 18V-60 · 20 V · 2 batteries</p>
                    <div class="hero-card-foot">
                        <b>25 € <small>/jour</small></b>
                        <a href="<?= BASE_URL ?>equipement/4" class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="hero-card mini mini-1">
                <i class="fas fa-shield-halved"></i>
                <div><b>Assurance incluse</b><span>Chaque location est couverte</span></div>
            </div>
            <div class="hero-card mini mini-2">
                <i class="fas fa-truck-fast"></i>
                <div><b>Livraison rapide</b><span>Sur chantier ou à domicile</span></div>
            </div>
        </div>
    </div>
    <?php require __DIR__ . '/../partials/wave.php'; ?>
</section>

<section class="section section-banner">
    <div class="container">
        <a href="<?= BASE_URL ?>catalogue" class="banner-pub reveal" data-glow>
            <div class="banner-pub-icon"><i class="fas fa-truck-ramp-box"></i></div>
            <div class="banner-pub-text">
                <span class="banner-pub-tag"><i class="fas fa-gem"></i> Partenaire <?= e(APP_NAME) ?></span>
                <h3>LocaPro Chantier : -20% sur les engins de chantier ce mois-ci</h3>
                <p>Échafaudages, minipelles, groupes électrogènes… profitez de tarifs négociés pour vos travaux. Offre valable jusqu'au 31 du mois.</p>
            </div>
            <div class="banner-pub-cta">
                <span class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i> Découvrir</span>
            </div>
        </a>
    </div>
</section>

<section class="section section-categories">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow">Nos univers</span>
                <h2>Explorez par <span class="grad-text anim">catégorie</span></h2>
            </div>
            <a href="<?= BASE_URL ?>catalogue" class="link-more">Voir tout le catalogue <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="cat-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>catalogue?categorie_id=<?= (int) $cat['id'] ?>" class="cat-card reveal" data-tilt="9">
                <div class="cat-card-img">
                    <img src="<?= image_url($cat['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($cat['nom']) ?>" loading="lazy">
                    <span class="cat-count"><?= (int) $cat['nb_equipements'] ?> articles</span>
                </div>
                <div class="cat-card-body">
                    <h3><?= e($cat['nom']) ?></h3>
                    <p><?= e(excerpt($cat['description'], 70)) ?></p>
                    <span class="cat-link">Découvrir <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-promo">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow"><i class="fas fa-fire"></i> Offres du moment</span>
                <h2>Les <span class="grad-text anim">promos</span> de la semaine</h2>
            </div>
            <a href="<?= BASE_URL ?>catalogue" class="link-more">Voir tout le catalogue <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php if (empty($promos)): ?>
        <div class="empty-state reveal">
            <i class="fas fa-tags"></i>
            <h3>Bientôt des promos !</h3>
            <p>Les offres spéciales de la semaine arrivent très vite. Revenez bientôt.</p>
        </div>
        <?php else: ?>
        <div class="equip-grid">
            <?php foreach ($promos as $eq): ?>
            <article class="equip-card reveal" data-tilt="8">
                <div class="equip-card-img">
                    <img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($eq['nom']) ?>" loading="lazy">
                    <span class="equip-cat"><?= e($eq['categorie_nom']) ?></span>
                    <?= promo_badge($eq) ?>
                </div>
                <div class="equip-card-body">
                    <h3><?= e($eq['nom']) ?></h3>
                    <p class="equip-meta"><?= e($eq['marque']) ?> <?= e($eq['modele']) ?></p>
                    <div class="equip-card-foot">
                        <div class="price"><?= price_block($eq) ?></div>
                        <a href="<?= BASE_URL ?>equipement/<?= (int) $eq['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow">Les plus demandés</span>
                <h2>Nos <span class="grad-text anim">best-sellers</span></h2>
            </div>
            <a href="<?= BASE_URL ?>catalogue" class="link-more">Tout le catalogue <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="equip-grid">
            <?php foreach ($plusLoues as $eq): ?>
            <article class="equip-card reveal" data-tilt="8">
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
                        <a href="<?= BASE_URL ?>equipement/<?= (int) $eq['id'] ?>" class="btn btn-ghost btn-sm">Voir</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-adv">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow">Nos engagements</span>
                <h2>Pourquoi nous <span class="grad-text anim">choisir</span> ?</h2>
            </div>
        </div>
        <div class="adv-grid">
            <div class="adv-card reveal" data-tilt="6">
                <div class="adv-icon"><i class="fas fa-shield-halved"></i></div>
                <h3>Assurance incluse</h3>
                <p>Chaque location est couverte en responsabilité civile et vol, sans supplément.</p>
            </div>
            <div class="adv-card reveal reveal-delay-1" data-tilt="6">
                <div class="adv-icon"><i class="fas fa-truck-fast"></i></div>
                <h3>Livraison en 24h</h3>
                <p>Livré sur chantier ou à domicile partout en France, et récupéré en fin de location.</p>
            </div>
            <div class="adv-card reveal reveal-delay-2" data-tilt="6">
                <div class="adv-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3>Prix transparents</h3>
                <p>Tarifs clairs à la journée, zéro frais caché, facture PDF téléchargeable aussitôt.</p>
            </div>
            <div class="adv-card reveal reveal-delay-3" data-tilt="6">
                <div class="adv-icon"><i class="fas fa-headset"></i></div>
                <h3>Support 7j/7</h3>
                <p>Une équipe d'experts disponible pour vous conseiller et vous dépanner rapidement.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-brands">
    <div class="container">
        <div class="section-head brands-head reveal">
            <div>
                <span class="section-eyebrow">Notre parc</span>
                <h2>Les grandes <span class="grad-text anim">marques</span> que nous louons</h2>
                <p>Du matériel professionnel d'origine, entretenu et contrôlé entre chaque location.</p>
            </div>
        </div>
        <div class="brands-marquee reveal">
            <div class="brands-track">
                <?php $marques = [
                    ['BOSCH', 'fa-bolt'], ['STIHL', 'fa-leaf'], ['HUSQVARNA', 'fa-gear'],
                    ['HILTI', 'fa-hammer'], ['MAKITA', 'fa-screwdriver-wrench'], ['DEWALT', 'fa-screwdriver'],
                    ['MILWAUKEE', 'fa-battery-full'], ['FEIN', 'fa-fan'], ['WACKER NEUSON', 'fa-industry'],
                    ['BOMAG', 'fa-road'],
                ]; ?>
                <?php for ($i = 0; $i < 2; $i++): foreach ($marques as $m): ?>
                <span class="brand-pill"><i class="fas <?= $m[1] ?>"></i><?= $m[0] ?></span>
                <?php endforeach; endfor; ?>
            </div>
        </div>
    </div>
</section>

<section class="section cta-band">
    <div class="container cta-inner reveal">
        <div>
            <h2>Prêt à lancer votre projet ?</h2>
            <p>Rejoignez plus de 5 000 professionnels et particuliers qui nous font confiance.</p>
        </div>
        <a href="<?= Auth::check() ? BASE_URL . 'catalogue' : BASE_URL . 'register' ?>" class="btn btn-light btn-lg" data-magnetic>
            <?= Auth::check() ? 'Commencer une location' : 'Créer un compte gratuit' ?>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
