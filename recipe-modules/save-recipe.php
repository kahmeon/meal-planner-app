<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Sanitize and collect form inputs
$title        = trim($_POST['title'] ?? '');
$description  = trim($_POST['description'] ?? '');
$cuisine      = trim($_POST['cuisine'] ?? '');
$prep_time    = (int) ($_POST['prep_time'] ?? 0);
$cook_time    = (int) ($_POST['cook_time'] ?? 0);
$total_time   = (int) ($_POST['total_time'] ?? 0);
$difficulty   = trim($_POST['difficulty'] ?? '');
$status       = $_POST['status'] ?? 'draft';
$ingredients  = array_filter(array_map('trim', explode("\n", $_POST['ingredients'] ?? '')));
$steps        = array_filter(array_map('trim', explode("\n", $_POST['steps'] ?? '')));
$nutrition    = trim($_POST['nutrition'] ?? '');
$is_public    = isset($_POST['is_public']) ? 1 : 0;
$tags         = $_POST['tags'] ?? [];

// ✅ Field-level validation
$errors = [];

if (empty($title))        $errors[] = "Title is required.";
if (empty($description))  $errors[] = "Description is required.";
if (empty($cuisine))      $errors[] = "Cuisine is required.";
if ($prep_time <= 0)      $errors[] = "Preparation time must be greater than 0.";
if ($cook_time <= 0)      $errors[] = "Cooking time must be greater than 0.";
if (empty($difficulty))   $errors[] = "Please select a difficulty level.";
if (empty($ingredients))  $errors[] = "Please provide at least one ingredient.";
if (empty($steps))        $errors[] = "Please describe at least one step.";

// If errors found, redirect back with messages
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header("Location: add-recipe.php");
    exit;
}

// Prepare JSON strings for DB
$ingredients_json = json_encode($ingredients);
$steps_json = json_encode($steps);

// Insert recipe
$stmt = $conn->prepare("INSERT INTO recipes (
    title, description, cuisine, prep_time, cook_time, total_time,
    difficulty, status, ingredients, steps, nutrition, is_public, created_by, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: add-recipe.php");
    exit;
}

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
    $ingredients_json,
    $steps_json,
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

    // Upload images
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

    header("Location: view-recipe.php?id=$recipe_id");
    exit;
} else {
    $_SESSION['error'] = "Error saving recipe: " . $stmt->error;
    header("Location: add-recipe.php");
    exit;
}
