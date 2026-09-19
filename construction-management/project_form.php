<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$editMode = isset($_GET['id']);
$project = [
    'project_id' => '', 'project_name' => '', 'location' => '', 'client_name' => '',
    'budget' => '', 'start_date' => '', 'end_date' => '', 'status' => 'Planned'
];

if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    if ($row) $project = $row;
}

$pageTitle = $editMode ? "Edit Project" : "Add Project";
$activePage = "projects";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['project_name']);
    $loc     = trim($_POST['location']);
    $client  = trim($_POST['client_name']);
    $budget  = (float)$_POST['budget'];
    $start   = $_POST['start_date'];
    $end     = $_POST['end_date'];
    $status  = $_POST['status'];

    if ($editMode) {
        $stmt = $pdo->prepare("UPDATE projects SET project_name=?, location=?, client_name=?, budget=?, start_date=?, end_date=?, status=? WHERE project_id=?");
        $stmt->execute([$name, $loc, $client, $budget, $start, $end, $status, (int)$_POST['project_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO projects (project_name, location, client_name, budget, start_date, end_date, status) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$name, $loc, $client, $budget, $start, $end, $status]);
    }
    header("Location: projects.php");
    exit;
}

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2><?= $editMode ? "Edit Project" : "Add New Project" ?></h2>
    </div>

    <form method="POST">
        <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['project_id']) ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Project Name</label>
                <input type="text" name="project_name" value="<?= htmlspecialchars($project['project_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($project['location']) ?>">
            </div>
            <div class="form-group">
                <label>Client Name</label>
                <input type="text" name="client_name" value="<?= htmlspecialchars($project['client_name']) ?>">
            </div>
            <div class="form-group">
                <label>Budget (₹)</label>
                <input type="number" step="0.01" name="budget" value="<?= htmlspecialchars($project['budget']) ?>">
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($project['start_date']) ?>">
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($project['end_date']) ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['Planned','Active','On Hold','Completed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $project['status']===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn"><?= $editMode ? "Update Project" : "Save Project" ?></button>
            <a href="projects.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
