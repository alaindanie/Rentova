<?php use App\Core\Auth; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? APP_NAME) ?> · <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS ?>css/style.css">
<link rel="icon" href="<?= ASSETS ?>img/logo.png">
</head>
<body class="page-public">

<div class="preloader" id="preloader">
    <div class="preloader-box">
        <span class="preloader-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
        <span class="preloader-brand">Rent<em>ova</em></span>
        <span class="preloader-bar"><i></i></span>
    </div>
</div>
<noscript><style>.preloader{display:none!important}</style></noscript>

<div class="scroll-progress" data-scroll-progress></div>

<header class="navbar public-nav" id="navbar">
    <div class="container nav-inner">
        <a href="<?= BASE_URL ?>" class="brand">
            <span class="brand-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
            <span class="brand-text">Rent<em>ova</em></span>
        </a>
        <nav class="nav-links" id="navLinks">
            <a href="<?= BASE_URL ?>" class="<?= (($uri ?? '') === '') ? 'active' : '' ?>">Accueil</a>
            <a href="<?= BASE_URL ?>catalogue" class="<?= (($uri ?? '') === 'catalogue') ? 'active' : '' ?>">Catalogue</a>
            <?php if (Auth::check()): ?>
                <a href="<?= BASE_URL ?>dashboard">Mon espace</a>
                <form method="post" action="<?= BASE_URL ?>logout" class="nav-logout">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="nav-cta">Déconnexion</button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login">Connexion</a>
                <a href="<?= BASE_URL ?>register" class="nav-cta">Créer un compte</a>
            <?php endif; ?>
        </nav>
        <button class="nav-toggle" id="navToggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
    </div>
</header>

<main><?= $content ?></main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <span class="brand-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
            <p>La plateforme de référence pour la location d'équipements professionnels en France.</p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Navigation</h4>
            <a href="<?= BASE_URL ?>">Accueil</a>
            <a href="<?= BASE_URL ?>catalogue">Catalogue</a>
            <a href="<?= BASE_URL ?>login">Espace client</a>
            <a href="<?= BASE_URL ?>register">Inscription</a>
        </div>
        <div class="footer-col">
            <h4>Nos services</h4>
            <a href="#">Location courte durée</a>
            <a href="#">Location longue durée</a>
            <a href="#">Livraison &amp; récupération</a>
            <a href="#">Maintenance incluse</a>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <p><i class="fas fa-map-marker-alt"></i> 12 rue de la République, Paris</p>
            <p><i class="fas fa-phone"></i> 01 23 45 67 89</p>
            <p><i class="fas fa-envelope"></i> contact@rentova.fr</p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">© <?= date('Y') ?> <?= e(APP_NAME) ?> — Tous droits réservés.</div>
    </div>
</footer>

<?php require __DIR__ . '/_flash.php'; ?>
<button class="fab-top" data-top aria-label="Retour en haut"><i class="fas fa-arrow-up"></i></button>
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= ASSETS ?>js/app.js"></script>
<script src="<?= ASSETS ?>js/validation.js"></script>
</body>
</html>
