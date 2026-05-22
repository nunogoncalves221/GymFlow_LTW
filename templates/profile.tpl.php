<?php
declare(strict_types=1);
?>

<?php function drawProfileForm(User $user, ?Trainer $trainer) { ?>
  <section class="profile-page">

    <a href="/pages/index.php" class="back-link">← Back to classes</a>

    <article class="profile-hero">
      <div class="profile-avatar-wrap">
        <?php if ($user->profile_picture): ?>
          <img class="profile-avatar-img" src="<?= h($user->getAvatarPath()) ?>" alt="Profile picture" />
        <?php else: ?>
          <div class="profile-avatar"><?= h(initials($user->name)) ?></div>
        <?php endif; ?>
      </div>
      <div class="profile-hero-text">
        <h2><?= h($user->name) ?></h2>
        <p><?= h($user->username) ?></p>
      </div>
      <span class="profile-badge"><?= h($user->type) ?></span>
    </article>

    <article class="profile-form-card">
      <h3>Profile Picture</h3>
      <div class="profile-picture-section">
        <img class="profile-picture-preview" src="<?= h($user->getAvatarPath()) ?>" alt="Current profile picture" />
        <form action="../actions/action_upload_profile_picture.php" method="post" enctype="multipart/form-data" class="profile-picture-form">
          <label for="profile_picture" class="btn-upload">Choose image</label>
          <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp" />
          <span class="upload-hint">JPEG, PNG, GIF or WebP · max 2 MB</span>
          <button type="submit" class="btn-save">Upload</button>
        </form>
      </div>
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

    <?php if ($trainer !== null): ?>
      <article class="profile-form-card">
        <h3>Trainer Profile</h3>
        <form action="../actions/action_edit_trainer_profile.php" method="post">
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
      <h3>Logout</h3>
      <p style="color: var(--text-muted); margin-bottom: 1rem;">You are logged in as <strong><?= h($user->username) ?></strong>.</p>
      <form action="/pages/profile.php" method="post">
        <input type="hidden" name="action" value="logout" />
        <button type="submit" class="btn-cancel">Logout</button>
      </form>
    </article>
  </section>
<?php } ?>
