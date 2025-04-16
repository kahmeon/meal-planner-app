<?php
session_start();
require_once '../includes/db.php';

// Validate admin access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "⚠️ Invalid request method";
    header("Location: add-competition.php");
    exit();
}

// Validate required fields
$required = ['title', 'description', 'start_date', 'end_date'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "⚠️ Please fill in all required fields";
        $_SESSION['form_data'] = $_POST;
        header("Location: add-competition.php");
        exit();
    }
}

// Sanitize inputs
$title = htmlspecialchars(trim($_POST['title']));
$description = htmlspecialchars(trim($_POST['description']));
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$prize = htmlspecialchars(trim($_POST['prize'] ?? ''));

// Validate dates
$today = date('Y-m-d');
if ($start_date < $today) {
    $_SESSION['error'] = "⚠️ Start date cannot be in the past";
    $_SESSION['form_data'] = $_POST;
    header("Location: add-competition.php");
    exit();
}

if ($end_date <= $start_date) {
    $_SESSION['error'] = "⚠️ End date must be after start date";
    $_SESSION['form_data'] = $_POST;
    header("Location: add-competition.php");
    exit();
}

// Image upload handling
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $uploadDir = '../uploads/competitions/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $_SESSION['error'] = "❌ Failed to create upload directory";
        header("Location: add-competition.php");
        exit();
    }

    // Validate image
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
    finfo_close($fileInfo);

    if (!array_key_exists($fileType, $allowedTypes)) {
        $_SESSION['error'] = "❌ Only JPEG and PNG images are allowed";
        header("Location: add-competition.php");
        exit();
    }

    // Validate file size (5MB max)
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $_SESSION['error'] = "❌ Image must be less than 5MB";
        header("Location: add-competition.php");
        exit();
    }

    // Generate secure filename
    $extension = $allowedTypes[$fileType];
    $filename = bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $_SESSION['error'] = "❌ Failed to upload image";
        error_log("File upload error: " . print_r($_FILES['image'], true)); // Log the file upload error
        header("Location: add-competition.php");
        exit();
    }
    

    $imagePath = 'uploads/competitions/' . $filename;
} else {
    // Use default image if none uploaded
    $imagePath = 'uploads/competitions/default.png';
}

// Database operation with error handling
try {
    $conn->begin_transaction();
    
    $stmt = $conn->prepare("INSERT INTO competitions 
                          (title, description, start_date, end_date, prize, image_url) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $start_date, $end_date, $prize, $imagePath);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $conn->commit();
    $_SESSION['success'] = "✅ Competition added successfully!";
    header("Location:competition-list.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    
    // Clean up uploaded file if database operation failed
    if ($imagePath && $imagePath !== 'uploads/competitions/default.png') {
        @unlink('../' . $imagePath);
    }
    
    if (!$stmt->execute()) {
        error_log("Database error: " . $stmt->error);  // Log the error
        $_SESSION['error'] = "❌ Database error: " . $stmt->error;
        $_SESSION['form_data'] = $_POST;
        header("Location: add-competition.php");
        exit();
    }
    
}