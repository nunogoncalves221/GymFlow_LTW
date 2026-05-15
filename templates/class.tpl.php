<?php
declare(strict_types=1);

function classFilterUrl(string $type, string $day, array $override = []): string {
    $p = array_filter(array_merge(['type' => $type, 'day' => $day], $override));
    return '/pages/index.php' . ($p ? '?' . http_build_query($p) : '');
}
?>

<?php function drawClassList(array $classes, array $types, string $filter_type, string $filter_day) { ?>
  <div class="filters">
    <a href="/pages/index.php"
       class="filter-btn <?= !$filter_type && !$filter_day ? 'active' : '' ?>">All</a>

    <?php foreach ($types as $t): ?>
      <a href="<?= h(classFilterUrl($filter_type, $filter_day, ['type' => $filter_type === $t ? '' : $t])) ?>"
         class="filter-btn <?= $filter_type === $t ? 'active' : '' ?>"><?= h($t) ?></a>
    <?php endforeach; ?>

    <span style="color:var(--border);margin:0 .25rem">|</span>

    <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
      <a href="<?= h(classFilterUrl($filter_type, $filter_day, ['day' => $filter_day === $d ? '' : $d])) ?>"
         class="filter-btn <?= $filter_day === $d ? 'active' : '' ?>"><?= h(substr($d, 0, 3)) ?></a>
    <?php endforeach; ?>
  </div>

  <section class="classes-grid">
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
