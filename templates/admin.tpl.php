<?php
declare(strict_types=1);

require_once(__DIR__ . '/../database/user.class.php');
require_once(__DIR__ . '/../database/trainer.class.php');
require_once(__DIR__ . '/../database/fitnessclass.class.php');
require_once(__DIR__ . '/../database/equipment.class.php');
?>

<?php function drawAdminNav(string $active = '') { ?>
<nav class="admin-nav">
  <a href="../pages/admin.php"              class="<?= $active === 'dashboard'   ? 'active' : '' ?>">Dashboard</a>
  <a href="../pages/admin_users.php"        class="<?= $active === 'users'       ? 'active' : '' ?>">Users</a>
  <a href="../pages/admin_classes.php"      class="<?= $active === 'classes'     ? 'active' : '' ?>">Classes</a>
  <a href="../pages/admin_equipment.php"    class="<?= $active === 'equipment'   ? 'active' : '' ?>">Equipment</a>
  <a href="../pages/admin_memberships.php"  class="<?= $active === 'memberships' ? 'active' : '' ?>">Memberships</a>
  <a href="../pages/admin_disputes.php" class="<?= $active === 'disputes' ? 'active' : '' ?>">Reports</a>
</nav>
<?php } ?>

<?php function drawAdminDashboard(array $stats) { ?>
<section class="admin-dashboard">
  <h2>Dashboard</h2>
  <div class="admin-stats">
    <div class="stat-card">
      <span class="stat-number"><?= h((string)$stats['members']) ?></span>
      <span class="stat-label">Members</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= h((string)$stats['trainers']) ?></span>
      <span class="stat-label">Trainers</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= h((string)$stats['classes']) ?></span>
      <span class="stat-label">Classes</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= h((string)$stats['equipment']) ?></span>
      <span class="stat-label">Equipment</span>
    </div>
    <div class="stat-card">
      <span class="stat-number"><?= h((string)($stats['open_disputes'] ?? 0)) ?></span>
      <span class="stat-label">Open Reports</span>
    </div>
  </div>
  <div class="admin-quick-links">
    <a href="../pages/admin_users.php?action=create_member" class="btn">Add Member</a>
    <a href="../pages/admin_users.php?action=create_trainer" class="btn">Add Trainer</a>
    <a href="../pages/admin_classes.php?action=create" class="btn">Add Class</a>
    <a href="../pages/admin_equipment.php?action=create" class="btn">Add Equipment</a>
    <a href="../pages/admin_disputes.php" class="btn">View Reports</a>
  </div>
</section>
<?php } ?>

