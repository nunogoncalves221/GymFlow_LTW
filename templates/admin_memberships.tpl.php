<?php
declare(strict_types=1);

require_once(__DIR__ . '/../database/membership_plan.class.php');
require_once(__DIR__ . '/../database/user.class.php');
?>

<?php function drawAdminMembershipsPage(array $plans, array $pending, array $members, Session $session) { ?>

  <?php if ($pending): ?>
  <section class="admin-section">
    <div class="admin-section-header">
      <h2>Pending Requests <span class="badge-count"><?= count($pending) ?></span></h2>
    </div>
    <table class="admin-table">
      <thead>
        <tr><th>Member</th><th>Requested Plan</th><th>Submitted</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($pending as $req): ?>
          <tr>
            <td><?= h($req['user_name']) ?> <small class="text-muted">(<?= h($req['username']) ?>)</small></td>
            <td><?= h($req['plan_name']) ?></td>
            <td><?= h(substr($req['created_at'], 0, 10)) ?></td>
            <td class="actions">
              <form method="post" action="/actions/action_admin_membership.php" class="form-inline">
                <?= $session->csrfField() ?>
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <button type="submit" class="btn btn-small">Approve</button>
              </form>
              <form method="post" action="/actions/action_admin_membership.php" class="form-inline">
                <?= $session->csrfField() ?>
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                <button type="submit" class="btn-link" onclick="return confirm('Reject this request?')">Reject</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <?php endif; ?>

  <section class="admin-section">
    <div class="admin-section-header">
      <h2>Membership Plans</h2>
      <a href="?action=create" class="btn btn-small">+ New Plan</a>
    </div>
    <table class="admin-table">
      <thead>
        <tr><th>Name</th><th>Price</th><th>Weekly Limit</th><th>Level</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($plans as $plan): ?>
          <tr>
            <td><?= h($plan->name) ?></td>
            <td>€<?= number_format($plan->price, 2) ?>/mo</td>
            <td><?= $plan->max_enrollments_per_week === null ? 'Unlimited' : $plan->max_enrollments_per_week ?></td>
            <td><?= $plan->access_level ?></td>
            <td>
              <span class="badge badge-<?= $plan->is_active ? 'available' : 'unavailable' ?>">
                <?= $plan->is_active ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td class="actions">
              <a href="?action=edit&id=<?= $plan->id ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="admin-section">
    <div class="admin-section-header">
      <h2>Members' Plans</h2>
    </div>
    <table class="admin-table">
      <thead>
        <tr><th>Member</th><th>Current Plan</th><th>Set Plan</th></tr>
      </thead>
      <tbody>
        <?php foreach ($members as $member): ?>
          <tr>
            <td><?= h($member->name) ?> <small class="text-muted">(<?= h($member->username) ?>)</small></td>
            <td><?php
              if ($member->plan_id) {
                $mp = array_values(array_filter($plans, fn($p) => $p->id === $member->plan_id));
                echo $mp ? h($mp[0]->name) : '<em>Unknown</em>';
              } else {
                echo '<em>None</em>';
              }
            ?></td>
            <td>
              <form method="post" action="/actions/action_admin_membership.php" class="form-set-plan">
                <?= $session->csrfField() ?>
                <input type="hidden" name="action" value="set_plan">
                <input type="hidden" name="user_id" value="<?= $member->id ?>">
                <select name="plan_id" class="admin-select-inline">
                  <option value="">— None —</option>
                  <?php foreach ($plans as $p): ?>
                    <option value="<?= $p->id ?>" <?= $member->plan_id === $p->id ? 'selected' : '' ?>>
                      <?= h($p->name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-small">Set</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

<?php } ?>

<?php function drawPlanForm(?MembershipPlan $plan, Session $session) {
  $isEdit = $plan !== null;
?>
<section class="admin-section">
  <h2><?= $isEdit ? 'Edit Plan' : 'New Membership Plan' ?></h2>
  <form action="/actions/action_save_plan.php" method="post" class="admin-form">
    <?= $session->csrfField() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $plan->id ?>"><?php endif; ?>

    <label>Plan Name
      <input type="text" name="name" value="<?= h($plan?->name ?? '') ?>" required>
    </label>
    <label>Price (€/month)
      <input type="number" name="price" value="<?= $plan?->price ?? '0' ?>" min="0" step="0.01" required>
    </label>
    <label>Description
      <textarea name="description" rows="2"><?= h($plan?->description ?? '') ?></textarea>
    </label>
    <label>Weekly class limit <small>(leave 0 for unlimited)</small>
      <input type="number" name="max_enrollments_per_week" value="<?= $plan?->max_enrollments_per_week ?? 0 ?>" min="0">
    </label>
    <label>Access Level <small>(1 = basic, 2 = standard, 3 = premium)</small>
      <input type="number" name="access_level" value="<?= $plan?->access_level ?? 1 ?>" min="1" max="10" required>
    </label>
    <label>Features <small>(one per line)</small>
      <textarea name="features" rows="6" placeholder="e.g. 2 classes per week&#10;Locker room access"><?= h(implode("\n", $plan?->features ?? [])) ?></textarea>
    </label>
    <label class="label-checkbox">
      <input type="checkbox" name="is_active" value="1" <?= (!$isEdit || $plan->is_active) ? 'checked' : '' ?>>
      Active (visible to members)
    </label>
    <div class="form-actions">
      <button type="submit" class="btn"><?= $isEdit ? 'Save Changes' : 'Create Plan' ?></button>
      <a href="/pages/admin_memberships.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</section>
<?php } ?> 