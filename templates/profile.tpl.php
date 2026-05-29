<?php
declare(strict_types=1);
require_once(__DIR__ . '/../database/membership_plan.class.php');
?>

<?php function drawProfileForm(User $user, ?Trainer $trainer, Session $session, ?MembershipPlan $currentPlan = null) { ?>
  <section class="profile-page">

    <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>

    <article class="profile-hero">
      <div class="profile-avatar-wrap">
        <?php if ($user->profile_picture): ?>
          <img class="profile-avatar-img" src="<?= h($user->getAvatarPath()) ?>" alt="Profile picture" />
        <?php else: ?>
          <div class="profile-avatar"><?= h(initials($user->name)) ?></div>
        <?php endif; ?>
        <button type="button" class="avatar-edit-overlay" id="avatar-edit-btn">
          <?php include __DIR__ . '/../docs/icons/edit.svg'; ?>
          <span>Edit</span>
        </button>
        <div class="avatar-edit-menu" id="avatar-edit-menu">
          <form action="../actions/action_upload_profile_picture.php" method="post" enctype="multipart/form-data" id="avatar-upload-form">
            <?= $session->csrfField() ?>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" />
            <label for="profile_picture" class="avatar-menu-item">Change photo</label>
            <button type="submit" class="avatar-menu-item avatar-menu-save" id="avatar-upload-btn" style="display:none">Save photo</button>
          </form>
          <?php if ($user->profile_picture && $user->profile_picture !== '/docs/profile_pictures/default.jpg'): ?>
            <form action="../actions/action_remove_profile_picture.php" method="post">
              <?= $session->csrfField() ?>
              <button type="submit" class="avatar-menu-item avatar-menu-remove">Remove photo</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <div class="profile-hero-text">
        <h2><?= h($user->name) ?></h2>
        <p><?= h($user->username) ?></p>
      </div>
      <span class="profile-badge"><?= h($user->type) ?></span>
    </article>

    <article class="profile-form-card">
      <h3>Personal Information</h3>
      <form action="../actions/action_edit_profile.php" method="post">
        <?= $session->csrfField() ?>
        <div class="form-grid">
          <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" value="<?= h($user->name) ?>" required />
          </div>
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= h($user->username) ?>" required />
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save">Save changes</button>
        </div>
      </form>
    </article>

    <article class="profile-form-card">
      <h3>Change Password</h3>
      <form action="../actions/action_change_password.php" method="post">
        <?= $session->csrfField() ?>
        <div class="form-grid">
          <div class="form-group full">
            <label for="currentPw">Current password</label>
            <input type="password" id="currentPw" name="current_password" placeholder="Enter current password" />
          </div>
          <div class="form-group">
            <label for="newPw">New password</label>
            <input type="password" id="newPw" name="new_password" placeholder="Enter new password" />
          </div>
          <div class="form-group">
            <label for="confirmPw">Confirm new password</label>
            <input type="password" id="confirmPw" name="confirm_password" placeholder="Repeat new password" />
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save">Update password</button>
        </div>
      </form>
    </article>

    <?php if ($trainer !== null): ?>
      <article class="profile-form-card">
        <h3>Trainer Profile</h3>
        <form action="../actions/action_edit_trainer_profile.php" method="post">
          <?= $session->csrfField() ?>
          <div class="form-group full">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"><?= h($trainer->bio) ?></textarea>
          </div>
          <div class="form-group full">
            <label for="specializations">Specializations</label>
            <input type="text" id="specializations" name="specializations" value="<?= h($trainer->specializations) ?>" />
          </div>
          <div class="form-group full">
            <label for="certifications">Certifications</label>
            <input type="text" id="certifications" name="certifications" value="<?= h($trainer->certifications) ?>" />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-save">Save trainer profile</button>
          </div>
        </form>
      </article>
    <?php endif; ?>

    <article class="profile-form-card">
      <h3>Membership Plan</h3>
      <?php if ($currentPlan): ?>
        <p style="margin-bottom:.5rem;">You are on the <strong><?= h($currentPlan->name) ?></strong> plan — €<?= number_format($currentPlan->price, 2) ?>/month.</p>
        <ul style="list-style:none;padding:0;margin:0 0 1rem;color:var(--text-muted);font-size:.9rem;">
          <?php foreach ($currentPlan->features as $f): ?>
            <li>✓ <?= h($f) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="color:var(--text-muted);margin-bottom:1rem;">You don't have an active membership plan.</p>
      <?php endif; ?>
      <a href="/pages/membership.php" class="btn-save" style="display:inline-block;text-decoration:none;">View all plans</a>
    </article>

    <?php if ($user->type === 'member'): ?>
      <article class="profile-form-card">
        <h3 class="profile-section-title">Reports &amp; Feedback</h3>
        <p class="profile-logout-info">Report issues such as equipment malfunctions or class cancellations.</p>
        <a href="/pages/disputes.php" class="btn-save" style="display:inline-block;text-decoration:none;">My Reports</a>
      </article>
    <?php endif; ?>

    <article class="profile-form-card">
      <p style="color: var(--text-muted); margin-bottom: 1rem;">You are logged in as <strong><?= h($user->username) ?></strong>.</p>
      <form action="/pages/profile.php" method="post">
        <?= $session->csrfField() ?>
        <input type="hidden" name="action" value="logout" />
        <button type="submit" class="btn-cancel">Logout</button>
      </form>
    </article>
  </section>
<?php } ?> 