<?php
$old = \App\Core\Session::get('old') ?? [];
\App\Core\Session::remove('old');
?>
<div class="auth-head">
    <h1>Créer un compte</h1>
    <p>Rejoignez <?= e(APP_NAME) ?> et louez le matériel qu'il vous faut.</p>
</div>
<form method="post" action="<?= BASE_URL ?>register" class="form" novalidate>
    <?= \App\Core\Csrf::field() ?>
    <div class="form-row">
        <div class="field">
            <label for="prenom">Prénom</label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" id="prenom" name="prenom" value="<?= e($old['prenom'] ?? '') ?>"
                       placeholder="Sophie" data-validate="required|min:2">
            </div>
            <span class="field-error"></span>
        </div>
        <div class="field">
            <label for="nom">Nom</label>
            <div class="input-wrap">
                <i class="fas fa-user"></i>
                <input type="text" id="nom" name="nom" value="<?= e($old['nom'] ?? '') ?>"
                       placeholder="Bernard" data-validate="required|min:2">
            </div>
            <span class="field-error"></span>
        </div>
    </div>
    <div class="field">
        <label for="email">Adresse e-mail</label>
        <div class="input-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>"
                   placeholder="vous@exemple.fr" data-validate="required|email">
        </div>
        <span class="field-error"></span>
    </div>
    <div class="form-row">
        <div class="field">
            <label for="telephone">Téléphone <small>(facultatif)</small></label>
            <div class="input-wrap">
                <i class="fas fa-phone"></i>
                <input type="tel" id="telephone" name="telephone" value="<?= e($old['telephone'] ?? '') ?>"
                       placeholder="06 12 34 56 78">
            </div>
            <span class="field-error"></span>
        </div>
        <div class="field">
            <label for="adresse">Adresse <small>(facultatif)</small></label>
            <div class="input-wrap">
                <i class="fas fa-location-dot"></i>
                <input type="text" id="adresse" name="adresse" value="<?= e($old['adresse'] ?? '') ?>"
                       placeholder="Ville, rue…">
            </div>
            <span class="field-error"></span>
        </div>
    </div>
    <div class="form-row">
        <div class="field">
            <label for="password">Mot de passe</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="6 caractères min."
                       data-validate="required|min:6" autocomplete="new-password">
                <button type="button" class="input-eye" data-toggle-password="#password"><i class="far fa-eye"></i></button>
            </div>
            <span class="field-error"></span>
        </div>
        <div class="field">
            <label for="confirm">Confirmer</label>
            <div class="input-wrap">
                <i class="fas fa-lock"></i>
                <input type="password" id="confirm" name="confirm" placeholder="Reprenez le mot de passe"
                       data-validate="required|same:password" autocomplete="new-password">
            </div>
            <span class="field-error"></span>
        </div>
    </div>
    <div class="field">
        <label class="checkbox">
            <input type="checkbox" id="cgv" name="cgv" value="1">
            <span></span>
            J'accepte les conditions d'utilisation
        </label>
        <span class="field-error"></span>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg" data-magnetic>
        <span class="btn-label">Créer mon compte</span>
        <i class="fas fa-arrow-right btn-arrow"></i>
    </button>
    <p class="form-foot">Déjà inscrit ? <a href="<?= BASE_URL ?>login">Connectez-vous</a></p>
</form>
