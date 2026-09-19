<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Materials";
$activePage = "materials";

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM materials WHERE material_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: materials.php");
    exit;
}

// Total quantity used per material (from material_usage)
$materials = $pdo->query("
    SELECT m.*,
        COALESCE((SELECT SUM(mu.quantity_used) FROM material_usage mu WHERE mu.material_id = m.material_id), 0) AS used_qty
    FROM materials m
    ORDER BY m.material_name
")->fetchAll();

// Recent usage log (joined with project + material names)
$usageLog = $pdo->query("
    SELECT mu.*, p.project_name, m.material_name, m.unit
    FROM material_usage mu
    JOIN projects p ON p.project_id = mu.project_id
    JOIN materials m ON m.material_id = mu.material_id
    ORDER BY mu.usage_date DESC
    LIMIT 15
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>Material Inventory</h2>
        <a href="material_form.php" class="btn">+ Add Material</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Material</th><th>Unit</th><th>Received (Stock+Used)</th>
                <th>Used</th><th>Current Stock</th><th>Unit Price</th><th>Supplier</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($materials) === 0): ?>
            <tr><td colspan="8" class="empty-state">No materials added yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($materials as $m):
            $received = $m['quantity'] + $m['used_qty']; // quantity = current stock
        ?>
            <tr>
                <td><?= htmlspecialchars($m['material_name']) ?></td>
                <td><?= htmlspecialchars($m['unit']) ?></td>
                <td><?= number_format($received, 2) ?></td>
                <td><?= number_format($m['used_qty'], 2) ?></td>
                <td><b><?= number_format($m['quantity'], 2) ?></b></td>
                <td>₹<?= number_format($m['unit_price'], 2) ?></td>
                <td><?= htmlspecialchars($m['supplier']) ?></td>
                <td class="actions-cell">
                    <a href="material_form.php?id=<?= $m['material_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                    <a href="materials.php?delete=<?= $m['material_id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this material?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-header">
        <h2>Log Material Usage</h2>
    </div>
    <form method="POST" action="material_usage_save.php">
        <div class="form-grid">
            <div class="form-group">
                <label>Project</label>
                <select name="project_id" required>
                    <?php foreach ($pdo->query("SELECT project_id, project_name FROM projects ORDER BY project_name") as $p): ?>
                        <option value="<?= $p['project_id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Material</label>
                <select name="material_id" required>
                    <?php foreach ($materials as $m): ?>
                        <option value="<?= $m['material_id'] ?>"><?= htmlspecialchars($m['material_name']) ?> (<?= $m['unit'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity Used</label>
                <input type="number" step="0.01" name="quantity_used" required>
            </div>
            <div class="form-group">
                <label>Usage Date</label>
                <input type="date" name="usage_date" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Log Usage</button>
        </div>
    </form>
</div>

<div class="panel">
    <div class="panel-header"><h2>Recent Usage Log</h2></div>
    <table>
        <thead><tr><th>Date</th><th>Project</th><th>Material</th><th>Quantity Used</th></tr></thead>
        <tbody>
        <?php if (count($usageLog) === 0): ?>
            <tr><td colspan="4" class="empty-state">No usage logged yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($usageLog as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['usage_date']) ?></td>
                <td><?= htmlspecialchars($u['project_name']) ?></td>
                <td><?= htmlspecialchars($u['material_name']) ?></td>
                <td><?= number_format($u['quantity_used'],2) ?> <?= htmlspecialchars($u['unit']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
