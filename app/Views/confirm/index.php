<?php $pageTitle = $confirmTitle; ?>

<div class="detail-head reveal">
    <div>
        <a href="<?= $backUrl ?>" class="link-more-inline"><i class="fas fa-arrow-left"></i> <?= $backLabel ?></a>
        <h1><?= $confirmTitle ?></h1>
    </div>
</div>

<div class="confirm-box reveal">
    <i class="fas <?= $confirmIcon ?> confirm-icon"></i>
    <div>
        <h3><?= $confirmMessage ?></h3>
        <?php if (!empty($confirmMessage2)): ?><p><?= $confirmMessage2 ?></p><?php endif; ?>
    </div>
</div>

<div class="panel reveal">
    <div class="panel-head"><h3><i class="fas fa-clipboard-list"></i> Récapitulatif</h3></div>
    <div class="info-grid">
        <?php foreach ($confirmRows as $row): ?>
        <div><span><?= $row['label'] ?></span><b><?= $row['value'] ?></b></div>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel workflow reveal">
    <div class="panel-head"><h3><i class="fas fa-check-double"></i> Validation finale</h3></div>
    <div class="workflow-actions">
        <a href="<?= $backUrl ?>" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> <?= $backLabel ?></a>
        <form method="post" action="<?= $confirmPost ?>">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="btn <?= $confirmButtonClass ?> btn-lg"><i class="fas <?= $confirmButtonIcon ?>"></i> <?= $confirmButton ?></button>
        </form>
    </div>
</div>
