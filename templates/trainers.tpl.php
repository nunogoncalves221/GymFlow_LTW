<?php
declare(strict_types=1);
?>

<?php function drawTrainersList(array $trainers): void { ?>
<a href="#" onclick="history.back();return false;" class="back-link"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg></a>

<section class="trainers-list-page">
  <header class="trainers-list-header">
    <h1>Our Trainers</h1>
    <p>The experts behind every class at GymFlow</p>
  </header>

  <div class="trainers-list-grid">
    <?php foreach ($trainers as $tr):
      $initials = strtoupper(implode('', array_map(
        fn($w) => $w[0],
        array_filter(explode(' ', $tr->name))
      )));
      $initials = substr($initials, 0, 2);
      $specs = array_filter(array_map('trim', preg_split('/[·,\/]+/', $tr->specializations)));
    ?>
    <a href="/pages/trainer.php?id=<?= $tr->id ?>" class="trainer-list-card">
      <div class="trainer-list-avatar"><?= h($initials) ?></div>
      <div class="trainer-list-info">
        <h2 class="trainer-list-name"><?= h($tr->name) ?></h2>
        <div class="trainer-list-specs">
          <?php foreach ($specs as $s): ?>
            <span class="trainer-list-pill"><?= h($s) ?></span>
          <?php endforeach; ?>
        </div>
        <p class="trainer-list-bio"><?= h(substr($tr->bio, 0, 100)) . (strlen($tr->bio) > 100 ? '…' : '') ?></p>
      </div>
      <span class="trainer-list-arrow">&rarr;</span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php } ?> 