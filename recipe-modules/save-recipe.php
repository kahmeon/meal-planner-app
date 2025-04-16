<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Sanitize and collect form inputs
$title        = $_POST['title'] ?? '';
$description  = $_POST['description'] ?? '';
$cuisine      = $_POST['cuisine'] ?? '';
$prep_time    = (int) ($_POST['prep_time'] ?? 0);
$cook_time    = (int) ($_POST['cook_time'] ?? 0);
$total_time   = (int) ($_POST['total_time'] ?? 0);
$difficulty   = $_POST['difficulty'] ?? '';
$status       = $_POST['status'] ?? 'draft';
$ingredients  = json_encode(array_filter(array_map('trim', explode("\n", $_POST['ingredients'] ?? ''))));
$steps        = json_encode(array_filter(array_map('trim', explode("\n", $_POST['steps'] ?? ''))));
$nutrition    = $_POST['nutrition'] ?? '';
$is_public    = isset($_POST['is_public']) ? 1 : 0;
$tags         = $_POST['tags'] ?? [];

// Validate prepare success
$stmt = $conn->prepare("INSERT INTO recipes (
    title, description, cuisine, prep_time, cook_time, total_time,
    difficulty, status, ingredients, steps, nutrition, is_public, created_by, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param(
    "sssiiisssssii",
    $title,
    $description,
    $cuisine,
    $prep_time,
    $cook_time,
    $total_time,
    $difficulty,
    $status,
    $ingredients,
    $steps,
    $nutrition,
    $is_public,
    $user_id
);

if ($stmt->execute()) {
    $recipe_id = $stmt->insert_id;

    // Insert tags
    if (!empty($tags)) {
        $tagStmt = $conn->prepare("INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (?, ?)");
        foreach ($tags as $tag_id) {
            $tagStmt->bind_param("ii", $recipe_id, $tag_id);
            $tagStmt->execute();
        }
        $tagStmt->close();
    }

    // Handle image uploads
    $uploadDir = '../uploads/recipes/';
    $webPathPrefix = 'uploads/recipes/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $cleanName = preg_replace("/[^A-Za-z0-9.\-_]/", "_", basename($_FILES['images']['name'][$index]));
                $filename = time() . '_' . $cleanName;
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $webPath = '/' . ltrim($webPathPrefix . $filename, '/');
                    $imgStmt = $conn->prepare("INSERT INTO recipe_images (recipe_id, image_url) VALUES (?, ?)");
                    $imgStmt->bind_param("is", $recipe_id, $webPath);
                    $imgStmt->execute();
                    $imgStmt->close();
                }
            }
        }
    }

    // Redirect to view page
    header("Location: view-recipe.php?id=$recipe_id");
    exit;
} else {
    echo "Error saving recipe: " . $stmt->error;
}
