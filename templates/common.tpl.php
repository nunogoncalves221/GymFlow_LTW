<?php
declare(strict_types=1);
require_once(__DIR__ . '/../utils/session.php');
?>

<?php function drawHeader(Session $session, string $title = 'GymFlow') { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="../css/style.css" />
  <script src="../javascript/script.js" defer></script>
</head>
<body>
  <header class="site-header">
    <h1><a href="/pages/index.php" class="header-logo">GymFlow</a></h1>
    <?php
      if ($session->isLoggedIn()) drawLogoutForm($session);
      else drawLoginForm();
    ?>
  </header>

  <section id="messages">
    <?php foreach ($session->getMessages() as $message) { ?>
      <article class="message <?= h($message['type']) ?>">
        <?= h($message['text']) ?>
      </article>
    <?php } ?>
  </section>

  <main>
<?php } ?>

<?php function drawLoginForm() { ?>
  <a href="../pages/login.php" class="btn-profile">Login</a>
<?php } ?>

<?php function drawLogoutForm(Session $session) { ?>
  <div class="header-user-actions">
    <?php if ($session->getType() === 'trainer'): ?>
      <a href="/pages/trainer_dashboard.php" class="btn-profile">Dashboard</a>
    <?php endif; ?>
    <?php if ($session->getType() === 'admin'): ?>
      <a href="../pages/admin.php" class="btn-admin-icon" title="Admin Dashboard">
        <img src="../docs/icons/admin-dashboard.png" alt="Admin Dashboard">
      </a>
    <?php endif; ?>
    <a href="/pages/profile.php" class="btn-profile"><?= h($session->getName()) ?></a>
  </div>
<?php } ?>

<?php function drawFooter() { ?>
  </main>

  <footer class="site-footer">
    <span>GymFlow</span>
    <span>© 2026 GymFlow. All rights reserved.</span>
  </footer>
</body>
</html>
<?php } ?>
