<?php
session_start();
require_once 'includes/db.php';

$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

if (!$isAdmin) {
    header("Location: feedback.php");
    exit;
}

// Fetch all feedback entries
$result = $conn->query("SELECT name, email, message, status, created_at FROM feedback ORDER BY created_at DESC");

if (!$result || $result->num_rows === 0) {
    die("No feedback found.");
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=feedback_export_' . date('Ymd_His') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Add column headings
fputcsv($output, ['Name', 'Email', 'Message', 'Status', 'Date']);

// Output rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['name'],
        $row['email'],
        $row['message'],
        ucfirst($row['status']),
        $row['created_at']
    ]);
}

fclose($output);
exit;
