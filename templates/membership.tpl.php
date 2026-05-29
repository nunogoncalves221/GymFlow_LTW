<?php
declare(strict_types=1);
?>

<?php function drawMembershipPage(User $user, array $plans, ?MembershipPlan $current, ?array $pending, Session $session, int $weeklyUsed = 0) { ?>
<section class="membership-page">

  <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>

  <div class="membership-header">
    <h2>Membership Plans</h2>
    <?php if ($user->type === 'member'): ?>
    <p class="membership-subtitle">Choose the plan that fits your goals. Request a plan and an admin will activate it for you.</p>
    <?php else: ?>
    <p class="membership-subtitle">Overview of available membership plans.</p>
    <?php endif; ?>
  </div>

  <?php if ($user->type === 'member'): ?>
  <?php if ($current): ?>
    <div class="membership-current-banner">
      <span class="membership-current-label">Your current plan</span>
      <strong><?= h($current->name) ?></strong>
      <span class="membership-current-price">€<?= number_format($current->price, 2) ?>/month</span>
      <?php if ($current->max_enrollments_per_week !== null): ?>
        <span class="membership-current-quota">
          <?= $weeklyUsed ?> / <?= $current->max_enrollments_per_week ?> classes used this week
        </span>
      <?php else: ?>
        <span class="membership-current-quota">Unlimited classes</span>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="membership-no-plan-banner">
      <span>⚠ You don't have an active membership plan. Choose one below to start enrolling in classes.</span>
    </div>
  <?php endif; ?>
  <?php if ($pending): ?>
    <div class="membership-pending-banner">
      <span>⏳ You have a pending request for the <strong><?= h($pending['plan_name']) ?></strong> plan, submitted on <?= h(substr($pending['created_at'], 0, 10)) ?>. Waiting for admin approval.</span>
    </div>
  <?php endif; ?>
  <?php endif; ?>

  <div class="membership-plans-grid">
    <?php foreach ($plans as $plan):
      $isCurrent = $current && $current->id === $plan->id;
      $isPending = $pending && (int)$pending['plan_id'] === $plan->id;
    ?>
      <article class="plan-card <?= $isCurrent ? 'plan-card--current' : '' ?> <?= $plan->access_level >= 3 ? 'plan-card--featured' : '' ?>">

        <?php if ($plan->access_level >= 3): ?>
          <div class="plan-badge">Most Popular</div>
        <?php endif; ?>
        <?php if ($isCurrent): ?>
          <div class="plan-badge plan-badge--active">Active</div>
        <?php endif; ?>

        <div class="plan-header">
          <h3 class="plan-name"><?= h($plan->name) ?></h3>
          <div class="plan-price">
            <span class="plan-price-amount">€<?= number_format($plan->price, 2) ?></span>
            <span class="plan-price-period">/month</span>
          </div>
          <p class="plan-description"><?= h($plan->description) ?></p>
        </div>

        <ul class="plan-features">
          <?php foreach ($plan->features as $feature): ?>
            <li>
              <?php include __DIR__ . '/../docs/icons/check.svg'; ?>
              <?= h($feature) ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="plan-action">
          <?php if ($isCurrent): ?>
            <button class="btn-plan btn-plan--current" disabled>Current Plan</button>
          <?php elseif ($isPending): ?>
            <button class="btn-plan btn-plan--pending" disabled>Request Pending</button>
          <?php elseif ($user->type === 'member'): ?>
            <form method="post" action="/actions/action_request_plan.php">
              <?= $session->csrfField() ?>
              <input type="hidden" name="plan_id" value="<?= $plan->id ?>">
              <button type="submit" class="btn-plan">
                <?= $current ? 'Switch to ' . h($plan->name) : 'Get Started' ?>
              </button>
            </form>
          <?php endif; ?>
        </div>

      </article>
    <?php endforeach; ?>
  </div>

</section>
<?php } ?> 