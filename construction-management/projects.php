<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Projects";
$activePage = "projects";

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE project_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: projects.php");
    exit;
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();

function money($v) { return '₹' . number_format($v, 0); }

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2>All Projects</h2>
        <a href="project_form.php" class="btn">+ Add Project</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Location</th>
                <th>Client</th>
                <th>Budget</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($projects) === 0): ?>
            <tr><td colspan="8" class="empty-state">No projects added yet. Click "+ Add Project" to create one.</td></tr>
        <?php endif; ?>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['project_name']) ?></td>
                <td><?= htmlspecialchars($p['location']) ?></td>
                <td><?= htmlspecialchars($p['client_name']) ?></td>
                <td><?= money($p['budget']) ?></td>
                <td><?= htmlspecialchars($p['start_date']) ?></td>
                <td><?= htmlspecialchars($p['end_date']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ','',$p['status'])) ?>"><?= $p['status'] ?></span></td>
                <td class="actions-cell">
                    <a href="project_form.php?id=<?= $p['project_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                    <a href="progress.php?project_id=<?= $p['project_id'] ?>" class="btn btn-sm">Progress</a>
                    <a href="projects.php?delete=<?= $p['project_id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this project? This will remove related records too.');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
