<div class="install-processing">
    <div class="spinner" id="installSpinner"></div>
    <p class="install-help" id="installHelp"><?= esc(lang('Install.process_help')) ?></p>

    <form id="executeForm" method="post" action="<?= site_url('install/execute') ?>">
        <?= csrf_field() ?>
        <div class="install-actions">
            <button type="submit" class="btn btn-primary" id="installSubmitBtn"><?= esc(lang('Install.install_now')) ?></button>
            <a href="<?= site_url('install/setup') ?>" class="btn btn-secondary"><?= esc(lang('Install.back')) ?></a>
        </div>
    </form>
</div>

<script>
    (function () {
        var form = document.getElementById('executeForm');
        var btn = document.getElementById('installSubmitBtn');
        var spinner = document.getElementById('installSpinner');

        form.addEventListener('submit', function () {
            btn.disabled = true;
            spinner.style.display = 'block';
        });

        setTimeout(function () {
            if (!btn.disabled) {
                form.requestSubmit();
            }
        }, 800);
    })();
</script>
