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
    <a href="/pages/profile.php" class="btn-profile">Profile</a>
  </header>

  <main>
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
