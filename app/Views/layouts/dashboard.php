<?php use App\Core\Auth; ?>
<?php
$reqUri   = str_replace(['https://', 'http://'], '', ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''));
$baseUri  = str_replace(['https://', 'http://'], '', rtrim(BASE_URL, '/'));
$current  = trim(substr($reqUri, strlen($baseUri)), '/');
$section  = explode('/', $current)[0];
$role     = Auth::role();
$user     = Auth::user();
$pending  = \App\Models\Location::count('en_attente');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Espace') ?> · <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS ?>css/style.css">
<link rel="icon" href="<?= ASSETS ?>img/logo.png">
</head>
<body class="page-dashboard">

<div class="app-shell">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-head">
            <a href="<?= BASE_URL ?>dashboard" class="brand brand-dark">
                <span class="brand-logo"><img src="<?= ASSETS ?>img/logo.png" alt="Rentova"></span>
                <span class="brand-text">Rent<em>ova</em></span>
            </a>
            <button class="sidebar-close" id="sidebarClose" aria-label="Fermer"><i class="fas fa-times"></i></button>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-label">Navigation</p>
            <a href="<?= BASE_URL ?>dashboard" class="<?= $section === 'dashboard' || $current === '' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Tableau de bord</span>
            </a>
            <?php if ($role === 'client'): ?>
                <a href="<?= BASE_URL ?>catalogue" class="<?= $section === 'catalogue' ? 'active' : '' ?>">
                    <i class="fas fa-box-open"></i><span>Catalogue</span>
                </a>
                <a href="<?= BASE_URL ?>mes-locations" class="<?= $section === 'mes-locations' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-check"></i><span>Mes locations</span>
                </a>
            <?php endif; ?>

            <?php if (in_array($role, ['agent', 'responsable'], true)): ?>
                <p class="nav-label">Exploitation</p>
                <a href="<?= BASE_URL ?>locations" class="<?= $section === 'locations' ? 'active' : '' ?>">
                    <i class="fas fa-truck-ramp-box"></i><span>Locations</span>
                    <?php if ($pending > 0): ?><em class="nav-badge"><?= $pending ?></em><?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if ($role === 'responsable'): ?>
                <p class="nav-label">Inventaire</p>
                <a href="<?= BASE_URL ?>equipements" class="<?= $section === 'equipements' ? 'active' : '' ?>">
                    <i class="fas fa-gear"></i><span>Équipements</span>
                    <?php if (count(\App\Models\Equipement::enAlerte()) > 0): ?><em class="nav-badge warn">!</em><?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>categories" class="<?= $section === 'categories' ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i><span>Catégories</span>
                </a>
                <a href="<?= BASE_URL ?>utilisateurs" class="<?= $section === 'utilisateurs' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i><span>Utilisateurs</span>
                </a>
            <?php endif; ?>

            <p class="nav-label">Compte</p>
            <a href="<?= BASE_URL ?>profil" class="<?= $section === 'profil' ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i><span>Mon profil</span>
            </a>
            <a href="<?= BASE_URL ?>"><i class="fas fa-globe"></i><span>Voir le site</span></a>
            <form method="post" action="<?= BASE_URL ?>logout" class="sidebar-logout">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit"><i class="fas fa-right-from-bracket"></i><span>Déconnexion</span></button>
            </form>
        </nav>

        <div class="sidebar-user">
            <div class="avatar avatar-img avatar-sm"><?php if (!empty($user['photo'])): ?><img src="<?= e(image_url($user['photo'])) ?>" alt="Photo de profil"><?php else: ?><?= e(mb_strtoupper(mb_substr($user['prenom'] ?? 'U', 0, 1))) ?><?php endif; ?></div>
            <div class="sidebar-user-info">
                <strong><?= e(trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''))) ?></strong>
                <span><?= e(role_label($role)) ?></span>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="app-main">
        <header class="topbar">
            <button class="topbar-burger" id="sidebarOpen" aria-label="Ouvrir le menu"><i class="fas fa-bars"></i></button>
            <div class="topbar-title">
                <h1><?= e($pageTitle ?? 'Tableau de bord') ?></h1>
                <p><?= e(date_longue_fr(date('Y-m-d'))) ?></p>
            </div>
            <div class="topbar-actions">
                <?php if ($role === 'responsable'): ?>
                    <a href="<?= BASE_URL ?>equipements/creer" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Ajouter un équipement
                    </a>
                <?php elseif ($role === 'agent'): ?>
                    <a href="<?= BASE_URL ?>locations" class="btn btn-primary btn-sm">
                        <i class="fas fa-clock"></i> Demandes (<?= $pending ?>)
                    </a>
                <?php elseif ($role === 'client'): ?>
                    <a href="<?= BASE_URL ?>catalogue" class="btn btn-primary btn-sm">
                        <i class="fas fa-magnifying-glass"></i> Louer un équipement
                    </a>
                <?php endif; ?>
                <div class="avatar avatar-img avatar-md"><?php if (!empty($user['photo'])): ?><img src="<?= e(image_url($user['photo'])) ?>" alt="Photo de profil"><?php else: ?><?= e(mb_strtoupper(mb_substr($user['prenom'] ?? 'U', 0, 1))) ?><?php endif; ?></div>
            </div>
        </header>

        <main class="page-content"><?= $content ?></main>
    </div>
</div>

<?php require __DIR__ . '/_flash.php'; ?>
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= ASSETS ?>js/app.js"></script>
<script src="<?= ASSETS ?>js/validation.js"></script>
</body>
</html>
