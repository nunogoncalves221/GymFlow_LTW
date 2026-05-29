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

<?php function drawEquipmentPage(array $equipment, array $types, string $activeType, Session $session, bool $is_member, array $reservations): void {
    $today = date('Y-m-d');
?>

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

  <?php if ($is_member && !empty($reservations)): ?>
    <div class="equip-my-reservations">
      <h3 class="equip-my-res-title">My upcoming reservations</h3>
      <div class="equip-res-list">
        <?php foreach ($reservations as $res):
            [$resDay,   $resMonth] = [date('d', strtotime($res->date)), date('m', strtotime($res->date))];
            [$startH,  $startM]   = explode(':', $res->start_time);
            [$endH,    $endM]     = explode(':', $res->end_time);
        ?>
          <div class="equip-res-item">
            <div class="equip-res-row">
              <div class="equip-res-info">
                <span class="equip-res-name"><?= h($res->equipment_name) ?></span>
                <span class="equip-res-time">
                  <?= h(date('D, d M', strtotime($res->date))) ?>
                  &middot;
                  <?= h($res->start_time) ?> – <?= h($res->end_time) ?>
                </span>
              </div>
              <div class="equip-res-actions">
                <button type="button" class="equip-res-edit-btn"
                        data-target="edit-form-<?= $res->id ?>">Edit</button>
                <form action="/actions/action_cancel_reservation.php" method="post"
                      data-confirm="Cancel this reservation?">
                  <?= $session->csrfField() ?>
                  <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                  <button type="submit" class="equip-res-cancel-btn">Cancel</button>
                </form>
              </div>
            </div>
            <div class="equip-res-edit-form" id="edit-form-<?= $res->id ?>">
              <form action="/actions/action_edit_reservation.php" method="post">
                <?= $session->csrfField() ?>
                <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                <div class="equip-res-fields">
                  <div class="equip-res-row-line">
                    <span class="equip-res-label">Date</span>
                    <input type="text" name="date_day"   value="<?= $resDay ?>"   placeholder="dd" maxlength="2" class="equip-res-tiny" required>
                    <span class="equip-res-sep">/</span>
                    <input type="text" name="date_month" value="<?= $resMonth ?>" placeholder="mm" maxlength="2" class="equip-res-tiny" required>
                    <span class="equip-res-year"><?= date('Y') ?></span>
                  </div>
                  <div class="equip-res-times">
                    <div class="equip-res-time-group">
                      <span class="equip-res-label">From</span>
                      <div class="equip-res-time-row">
                        <input type="text" name="start_hour"   value="<?= $startH ?>" placeholder="hh" maxlength="2" class="equip-res-tiny" required>
                        <span class="equip-res-sep">:</span>
                        <input type="text" name="start_minute" value="<?= $startM ?>" placeholder="mm" maxlength="2" class="equip-res-tiny" required>
                      </div>
                    </div>
                    <div class="equip-res-time-group">
                      <span class="equip-res-label">To</span>
                      <div class="equip-res-time-row">
                        <input type="text" name="end_hour"   value="<?= $endH ?>" placeholder="hh" maxlength="2" class="equip-res-tiny" required>
                        <span class="equip-res-sep">:</span>
                        <input type="text" name="end_minute" value="<?= $endM ?>" placeholder="mm" maxlength="2" class="equip-res-tiny" required>
                      </div>
                    </div>
                  </div>
                </div>
                <button type="submit" class="equip-res-submit">Save changes</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

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

            <?php if ($is_member): ?>
              <div class="equip-reserve-wrap">
                <button type="button"
                        class="equip-reserve-toggle"
                        data-target="reserve-form-<?= $item->id ?>">
                  Reserve a slot
                </button>
                <div class="equip-reserve-form" id="reserve-form-<?= $item->id ?>">
                  <form action="/actions/action_reserve_equipment.php" method="post">
                    <?= $session->csrfField() ?>
                    <input type="hidden" name="equipment_id" value="<?= $item->id ?>">
                    <div class="equip-res-fields">
                      <div class="equip-res-row-line">
                        <span class="equip-res-label">Date</span>
                        <input type="text" name="date_day"   placeholder="dd" maxlength="2" class="equip-res-tiny" required>
                        <span class="equip-res-sep">/</span>
                        <input type="text" name="date_month" placeholder="mm" maxlength="2" class="equip-res-tiny" required>
                        <span class="equip-res-year"><?= date('Y') ?></span>
                      </div>
                      <div class="equip-res-times">
                        <div class="equip-res-time-group">
                          <span class="equip-res-label">From</span>
                          <div class="equip-res-time-row">
                            <input type="text" name="start_hour"   placeholder="hh" maxlength="2" class="equip-res-tiny" required>
                            <span class="equip-res-sep">:</span>
                            <input type="text" name="start_minute" placeholder="mm" maxlength="2" class="equip-res-tiny" required>
                          </div>
                        </div>
                        <div class="equip-res-time-group">
                          <span class="equip-res-label">To</span>
                          <div class="equip-res-time-row">
                            <input type="text" name="end_hour"   placeholder="hh" maxlength="2" class="equip-res-tiny" required>
                            <span class="equip-res-sep">:</span>
                            <input type="text" name="end_minute" placeholder="mm" maxlength="2" class="equip-res-tiny" required>
                          </div>
                        </div>
                      </div>
                    </div>
                    <p class="equip-res-hint">10 min – 2 h · up to 1 week in advance · <?= $item->quantity ?> unit<?= $item->quantity !== 1 ? 's' : '' ?> total</p>
                    <button type="submit" class="equip-res-submit">Confirm reservation</button>
                  </form>
                </div>
              </div>
            <?php endif; ?>

          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<?php } ?>
