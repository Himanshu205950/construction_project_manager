<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$editMode = isset($_GET['id']);
$worker = ['worker_id'=>'','name'=>'','trade'=>'','daily_wage'=>'','phone'=>''];

if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM workers WHERE worker_id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    if ($row) $worker = $row;
}

$pageTitle = $editMode ? "Edit Worker" : "Add Worker";
$activePage = "workers";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $trade = trim($_POST['trade']);
    $wage = (float)$_POST['daily_wage'];
    $phone = trim($_POST['phone']);

    if ($editMode) {
        $stmt = $pdo->prepare("UPDATE workers SET name=?, trade=?, daily_wage=?, phone=? WHERE worker_id=?");
        $stmt->execute([$name, $trade, $wage, $phone, (int)$_POST['worker_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO workers (name, trade, daily_wage, phone) VALUES (?,?,?,?)");
        $stmt->execute([$name, $trade, $wage, $phone]);
    }
    header("Location: workers.php");
    exit;
}

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header"><h2><?= $editMode ? "Edit Worker" : "Add New Worker" ?></h2></div>
    <form method="POST">
        <input type="hidden" name="worker_id" value="<?= htmlspecialchars($worker['worker_id']) ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($worker['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Trade / Skill</label>
                <input type="text" name="trade" value="<?= htmlspecialchars($worker['trade']) ?>" placeholder="e.g. Mason, Carpenter, Electrician">
            </div>
            <div class="form-group">
                <label>Daily Wage (₹)</label>
                <input type="number" step="0.01" name="daily_wage" value="<?= htmlspecialchars($worker['daily_wage']) ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($worker['phone']) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn"><?= $editMode ? "Update Worker" : "Save Worker" ?></button>
            <a href="workers.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
