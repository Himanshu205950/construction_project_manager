<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Dashboard";
$activePage = "dashboard";

// ---- KPI queries ----
$totalProjects  = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$activeProjects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status='Active'")->fetchColumn();
$totalWorkers   = $pdo->query("SELECT COUNT(*) FROM workers")->fetchColumn();

$materialSpend = $pdo->query("
    SELECT COALESCE(SUM(mu.quantity_used * m.unit_price),0)
    FROM material_usage mu
    JOIN materials m ON m.material_id = mu.material_id
")->fetchColumn();

// Average actual completion % across projects (latest entry per project)
$avgCompletion = $pdo->query("
    SELECT COALESCE(AVG(actual_percentage),0) FROM progress p
    WHERE p.progress_id IN (
        SELECT MAX(progress_id) FROM progress GROUP BY project_id
    )
")->fetchColumn();

// ---- Cost control totals (all projects combined) ----
$totalBudget = $pdo->query("SELECT COALESCE(SUM(budget),0) FROM projects")->fetchColumn();

$costByCategory = $pdo->query("
    SELECT category, COALESCE(SUM(amount),0) AS total
    FROM expenses GROUP BY category
")->fetchAll(PDO::FETCH_KEY_PAIR);

$materialCost = $costByCategory['Material'] ?? 0;
$labourCost   = $costByCategory['Labour'] ?? 0;
$equipmentCost = $costByCategory['Equipment'] ?? 0;
$transportCost = $costByCategory['Transportation'] ?? 0;
$otherCost    = $costByCategory['Other'] ?? 0;

$totalSpent = $materialCost + $labourCost + $equipmentCost + $transportCost + $otherCost;
$remainingBudget = $totalBudget - $totalSpent;

function money($v) { return '₹' . number_format($v, 0); }

// ---- Recent projects for quick table ----
$recentProjects = $pdo->query("
    SELECT * FROM projects ORDER BY created_at DESC LIMIT 5
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total Projects</div>
        <div class="kpi-value"><?= $totalProjects ?></div>
    </div>
    <div class="kpi-card success">
        <div class="kpi-label">Active Projects</div>
        <div class="kpi-value"><?= $activeProjects ?></div>
    </div>
    <div class="kpi-card info">
        <div class="kpi-label">Total Workers</div>
        <div class="kpi-value"><?= $totalWorkers ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Material Expenditure</div>
        <div class="kpi-value"><?= money($materialSpend) ?></div>
    </div>
    <div class="kpi-card success">
        <div class="kpi-label">Avg. Completion %</div>
        <div class="kpi-value"><?= number_format($avgCompletion, 1) ?>%</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2>💰 Project Cost Control (All Projects)</h2>
    </div>
    <div class="cost-flow">
        <div class="flow-item">Total Budget <span><?= money($totalBudget) ?></span></div>
        <div class="arrow">↓</div>
        <div class="flow-item">Material Cost <span><?= money($materialCost) ?></span></div>
        <div class="flow-item">Labour Cost <span><?= money($labourCost) ?></span></div>
        <div class="flow-item">Equipment Cost <span><?= money($equipmentCost) ?></span></div>
        <div class="flow-item">Transportation Cost <span><?= money($transportCost) ?></span></div>
        <div class="flow-item">Other Expenses <span><?= money($otherCost) ?></span></div>
        <div class="arrow">↓</div>
        <div class="flow-item total">Total Spent <span><?= money($totalSpent) ?></span></div>
        <div class="arrow">↓</div>
        <div class="flow-item remaining">Remaining Budget <span><?= money($remainingBudget) ?></span></div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2>Recent Projects</h2>
        <a href="projects.php" class="btn btn-sm">View All</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Project</th><th>Location</th><th>Client</th><th>Budget</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($recentProjects) === 0): ?>
            <tr><td colspan="5" class="empty-state">No projects yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentProjects as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['project_name']) ?></td>
                <td><?= htmlspecialchars($p['location']) ?></td>
                <td><?= htmlspecialchars($p['client_name']) ?></td>
                <td><?= money($p['budget']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ','',$p['status'])) ?>"><?= $p['status'] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
