<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'], $_POST['status'])) {
    $recipe_id = intval($_POST['recipe_id']);
    $status = $_POST['status'];
    $admin_note = trim($_POST['admin_note'] ?? null);

    $allowed_statuses = ['draft', 'pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid status provided.";
        header("Location: recipe-management.php");
        exit;
    }

    if ($status === 'rejected') {
        $stmt = $conn->prepare("UPDATE recipes SET status = ?, admin_note = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ssi", $status, $admin_note, $recipe_id);
    } else {
        $stmt = $conn->prepare("UPDATE recipes SET status = ?, admin_note = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $status, $recipe_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipe status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update status. Please try again.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: recipe-management.php");
exit;
