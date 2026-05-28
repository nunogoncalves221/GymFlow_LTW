<?php
declare(strict_types=1);
?>

<?php function drawMembershipPage(User $user, array $plans, ?MembershipPlan $current, ?array $pending, Session $session, int $weeklyUsed = 0) { ?>
<section class="membership-page">

  <a href="#" onclick="history.back();return false;" class="back-link"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg></a>

  <div class="membership-header">
    <h2>Membership Plans</h2>
    <p class="membership-subtitle">Choose the plan that fits your goals. Request a plan and an admin will activate it for you.</p>
  </div>

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
              <svg class="feature-check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l6.879-6.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
              </svg>
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