<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'user';

// 🧾 Collect submitted data
$recipe_id   = (int) $_POST['recipe_id'];
$title       = trim($_POST['title']);
$description = trim($_POST['description'] ?? '');
$cuisine     = trim($_POST['cuisine']);
$prep_time   = (int) $_POST['prep_time'];
$cook_time   = (int) $_POST['cook_time'];
$total_time  = (int) $_POST['total_time'];
$difficulty  = $_POST['difficulty'] ?? '';
$status      = $_POST['status'] ?? 'pending';
$nutrition   = trim($_POST['nutrition'] ?? '');
$tags        = $_POST['tags'] ?? [];
$delete_images = $_POST['delete_images'] ?? [];

// ✅ Convert multi-line input to JSON array
function convertToJsonList($text) {
    $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text)));
    return json_encode($lines, JSON_UNESCAPED_UNICODE);
}

$ingredients = convertToJsonList($_POST['ingredients'] ?? '');
$steps       = convertToJsonList($_POST['steps'] ?? '');

// 🔒 Access control
$check = $conn->prepare("SELECT created_by FROM recipes WHERE id = ?");
$check->bind_param("i", $recipe_id);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    $_SESSION['error'] = "Recipe not found.";
    header("Location: recipe-management.php");
    exit;
}
$row = $res->fetch_assoc();
if ($role !== 'admin' && $row['created_by'] != $user_id) {
    $_SESSION['error'] = "Unauthorized update.";
    header("Location: recipe-management.php");
    exit;
}

// 🛠 Update main recipe info
$update = $conn->prepare("
  UPDATE recipes 
  SET title = ?, description = ?, cuisine = ?, prep_time = ?, cook_time = ?, total_time = ?, 
      difficulty = ?, status = ?, nutrition = ?, ingredients = ?, steps = ? 
  WHERE id = ?
");

// 💡 Fixing bind_param types — all strings are "s", integers are "i"
$update->bind_param(
    "ssssiiissssi", 
    $title,
    $description,
    $cuisine,
    $prep_time,
    $cook_time,
    $total_time,
    $difficulty,
    $status,
    $nutrition,
    $ingredients,
    $steps,
    $recipe_id
);

$update->execute();
$update->close();

// 🔄 Update tags
$conn->query("DELETE FROM recipe_tags WHERE recipe_id = $recipe_id");
if (!empty($tags)) {
    $tagInsert = $conn->prepare("INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (?, ?)");
    foreach ($tags as $tag_id) {
        $tagInsert->bind_param("ii", $recipe_id, $tag_id);
        $tagInsert->execute();
    }
    $tagInsert->close();
}

// ❌ Delete selected images
if (!empty($delete_images)) {
    foreach ($delete_images as $img_id) {
        $imgQuery = $conn->prepare("SELECT image_url FROM recipe_images WHERE id = ? AND recipe_id = ?");
        $imgQuery->bind_param("ii", $img_id, $recipe_id);
        $imgQuery->execute();
        $result = $imgQuery->get_result();
        if ($result->num_rows > 0) {
            $img = $result->fetch_assoc();
            if (file_exists($img['image_url'])) {
                unlink($img['image_url']);
            }
        }
        $conn->query("DELETE FROM recipe_images WHERE id = $img_id AND recipe_id = $recipe_id");
    }
}

// 📥 Upload new images
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        $imageName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", basename($_FILES['images']['name'][$index]));
        $targetPath = $uploadDir . time() . "_" . $imageName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $imgStmt = $conn->prepare("INSERT INTO recipe_images (recipe_id, image_url) VALUES (?, ?)");
            $imgStmt->bind_param("is", $recipe_id, $targetPath);
            $imgStmt->execute();
            $imgStmt->close();
        }
    }
}

$_SESSION['success'] = "✅ Recipe updated successfully!";
header("Location: recipe-management.php");
exit;
