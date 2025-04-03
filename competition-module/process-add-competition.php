<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $prize = trim($_POST['prize']);

    // Image Upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/competitions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['image']['name']));
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        } else {
            $_SESSION['error'] = "Image upload failed.";
            header("Location: add-competition.php");
            exit();
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO competitions (title, description, start_date, end_date, prize, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $start_date, $end_date, $prize, $imagePath);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Competition added successfully!";
    } else {
        $_SESSION['error'] = "❌ Failed to add competition.";
    }
    $stmt->close();

    header("Location: competition.php");
    exit();
} else {
    header("Location: add-competition.php");
    exit();
} 
