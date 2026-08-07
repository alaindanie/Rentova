<?php $pageTitle = 'Mon profil'; ?>

<div class="profile-grid">
    <div class="panel profile-card reveal">
        <div class="profile-avatar">
            <?php if (!empty($utilisateur['photo'])): ?>
                <img src="<?= e(image_url($utilisateur['photo'])) ?>" alt="Photo de profil">
            <?php else: ?>
                <?= e(mb_strtoupper(mb_substr($utilisateur['prenom'], 0, 1))) ?><?= e(mb_strtoupper(mb_substr($utilisateur['nom'], 0, 1))) ?>
            <?php endif; ?>
        </div>
        <h2><?= e($utilisateur['prenom']) ?> <?= e($utilisateur['nom']) ?></h2>
        <span class="badge badge-<?= $utilisateur['role'] === 'responsable' ? 'violet' : ($utilisateur['role'] === 'agent' ? 'primary' : 'info') ?>"><?= e(role_label($utilisateur['role'])) ?></span>
        <ul class="profile-info">
            <li><i class="fas fa-envelope"></i> <?= e($utilisateur['email']) ?></li>
            <li><i class="fas fa-phone"></i> <?= e($utilisateur['telephone'] ?? 'Non renseigné') ?></li>
            <li><i class="fas fa-location-dot"></i> <?= e($utilisateur['adresse'] ?? 'Non renseignée') ?></li>
            <li><i class="fas fa-calendar"></i> Membre depuis le <?= e(date_fr(substr($utilisateur['cree_le'], 0, 10))) ?></li>
        </ul>
    </div>

    <div class="panel panel-form reveal reveal-delay-1">
        <div class="panel-head">
            <h3><i class="fas fa-pen"></i> Modifier mes informations</h3>
        </div>
        <form method="post" action="<?= BASE_URL ?>profil" class="form" enctype="multipart/form-data" novalidate>
            <?= \App\Core\Csrf::field() ?>
            <div class="field">
                <label for="photo">Photo de profil</label>
                <div class="profile-photo-upload">
                    <div class="profile-photo-preview" data-upload-preview>
                        <?php if (!empty($utilisateur['photo'])): ?>
                            <img src="<?= e(image_url($utilisateur['photo'])) ?>" alt="Photo actuelle">
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="btn btn-ghost btn-sm" for="photo"><i class="fas fa-camera"></i> Choisir une photo</label>
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" data-upload-input hidden>
                        <span class="field-hint">JPG, PNG ou WEBP — 5 Mo max. (optionnel)</span>
                    </div>
                </div>
                <span class="field-error"></span>
            </div>
            <div class="form-grid-2">
                <div class="field">
                    <label for="prenom">Prénom *</label>
                    <div class="input-wrap"><i class="fas fa-user"></i>
                        <input type="text" id="prenom" name="prenom" value="<?= e($utilisateur['prenom']) ?>" data-validate="required|min:2">
                    </div>
                    <span class="field-error"></span>
                </div>
                <div class="field">
                    <label for="nom">Nom *</label>
                    <div class="input-wrap"><i class="fas fa-user"></i>
                        <input type="text" id="nom" name="nom" value="<?= e($utilisateur['nom']) ?>" data-validate="required|min:2">
                    </div>
                    <span class="field-error"></span>
                </div>
            </div>
            <div class="field">
                <label for="email">Adresse e-mail *</label>
                <div class="input-wrap"><i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" value="<?= e($utilisateur['email']) ?>" data-validate="required|email">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="form-grid-2">
                <div class="field">
                    <label for="telephone">Téléphone</label>
                    <div class="input-wrap"><i class="fas fa-phone"></i>
                        <input type="tel" id="telephone" name="telephone" value="<?= e($utilisateur['telephone'] ?? '') ?>">
                    </div>
                    <span class="field-error"></span>
                </div>
                <div class="field">
                    <label for="adresse">Adresse</label>
                    <div class="input-wrap"><i class="fas fa-location-dot"></i>
                        <input type="text" id="adresse" name="adresse" value="<?= e($utilisateur['adresse'] ?? '') ?>">
                    </div>
                    <span class="field-error"></span>
                </div>
            </div>
            <div class="form-grid-2">
                <div class="field">
                    <label for="password">Nouveau mot de passe <small>(facultatif)</small></label>
                    <div class="input-wrap"><i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" data-validate="min:6" autocomplete="new-password">
                        <button type="button" class="input-eye" data-toggle-password="#password"><i class="far fa-eye"></i></button>
                    </div>
                    <span class="field-error"></span>
                </div>
                <div class="field">
                    <label for="confirm">Confirmer le mot de passe</label>
                    <div class="input-wrap"><i class="fas fa-lock"></i>
                        <input type="password" id="confirm" name="confirm" placeholder="••••••••" data-validate="same:password">
                    </div>
                    <span class="field-error"></span>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
