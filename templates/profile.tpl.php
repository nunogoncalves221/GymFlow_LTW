<?php
declare(strict_types=1);
?>

<?php function drawProfileForm(User $user) { ?>
  <section class="profile-page">

    <a href="/pages/index.php" class="back-link">← Back to classes</a>

    <article class="profile-hero">
      <div class="profile-avatar-wrap">
        <div class="profile-avatar"><?= h(initials($user->name)) ?></div>
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

    <article class="profile-form-card">
      <h3>Logout</h3>
      <p style="color: var(--text-muted); margin-bottom: 1rem;">You are logged in as <strong><?= h($user->username) ?></strong>.</p>
      <form action="/pages/profile.php" method="post">
        <input type="hidden" name="action" value="logout" />
        <button type="submit" class="btn-cancel">Logout</button>
      </form>
    </article>

  </section>
<?php } ?>
