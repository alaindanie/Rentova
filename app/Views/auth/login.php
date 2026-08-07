<?php
$oldEmail = \App\Core\Session::get('old_email') ?? '';
\App\Core\Session::remove('old_email');
?>
<div class="auth-head">
    <h1>Bon retour !</h1>
    <p>Connectez-vous pour accéder à votre espace.</p>
</div>
<form method="post" action="<?= BASE_URL ?>login" class="form" novalidate>
    <?= \App\Core\Csrf::field() ?>
    <div class="field">
        <label for="email">Adresse e-mail</label>
        <div class="input-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" value="<?= e($oldEmail) ?>"
                   placeholder="vous@exemple.fr" data-validate="required|email" autofocus>
        </div>
        <span class="field-error"></span>
    </div>
    <div class="field">
        <label for="password">Mot de passe</label>
        <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="••••••••"
                   data-validate="required|min:6" autocomplete="current-password">
            <button type="button" class="input-eye" data-toggle-password="#password"><i class="far fa-eye"></i></button>
        </div>
        <span class="field-error"></span>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg">
        <span class="btn-label">Se connecter</span>
        <i class="fas fa-arrow-right btn-icon"></i>
    </button>
    <p class="form-foot">Pas encore de compte ? <a href="<?= BASE_URL ?>register">Inscrivez-vous</a></p>
</form>

<div class="auth-demo">
    <p><i class="fas fa-info-circle"></i> Comptes de démonstration :</p>
    <div class="demo-row">
        <button class="demo-pill" data-fill="admin@Rentova.com|password123">Responsable</button>
        <button class="demo-pill" data-fill="agent@Rentova.com|password123">Agent</button>
        <button class="demo-pill" data-fill="client@Rentova.com|password123">Client</button>
    </div>
</div>