<?php function drawUserList(array $users, Session $session, string $typeFilter = '') { ?>
<section class="admin-section">
  <div class="admin-section-header">
    <h2><?= $typeFilter ? ucfirst($typeFilter) . 's' : 'All Users' ?></h2>
    <div class="admin-section-actions">
      <a href="?action=create_member" class="btn btn-small">+ Member</a>
      <a href="?action=create_trainer" class="btn btn-small">+ Trainer</a>
    </div>
  </div>
  <div class="admin-filter-tabs">
    <a href="?" class="<?= $typeFilter === '' ? 'active' : '' ?>">All</a>
    <a href="?type=member" class="<?= $typeFilter === 'member' ? 'active' : '' ?>">Members</a>
    <a href="?type=trainer" class="<?= $typeFilter === 'trainer' ? 'active' : '' ?>">Trainers</a>
    <a href="?type=admin" class="<?= $typeFilter === 'admin' ? 'active' : '' ?>">Admins</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr><th>Name</th><th>Username</th><th>Type</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user) { ?>
        <tr>
          <td><?= h($user->name) ?></td>
          <td><?= h($user->username) ?></td>
          <td><span class="badge badge-<?= h($user->type) ?>"><?= h($user->type) ?></span></td>
          <td class="actions">
            <a href="?action=edit&id=<?= $user->id ?>">Edit</a>
            <?php if ($user->type !== 'admin') { ?>
              <form method="post" style="display:inline">
                <?= $session->csrfField() ?>
                <input type="hidden" name="action" value="elevate">
                <input type="hidden" name="id" value="<?= $user->id ?>">
                <button type="submit" class="btn-link" onclick="return confirm('Elevate <?= h($user->name) ?> to admin?')">Elevate</button>
              </form>
            <?php } ?>
            <?php if ($user->type === 'member' || $user->type === 'trainer') { ?>
              <form method="post" style="display:inline">
                <?= $session->csrfField() ?>
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="id" value="<?= $user->id ?>">
                <button type="submit" class="btn-link" onclick="return confirm('Deactivate this account?')">Deactivate</button>
              </form>
            <?php } ?>
            <?php if ($user->type === 'deactivated') { ?>
              <form method="post" style="display:inline">
                <?= $session->csrfField() ?>
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="id" value="<?= $user->id ?>">
                <button type="submit" class="btn-link" onclick="return confirm('Reactivate this account?')">Activate</button>
              </form>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>
<?php } ?>

<?php function drawCreateUserForm(string $type, Session $session) { ?>
<section class="admin-section">
  <h2>Create <?= ucfirst($type) ?></h2>
  <form action="../actions/action_create_user.php" method="post" class="admin-form">
    <?= $session->csrfField() ?>
    <input type="hidden" name="type" value="<?= h($type) ?>">
    <label>Name
      <input type="text" name="name" required>
    </label>
    <label>Username
      <input type="text" name="username" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <?php if ($type === 'trainer') { ?>
      <label>Bio
        <textarea name="bio" rows="3"></textarea>
      </label>
      <label>Specializations
        <input type="text" name="specializations" placeholder="e.g. Yoga · HIIT">
      </label>
      <label>Certifications
        <input type="text" name="certifications">
      </label>
    <?php } ?>
    <div class="form-actions">
      <button type="submit" class="btn">Create <?= ucfirst($type) ?></button>
      <a href="../pages/admin_users.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</section>
<?php } ?>

<?php function drawEditUserForm(User $user, Session $session) { ?>
<section class="admin-section">
  <h2>Edit User</h2>
  <form action="../actions/action_edit_user.php" method="post" class="admin-form">
    <?= $session->csrfField() ?>
    <input type="hidden" name="id" value="<?= $user->id ?>">
    <label>Name
      <input type="text" name="name" value="<?= h($user->name) ?>" required>
    </label>
    <label>Username
      <input type="text" name="username" value="<?= h($user->username) ?>" required>
    </label>
    <label>New Password <small>(leave blank to keep current)</small>
      <input type="password" name="password">
    </label>
    <div class="form-actions">
      <button type="submit" class="btn">Save</button>
      <a href="../pages/admin_users.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</section>
<?php } ?>

<?php function drawClassList(array $classes, Session $session) { ?>
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Classes</h2>
    <a href="?action=create" class="btn btn-small">+ Add Class</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr><th>Name</th><th>Type</th><th>Trainer</th><th>Day</th><th>Time</th><th>Cap.</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($classes as $class) { ?>
        <tr>
          <td><?= h($class->name) ?></td>
          <td><?= h($class->type) ?></td>
          <td><?= h($class->trainer_name) ?></td>
          <td><?= h($class->day_of_week) ?></td>
          <td><?= h($class->time) ?></td>
          <td><?= $class->capacity ?></td>
          <td class="actions">
            <a href="?action=edit&id=<?= $class->id ?>">Edit</a>
            <form method="post" style="display:inline">
              <?= $session->csrfField() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $class->id ?>">
              <button type="submit" class="btn-link" onclick="return confirm('Delete this class?')">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>
<?php } ?>

<?php function drawClassForm(?FitnessClass $class, array $trainers, Session $session) {
  $isEdit = $class !== null;
  $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
?>
<section class="admin-section">
  <h2><?= $isEdit ? 'Edit Class' : 'Create Class' ?></h2>
  <form action="../actions/action_save_class.php" method="post" class="admin-form">
    <?= $session->csrfField() ?>
    <?php if ($isEdit) { ?><input type="hidden" name="id" value="<?= $class->id ?>"><?php } ?>
    <label>Name
      <input type="text" name="name" value="<?= h($class?->name ?? '') ?>" required>
    </label>
    <label>Type
      <input type="text" name="type" value="<?= h($class?->type ?? '') ?>" required placeholder="e.g. Yoga, HIIT">
    </label>
    <label>Description
      <textarea name="description" rows="3"><?= h($class?->description ?? '') ?></textarea>
    </label>
    <label>Duration (minutes)
      <input type="number" name="duration_min" value="<?= $class?->duration_min ?? 60 ?>" min="1" required>
    </label>
    <label>Capacity
      <input type="number" name="capacity" value="<?= $class?->capacity ?? 20 ?>" min="1" required>
    </label>
    <label>Trainer
      <select name="trainer_id">
        <option value="">— No trainer —</option>
        <?php foreach ($trainers as $t) { ?>
          <option value="<?= $t->id ?>" <?= ($class?->trainer_id === $t->id) ? 'selected' : '' ?>>
            <?= h($t->name) ?>
          </option>
        <?php } ?>
      </select>
    </label>
    <label>Day of Week
      <select name="day_of_week" required>
        <?php foreach ($days as $day) { ?>
          <option value="<?= $day ?>" <?= ($class?->day_of_week === $day) ? 'selected' : '' ?>><?= $day ?></option>
        <?php } ?>
      </select>
    </label>
    <label>Time
      <input type="time" name="time" value="<?= h($class?->time ?? '09:00') ?>" required>
    </label>
    <label>Image path
      <input type="text" name="image" value="<?= h($class?->image ?? '') ?>" placeholder="/docs/images/...">
    </label>
    <div class="form-actions">
      <button type="submit" class="btn"><?= $isEdit ? 'Save Changes' : 'Create Class' ?></button>
      <a href="../pages/admin_classes.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</section>
<?php } ?>

<?php function drawEquipmentList(array $equipment, Session $session) { ?>
<section class="admin-section">
  <div class="admin-section-header">
    <h2>Equipment</h2>
    <a href="?action=create" class="btn btn-small">+ Add Equipment</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr><th>Name</th><th>Type</th><th>Total</th><th>In Use</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($equipment as $item) { ?>
        <tr>
          <td><?= h($item->name) ?></td>
          <td><?= h($item->type) ?></td>
          <td><?= $item->quantity ?></td>
          <td><?= $item->in_use ?> / <?= $item->quantity ?></td>
          <td>
            <span class="badge badge-<?= $item->isAvailable() ? 'available' : 'unavailable' ?>">
              <?= $item->isAvailable() ? 'Available' : 'Not available' ?>
            </span>
          </td>
          <td class="actions">
            <a href="?action=edit&id=<?= $item->id ?>">Edit</a>
            <form method="post" style="display:inline">
              <?= $session->csrfField() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $item->id ?>">
              <button type="submit" class="btn-link" onclick="return confirm('Delete this equipment?')">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>
<?php } ?>

<?php function drawEquipmentForm(?Equipment $item, Session $session) {
  $isEdit = $item !== null;
?>
<section class="admin-section">
  <h2><?= $isEdit ? 'Edit Equipment' : 'Add Equipment' ?></h2>
  <form action="../actions/action_save_equipment.php" method="post" class="admin-form">
    <?= $session->csrfField() ?>
    <?php if ($isEdit) { ?><input type="hidden" name="id" value="<?= $item->id ?>"><?php } ?>
    <label>Name
      <input type="text" name="name" value="<?= h($item?->name ?? '') ?>" required>
    </label>
    <label>Type
      <input type="text" name="type" value="<?= h($item?->type ?? '') ?>" placeholder="e.g. Cardio, Weights">
    </label>
    <label>Total Quantity
      <input type="number" name="quantity" value="<?= $item?->quantity ?? 1 ?>" min="1" required>
    </label>
    <label>In Use
      <input type="number" name="in_use" value="<?= $item?->in_use ?? 0 ?>" min="0">
    </label>
    <div class="form-actions">
      <button type="submit" class="btn"><?= $isEdit ? 'Save Changes' : 'Add Equipment' ?></button>
      <a href="../pages/admin_equipment.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</section>
<?php } ?> 