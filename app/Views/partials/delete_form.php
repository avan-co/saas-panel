<form method="post" action="<?= esc($action, 'attr') ?>" class="inline-delete-form" onsubmit="return confirm(<?= json_encode(lang('App.confirm_delete')) ?>)">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-ghost btn-sm text-error"><?= esc(lang('App.delete')) ?></button>
</form>
