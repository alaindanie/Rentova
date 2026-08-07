<?php use App\Core\Auth; ?>
<?php $pageTitle = 'Accueil'; ?>

<section class="hero">
    <div class="hero-blobs">
        <span class="hero-blob b1"></span>
        <span class="hero-blob b2"></span>
        <span class="hero-blob b3"></span>
    </div>
    <div class="container hero-grid">
        <div class="hero-content reveal">
            <span class="hero-eyebrow"><i class="fas fa-bolt"></i> Livraison en 24h partout en France</span>
            <h1>Louez le bon <span class="grad-text">équipement</span>, au bon moment.</h1>
            <p>Échafaudages, outillage, matériel d'événementiel, engins de chantier… <?= e(APP_NAME) ?> met à votre disposition plus de 1 200 équipements professionnels, vérifiés et entretenus.</p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary btn-lg">
                    <i class="fas fa-magnifying-glass"></i> Explorer le catalogue
                </a>
                <a href="<?= BASE_URL ?>register" class="btn btn-ghost btn-lg">
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
    <div class="hero-wave"><svg viewBox="0 0 1440 120" preserveAspectRatio="none"><path fill="#f8fafc" d="M0,64 C360,120 720,0 1080,48 C1260,72 1380,64 1440,48 L1440,120 L0,120 Z"/></svg></div>
</section>

<section class="section section-categories">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow">Nos univers</span>
                <h2>Explorez par <span class="grad-text">catégorie</span></h2>
            </div>
            <a href="<?= BASE_URL ?>catalogue" class="link-more">Voir tout le catalogue <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="cat-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>catalogue?categorie_id=<?= (int) $cat['id'] ?>" class="cat-card reveal">
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

<section class="section section-soft">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow">Les plus demandés</span>
                <h2>Nos <span class="grad-text">best-sellers</span></h2>
            </div>
            <a href="<?= BASE_URL ?>catalogue" class="link-more">Tout le catalogue <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="equip-grid">
            <?php foreach ($plusLoues as $eq): ?>
            <article class="equip-card reveal">
                <div class="equip-card-img">
                    <img src="<?= image_url($eq['image'], IMG . 'equipements/eq_perceuse.jpg') ?>" alt="<?= e($eq['nom']) ?>" loading="lazy">
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
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head reveal">
            <div>
                <span class="section-eyebrow">Comment ça marche ?</span>
                <h2>Louer en <span class="grad-text">3 étapes</span></h2>
            </div>
        </div>
        <div class="steps">
            <div class="step reveal">
                <div class="step-icon"><i class="fas fa-magnifying-glass"></i></div>
                <h3>1. Choisissez</h3>
                <p>Parcourez le catalogue et sélectionnez l'équipement adapté à vos besoins et à votre budget.</p>
            </div>
            <div class="step reveal reveal-delay-1">
                <div class="step-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>2. Réservez</h3>
                <p>Indiquez vos dates et la durée. Votre demande est confirmée en moins de 2 heures ouvrées.</p>
            </div>
            <div class="step reveal reveal-delay-2">
                <div class="step-icon"><i class="fas fa-truck-fast"></i></div>
                <h3>3. Recevez</h3>
                <p>Nous livrons, vous profitez. À la fin, retournez le matériel et téléchargez votre facture.</p>
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
        <a href="<?= Auth::check() ? BASE_URL . 'catalogue' : BASE_URL . 'register' ?>" class="btn btn-light btn-lg">
            <?= Auth::check() ? 'Commencer une location' : 'Créer un compte gratuit' ?>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
