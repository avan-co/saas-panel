<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-finance">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Finance.reminders')) ?></h2></div>

<div class="card form-card" style="margin-bottom:20px">
    <div class="card-body">
        <form method="post" action="<?= site_url('module/finance/reminders/store') ?>" class="app-form">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label><?= esc(lang('Finance.reminder_title')) ?></label><input type="text" name="title" required></div>
                <div class="form-group"><label><?= esc(lang('Finance.amount')) ?></label><input type="number" name="amount" min="0" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label><?= esc(lang('Finance.due_date')) ?></label><input type="text" name="due_date" class="jalali-date" value="<?= esc(today_for_input(session('locale') ?? 'fa')) ?>" required></div>
                <div class="form-group">
                    <label><?= esc(lang('Finance.reminder_type')) ?></label>
                    <select name="type">
                        <?php foreach (['tax','insurance','rent','loan','check','contract','other'] as $t): ?>
                            <option value="<?= $t ?>"><?= esc(lang('Finance.reminder_' . $t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
        <table class="data-table">
            <thead><tr><th><?= esc(lang('Finance.reminder_title')) ?></th><th><?= esc(lang('Finance.due_date')) ?></th><th><?= esc(lang('Finance.amount')) ?></th><th><?= esc(lang('App.status')) ?></th><th></th></tr></thead>
            <tbody>
                <?php foreach ($reminders as $r): ?>
                    <tr>
                        <td><?= esc($r['title']) ?></td>
                        <td><?= esc(jalali_date($r['due_date'])) ?></td>
                        <td><?= esc($fmt((float) $r['amount'])) ?></td>
                        <td><span class="badge badge-<?= esc($r['status']) ?>"><?= esc(lang('Finance.status_' . $r['status'])) ?></span></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <form method="post" action="<?= site_url('module/finance/reminders/' . $r['id'] . '/paid') ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-ghost btn-sm"><?= esc(lang('Finance.mark_paid')) ?></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?= $this->endSection() ?>
