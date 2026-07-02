<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="page-module page-search">
<?= $this->include('partials/breadcrumb') ?>
<div class="page-header"><h2 class="page-heading"><?= esc(lang('Search.title')) ?></h2></div>
<form method="get" action="<?= site_url('search') ?>" class="search-form card form-card" style="margin-bottom:20px">
    <div class="card-body" style="display:flex;gap:8px">
        <input type="search" name="q" value="<?= esc($query) ?>" placeholder="<?= esc(lang('Search.placeholder')) ?>" class="search-input" style="flex:1" autofocus>
        <button type="submit" class="btn btn-primary"><?= esc(lang('Search.search')) ?></button>
    </div>
</form>
<?php if (strlen($query) < 2): ?>
    <p class="text-muted"><?= esc(lang('Search.hint')) ?></p>
<?php else: ?>
    <?php $fmt = static fn (float $n): string => number_format($n, 0, '.', ','); ?>
    <?php if ($results['transactions'] !== []): ?>
    <div class="card" style="margin-bottom:16px"><div class="card-header"><h3><?= esc(lang('Finance.transactions')) ?></h3></div><div class="table-wrap"><table class="data-table data-table-compact"><tbody>
        <?php foreach ($results['transactions'] as $r): ?><tr><td><?= esc(jalali_date($r['txn_date'])) ?></td><td><?= esc($r['description'] ?? '—') ?></td><td><?= esc($fmt((float) $r['amount'])) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
    <?php endif; ?>
    <?php if ($results['projects'] !== []): ?>
    <div class="card" style="margin-bottom:16px"><div class="card-header"><h3><?= esc(lang('App.menu.projects')) ?></h3></div><ul class="search-results-list">
        <?php foreach ($results['projects'] as $r): ?><li><a href="<?= site_url('module/projects/' . $r['id']) ?>"><?= esc($r['name']) ?></a> <span class="text-muted"><?= esc($r['code']) ?></span></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>
    <?php if ($results['contacts'] !== []): ?>
    <div class="card" style="margin-bottom:16px"><div class="card-header"><h3><?= esc(lang('Finance.contacts')) ?></h3></div><ul class="search-results-list">
        <?php foreach ($results['contacts'] as $r): ?><li><a href="<?= site_url('module/finance/contacts/' . $r['id']) ?>"><?= esc($r['name']) ?></a></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>
    <?php if ($results['invoices'] !== []): ?>
    <div class="card" style="margin-bottom:16px"><div class="card-header"><h3><?= esc(lang('Finance.invoices')) ?></h3></div><ul class="search-results-list">
        <?php foreach ($results['invoices'] as $r): ?><li><a href="<?= site_url('module/finance/invoices/' . $r['id'] . '/edit') ?>"><?= esc($r['number']) ?></a> — <?= esc($fmt((float) $r['amount'])) ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>
    <?php if ($results['assets'] !== []): ?>
    <div class="card" style="margin-bottom:16px"><div class="card-header"><h3><?= esc(lang('Finance.assets')) ?></h3></div><ul class="search-results-list">
        <?php foreach ($results['assets'] as $r): ?><li><a href="<?= site_url('module/finance/assets/' . $r['id'] . '/edit') ?>"><?= esc($r['name']) ?></a></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>
    <?php if (array_sum(array_map('count', $results)) === 0): ?>
        <p class="text-muted"><?= esc(lang('Search.no_results')) ?></p>
    <?php endif; ?>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
