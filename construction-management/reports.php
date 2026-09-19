<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Reports";
$activePage = "reports";

function money($v) { return '₹' . number_format($v, 0); }

$projects = $pdo->query("SELECT * FROM projects ORDER BY project_name")->fetchAll();

$reportRows = [];
foreach ($projects as $p) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE project_id = ?");
    $stmt->execute([$p['project_id']]);
    $spent = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT actual_percentage FROM progress WHERE project_id = ? ORDER BY date DESC LIMIT 1");
    $stmt->execute([$p['project_id']]);
    $completion = (float)($stmt->fetchColumn() ?: 0);

    $reportRows[] = [
        'name' => $p['project_name'],
        'budget' => $p['budget'],
        'spent' => $spent,
        'remaining' => $p['budget'] - $spent,
        'completion' => $completion,
        'status' => $p['status'],
    ];
}

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>Project Summary Report</h2>
        <button class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Project</th><th>Status</th><th>Budget</th><th>Spent</th><th>Remaining</th><th>Completion %</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($reportRows) === 0): ?>
            <tr><td colspan="6" class="empty-state">No project data available.</td></tr>
        <?php endif; ?>
        <?php foreach ($reportRows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ','',$r['status'])) ?>"><?= $r['status'] ?></span></td>
                <td><?= money($r['budget']) ?></td>
                <td><?= money($r['spent']) ?></td>
                <td style="color:<?= $r['remaining']<0?'#dc2626':'#16a34a' ?>;"><b><?= money($r['remaining']) ?></b></td>
                <td><?= number_format($r['completion'],1) ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-header"><h2>Expense Breakdown by Category (All Projects)</h2></div>
    <?php
    $byCat = $pdo->query("SELECT category, SUM(amount) AS total FROM expenses GROUP BY category ORDER BY total DESC")->fetchAll();
    $grandTotal = array_sum(array_column($byCat, 'total'));
    ?>
    <table>
        <thead><tr><th>Category</th><th>Amount</th><th>% of Total</th></tr></thead>
        <tbody>
        <?php foreach ($byCat as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['category']) ?></td>
                <td><?= money($c['total']) ?></td>
                <td><?= $grandTotal > 0 ? number_format(($c['total']/$grandTotal)*100, 1) : 0 ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
