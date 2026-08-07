<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>404 · <?= e(APP_NAME) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS ?>css/style.css">
</head>
<body class="page-error">
<div class="error-box">
    <div class="error-code">4<span>0</span>4</div>
    <h1>Page introuvable</h1>
    <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
    <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg"><i class="fas fa-house"></i> Retour à l'accueil</a>
</div>
<script src="<?= ASSETS ?>js/app.js"></script>
</body>
</html>
