<?php
declare(strict_types=1);

function equipmentIcon(string $type): string {
    return match (strtolower($type)) {
        'cardio'     => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
        'weights'    => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" y1="12" x2="18" y2="12"/><path d="M4 8h2v8H4zM18 8h2v8h-2z"/><path d="M2 10h2v4H2zM20 10h2v4h-2z"/></svg>',
        'functional' => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
        default      => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
    };
}
?>

<?php function drawEquipmentPage(array $equipment, array $types, string $activeType): void { ?>

<section class="equip-page">

  <div class="equip-header">
    <div class="equip-header-text">
      <a href="#" onclick="history.back();return false;" class="back-link"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg></a>
      <h2 class="equip-title">Equipment</h2>
      <p class="equip-subtitle">Current availability in the main training area</p>
    </div>
    <div class="equip-live-badge">
      <span class="equip-live-dot"></span>
      <span>Live</span>
    </div>
  </div>

  <?php
    $total       = count($equipment);
    $avail_count = count(array_filter($equipment, fn($e) => $e->isAvailable()));
    $units       = array_sum(array_map(fn($e) => $e->quantity, $equipment));
  ?>
  <div class="equip-stats">
    <div class="equip-stat">
      <span class="equip-stat-num"><?= $total ?></span>
      <span class="equip-stat-label">Equipment types</span>
    </div>
    <div class="equip-stat equip-stat--accent">
      <span class="equip-stat-num"><?= $avail_count ?></span>
      <span class="equip-stat-label">Available now</span>
    </div>
    <div class="equip-stat">
      <span class="equip-stat-num"><?= $units ?></span>
      <span class="equip-stat-label">Total units</span>
    </div>
  </div>

  <div class="equip-filters">
    <a href="/pages/equipment.php"
       class="equip-pill <?= $activeType === '' ? 'active' : '' ?>">All</a>
    <?php foreach ($types as $t): ?>
      <a href="/pages/equipment.php?type=<?= urlencode($t) ?>"
         class="equip-pill <?= $activeType === $t ? 'active' : '' ?>"><?= h($t) ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($equipment)): ?>
    <p class="equip-empty">No equipment found for this category.</p>
  <?php else: ?>
    <div class="equip-grid">
      <?php foreach ($equipment as $item): ?>
        <article class="equip-card <?= $item->isAvailable() ? '' : 'equip-card--unavailable' ?>">
          <div class="equip-card-stripe <?= $item->isAvailable() ? 'stripe--available' : 'stripe--unavailable' ?>"></div>
          <div class="equip-card-body">
            <div class="equip-card-top">
              <div class="equip-icon-wrap">
                <?= equipmentIcon($item->type) ?>
              </div>
              <span class="equip-type-tag"><?= h($item->type) ?></span>
            </div>
            <h3 class="equip-name"><?= h($item->name) ?></h3>
            <div class="equip-card-footer">
              <span class="equip-qty-label">
                <strong><?= $item->in_use ?> / <?= $item->quantity ?></strong> em uso
              </span>
              <div class="equip-status <?= $item->isAvailable() ? 'status--available' : 'status--unavailable' ?>">
                <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                <?= $item->isAvailable() ? 'Available' : 'Not available' ?>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<?php } ?> 