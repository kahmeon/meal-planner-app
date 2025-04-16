<?php
include '../includes/db.php';
include '../includes/auth.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if `id` and `action` are provided
if (!isset($_GET['id'], $_GET['action']) || !in_array($_GET['action'], ['approve', 'reject'])) {
    echo "Invalid request.";
    exit();
}

// Get entry ID and action
$entry_id = $_GET['id'];
$action = $_GET['action'];

// Validate that the entry exists
$query = "SELECT * FROM competition_entries WHERE entry_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $entry_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Entry not found.";
    exit();
}

// Update status in the database
$status = ($action === 'approve') ? 'approved' : 'rejected';
$update_query = "UPDATE competition_entries SET status = ? WHERE entry_id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param('si', $status, $entry_id);
if ($update_stmt->execute()) {
    $_SESSION['success'] = "Entry successfully " . ucfirst($status) . ".";
} else {
    $_SESSION['error'] = "Failed to update status.";
}

// Redirect back to entries page
header("Location: view-entries.php");
exit();
?>
