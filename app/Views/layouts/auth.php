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
<body class="page-auth">

<div class="auth-bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="auth-grid"></div>
</div>

<a href="<?= BASE_URL ?>" class="auth-back"><i class="fas fa-arrow-left"></i> Retour au site</a>

<div class="auth-shell">
    <div class="auth-aside">
        <div class="auth-brand">
            <span class="brand-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
            <span class="brand-text">Rent<em>ova</em></span>
        </div>
        <h2><?= e(APP_TAGLINE) ?></h2>
        <p>Des milliers d'équipements professionnels, livrés en 24h partout en France. Gérez vos chantiers, vos événements et vos espaces verts en toute simplicité.</p>
        <ul class="auth-features">
            <li><i class="fas fa-check-circle"></i> Matériel vérifié et entretenu</li>
            <li><i class="fas fa-check-circle"></i> Tarifs transparents, sans surprise</li>
            <li><i class="fas fa-check-circle"></i> Documents et factures instantanés</li>
        </ul>
        <div class="auth-stats">
            <div><b>+1 200</b><span>Équipements</span></div>
            <div><b>98%</b><span>Clients satisfaits</span></div>
            <div><b>24h</b><span>Livraison</span></div>
        </div>
    </div>
    <div class="auth-card">
        <?= $content ?>
    </div>
</div>

<?php require __DIR__ . '/_flash.php'; ?>
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= ASSETS ?>js/app.js"></script>
<script src="<?= ASSETS ?>js/validation.js"></script>
</body>
</html>
