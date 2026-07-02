<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-notifications">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc(lang('Notifications.title')) ?></h2>
    </div>
</div>

<div class="card card-elevated">
    <div class="card-body">
        <?php if ($notifications === []): ?>
            <?= view('partials/empty_state', ['message' => lang('Notifications.empty')]) ?>
        <?php else: ?>
            <ul class="notification-list">
                <?php foreach ($notifications as $n): ?>
                    <li class="notification-item">
                        <div class="notification-content">
                            <strong><?= esc($n['title']) ?></strong>
                            <?php if (! empty($n['body'])): ?>
                                <p class="text-muted"><?= esc($n['body']) ?></p>
                            <?php endif; ?>
                            <span class="notification-time text-muted"><?= esc(jalali_date(substr($n['created_at'], 0, 10))) ?></span>
                        </div>
                        <div class="notification-actions">
                            <?php if (! empty($n['link'])): ?>
                                <a href="<?= esc($n['link']) ?>" class="btn btn-ghost btn-sm"><?= esc(lang('Notifications.view_all')) ?></a>
                            <?php endif; ?>
                            <form method="post" action="<?= site_url('notifications/' . $n['id'] . '/read') ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm"><?= esc(lang('Notifications.mark_read')) ?></button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
</div>
<?= $this->endSection() ?>
