<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

$pageTitle = "Attendance";
$activePage = "attendance";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO attendance (worker_id, project_id, date, status) VALUES (?,?,?,?)");
    $stmt->execute([
        (int)$_POST['worker_id'],
        (int)$_POST['project_id'],
        $_POST['date'],
        $_POST['status']
    ]);
    header("Location: attendance.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM attendance WHERE attendance_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: attendance.php");
    exit;
}

$workers = $pdo->query("SELECT worker_id, name FROM workers ORDER BY name")->fetchAll();
$projects = $pdo->query("SELECT project_id, project_name FROM projects ORDER BY project_name")->fetchAll();

$attendanceLog = $pdo->query("
    SELECT a.*, w.name AS worker_name, p.project_name
    FROM attendance a
    JOIN workers w ON w.worker_id = a.worker_id
    JOIN projects p ON p.project_id = a.project_id
    ORDER BY a.date DESC, a.attendance_id DESC
    LIMIT 25
")->fetchAll();

require_once 'includes/header.php';
?>

<div class="panel">
    <div class="panel-header"><h2>Mark Attendance</h2></div>
    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>Worker</label>
                <select name="worker_id" required>
                    <?php foreach ($workers as $w): ?>
                        <option value="<?= $w['worker_id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Project</label>
                <select name="project_id" required>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p['project_id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Half Day">Half Day</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Mark Attendance</button>
        </div>
    </form>
</div>

<div class="panel">
    <div class="panel-header"><h2>Recent Attendance Log</h2></div>
    <table>
        <thead><tr><th>Date</th><th>Worker</th><th>Project</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (count($attendanceLog) === 0): ?>
            <tr><td colspan="5" class="empty-state">No attendance records yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($attendanceLog as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['date']) ?></td>
                <td><?= htmlspecialchars($a['worker_name']) ?></td>
                <td><?= htmlspecialchars($a['project_name']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ','',$a['status'])) ?>"><?= $a['status'] ?></span></td>
                <td class="actions-cell">
                    <a href="attendance.php?delete=<?= $a['attendance_id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this record?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
