<?php
declare(strict_types=1);

function classFilterUrl(string $type, string $day, string $trainer, string $time, array $override = []): string {
    $p = array_filter(array_merge(['type' => $type, 'day' => $day, 'trainer' => $trainer, 'time' => $time], $override));
    return '/pages/index.php' . ($p ? '?' . http_build_query($p) : '');
}
?>

<?php function drawClassList(array $classes, array $types, array $trainers, string $filter_type, string $filter_day, string $filter_trainer, string $filter_time) { ?>
  <?php
    $hasFilter = $filter_type || $filter_day || $filter_trainer || $filter_time;
  ?>
  <div class="search-filters">
    <div class="filter-row">

      <!-- Type pills -->
      <div class="filter-group">
        <span class="filter-label">Type</span>
        <div class="filter-pills">
          <?php foreach ($types as $t): ?>
            <a href="<?= h(classFilterUrl($filter_type, $filter_day, $filter_trainer, $filter_time, ['type' => $filter_type === $t ? '' : $t])) ?>"
               class="filter-btn <?= $filter_type === $t ? 'active' : '' ?>"><?= h($t) ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Day pills -->
      <div class="filter-group">
        <span class="filter-label">Day</span>
        <div class="filter-pills">
          <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
            <a href="<?= h(classFilterUrl($filter_type, $filter_day, $filter_trainer, $filter_time, ['day' => $filter_day === $d ? '' : $d])) ?>"
               class="filter-btn <?= $filter_day === $d ? 'active' : '' ?>"><?= h(substr($d, 0, 3)) ?></a>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <div class="filter-row filter-row-selects">

      <!-- Trainer dropdown -->
      <div class="filter-select-group">
        <label class="filter-label" for="filter-trainer">Trainer</label>
        <select id="filter-trainer" class="filter-select" onchange="applySelectFilter('trainer', this.value)">
          <option value="">All trainers</option>
          <?php foreach ($trainers as $tr): ?>
            <option value="<?= h((string)$tr['id']) ?>"
              <?= $filter_trainer === (string)$tr['id'] ? 'selected' : '' ?>>
              <?= h($tr['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Time slot dropdown -->
      <div class="filter-select-group">
        <label class="filter-label" for="filter-time">Time</label>
        <select id="filter-time" class="filter-select" onchange="applySelectFilter('time', this.value)">
          <option value="">Any time</option>
          <option value="morning"   <?= $filter_time === 'morning'   ? 'selected' : '' ?>>Morning (6am – 12pm)</option>
          <option value="afternoon" <?= $filter_time === 'afternoon' ? 'selected' : '' ?>>Afternoon (12pm – 5pm)</option>
          <option value="evening"   <?= $filter_time === 'evening'   ? 'selected' : '' ?>>Evening (5pm – midnight)</option>
        </select>
      </div>

      <?php if ($hasFilter): ?>
      <a href="/pages/index.php" class="filter-clear">✕ Clear filters</a>
      <?php endif; ?>

    </div>

    <?php if ($hasFilter): ?>
    <p class="filter-results-count"><?= count($classes) ?> class<?= count($classes) !== 1 ? 'es' : '' ?> found</p>
    <?php endif; ?>

  </div>

  <script>
  function applySelectFilter(param, value) {
    var url = new URL(window.location.href);
    if (value) url.searchParams.set(param, value);
    else url.searchParams.delete(param);
    window.location.href = url.toString();
  }
  </script>

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

<?php function drawClassDetail(FitnessClass $class, ?Trainer $trainer, array $reviews) { ?>
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

    <section class="capacity-section">
      <article class="capacity-card">
        <div class="capacity-row">
          <span>0 / <?= $class->capacity ?> enrolled</span>
          <span><?= $class->capacity ?> spots left</span>
        </div>
        <div class="capacity-bar-bg">
          <div class="capacity-bar-fill" style="width: 0%;"></div>
        </div>
        <button class="btn-book">Book a spot</button>
      </article>
    </section>

    <section class="reviews-section">
      <h2>Reviews</h2>
      <?php foreach ($reviews as $r): ?>
      <article class="review-card">
        <header class="review-header">
          <span class="review-author"><?= h($r->author_name) ?></span>
          <div class="review-stars">
            <?= str_repeat('★', $r->rating) . str_repeat('☆', 5 - $r->rating) ?>
          </div>
        </header>
        <p class="review-message"><?= h($r->comment) ?></p>
      </article>
      <?php endforeach; ?>
    </section>

  </section>
<?php } ?>