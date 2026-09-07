<?php
$oldEmail = \App\Core\Session::get('old_email') ?? '';
\App\Core\Session::remove('old_email');
?>
<div class="auth-card-top">
    <span class="auth-card-icon"><i class="fas fa-user-astronaut"></i></span>
    <span class="auth-chip"><i class="fas fa-shield-halved"></i> Espace personnel sécurisé</span>
</div>

<div class="auth-head">
    <h1>Bon retour <span class="grad-text anim">sur Rentova</span> !</h1>
    <p>Connectez-vous pour retrouver vos locations, suivre votre matériel et générer vos documents.</p>
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

    <button type="submit" class="btn btn-primary btn-block btn-lg" data-magnetic>
        <span class="btn-label">Se connecter</span>
        <i class="fas fa-arrow-right btn-arrow"></i>
    </button>

    <div class="auth-divider"><span>Pas encore de compte ?</span></div>

    <a href="<?= BASE_URL ?>register" class="btn btn-ghost btn-block btn-lg"><i class="fas fa-user-plus"></i> Créer un compte gratuit</a>
</form>
