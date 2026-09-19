<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO expenses (project_id, category, amount, expense_date, description) VALUES (?,?,?,?,?)");
    $stmt->execute([
        (int)$_POST['project_id'],
        $_POST['category'],
        (float)$_POST['amount'],
        $_POST['expense_date'],
        trim($_POST['description'])
    ]);
}
header("Location: expenses.php");
exit;
