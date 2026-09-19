<?php
// Expects $pageTitle and $activePage to be set by the including page
if (!isset($pageTitle)) $pageTitle = "Construction Management System";
if (!isset($activePage)) $activePage = "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | Construction Management System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-shell">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">🏗️</span>
            <span class="brand-text">BuildTrack</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?= $activePage=='dashboard'?'active':'' ?>">📊 Dashboard</a>
            <a href="projects.php" class="<?= $activePage=='projects'?'active':'' ?>">🏢 Projects</a>
            <a href="materials.php" class="<?= $activePage=='materials'?'active':'' ?>">🧱 Materials</a>
            <a href="workers.php" class="<?= $activePage=='workers'?'active':'' ?>">👷 Workers</a>
            <a href="attendance.php" class="<?= $activePage=='attendance'?'active':'' ?>">🗓️ Attendance</a>
            <a href="expenses.php" class="<?= $activePage=='expenses'?'active':'' ?>">💰 Expenses</a>
            <a href="progress.php" class="<?= $activePage=='progress'?'active':'' ?>">📈 Progress</a>
            <a href="reports.php" class="<?= $activePage=='reports'?'active':'' ?>">📄 Reports</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link">🚪 Logout</a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <header class="topbar">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <div class="topbar-user">
                👤 <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>
            </div>
        </header>

        <div class="page-body">
