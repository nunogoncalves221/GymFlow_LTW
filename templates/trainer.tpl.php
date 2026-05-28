<?php
declare(strict_types=1);
?>

<?php function drawTrainerProfile(Trainer $trainer, array $classes) { ?>
  <a href="#" onclick="history.back();return false;" class="back-link"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg></a>

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