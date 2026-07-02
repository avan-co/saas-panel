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

            <div class="form-row">
                <div class="form-group">
                    <label for="client_name"><?= esc(lang('Projects.client')) ?></label>
                    <input type="text" id="client_name" name="client_name" value="<?= esc(old('client_name', $project['client_name'] ?? '')) ?>" maxlength="120">
                </div>
                <?php if (! empty($contacts)): ?>
                <div class="form-group">
                    <label><?= esc(lang('Finance.contact')) ?></label>
                    <select name="contact_id"><option value="">—</option>
                    <?php foreach ($contacts as $c): ?><option value="<?= $c['id'] ?>" <?= (string)old('contact_id', $project['contact_id'] ?? '')===(string)$c['id']?'selected':'' ?>><?= esc($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="manager_user_id"><?= esc(lang('Projects.manager')) ?></label>
                    <select id="manager_user_id" name="manager_user_id">
                        <option value="">—</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>" <?= (string)old('manager_user_id', $project['manager_user_id'] ?? '')===(string)$u['user_id']?'selected':'' ?>><?= esc($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="priority"><?= esc(lang('Projects.priority')) ?></label>
                    <select id="priority" name="priority">
                        <?php foreach (['low','medium','high','critical'] as $p): ?>
                        <option value="<?= $p ?>" <?= old('priority', $project['priority'] ?? 'medium')===$p?'selected':'' ?>><?= esc(lang('Projects.priority_'.$p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description"><?= esc(lang('Projects.description')) ?></label>
                <textarea id="description" name="description" rows="3"><?= esc(old('description', $project['description'] ?? '')) ?></textarea>
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
                    <label for="start_date"><?= esc(lang('Projects.start_date')) ?></label>
                    <input type="text" id="start_date" name="start_date" class="jalali-date" value="<?= esc(old('start_date', !empty($project['start_date']) ? jalali_date($project['start_date']) : '')) ?>">
                </div>
                <div class="form-group">
                    <label for="end_date"><?= esc(lang('Projects.end_date')) ?></label>
                    <input type="text" id="end_date" name="end_date" class="jalali-date" value="<?= esc(old('end_date', !empty($project['end_date']) ? jalali_date($project['end_date']) : '')) ?>">
                </div>
            </div>

            <h3><?= esc(lang('Projects.members')) ?></h3>
            <?php $memberRows = $members !== [] ? $members : [['user_id'=>'','role'=>'expert']]; ?>
            <?php foreach ($memberRows as $i => $m): ?>
            <div class="form-row">
                <div class="form-group">
                    <select name="member_user_id[]">
                        <option value="">—</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>" <?= (string)($m['user_id']??'')===(string)$u['user_id']?'selected':'' ?>><?= esc($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="member_role[]">
                        <?php foreach (['manager','expert','intern','client','viewer'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($m['role']??'expert')===$r?'selected':'' ?>><?= esc(lang('Projects.role_'.$r)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endforeach; ?>

            <h3><?= esc(lang('Projects.teams')) ?></h3>
            <div class="form-group">
                <select name="team_id[]" multiple size="4">
                <?php
                $selectedTeamIds = array_column($teams ?? [], 'team_id');
                foreach ($allTeams ?? [] as $t):
                ?>
                    <option value="<?= $t['id'] ?>" <?= in_array($t['id'], $selectedTeamIds, false) ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
                </select>
                <small><?= esc(lang('Projects.teams_hint')) ?></small>
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
