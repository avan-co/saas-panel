<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-insurance">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc($policy ? lang('Insurance.edit_policy') : lang('Insurance.new_policy')) ?></h2>
    </div>
</div>

<div class="card card-elevated form-card">
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <div><?= esc($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        $action = $policy
            ? site_url('module/insurance/' . $policy['id'] . '/update')
            : site_url('module/insurance/store');
        ?>

        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="policy_number"><?= esc(lang('Insurance.policy_number')) ?></label>
                    <input type="text" id="policy_number" name="policy_number" value="<?= esc(old('policy_number', $policy['policy_number'] ?? '')) ?>" required maxlength="80">
                </div>
                <div class="form-group">
                    <label for="provider"><?= esc(lang('Insurance.provider')) ?></label>
                    <input type="text" id="provider" name="provider" value="<?= esc(old('provider', $policy['provider'] ?? '')) ?>" required maxlength="120">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type"><?= esc(lang('Insurance.type')) ?></label>
                    <select id="type" name="type" required>
                        <?php foreach (['social', 'health', 'liability', 'other'] as $t): ?>
                            <option value="<?= $t ?>" <?= old('type', $policy['type'] ?? 'social') === $t ? 'selected' : '' ?>>
                                <?= esc(lang('Insurance.type_' . $t)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="premium"><?= esc(lang('Insurance.premium')) ?></label>
                    <input type="number" id="premium" name="premium" min="0" step="1" value="<?= esc(old('premium', $policy['premium'] ?? '')) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date"><?= esc(lang('Insurance.start_date')) ?></label>
                    <input type="date" id="start_date" name="start_date" value="<?= esc(old('start_date', $policy['start_date'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date"><?= esc(lang('Insurance.valid_until')) ?></label>
                    <input type="date" id="end_date" name="end_date" value="<?= esc(old('end_date', $policy['end_date'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="status"><?= esc(lang('App.status')) ?></label>
                <select id="status" name="status" required>
                    <?php foreach (['active', 'pending', 'expired'] as $st): ?>
                        <option value="<?= $st ?>" <?= old('status', $policy['status'] ?? 'active') === $st ? 'selected' : '' ?>>
                            <?= esc(lang('Insurance.status_' . $st)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="<?= site_url('module/insurance') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
