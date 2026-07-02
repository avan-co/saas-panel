<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="page-module page-settings">
<?= $this->include('partials/module_subnav') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Settings.teams')) ?></h2></div>

<div class="content-grid">
    <div class="card form-card">
        <div class="card-header"><h3><?= esc(lang('Settings.add_team')) ?></h3></div>
        <div class="card-body">
            <form method="post" action="<?= site_url('module/settings/teams/store') ?>" class="app-form">
                <?= csrf_field() ?>
                <div class="form-group"><label><?= esc(lang('Settings.team_name')) ?></label><input type="text" name="name" required></div>
                <div class="form-group"><label><?= esc(lang('Settings.team_description')) ?></label><input type="text" name="description"></div>
                <div class="form-group"><label><?= esc(lang('Settings.team_leader')) ?></label>
                    <select name="leader_user_id"><option value="">—</option>
                    <?php foreach ($members as $m): ?><option value="<?= $m['user_id'] ?>"><?= esc($m['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label><?= esc(lang('Settings.team_members')) ?></label>
                    <select name="member_user_id[]" multiple size="5">
                    <?php foreach ($members as $m): ?><option value="<?= $m['user_id'] ?>"><?= esc($m['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?= esc(lang('Settings.add_team')) ?></button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3><?= esc(lang('Settings.teams_list')) ?></h3></div>
        <div class="card-body">
            <?php foreach ($teams as $team): ?>
            <details class="team-details">
                <summary><strong><?= esc($team['name']) ?></strong> (<?= (int) $team['member_count'] ?> <?= esc(lang('Settings.members')) ?>)</summary>
                <form method="post" action="<?= site_url('module/settings/teams/' . $team['id'] . '/update') ?>" class="app-form" style="padding:12px">
                    <?= csrf_field() ?>
                    <input type="text" name="name" value="<?= esc($team['name']) ?>" required>
                    <input type="text" name="description" value="<?= esc($team['description'] ?? '') ?>">
                    <select name="leader_user_id"><option value="">—</option>
                    <?php foreach ($members as $m): ?><option value="<?= $m['user_id'] ?>" <?= (int)($team['leader_user_id']??0)===(int)$m['user_id']?'selected':'' ?>><?= esc($m['name']) ?></option><?php endforeach; ?>
                    </select>
                    <select name="member_user_id[]" multiple size="4">
                    <?php $memberIds = array_column($team['members'], 'user_id'); foreach ($members as $m): ?>
                        <option value="<?= $m['user_id'] ?>" <?= in_array($m['user_id'], $memberIds, false) ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                    <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc(lang('App.save')) ?></button>
                </form>
                <form method="post" action="<?= site_url('module/settings/teams/' . $team['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('<?= esc(lang('App.delete_confirm')) ?>')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-ghost btn-sm btn-danger"><?= esc(lang('App.delete')) ?></button>
                </form>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>
