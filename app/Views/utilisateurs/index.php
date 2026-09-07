<?php $pageTitle = 'Gestion des utilisateurs'; ?>

<div class="toolbar">
    <p class="toolbar-count"><?= count($utilisateurs) ?> utilisateur(s)</p>
    <a href="<?= BASE_URL ?>utilisateurs/creer" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel utilisateur</a>
</div>

<div class="panel reveal">
    <div class="panel-head">
        <h3><i class="fas fa-users"></i> Comptes de la plateforme</h3>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Utilisateur</th><th>Contact</th><th>Rôle</th><th>Inscrit le</th><th class="th-actions">Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($utilisateurs as $u): ?>
                <tr>
                    <td>
                        <div class="cell-eq">
                            <div class="avatar avatar-img"><?php if (!empty($u['photo'])): ?><img src="<?= e(image_url($u['photo'])) ?>" alt="Photo de profil"><?php else: ?><?= e(mb_strtoupper(mb_substr($u['prenom'], 0, 1))) ?><?= e(mb_strtoupper(mb_substr($u['nom'], 0, 1))) ?><?php endif; ?></div>
                            <div><b><?= e($u['prenom']) ?> <?= e($u['nom']) ?></b><span class="cell-sub"><?= e($u['email']) ?></span></div>
                        </div>
                    </td>
                    <td><?= e($u['telephone'] ?? '—') ?><br><span class="cell-sub"><?= e($u['adresse'] ?? '') ?></span></td>
                    <td>
                        <span class="badge badge-<?= $u['role'] === 'responsable' ? 'violet' : ($u['role'] === 'agent' ? 'primary' : 'info') ?>">
                            <?= e(role_label($u['role'])) ?>
                        </span>
                    </td>
                    <td><?= e(datetime_fr($u['cree_le'])) ?></td>
                    <td>
                        <div class="row-actions">
                            <a href="<?= BASE_URL ?>utilisateurs/modifier/<?= (int) $u['id'] ?>" class="btn-icon" title="Modifier"><i class="fas fa-pen"></i></a>
                            <a href="<?= BASE_URL ?>utilisateurs/supprimer/<?= (int) $u['id'] ?>" class="btn-icon danger" title="Supprimer"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
