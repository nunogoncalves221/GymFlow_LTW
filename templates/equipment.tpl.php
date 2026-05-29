<?php
declare(strict_types=1);

function equipmentIcon(string $type): string {
    $dir = __DIR__ . '/../docs/icons/';
    return match (strtolower($type)) {
        'cardio'     => file_get_contents($dir . 'cardio.svg'),
        'weights'    => file_get_contents($dir . 'dumbbell.svg'),
        'functional' => file_get_contents($dir . 'clock.svg'),
        default      => file_get_contents($dir . 'monitor.svg'),
    };
}
?>

<?php function drawEquipmentPage(array $equipment, array $types, string $activeType): void { ?>

<section class="equip-page">

  <div class="equip-header">
    <div class="equip-header-text">
      <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>
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
                <?php include __DIR__ . '/../docs/icons/dot.svg'; ?>
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