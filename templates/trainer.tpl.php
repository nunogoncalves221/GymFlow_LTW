<?php
declare(strict_types=1);
?>

<?php function drawTrainerProfile(Trainer $trainer, array $classes) { ?>
  <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>

  <section class="trainer-detail">

    <header class="trainer-info-card">
      <span class="trainer-photo"><?= h(initials($trainer->name)) ?></span>
      <section class="trainer-info-text">
        <h1><?= h($trainer->name) ?></h1>
        <div class="trainer-specialty"><?= h($trainer->specializations) ?></div>
        <p><?= h($trainer->bio) ?></p>
      </section>
    </header>

    <section class="trainer-classes">
      <h2>Classes</h2>
      <?php foreach ($classes as $c): ?>
      <a href="/pages/class.php?id=<?= $c->id ?>" class="trainer-class-item">
        <dl>
          <dt class="trainer-class-name"><?= h($c->name) ?></dt>
          <dd class="trainer-class-schedule"><?= h($c->day_of_week) ?> · <?= h($c->time) ?> · <?= $c->duration_min ?> min</dd>
        </dl>
        <span>→</span>
      </a>
      <?php endforeach; ?>
    </section>

  </section>
<?php } ?> 