<?php $flash = \App\Core\Session::flash(); ?>
<?php if ($flash): ?>
<div class="toast-stack" id="toastStack">
    <div class="toast toast-<?= e($flash['type']) ?> show" data-toast>
        <span class="toast-icon"><?= $flash['type'] === 'success' ? '✓' : ($flash['type'] === 'error' ? '✕' : ($flash['type'] === 'warning' ? '⚠' : 'ℹ')) ?></span>
        <span class="toast-msg"><?= e($flash['message']) ?></span>
        <button type="button" class="toast-close" data-toast-close>×</button>
    </div>
</div>
<?php endif; ?>
