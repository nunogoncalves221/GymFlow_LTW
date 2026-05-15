<?php
declare(strict_types=1);
?>

<?php function drawLoginPage() { ?>
  <section class="auth-page">
    <article class="auth-card">
      <h2>Login</h2>
      <form action="../actions/action_login.php" method="post">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-save">Login</button>
        </div>
      </form>
      <p class="auth-switch">Don't have an account? <a href="/pages/register.php">Register</a></p>
    </article>
  </section>
<?php } ?>
