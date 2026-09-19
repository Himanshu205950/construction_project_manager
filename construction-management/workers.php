<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Workers";
$activePage = "workers";

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM workers WHERE worker_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: workers.php");
    exit;
}

// Total wages = daily_wage * (Present days + 0.5*Half days)
$workers = $pdo->query("
    SELECT w.*,
        COALESCE(SUM(CASE WHEN a.status='Present' THEN 1 WHEN a.status='Half Day' THEN 0.5 ELSE 0 END), 0) AS paid_days
    FROM workers w
    LEFT JOIN attendance a ON a.worker_id = w.worker_id
    GROUP BY w.worker_id
    ORDER BY w.name
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>Worker List</h2>
        <a href="worker_form.php" class="btn">+ Add Worker</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th><th>Trade</th><th>Daily Wage</th><th>Phone</th>
                <th>Paid Days (so far)</th><th>Total Wages</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($workers) === 0): ?>
            <tr><td colspan="7" class="empty-state">No workers added yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($workers as $w):
            $totalWages = $w['daily_wage'] * $w['paid_days'];
        ?>
            <tr>
                <td><?= htmlspecialchars($w['name']) ?></td>
                <td><?= htmlspecialchars($w['trade']) ?></td>
                <td>₹<?= number_format($w['daily_wage'],2) ?></td>
                <td><?= htmlspecialchars($w['phone']) ?></td>
                <td><?= $w['paid_days'] ?></td>
                <td><b>₹<?= number_format($totalWages,2) ?></b></td>
                <td class="actions-cell">
                    <a href="worker_form.php?id=<?= $w['worker_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                    <a href="workers.php?delete=<?= $w['worker_id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this worker?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
