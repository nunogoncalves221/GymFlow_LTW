<?php
declare(strict_types=1);

function classFilterUrl(string $type, string $day, string $trainer, string $time, array $override = []): string {
    $p = array_filter(array_merge(['type' => $type, 'day' => $day, 'trainer' => $trainer, 'time' => $time], $override));
    return '/pages/index.php' . ($p ? '?' . http_build_query($p) : '');
}

if (!function_exists('buildCalendarRows')) {
    function buildCalendarRows(array $classes): array {
        $needed = [];
        foreach ($classes as $c) {
            [$h, $m] = array_map('intval', explode(':', $c->time));
            $start = $h * 60 + $m;
            for ($min = $start; $min < $start + $c->duration_min; $min += 30) $needed[$min] = true;
        }
        if (empty($needed)) {
            for ($m = 7 * 60; $m < 22 * 60; $m += 30) $needed[$m] = true;
        }
        ksort($needed);

        $rows = []; $timeToRow = []; $rowNum = 2; $prev = null;
        foreach (array_keys($needed) as $min) {
            if ($prev !== null && $min - $prev > 30) {
                $fH = intdiv($prev + 30, 60); $fM = ($prev + 30) % 60;
                $tH = intdiv($min, 60);       $tM = $min % 60;
                $rows[] = ['type' => 'gap', 'row' => $rowNum++,
                    'label' => sprintf('%02d:%02d', $fH, $fM) . ' – ' . sprintf('%02d:%02d', $tH, $tM)];
            }
            $hh = intdiv($min, 60); $mm = $min % 60;
            $t  = sprintf('%02d:%02d', $hh, $mm);
            $rows[] = ['type' => 'slot', 'row' => $rowNum, 'min' => $min, 'timeStr' => $t,
                'label' => $mm === 0 ? sprintf('%02d:00', $hh) : ''];
            $timeToRow[$t] = $rowNum++;
            $prev = $min;
        }
        $gtr = '48px';
        foreach ($rows as $r) $gtr .= ' ' . ($r['type'] === 'gap' ? '18px' : '40px');
        return ['rows' => $rows, 'timeToRow' => $timeToRow, 'gtr' => $gtr];
    }
}
?>

<?php function drawHeroBanner() { ?>
<div class="hero-banner">
  <img class="hero-bg" src="/docs/images/banner.jpeg" alt="">
  <div class="hero-content">
    <p class="hero-eyebrow">Welcome to</p>
    <h1 class="hero-title">GymFlow</h1>
    <p class="hero-sub">Find your class. Book your spot. Show up.</p>
  </div>
</div>
<?php } ?>

<?php function drawEquipmentBanner(array $equipment, Session $session): void {
    if (!$session->isLoggedIn()) return;

    $avail_units = array_sum(array_map(fn($e) => $e->quantity - $e->in_use, array_filter($equipment, fn($e) => $e->isAvailable())));
    $total_units = array_sum(array_map(fn($e) => $e->quantity, $equipment));
    $types_avail = array_filter($equipment, fn($e) => $e->isAvailable());

    // group by type for the mini summary
    $by_type = [];
    foreach ($types_avail as $e) {
        $by_type[$e->type] = ($by_type[$e->type] ?? 0) + ($e->quantity - $e->in_use);
    }
?>
<a href="/pages/equipment.php" class="equip-banner">
  <div class="equip-banner-left">
    <div class="equip-banner-icon">
      <?php include __DIR__ . '/../docs/icons/dumbbell.svg'; ?>
    </div>
    <div class="equip-banner-text">
      <span class="equip-banner-title">Training Area</span>
      <span class="equip-banner-sub">
        <?php if ($avail_units > 0): ?>
          <?= $avail_units ?> of <?= $total_units ?> units available now
        <?php else: ?>
          Check equipment availability
        <?php endif; ?>
      </span>
    </div>
  </div>
  <div class="equip-banner-types">
    <?php foreach (array_slice($by_type, 0, 3, true) as $type => $count): ?>
      <span class="equip-banner-tag">
        <span class="equip-banner-tag-dot"></span>
        <?= h($type) ?> <strong><?= $count ?></strong>
      </span>
    <?php endforeach; ?>
    <?php if (count($by_type) > 3): ?>
      <span class="equip-banner-more">+<?= count($by_type) - 3 ?> more</span>
    <?php endif; ?>
  </div>
  <div class="equip-banner-arrow">
    <?php include __DIR__ . '/../docs/icons/arrow-right.svg'; ?>
  </div>
</a>
<?php } ?>

