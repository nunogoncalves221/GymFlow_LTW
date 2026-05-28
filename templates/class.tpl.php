<?php
declare(strict_types=1);

function classFilterUrl(string $type, string $day, string $trainer, string $time, array $override = []): string {
    $p = array_filter(array_merge(['type' => $type, 'day' => $day, 'trainer' => $trainer, 'time' => $time], $override));
    return '/pages/index.php' . ($p ? '?' . http_build_query($p) : '');
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
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="6" y1="12" x2="18" y2="12"/><path d="M4 8h2v8H4zM18 8h2v8h-2z"/><path d="M2 10h2v4H2zM20 10h2v4h-2z"/></svg>
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
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
  </div>
</a>
<?php } ?>

<?php function drawTrainersBanner(Session $session): void {
    if (!$session->isLoggedIn()) return;
?>
<a href="/pages/trainers.php" class="trainers-cta-banner">
  <div class="trainers-cta-left">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    <div class="trainers-cta-text">
      <span class="trainers-cta-title">Our Trainers</span>
      <span class="trainers-cta-sub">Meet the experts behind every class</span>
    </div>
  </div>
  <span class="trainers-cta-btn">View profiles &rarr;</span>
</a>
<?php } ?>

<?php function drawClassList(array $classes, array $types, array $trainers, string $filter_type, string $filter_day, string $filter_trainer, string $filter_time) { ?>
  <?php $hasFilter = $filter_type || $filter_day || $filter_trainer || $filter_time; ?>

  <div class="search-filters">
    <div class="filter-search-row">
      <svg class="filter-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
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

      <select class="filter-select-inline" onchange="applySelectFilter('trainer', this.value)">
        <option value="">All trainers</option>
        <?php foreach ($trainers as $tr): ?>
          <option value="<?= h((string)$tr['id']) ?>" <?= $filter_trainer === (string)$tr['id'] ? 'selected' : '' ?>>
            <?= h($tr['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select class="filter-select-inline" onchange="applySelectFilter('time', this.value)">
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
      <?= count($classes) ?> class<?= count($classes) !== 1 ? 'es' : '' ?> found
    </p>
  </div>

  <section class="classes-grid">
    <?php if (empty($classes)): ?>
    <p class="no-results">No classes match your filters. <a href="/pages/index.php" class="filter-clear-inline">Clear filters</a></p>
    <?php endif; ?>
    <?php foreach ($classes as $c): ?>
    <a href="/pages/class.php?id=<?= $c->id ?>" class="class-card">
      <img src="<?= h($c->image) ?>" alt="<?= h($c->name) ?>" class="class-card-thumb">
      <article class="class-card-body">
        <h2 class="class-card-name"><?= h($c->name) ?></h2>
        <p class="class-card-meta"><?= h(substr($c->day_of_week, 0, 3)) ?> · <?= h($c->time) ?> · <?= $c->duration_min ?> min</p>
        <p class="class-card-trainer"><?= h($c->trainer_name ?: 'TBA') ?></p>
      </article>
    </a>
    <?php endforeach; ?>
  </section>
<?php } ?>

<?php function drawClassDetail(FitnessClass $class, ?Trainer $trainer, array $reviews, Session $session, int $enrolled_count, bool $is_enrolled, bool $has_reviewed = false) { ?>
  <a href="/pages/index.php" class="back-link">← Back to classes</a>

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