<?php
declare(strict_types=1);

function calTimeToRow(string $time): int {
    [$h, $m] = array_map('intval', explode(':', $time));
    return ($h - 7) * 2 + intdiv($m, 30) + 2;
}
?>

<?php function drawTrainerDashboard(Trainer $trainer, array $classes, array $rosters, Session $session) {
    $dayToCol = [
        'Monday' => 2, 'Tuesday' => 3, 'Wednesday' => 4,
        'Thursday' => 5, 'Friday' => 6, 'Saturday' => 7, 'Sunday' => 8
    ];
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
  <section class="trainer-dashboard">

    <header class="dashboard-header">
      <div>
        <h1>My Schedule</h1>
        <p class="dashboard-sub">Welcome back, <?= h($trainer->name) ?></p>
      </div>
    </header>

    <?php if (empty($classes)): ?>
      <p class="no-results">You have no classes assigned yet.</p>
    <?php else: ?>

    <div class="cal-wrapper">
      <div class="cal-grid">

        <!-- Corner + day headers -->
        <div class="cal-corner"></div>
        <?php foreach ($days as $day): ?>
          <div class="cal-day-header"><?= substr($day, 0, 3) ?></div>
        <?php endforeach; ?>

        <!-- Time labels + background cells (07:00 to 21:30, 30-min slots) -->
        <?php for ($i = 0; $i < 30; $i++):
            $totalMin = 7 * 60 + $i * 30;
            $hh    = intdiv($totalMin, 60);
            $mm    = $totalMin % 60;
            $label = $mm === 0 ? sprintf('%02d:00', $hh) : '';
            $row   = $i + 2;
        ?>
          <div class="cal-time-label" style="grid-column:1;grid-row:<?= $row ?>"><?= $label ?></div>
          <?php for ($col = 2; $col <= 8; $col++): ?>
            <div class="cal-cell <?= $mm === 30 ? 'hour-start' : '' ?>"
                 style="grid-column:<?= $col ?>;grid-row:<?= $row ?>"></div>
          <?php endfor; ?>
        <?php endfor; ?>

        <!-- Class cards -->
        <?php foreach ($classes as $class):
            $row      = calTimeToRow($class->time);
            $span     = max(1, (int)ceil($class->duration_min / 30));
            $col      = $dayToCol[$class->day_of_week] ?? 2;
            $enrolled = count($rosters[$class->id] ?? []);
            $rosterJson = h(json_encode($rosters[$class->id] ?? []));
        ?>
          <div class="cal-class-card"
               style="grid-column:<?= $col ?>;grid-row:<?= $row ?>/span <?= $span ?>"
               data-id="<?= $class->id ?>"
               data-name="<?= h($class->name) ?>"
               data-type="<?= h($class->type) ?>"
               data-day="<?= h($class->day_of_week) ?>"
               data-time="<?= h($class->time) ?>"
               data-duration="<?= $class->duration_min ?>"
               data-capacity="<?= $class->capacity ?>"
               data-enrolled="<?= $enrolled ?>"
               data-roster="<?= $rosterJson ?>">
            <div class="cal-class-name"><?= h($class->name) ?></div>
            <div class="cal-class-time"><?= h($class->time) ?></div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>

    <?php endif; ?>

  </section>

  <!-- Class detail + reschedule modal -->
  <div id="class-modal" class="cal-modal-overlay" style="display:none">
    <div class="cal-modal">
      <button id="modal-close" class="modal-close" type="button">×</button>
      <h2 id="modal-name" class="modal-class-name"></h2>
      <div class="modal-info-grid">
        <div class="modal-info-item">
          <div class="modal-info-label">Time</div>
          <div id="modal-time" class="modal-info-value"></div>
        </div>
        <div class="modal-info-item">
          <div class="modal-info-label">Duration</div>
          <div id="modal-duration" class="modal-info-value"></div>
        </div>
        <div class="modal-info-item">
          <div class="modal-info-label">Enrolled</div>
          <div id="modal-enrolled" class="modal-info-value"></div>
        </div>
      </div>

      <h3 class="modal-section-title">Members</h3>
      <table class="roster-table">
        <thead><tr><th>Name</th><th>Username</th></tr></thead>
        <tbody id="modal-roster"></tbody>
      </table>

      <h3 class="modal-section-title">Reschedule</h3>
      <form action="/actions/action_reschedule_class.php" method="post" class="modal-reschedule-form">
        <?= $session->csrfField() ?>
        <input type="hidden" name="class_id" id="modal-class-id">
        <div class="form-group">
          <label for="modal-reschedule-day">Day</label>
          <select name="day" id="modal-reschedule-day">
            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
              <option value="<?= $d ?>"><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="modal-reschedule-time">Time (07:00–22:00)</label>
          <input type="time" name="time" id="modal-reschedule-time" min="07:00" max="22:00" step="1800">
        </div>
        <div class="modal-submit">
          <button type="submit" class="btn-save" style="width:100%">Reschedule</button>
        </div>
      </form>
    </div>
  </div>

<?php } ?>