<?php function drawNutritionBanner(Session $session): void {
    if (!$session->isLoggedIn()) return;
    if (!in_array($session->getType(), ['member', 'trainer'])) return;
    $label = $session->getType() === 'trainer' ? 'Nutrition Plans' : 'Nutrition';
    $sub   = $session->getType() === 'trainer' ? 'Manage client nutrition plans' : 'View your personalised nutrition plans';
?>
<a href="/pages/nutrition_plans.php" class="equip-banner">
  <div class="equip-banner-left">
    <div class="equip-banner-icon">
      <?php include __DIR__ . '/../docs/icons/leaf.svg'; ?>
    </div>
    <div class="equip-banner-text">
      <span class="equip-banner-title"><?= h($label) ?></span>
      <span class="equip-banner-sub"><?= h($sub) ?></span>
    </div>
  </div>
</a>
<?php } ?>

<?php function drawTrainersBanner(Session $session): void {
    if (!$session->isLoggedIn()) return;
?>
<a href="/pages/trainers.php" class="trainers-cta-banner">
  <div class="trainers-cta-left">
    <?php include __DIR__ . '/../docs/icons/user.svg'; ?>
    <div class="trainers-cta-text">
      <span class="trainers-cta-title">Our Trainers</span>
      <span class="trainers-cta-sub">Meet the experts behind every class</span>
    </div>
  </div>
  <span class="trainers-cta-btn">View profiles &rarr;</span>
</a>
<?php } ?>

