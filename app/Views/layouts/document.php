<?php use App\Core\Auth; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Document') ?> · <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS ?>css/style.css">
<link rel="icon" href="<?= ASSETS ?>img/logo.png">
</head>
<body class="page-document">

<header class="doc-toolbar no-print">
    <a href="<?= BASE_URL ?>dashboard"><i class="fas fa-arrow-left"></i> Retour</a>
    <div class="doc-toolbar-title"><?= e($pageTitle ?? 'Document') ?></div>
    <div class="doc-toolbar-actions">
        <button class="btn btn-ghost btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
        <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>location/<?= (int) $location['id'] ?>/<?= e($docType ?? '') ?>/pdf"><i class="fas fa-file-pdf"></i> Télécharger PDF</a>
    </div>
</header>

<main class="doc-page"><?= $content ?></main>

<?php require __DIR__ . '/_flash.php'; ?>
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= ASSETS ?>js/app.js"></script>
</body>
</html>
