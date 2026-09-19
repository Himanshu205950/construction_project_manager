<?php
require_once 'includes/auth_check.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = (int)$_POST['project_id'];
    $materialId = (int)$_POST['material_id'];
    $qtyUsed = (float)$_POST['quantity_used'];
    $date = $_POST['usage_date'];

    $pdo->beginTransaction();
    try {
        // Insert usage record
        $stmt = $pdo->prepare("INSERT INTO material_usage (project_id, material_id, quantity_used, usage_date) VALUES (?,?,?,?)");
        $stmt->execute([$projectId, $materialId, $qtyUsed, $date]);

        // Deduct from current stock
        $stmt = $pdo->prepare("UPDATE materials SET quantity = quantity - ? WHERE material_id = ?");
        $stmt->execute([$qtyUsed, $materialId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

header("Location: materials.php");
exit;
