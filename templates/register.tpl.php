<?php
declare(strict_types=1);
?>

<?php function drawRegisterForm() { ?>
  <section class="auth-page">
    <article class="auth-card">
      <h2>Register</h2>
      <form action="../actions/action_register.php" method="post">
        <div class="form-group">
          <label for="name">Full name</label>
          <input type="text" id="name" name="name" required />
        </div>
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm password</label>
          <input type="password" id="confirm_password" name="confirm_password" required />
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save">Register</button>
        </div>
      </form>
      <p class="auth-switch">Already have an account? <a href="/pages/login.php">Login</a></p>
    </article>
  </section>
<?php } ?>
