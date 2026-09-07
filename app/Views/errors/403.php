<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 · <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS ?>css/style.css">
</head>
<body class="page-error">
<div class="error-box">
    <div class="error-code">4<span>0</span>3</div>
    <h1>Accès refusé</h1>
    <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    <div class="error-actions">
        <a href="<?= BASE_URL ?>" class="btn btn-ghost btn-lg"><i class="fas fa-house"></i> Accueil</a>
        <a href="<?= BASE_URL ?>dashboard" class="btn btn-primary btn-lg"><i class="fas fa-th-large"></i> Mon espace</a>
    </div>
</div>
<?php require __DIR__ . '/../partials/wave.php'; ?>
<script src="<?= ASSETS ?>js/app.js"></script>
</body>
</html>
