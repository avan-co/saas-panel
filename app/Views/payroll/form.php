<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-payroll">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc($employee ? lang('Payroll.edit_employee') : lang('Payroll.new_employee')) ?></h2>
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
        $action = $employee
            ? site_url('module/payroll/employees/' . $employee['id'] . '/update')
            : site_url('module/payroll/employees/store');
        ?>

        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name"><?= esc(lang('Payroll.name')) ?></label>
                <input type="text" id="name" name="name" value="<?= esc(old('name', $employee['name'] ?? '')) ?>" required maxlength="120">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="job_title"><?= esc(lang('Payroll.job_title')) ?></label>
                    <input type="text" id="job_title" name="job_title" value="<?= esc(old('job_title', $employee['job_title'] ?? '')) ?>" maxlength="120">
                </div>
                <div class="form-group">
                    <label for="base_salary"><?= esc(lang('Payroll.base_salary')) ?></label>
                    <input type="number" id="base_salary" name="base_salary" min="1" step="1" value="<?= esc(old('base_salary', $employee['base_salary'] ?? '')) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="national_id"><?= esc(lang('Payroll.national_id')) ?></label>
                    <input type="text" id="national_id" name="national_id" value="<?= esc(old('national_id', $employee['national_id'] ?? '')) ?>" maxlength="20" dir="ltr">
                </div>
                <div class="form-group">
                    <label for="insurance_number"><?= esc(lang('Payroll.insurance_number')) ?></label>
                    <input type="text" id="insurance_number" name="insurance_number" value="<?= esc(old('insurance_number', $employee['insurance_number'] ?? '')) ?>" maxlength="20" dir="ltr">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status"><?= esc(lang('App.status')) ?></label>
                    <select id="status" name="status" required>
                        <option value="active" <?= old('status', $employee['status'] ?? 'active') === 'active' ? 'selected' : '' ?>><?= esc(lang('Payroll.status_active')) ?></option>
                        <option value="inactive" <?= old('status', $employee['status'] ?? '') === 'inactive' ? 'selected' : '' ?>><?= esc(lang('Payroll.status_inactive')) ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hired_at"><?= esc(lang('Payroll.hired_at')) ?></label>
                    <input type="text" id="hired_at" name="hired_at" class="jalali-date" value="<?= esc(old('hired_at', ! empty($employee['hired_at']) ? jalali_date($employee['hired_at']) : '')) ?>">
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= site_url('module/payroll') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
