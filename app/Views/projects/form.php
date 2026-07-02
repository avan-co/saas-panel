<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-projects">
<?= $this->include('partials/breadcrumb') ?>

<div class="page-header">
    <div class="page-header-text">
        <h2 class="page-heading"><?= esc($project ? lang('Projects.edit_project') : lang('Projects.new_project')) ?></h2>
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
        $action = $project
            ? site_url('module/projects/' . $project['id'] . '/update')
            : site_url('module/projects/store');
        ?>

        <form method="post" action="<?= $action ?>" class="app-form">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name"><?= esc(lang('Projects.project_name')) ?></label>
                    <input type="text" id="name" name="name" value="<?= esc(old('name', $project['name'] ?? '')) ?>" required maxlength="160">
                </div>
                <div class="form-group">
                    <label for="code"><?= esc(lang('Projects.code')) ?></label>
                    <input type="text" id="code" name="code" value="<?= esc(old('code', $project['code'] ?? '')) ?>" required maxlength="40">
                </div>
            </div>

            <div class="form-group">
                <label for="client_name"><?= esc(lang('Projects.client')) ?></label>
                <input type="text" id="client_name" name="client_name" value="<?= esc(old('client_name', $project['client_name'] ?? '')) ?>" maxlength="120">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status"><?= esc(lang('App.status')) ?></label>
                    <select id="status" name="status" required>
                        <?php foreach (['planning', 'active', 'on_hold', 'completed'] as $st): ?>
                            <option value="<?= $st ?>" <?= old('status', $project['status'] ?? 'planning') === $st ? 'selected' : '' ?>>
                                <?= esc(lang('Projects.status_' . $st)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="budget"><?= esc(lang('Projects.budget')) ?></label>
                    <input type="number" id="budget" name="budget" min="0" step="1" value="<?= esc(old('budget', $project['budget'] ?? '')) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="progress"><?= esc(lang('Projects.progress')) ?> (%)</label>
                    <input type="number" id="progress" name="progress" min="0" max="100" step="1" value="<?= esc(old('progress', $project['progress'] ?? '0')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="start_date"><?= esc(lang('Projects.start_date')) ?></label>
                    <input type="date" id="start_date" name="start_date" value="<?= esc(old('start_date', $project['start_date'] ?? '')) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="end_date"><?= esc(lang('Projects.end_date')) ?></label>
                <input type="date" id="end_date" name="end_date" value="<?= esc(old('end_date', $project['end_date'] ?? '')) ?>">
            </div>

            <div class="form-actions">
                <a href="<?= site_url('module/projects') ?>" class="btn btn-secondary"><?= esc(lang('App.cancel')) ?></a>
                <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
            </div>
        </form>
    </div>
</div>
</div>
<?= $this->endSection() ?>
