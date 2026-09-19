<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$editMode = isset($_GET['id']);
$material = ['material_id'=>'','material_name'=>'','unit'=>'','quantity'=>'','unit_price'=>'','supplier'=>''];

if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM materials WHERE material_id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    if ($row) $material = $row;
}

$pageTitle = $editMode ? "Edit Material" : "Add Material";
$activePage = "materials";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['material_name']);
    $unit = trim($_POST['unit']);
    $qty  = (float)$_POST['quantity'];
    $price = (float)$_POST['unit_price'];
    $supplier = trim($_POST['supplier']);

    if ($editMode) {
        $stmt = $pdo->prepare("UPDATE materials SET material_name=?, unit=?, quantity=?, unit_price=?, supplier=? WHERE material_id=?");
        $stmt->execute([$name, $unit, $qty, $price, $supplier, (int)$_POST['material_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO materials (material_name, unit, quantity, unit_price, supplier) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $unit, $qty, $price, $supplier]);
    }
    header("Location: materials.php");
    exit;
}

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header"><h2><?= $editMode ? "Edit Material" : "Add New Material" ?></h2></div>
    <form method="POST">
        <input type="hidden" name="material_id" value="<?= htmlspecialchars($material['material_id']) ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Material Name</label>
                <input type="text" name="material_name" value="<?= htmlspecialchars($material['material_name']) ?>" placeholder="e.g. Cement, Steel, Sand" required>
            </div>
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" value="<?= htmlspecialchars($material['unit']) ?>" placeholder="e.g. bag, ton, cft, nos" required>
            </div>
            <div class="form-group">
                <label>Current Stock (Quantity)</label>
                <input type="number" step="0.01" name="quantity" value="<?= htmlspecialchars($material['quantity']) ?>" required>
            </div>
            <div class="form-group">
                <label>Unit Price (₹)</label>
                <input type="number" step="0.01" name="unit_price" value="<?= htmlspecialchars($material['unit_price']) ?>">
            </div>
            <div class="form-group">
                <label>Supplier</label>
                <input type="text" name="supplier" value="<?= htmlspecialchars($material['supplier']) ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn"><?= $editMode ? "Update Material" : "Save Material" ?></button>
            <a href="materials.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
