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
    <span><a href="/pages/index.php" class="header-logo">GymFlow</a></span>
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

<?php function drawPricingSection() { ?>
<section class="pricing-section">
  <div class="membership-header">
    <h2>Membership Plans</h2>
    <p class="membership-subtitle">Choose the plan that fits your lifestyle.</p>
  </div>
  <div class="membership-plans-grid">

    <article class="plan-card">
      <div class="plan-header">
        <h3 class="plan-name">Basic</h3>
        <div class="plan-price">
          <span class="plan-price-amount">€9.99</span>
          <span class="plan-price-period">/month</span>
        </div>
        <p class="plan-description">Perfect to get started. Access to selected classes with a weekly limit.</p>
      </div>
      <ul class="plan-features">
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          2 classes per week
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Access to Yoga and Meditation classes
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Locker room access
        </li>
      </ul>
      <div class="plan-action">
        <a href="/pages/membership.php" class="btn-plan">Get Started</a>
      </div>
    </article>

    <article class="plan-card">
      <div class="plan-header">
        <h3 class="plan-name">Standard</h3>
        <div class="plan-price">
          <span class="plan-price-amount">€19.99</span>
          <span class="plan-price-period">/month</span>
        </div>
        <p class="plan-description">Great for regular gym-goers. More classes, more flexibility.</p>
      </div>
      <ul class="plan-features">
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          5 classes per week
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Access to all class types
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Locker room access
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Guest pass once a month
        </li>
      </ul>
      <div class="plan-action">
        <a href="/pages/membership.php" class="btn-plan">Get Started</a>
      </div>
    </article>

    <article class="plan-card plan-card--featured">
      <div class="plan-badge">Most Popular</div>
      <div class="plan-header">
        <h3 class="plan-name">Premium</h3>
        <div class="plan-price">
          <span class="plan-price-amount">€29.99</span>
          <span class="plan-price-period">/month</span>
        </div>
        <p class="plan-description">Unlimited access to all classes and priority enrollment.</p>
      </div>
      <ul class="plan-features">
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Unlimited classes
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Access to all class types
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Priority enrollment
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Locker room access
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Guest pass twice a month
        </li>
        <li>
          <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
          Personal trainer consultation
        </li>
      </ul>
      <div class="plan-action">
        <a href="/pages/membership.php" class="btn-plan">Get Started</a>
      </div>
    </article>

  </div>
</section>
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