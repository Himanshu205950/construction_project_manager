<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Progress Tracking";
$activePage = "progress";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO progress (project_id, date, planned_percentage, actual_percentage, remarks) VALUES (?,?,?,?,?)");
    $stmt->execute([
        (int)$_POST['project_id'],
        $_POST['date'],
        (float)$_POST['planned_percentage'],
        (float)$_POST['actual_percentage'],
        trim($_POST['remarks'])
    ]);
    header("Location: progress.php?project_id=" . (int)$_POST['project_id']);
    exit;
}

$projects = $pdo->query("SELECT project_id, project_name FROM projects ORDER BY project_name")->fetchAll();
$selectedProject = isset($_GET['project_id']) ? (int)$_GET['project_id'] : ($projects[0]['project_id'] ?? 0);

$progressLog = [];
if ($selectedProject) {
    $stmt = $pdo->prepare("SELECT * FROM progress WHERE project_id = ? ORDER BY date DESC");
    $stmt->execute([$selectedProject]);
    $progressLog = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header"><h2>Select Project</h2></div>
    <form method="GET" class="form-grid" style="max-width:320px;">
        <div class="form-group">
            <label>Project</label>
            <select name="project_id" onchange="this.form.submit()">
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['project_id'] ?>" <?= $p['project_id']==$selectedProject?'selected':'' ?>>
                        <?= htmlspecialchars($p['project_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="panel">
    <div class="panel-header"><h2>Add Progress Entry</h2></div>
    <form method="POST">
        <input type="hidden" name="project_id" value="<?= $selectedProject ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Planned Completion %</label>
                <input type="number" step="0.01" max="100" min="0" name="planned_percentage" required>
            </div>
            <div class="form-group">
                <label>Actual Completion %</label>
                <input type="number" step="0.01" max="100" min="0" name="actual_percentage" required>
            </div>
            <div class="form-group">
                <label>Remarks</label>
                <input type="text" name="remarks" placeholder="Optional note">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Add Progress Entry</button>
        </div>
    </form>
</div>

<div class="panel">
    <div class="panel-header"><h2>Progress History</h2></div>

    <?php if (count($progressLog) === 0): ?>
        <div class="empty-state">No progress entries logged for this project yet.</div>
    <?php endif; ?>

    <?php foreach ($progressLog as $log):
        $actual = (float)$log['actual_percentage'];
        $planned = (float)$log['planned_percentage'];
        $barClass = $actual >= $planned ? 'on-track' : 'behind';
    ?>
        <div style="margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:6px;">
                <span><b><?= htmlspecialchars($log['date']) ?></b> — <?= htmlspecialchars($log['remarks']) ?></span>
                <span>Planned: <?= $planned ?>% | Actual: <b><?= $actual ?>%</b></span>
            </div>
            <div class="progress-track">
                <div class="progress-fill <?= $barClass ?>" style="width: <?= $actual ?>%;"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
