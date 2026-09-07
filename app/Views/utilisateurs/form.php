<?php $pageTitle = $title; $isEdit = $utilisateur !== null; ?>

<div class="panel panel-form reveal">
    <div class="panel-head">
        <h3><i class="fas fa-user-<?= $isEdit ? 'pen' : 'plus' ?>"></i> <?= e($title) ?></h3>
        <a href="<?= BASE_URL ?>utilisateurs" class="link-more-inline">Retour à la liste</a>
    </div>

    <form method="post" action="<?= BASE_URL . ($isEdit ? 'utilisateurs/modifier/' . $utilisateur['id'] : 'utilisateurs') ?>" class="form" enctype="multipart/form-data" novalidate>
        <?= \App\Core\Csrf::field() ?>

        <div class="form-grid-2">
            <div class="field">
                <label for="prenom">Prénom *</label>
                <div class="input-wrap"><i class="fas fa-user"></i>
                    <input type="text" id="prenom" name="prenom" value="<?= e($utilisateur['prenom'] ?? '') ?>" placeholder="Prénom">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="nom">Nom *</label>
                <div class="input-wrap"><i class="fas fa-user"></i>
                    <input type="text" id="nom" name="nom" value="<?= e($utilisateur['nom'] ?? '') ?>" placeholder="Nom">
                </div>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="field">
            <label for="email">Adresse e-mail *</label>
            <div class="input-wrap"><i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" value="<?= e($utilisateur['email'] ?? '') ?>" placeholder="vous@exemple.fr">
            </div>
            <span class="field-error"></span>
        </div>

        <div class="form-grid-2">
            <div class="field">
                <label for="telephone">Téléphone</label>
                <div class="input-wrap"><i class="fas fa-phone"></i>
                    <input type="tel" id="telephone" name="telephone" value="<?= e($utilisateur['telephone'] ?? '') ?>" placeholder="06 12 34 56 78">
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="adresse">Adresse</label>
                <div class="input-wrap"><i class="fas fa-location-dot"></i>
                    <input type="text" id="adresse" name="adresse" value="<?= e($utilisateur['adresse'] ?? '') ?>" placeholder="Adresse complète">
                </div>
                <span class="field-error"></span>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="field">
                <label for="role">Rôle *</label>
                <div class="input-wrap"><i class="fas fa-user-shield"></i>
                    <select id="role" name="role">
                        <option value="client" <?= isset($utilisateur) && $utilisateur['role'] === 'client' ? 'selected' : '' ?>>Client</option>
                        <option value="agent" <?= isset($utilisateur) && $utilisateur['role'] === 'agent' ? 'selected' : '' ?>>Agent de location</option>
                        <option value="responsable" <?= isset($utilisateur) && $utilisateur['role'] === 'responsable' ? 'selected' : '' ?>>Responsable inventaire</option>
                    </select>
                </div>
                <span class="field-error"></span>
            </div>
            <div class="field">
                <label for="password">Mot de passe <?= $isEdit ? '<small>(laisser vide pour conserver)</small>' : '*' ?></label>
                <div class="input-wrap"><i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="<?= $isEdit ? '••••••••' : '6 caractères min.' ?>" autocomplete="new-password">
                    <button type="button" class="input-eye" data-toggle-password="#password"><i class="far fa-eye"></i></button>
                </div>
                <span class="field-error"></span>
            </div>
        </div>

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

        <div class="form-actions">
            <a href="<?= BASE_URL ?>utilisateurs" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> <?= $isEdit ? 'Enregistrer' : 'Créer l\'utilisateur' ?></button>
        </div>
    </form>
</div>
