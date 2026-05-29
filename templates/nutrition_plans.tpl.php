<?php
declare(strict_types=1);

/* ─── TRAINER VIEW ─────────────────────────────────────────────────────── */
function drawTrainerNutritionPlans(
    Trainer $trainer, array $plans, array $members, Session $session
) { ?>
  <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>
  <section class="nutrition-section">

    <header class="dashboard-header">
      <div>
        <h1>Nutrition Plans</h1>
        <p class="dashboard-sub">Create and assign nutrition plans to your members</p>
      </div>
      <button class="btn btn-primary" id="btn-new-plan">+ New Plan</button>
    </header>

    <!-- Create Plan Modal -->
    <div id="plan-modal" class="modal" hidden>
      <div class="modal-box">
        <h2>Create Nutrition Plan</h2>
        <form method="post" action="/actions/action_create_nutrition_plan.php">
          <?= $session->csrfField() ?>

          <label>
            Assign to Member
            <select name="member_id" required>
              <option value="">— select a member —</option>
              <?php foreach ($members as $m): ?>
                <option value="<?= h((string)$m->id) ?>"><?= h($m->name) ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Plan Title
            <input type="text" name="title" placeholder="e.g. Weight Loss Plan" required maxlength="100">
          </label>

          <label>
            Description
            <textarea name="description" rows="3" placeholder="Brief overview of the plan…"></textarea>
          </label>

          <label>
            Daily Calorie Target
            <input type="number" name="calories" min="500" max="10000" placeholder="e.g. 2000">
          </label>

          <hr>
          <h3>Meals</h3>
          <p class="hint">Add at least one meal. More can be added after saving.</p>

          <div id="meals-container">
            <?php for ($i = 0; $i < 3; $i++): ?>
              <div class="meal-row">
                <span class="meal-index"><?= $i + 1 ?></span>
                <input type="text"   name="meals[<?= $i ?>][name]"        placeholder="Meal name"     maxlength="80">
                <input type="text"   name="meals[<?= $i ?>][time_of_day]" placeholder="e.g. 08:00"    maxlength="20">
                <input type="number" name="meals[<?= $i ?>][calories]"    placeholder="kcal" min="0" max="5000">
                <input type="text"   name="meals[<?= $i ?>][notes]"       placeholder="Notes (optional)" maxlength="200">
              </div>
            <?php endfor; ?>
          </div>

          <button type="button" class="btn btn-secondary" id="btn-add-meal">+ Add Meal Row</button>

          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" id="btn-cancel-plan">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Plan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Plans List -->
    <?php if (empty($plans)): ?>
      <p class="no-results">You haven't created any nutrition plans yet.</p>
    <?php else: ?>
      <div class="plans-grid">
        <?php foreach ($plans as $plan): ?>
          <article class="plan-card" data-plan-id="<?= h((string)$plan->id) ?>">
            <header class="plan-card-header">
              <div>
                <h3><?= h($plan->title) ?></h3>
                <span class="plan-member"><?= h($plan->member_name ?? 'Unknown member') ?></span>
              </div>
              <?php if ($plan->calories): ?>
                <span class="plan-calories"><?= h((string)$plan->calories) ?> kcal/day</span>
              <?php endif; ?>
            </header>

            <?php if ($plan->description): ?>
              <p class="plan-desc"><?= h($plan->description) ?></p>
            <?php endif; ?>

            <button class="btn btn-secondary btn-sm btn-meals-toggle" data-plan="<?= h((string)$plan->id) ?>">
              View Meals
            </button>

            <div class="meals-list" id="meals-<?= h((string)$plan->id) ?>" hidden>
              <p class="meals-loading">Loading…</p>
            </div>

            <footer class="plan-card-footer">
              <span class="plan-date">Created <?= h(substr($plan->created_at, 0, 10)) ?></span>
              <form method="post" action="/actions/action_delete_nutrition_plan.php"
                    data-confirm="Delete this plan?">
                <?= $session->csrfField() ?>
                <input type="hidden" name="plan_id" value="<?= h((string)$plan->id) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </footer>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
<?php } ?>

<?php
/* ─── MEMBER VIEW ───────────────────────────────────────────────────────── */
function drawMemberNutritionPlans(array $plans, Session $session, PDO $db) { ?>
  <a href="#" class="back-link"><?php include __DIR__ . '/../docs/icons/arrow-left.svg'; ?></a>
  <section class="nutrition-section">

    <header class="dashboard-header">
      <div>
        <h1>My Nutrition Plans</h1>
        <p class="dashboard-sub">Nutrition plans assigned to you by your trainers</p>
      </div>
    </header>

    <?php if (empty($plans)): ?>
      <p class="no-results">No nutrition plans have been assigned to you yet.</p>
    <?php else: ?>
      <div class="plans-grid">
        <?php foreach ($plans as $plan): ?>
          <?php $meals = $plan->getMeals($db); ?>
          <article class="plan-card">
            <header class="plan-card-header">
              <div>
                <h3><?= h($plan->title) ?></h3>
                <span class="plan-member">By <?= h($plan->member_name ?? 'your trainer') ?></span>
              </div>
              <?php if ($plan->calories): ?>
                <span class="plan-calories"><?= h((string)$plan->calories) ?> kcal/day</span>
              <?php endif; ?>
            </header>

            <?php if ($plan->description): ?>
              <p class="plan-desc"><?= h($plan->description) ?></p>
            <?php endif; ?>

            <?php if (!empty($meals)): ?>
              <table class="meals-table">
                <thead>
                  <tr>
                    <th>Meal</th>
                    <th>Time</th>
                    <th>kcal</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($meals as $meal): ?>
                    <tr>
                      <td><?= h($meal->name) ?></td>
                      <td><?= h($meal->time_of_day) ?></td>
                      <td><?= $meal->calories > 0 ? h((string)$meal->calories) : '—' ?></td>
                      <td><?= h($meal->notes) ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <tr class="meals-total-row">
                    <td colspan="2"><strong>Total</strong></td>
                    <td><strong><?= array_sum(array_column($meals, 'calories')) ?></strong></td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            <?php else: ?>
              <p class="hint">No meals added to this plan yet.</p>
            <?php endif; ?>

            <footer class="plan-card-footer">
              <span class="plan-date">Created <?= h(substr($plan->created_at, 0, 10)) ?></span>
            </footer>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
<?php } ?>