<?php function drawClassList(array $classes, array $types, array $trainers, string $filter_type, string $filter_day, string $filter_trainer, string $filter_time, array $enrollment_counts = []) { ?>
<?php
  $hasFilter = $filter_type || $filter_day || $filter_trainer || $filter_time;
  $dayToCol  = ['Monday'=>2,'Tuesday'=>3,'Wednesday'=>4,'Thursday'=>5,'Friday'=>6,'Saturday'=>7,'Sunday'=>8];

  $grouped = [];
  foreach ($classes as $c) {
    if (!isset($grouped[$c->name])) {
      $grouped[$c->name] = ['rep' => $c, 'sessions' => [], 'trainers' => [], 'days' => []];
    }
    $grouped[$c->name]['sessions'][] = $c;
    $day3 = substr($c->day_of_week, 0, 3);
    if (!in_array($day3, $grouped[$c->name]['days'])) $grouped[$c->name]['days'][] = $day3;
    if ($c->trainer_name && !in_array($c->trainer_name, $grouped[$c->name]['trainers']))
      $grouped[$c->name]['trainers'][] = $c->trainer_name;
  }
?>

  <div class="search-filters">
    <div class="filter-search-row">
      <?php include __DIR__ . '/../docs/icons/search.svg'; ?>
      <input type="search" id="class-search" class="filter-search-input" placeholder="Search classes, trainers…" autocomplete="off">
    </div>
    <div class="filter-inline">

      <div class="filter-group-inline">
        <?php foreach ($types as $t): ?>
          <a href="<?= h(classFilterUrl($filter_type, $filter_day, $filter_trainer, $filter_time, ['type' => $filter_type === $t ? '' : $t])) ?>"
             class="filter-btn <?= $filter_type === $t ? 'active' : '' ?>"><?= h($t) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="filter-divider"></div>

      <div class="filter-group-inline">
        <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
          <a href="<?= h(classFilterUrl($filter_type, $filter_day, $filter_trainer, $filter_time, ['day' => $filter_day === $d ? '' : $d])) ?>"
             class="filter-btn <?= $filter_day === $d ? 'active' : '' ?>"><?= h(substr($d, 0, 3)) ?></a>
        <?php endforeach; ?>
      </div>

      <div class="filter-divider"></div>

      <select class="filter-select-inline" data-filter-param="trainer">
        <option value="">All trainers</option>
        <?php foreach ($trainers as $tr): ?>
          <option value="<?= h((string)$tr['id']) ?>" <?= $filter_trainer === (string)$tr['id'] ? 'selected' : '' ?>>
            <?= h($tr['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select class="filter-select-inline" data-filter-param="time">
        <option value="">Any time</option>
        <option value="morning"   <?= $filter_time === 'morning'   ? 'selected' : '' ?>>Morning</option>
        <option value="afternoon" <?= $filter_time === 'afternoon' ? 'selected' : '' ?>>Afternoon</option>
        <option value="evening"   <?= $filter_time === 'evening'   ? 'selected' : '' ?>>Evening</option>
      </select>

      <?php if ($hasFilter): ?>
        <a href="/pages/index.php" class="filter-clear" title="Clear filters">✕</a>
      <?php endif; ?>

    </div>
    <p class="filter-results-count" id="filter-results-count"
       <?= $hasFilter ? '' : 'style="display:none"' ?>>
      <?= count($grouped) ?> class<?= count($grouped) !== 1 ? 'es' : '' ?> found
    </p>
  </div>

  <div class="view-toggle">
    <button class="view-btn active" id="btn-view-classes">Classes</button>
    <button class="view-btn" id="btn-view-schedule">Schedule</button>
  </div>

  <!-- Classes grid -->
  <section class="classes-grid" id="view-classes">
    <?php if (empty($grouped)): ?>
    <p class="no-results">No classes match your filters. <a href="/pages/index.php" class="filter-clear-inline">Clear filters</a></p>
    <?php endif; ?>

    <?php foreach ($grouped as $name => $group):
      $rep = $group['rep'];
      $sessionsData = [];
      foreach ($group['sessions'] as $s) {
        $enrolled = $enrollment_counts[$s->id] ?? 0;
        $sessionsData[] = [
          'id'       => $s->id,
          'day'      => $s->day_of_week,
          'time'     => $s->time,
          'duration' => $s->duration_min,
          'capacity' => $s->capacity,
          'enrolled' => $enrolled,
          'trainer'  => $s->trainer_name ?: 'TBA',
        ];
      }
    ?>
    <div class="class-card"
         data-name="<?= h($rep->name) ?>"
         data-type="<?= h($rep->type) ?>"
         data-description="<?= h($rep->description) ?>"
         data-trainers="<?= h(implode(', ', $group['trainers']) ?: 'TBA') ?>"
         data-sessions="<?= h(json_encode($sessionsData)) ?>">
      <img src="<?= h($rep->image ?? '') ?>" alt="<?= h($rep->name) ?>" class="class-card-thumb">
      <article class="class-card-body">
        <h2 class="class-card-name"><?= h($rep->name) ?></h2>
        <p class="class-card-meta"><?= h(implode(' · ', $group['days'])) ?></p>
        <p class="class-card-trainer"><?= h(implode(', ', $group['trainers']) ?: 'TBA') ?></p>
      </article>
    </div>
    <?php endforeach; ?>
  </section>

  <!-- Schedule grid -->
  <?php $cal = buildCalendarRows($classes); ?>
  <div class="cal-wrapper" id="view-schedule" style="display:none">
    <div class="cal-grid" style="grid-template-rows: <?= $cal['gtr'] ?>">
      <div class="cal-corner"></div>
      <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
        <div class="cal-day-header"><?= substr($day, 0, 3) ?></div>
      <?php endforeach; ?>

      <?php foreach ($cal['rows'] as $r): ?>
        <?php if ($r['type'] === 'gap'): ?>
          <div class="cal-gap-row" style="grid-column:1/span 8;grid-row:<?= $r['row'] ?>">
            <?= $r['label'] ?>
          </div>
        <?php else: ?>
          <div class="cal-time-label" style="grid-column:1;grid-row:<?= $r['row'] ?>"><?= $r['label'] ?></div>
          <?php for ($col = 2; $col <= 8; $col++): ?>
            <div class="cal-cell <?= $r['min'] % 60 === 30 ? 'hour-start' : '' ?>"
                 style="grid-column:<?= $col ?>;grid-row:<?= $r['row'] ?>"></div>
          <?php endfor; ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php
        $classLanes = []; $classHasOverlap = [];
        $byDayArr = [];
        foreach ($classes as $c) $byDayArr[$c->day_of_week][] = $c;
        foreach ($byDayArr as $_day => $dcs) {
            usort($dcs, fn($a, $b) => strcmp($a->time, $b->time));
            $laneEnd = [];
            foreach ($dcs as $c) {
                [$h, $m] = array_map('intval', explode(':', $c->time));
                $s = $h * 60 + $m; $e = $s + $c->duration_min;
                $ln = -1;
                foreach ($laneEnd as $l => $le) { if ($le <= $s) { $ln = $l; break; } }
                if ($ln < 0) $ln = count($laneEnd);
                $laneEnd[$ln] = $e;
                $classLanes[$c->id] = $ln;
            }
            foreach ($dcs as $ci) {
                [$hi, $mi] = array_map('intval', explode(':', $ci->time));
                $si = $hi*60+$mi; $ei = $si + $ci->duration_min;
                foreach ($dcs as $cj) {
                    if ($ci->id === $cj->id) continue;
                    [$hj, $mj] = array_map('intval', explode(':', $cj->time));
                    $sj = $hj*60+$mj; $ej = $sj + $cj->duration_min;
                    if ($si < $ej && $ei > $sj) { $classHasOverlap[$ci->id] = true; break; }
                }
            }
        }
      ?>
      <?php foreach ($classes as $class):
        $row  = $cal['timeToRow'][$class->time] ?? 2;
        $span = max(1, (int)ceil($class->duration_min / 30));
        $col  = $dayToCol[$class->day_of_week] ?? 2;
        $lane = $classLanes[$class->id] ?? 0;
        $marginExtra = !empty($classHasOverlap[$class->id])
            ? ($lane === 0 ? ';margin-right:50%' : ';margin-left:50%')
            : '';
      ?>
        <a href="/pages/class.php?id=<?= $class->id ?>" class="cal-class-card"
           style="grid-column:<?= $col ?>;grid-row:<?= $row ?>/span <?= $span ?><?= $marginExtra ?>">
          <div class="cal-class-name"><?= h($class->name) ?></div>
          <div class="cal-class-time"><?= h($class->time) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Class detail modal -->
  <div id="class-info-modal" class="cal-modal-overlay" style="display:none">
    <div class="cal-modal">
      <button id="class-modal-close" class="modal-close" type="button">×</button>
      <h2 id="cim-name" class="modal-class-name"></h2>
      <p id="cim-type" class="cim-type-tag"></p>
      <p id="cim-description" class="cim-description"></p>
      <div class="modal-info-item" style="margin-bottom:1.25rem">
        <div class="modal-info-label">Trainer(s)</div>
        <div id="cim-trainers" class="modal-info-value"></div>
      </div>
      <h3 class="modal-section-title">Sessions</h3>
      <table class="roster-table">
        <thead><tr><th>Day</th><th>Time</th><th>Duration</th><th>Enrolled</th><th></th></tr></thead>
        <tbody id="cim-sessions"></tbody>
      </table>
    </div>
  </div>
<?php } ?>

<?php function drawClassDetail(FitnessClass $class, ?Trainer $trainer, array $reviews, Session $session, int $enrolled_count, bool $is_enrolled, bool $has_reviewed = false) { ?>
  <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>
  <section class="class-detail">

    <article class="class-info-card">
      <h1><?= h($class->name) ?></h1>
      <div class="class-meta-row">
        <span><?= $class->duration_min ?> min</span>
        <span><?= h($class->day_of_week) ?> · <?= h($class->time) ?></span>
        <span><?= h($class->type) ?></span>
      </div>
      <p><?= h($class->description) ?></p>
    </article>

    <?php if ($trainer): ?>
    <article class="trainer-section">
      <a href="/pages/trainer.php?id=<?= $trainer->id ?>" class="trainer-snippet">
        <div class="avatar">
          <div class="avatar-placeholder"><?= h(initials($trainer->name)) ?></div>
        </div>
        <div>
          <div class="trainer-name"><?= h($trainer->name) ?></div>
          <div class="trainer-role"><?= h($trainer->specializations) ?></div>
        </div>
        <span class="trainer-link">View profile →</span>
      </a>
    </article>
    <?php endif; ?>

    <?php
      $spots_left = max(0, $class->capacity - $enrolled_count);
      $fill_pct   = $class->capacity > 0 ? round($enrolled_count / $class->capacity * 100) : 0;
      $is_full    = $spots_left === 0;
    ?>
    <section class="capacity-section">
      <article class="capacity-card">
        <div class="capacity-row">
          <span><?= $enrolled_count ?> / <?= $class->capacity ?> enrolled</span>
          <span><?= $spots_left ?> spot<?= $spots_left !== 1 ? 's' : '' ?> left</span>
        </div>
        <div class="capacity-bar-bg">
          <div class="capacity-bar-fill" style="width: <?= $fill_pct ?>%;"></div>
        </div>

      <?php if (!$session->isLoggedIn()): ?>
        <a href="/pages/login.php" class="btn-book" style="display:block; text-align:center;">Login to book a spot</a>

      <?php elseif ($session->getType() === 'member' && $is_enrolled): ?>
        <form method="POST" action="/actions/action_cancel_enrollment.php">
          <?= $session->csrfField() ?>
          <input type="hidden" name="class_id" value="<?= $class->id ?>">
          <button type="submit" class="btn-book btn-cancel">Cancel enrollment</button>
        </form>

      <?php elseif ($session->getType() === 'member' && $is_full): ?>
        <button class="btn-book btn-disabled" disabled>Class is full</button>

      <?php elseif ($session->getType() === 'member'): ?>
        <form method="POST" action="/actions/action_enroll.php">
          <?= $session->csrfField() ?>
          <input type="hidden" name="class_id" value="<?= $class->id ?>">
          <button type="submit" class="btn-book">Book a spot</button>
        </form>

      <?php endif; ?>
      </article>
    </section>

    <section class="reviews-section">
      <h2>Reviews</h2>

      <?php if ($session->isLoggedIn() && $session->getType() === 'member' && $is_enrolled && !$has_reviewed): ?>
      <article class="review-form-card">
        <h3>Leave a Review</h3>
        <p class="review-form-meta">
          <?= h($class->name) ?> · <?= h($class->day_of_week) ?> · <?= h($class->time) ?>
        </p>
        <form method="POST" action="/actions/action_submit_review.php" class="review-form">
          <?= $session->csrfField() ?>
          <input type="hidden" name="class_id" value="<?= $class->id ?>">
          <div class="star-picker" id="star-picker">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
              <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
            <?php endfor; ?>
          </div>
          <textarea name="comment" rows="3" placeholder="Share your experience..." required></textarea>
          <button type="submit" class="btn-review-submit">Submit Review</button>
        </form>
      </article>
      <?php endif; ?>

      <?php if ($session->isLoggedIn() && $session->getType() === 'member' && $is_enrolled && $has_reviewed): ?>
      <p class="review-already-done">✓ You have already reviewed this class.</p>
      <?php endif; ?>

      <?php if (empty($reviews)): ?>
      <p class="no-reviews">No reviews yet. Be the first!</p>
      <?php endif; ?>

      <?php foreach ($reviews as $r): ?>
      <article class="review-card">
        <header class="review-header">
          <div class="review-author-block">
            <span class="review-author"><?= h($r->author_name) ?></span>
            <span class="review-class-badge"><?= h($r->class_name) ?> · <?= h($r->class_day) ?> · <?= h($r->class_time) ?></span>
          </div>
          <div class="review-stars">
            <?= str_repeat('★', $r->rating) . str_repeat('☆', 5 - $r->rating) ?>
          </div>
        </header>
        <p class="review-message"><?= h($r->comment) ?></p>
        <?php if ($r->created_at): ?>
        <?php
          date_default_timezone_set('Europe/Lisbon');
        ?>
        <time class="review-date"><?= h(date('d M Y, H:i', strtotime($r->created_at . ' UTC'))) ?></time>
        <?php endif; ?>
      </article>
      <?php endforeach; ?>
    </section>

  </section>
<?php } ?> 