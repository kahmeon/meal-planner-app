<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'user';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid recipe ID.";
    header("Location: recipe-management.php");
    exit;
}

$recipe_id = (int)$_GET['id'];

// 🔐 Check ownership or admin
$check = $conn->prepare("SELECT created_by FROM recipes WHERE id = ?");
$check->bind_param("i", $recipe_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    $_SESSION['error'] = "Recipe not found.";
    header("Location: recipe-management.php");
    exit;
}

$owner = $res->fetch_assoc()['created_by'];
if ($role !== 'admin' && $owner != $user_id) {
    $_SESSION['error'] = "Unauthorized.";
    header("Location: recipe-management.php");
    exit;
}

// 🖼 Delete image files
$imageQuery = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ?");
$imageQuery->bind_param("i", $recipe_id);
$imageQuery->execute();
$imageResult = $imageQuery->get_result();
while ($img = $imageResult->fetch_assoc()) {
    if (file_exists($img['image_url'])) {
        unlink($img['image_url']);
    }
}

// 🧹 Delete associated data
$conn->query("DELETE FROM recipe_images WHERE recipe_id = $recipe_id");
$conn->query("DELETE FROM recipe_tags WHERE recipe_id = $recipe_id");
$conn->query("DELETE FROM recipes WHERE id = $recipe_id");

$_SESSION['success'] = "🗑️ Recipe deleted successfully.";
header("Location: recipe-management.php");
exit;
