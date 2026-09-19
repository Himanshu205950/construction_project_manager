<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Expenses";
$activePage = "expenses";

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE expense_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: expenses.php");
    exit;
}

$projects = $pdo->query("SELECT project_id, project_name FROM projects ORDER BY project_name")->fetchAll();

$expenses = $pdo->query("
    SELECT e.*, p.project_name
    FROM expenses e
    JOIN projects p ON p.project_id = e.project_id
    ORDER BY e.expense_date DESC
")->fetchAll();

$totalsByCategory = $pdo->query("
    SELECT category, SUM(amount) AS total FROM expenses GROUP BY category
")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once 'includes/header.php';
?>

<div class="kpi-grid">
    <?php foreach (['Material','Labour','Equipment','Transportation','Other'] as $cat): ?>
        <div class="kpi-card">
            <div class="kpi-label"><?= $cat ?></div>
            <div class="kpi-value">₹<?= number_format($totalsByCategory[$cat] ?? 0, 0) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="panel">
    <div class="panel-header"><h2>Add Expense</h2></div>
    <form method="POST" action="expense_save.php">
        <div class="form-grid">
            <div class="form-group">
                <label>Project</label>
                <select name="project_id" required>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p['project_id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <?php foreach (['Material','Labour','Equipment','Transportation','Other'] as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Amount (₹)</label>
                <input type="number" step="0.01" name="amount" required>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="Optional note">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Save Expense</button>
        </div>
    </form>
</div>

<div class="panel">
    <div class="panel-header"><h2>All Expenses</h2></div>
    <table>
        <thead><tr><th>Date</th><th>Project</th><th>Category</th><th>Amount</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (count($expenses) === 0): ?>
            <tr><td colspan="6" class="empty-state">No expenses recorded yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($expenses as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['expense_date']) ?></td>
                <td><?= htmlspecialchars($e['project_name']) ?></td>
                <td><?= htmlspecialchars($e['category']) ?></td>
                <td>₹<?= number_format($e['amount'],2) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
                <td class="actions-cell">
                    <a href="expenses.php?delete=<?= $e['expense_id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this expense?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
