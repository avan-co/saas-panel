<div class="install-processing">
    <div class="spinner"></div>
    <p class="install-help"><?= esc(lang('Install.process_help')) ?></p>

    <form id="executeForm" method="post" action="<?= site_url('install/execute') ?>">
        <?= csrf_field() ?>
    </form>
</div>

<script>
    document.getElementById('executeForm').submit();
</script>
