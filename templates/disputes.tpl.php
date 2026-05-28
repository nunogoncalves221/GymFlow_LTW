<?php
declare(strict_types=1);

$CATEGORIES = [
    'equipment_malfunction' => 'Equipment Malfunction',
    'class_cancellation'    => 'Class Cancellation',
    'billing'               => 'Billing Issue',
    'staff_conduct'         => 'Staff Conduct',
    'other'                 => 'Other',
];

$STATUS_LABELS = [
    'open'      => 'Open',
    'in_review' => 'In Review',
    'resolved'  => 'Resolved',
];
?>

<?php function drawDisputesPage(array $disputes, Session $session): void {
    global $CATEGORIES, $STATUS_LABELS; ?>
<section class="disputes-page">

  <?php foreach ($session->getMessages() as $m): ?>
    <div class="flash flash-<?= h($m['type']) ?>"><?= h($m['text']) ?></div>
  <?php endforeach; ?>

  <div class="admin-section-header">
    <h2>My Reports &amp; Feedback</h2>
  </div>

  <div class="review-card dispute-form-card">
    <h3 class="dispute-form-title">Report an Issue</h3>
    <form method="post" action="../actions/action_submit_dispute.php">
      <?= $session->csrfField() ?>
      <div class="dispute-field">
        <label class="dispute-label">Category</label>
        <select name="category" required class="dispute-input">
          <option value="">— Select a category —</option>
          <?php foreach ($CATEGORIES as $val => $label): ?>
            <option value="<?= h($val) ?>"><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dispute-field">
        <label class="dispute-label">Description</label>
        <textarea name="description" required minlength="10" rows="4"
          placeholder="Describe the issue in detail…"
          class="dispute-input dispute-textarea"></textarea>
      </div>
      <button type="submit" class="btn">Submit Report</button>
    </form>
  </div>

  <h3 class="dispute-history-title">My Previous Reports</h3>
  <?php if (empty($disputes)): ?>
    <p class="dispute-empty">You have not submitted any reports yet.</p>
  <?php else: ?>
    <?php foreach ($disputes as $d): ?>
      <div class="review-card dispute-item">
        <div class="dispute-item-header">
          <span class="badge <?= disputeStatusBadgeClass($d->status) ?>"><?= h($STATUS_LABELS[$d->status] ?? $d->status) ?></span>
          <span class="badge badge-category"><?= h($CATEGORIES[$d->category] ?? $d->category) ?></span>
          <span class="dispute-date"><?= h(substr($d->created_at, 0, 10)) ?></span>
        </div>
        <p class="dispute-description"><?= h($d->description) ?></p>
        <?php if ($d->admin_response): ?>
          <div class="dispute-admin-response">
            <span class="dispute-response-meta">Admin response · <?= h(substr($d->responded_at ?? '', 0, 10)) ?></span>
            <p class="dispute-response-text"><?= h($d->admin_response) ?></p>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</section>
<?php } ?>

<?php function disputeStatusBadgeClass(string $status): string {
    return match($status) {
        'open'      => 'badge-member',
        'in_review' => 'badge-in-review',
        'resolved'  => 'badge-available',
        default     => '',
    };
} ?>