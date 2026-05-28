<?php
declare(strict_types=1);

$DISPUTE_CATEGORIES = [
    'equipment_malfunction' => 'Equipment Malfunction',
    'class_cancellation'    => 'Class Cancellation',
    'billing'               => 'Billing Issue',
    'staff_conduct'         => 'Staff Conduct',
    'other'                 => 'Other',
];
?>

<?php function drawAdminDisputeList(array $disputes, array $counts, string $filter, Session $session): void {
    global $DISPUTE_CATEGORIES; ?>
<section class="admin-section">

  <?php foreach ($session->getMessages() as $m): ?>
    <div class="flash flash-<?= h($m['type']) ?>"><?= h($m['text']) ?></div>
  <?php endforeach; ?>

  <div class="admin-section-header">
    <h2>Reports &amp; Feedback</h2>
  </div>

  <div class="admin-filter-tabs">
    <a href="?" class="<?= $filter === '' ? 'active' : '' ?>">
      All <span class="badge-count"><?= array_sum($counts) ?></span>
    </a>
    <a href="?status=open" class="<?= $filter === 'open' ? 'active' : '' ?>">
      Open <span class="badge-count"><?= $counts['open'] ?></span>
    </a>
    <a href="?status=in_review" class="<?= $filter === 'in_review' ? 'active' : '' ?>">
      In Review <span class="badge-count"><?= $counts['in_review'] ?></span>
    </a>
    <a href="?status=resolved" class="<?= $filter === 'resolved' ? 'active' : '' ?>">
      Resolved <span class="badge-count"><?= $counts['resolved'] ?></span>
    </a>
  </div>

  <?php if (empty($disputes)): ?>
    <p class="dispute-empty">No reports found.</p>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th><th>Member</th><th>Category</th><th>Description</th><th>Status</th><th>Date</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($disputes as $d): ?>
          <tr>
            <td><?= h((string)$d->id) ?></td>
            <td><?= h($d->author_name) ?></td>
            <td><?= h($DISPUTE_CATEGORIES[$d->category] ?? $d->category) ?></td>
            <td class="dispute-desc-cell"><?php $desc = $d->description; echo h(strlen($desc) > 80 ? substr($desc, 0, 80) . '…' : $desc); ?></td>
            <td><span class="badge <?= disputeAdminBadgeClass($d->status) ?>"><?= h(disputeStatusLabel($d->status)) ?></span></td>
            <td><?= h(substr($d->created_at, 0, 10)) ?></td>
            <td class="actions">
              <form method="post" action="../actions/action_update_dispute_status.php" class="status-quick-form">
                <?= $session->csrfField() ?>
                <input type="hidden" name="id" value="<?= $d->id ?>">
                <select name="status" class="status-quick-select" onchange="this.form.submit()">
                  <option value="open"      <?= $d->status === 'open'      ? 'selected' : '' ?>>Open</option>
                  <option value="in_review" <?= $d->status === 'in_review' ? 'selected' : '' ?>>In Review</option>
                  <option value="resolved"  <?= $d->status === 'resolved'  ? 'selected' : '' ?>>Resolved</option>
                </select>
              </form>
              <a href="#" onclick="openDisputeModal(<?= $d->id ?>,<?= htmlspecialchars(json_encode([
                  'author'   => $d->author_name,
                  'category' => $DISPUTE_CATEGORIES[$d->category] ?? $d->category,
                  'desc'     => $d->description,
                  'status'   => $d->status,
                  'response' => $d->admin_response ?? '',
                  'date'     => substr($d->created_at, 0, 10),
              ]), ENT_QUOTES) ?>);return false;">Respond</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<!-- Response Modal -->
<div id="dispute-modal" class="dispute-modal-overlay">
  <div class="dispute-modal-box">
    <h3 class="dispute-modal-title">Report Details &amp; Response</h3>
    <div id="modal-info" class="dispute-modal-info">
      <div class="dispute-modal-meta" id="modal-meta"></div>
      <div class="dispute-modal-desc-label">Member's report</div>
      <div id="modal-desc" class="dispute-modal-desc"></div>
    </div>
    <form method="post" action="../actions/action_admin_dispute.php">
      <?= $session->csrfField() ?>
      <input type="hidden" name="id" id="modal-id">
      <div class="dispute-field">
        <label class="dispute-label">Status</label>
        <select name="status" id="modal-status" required class="dispute-input">
          <option value="open">Open</option>
          <option value="in_review">In Review</option>
          <option value="resolved">Resolved</option>
        </select>
      </div>
      <div class="dispute-field">
        <label class="dispute-label">Your Response</label>
        <textarea name="admin_response" id="modal-response" required rows="4"
          placeholder="Write a response to the member…"
          class="dispute-input dispute-textarea"></textarea>
      </div>
      <div class="dispute-modal-actions">
        <button type="submit" class="btn">Save Response</button>
        <button type="button" class="btn btn-ghost" onclick="closeDisputeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openDisputeModal(id, data) {
    document.getElementById('modal-id').value = id;
    document.getElementById('modal-status').value = data.status;
    document.getElementById('modal-response').value = data.response;
    document.getElementById('modal-meta').innerHTML =
        '<strong>' + esc(data.author) + '</strong> &mdash; ' + esc(data.category) +
        ' &mdash; <span class="dispute-date">' + esc(data.date) + '</span>';
    document.getElementById('modal-desc').textContent = data.desc;
    document.getElementById('dispute-modal').classList.add('is-open');
}
function closeDisputeModal() {
    document.getElementById('dispute-modal').classList.remove('is-open');
}
function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
document.getElementById('dispute-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDisputeModal();
});
</script>
<?php } ?>

<?php function disputeAdminBadgeClass(string $status): string {
    return match($status) {
        'open'      => 'badge-member',
        'in_review' => 'badge-in-review',
        'resolved'  => 'badge-available',
        default     => '',
    };
} ?>

<?php function disputeStatusLabel(string $status): string {
    return match($status) {
        'open'      => 'Open',
        'in_review' => 'In Review',
        'resolved'  => 'Resolved',
        default     => $status,
    };
} ?> 