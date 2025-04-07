<?php
session_start();
require_once '../includes/db.php';

$loggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['user_role'] ?? 'user';
$user_id = $_SESSION['user_id'] ?? null;

if (!$loggedIn || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: recipe-management.php");
    exit;
}

$recipe_id = (int) $_GET['id'];

$query = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$query->bind_param("i", $recipe_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger m-5'>Recipe not found.</div>";
    exit;
}

$recipe = $result->fetch_assoc();

if ($role !== 'admin' && $recipe['created_by'] != $user_id) {
    echo "<div class='alert alert-danger m-5'>Unauthorized access.</div>";
    exit;
}

$tagQuery = $conn->query("SELECT id, name FROM tags");
$allTags = $tagQuery->fetch_all(MYSQLI_ASSOC);

$recipeTagIds = [];
$tagMap = $conn->prepare("SELECT tag_id FROM recipe_tags WHERE recipe_id = ?");
$tagMap->bind_param("i", $recipe_id);
$tagMap->execute();
$tagResult = $tagMap->get_result();
while ($tagRow = $tagResult->fetch_assoc()) {
    $recipeTagIds[] = $tagRow['tag_id'];
}

$imageQuery = $conn->prepare("SELECT id, image_url FROM recipe_images WHERE recipe_id = ?");
$imageQuery->bind_param("i", $recipe_id);
$imageQuery->execute();
$imageResult = $imageQuery->get_result();
$existingImages = $imageResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Recipe | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .form-label { font-weight: 500; }
    .image-thumb {
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
    }
  </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">
  <h2 class="text-center mb-4">Edit Recipe: <?= htmlspecialchars($recipe['title']) ?></h2>

  <?php if ($recipe['status'] === 'rejected' && !empty($recipe['admin_note'])): ?>
    <div class="alert alert-warning">
      <strong>Admin Feedback:</strong> <?= htmlspecialchars($recipe['admin_note']) ?>
    </div>
  <?php endif; ?>

  <form action="update-recipe.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">
    <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">

    <div class="mb-3">
      <label class="form-label">Recipe Title</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($recipe['title']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($recipe['description'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Cuisine</label>
      <input type="text" name="cuisine" class="form-control" value="<?= htmlspecialchars($recipe['cuisine']) ?>">
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Prep Time</label>
        <input type="number" name="prep_time" class="form-control" value="<?= $recipe['prep_time'] ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Cook Time</label>
        <input type="number" name="cook_time" class="form-control" value="<?= $recipe['cook_time'] ?>">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Total Time</label>
        <input type="number" name="total_time" class="form-control" value="<?= $recipe['total_time'] ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Difficulty</label>
      <select name="difficulty" class="form-select">
        <option value="easy" <?= $recipe['difficulty'] === 'easy' ? 'selected' : '' ?>>Easy</option>
        <option value="medium" <?= $recipe['difficulty'] === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="hard" <?= $recipe['difficulty'] === 'hard' ? 'selected' : '' ?>>Hard</option>
      </select>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="draft" <?= $recipe['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="pending" <?= $recipe['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="approved" <?= $recipe['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
        <option value="rejected" <?= $recipe['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Ingredients</label>
      <textarea name="ingredients" class="form-control" rows="6"><?php
        echo is_array(json_decode($recipe['ingredients'], true))
          ? implode("\n", json_decode($recipe['ingredients'], true))
          : htmlspecialchars($recipe['ingredients']);
      ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Steps</label>
      <textarea name="steps" class="form-control" rows="6"><?php
        echo is_array(json_decode($recipe['steps'], true))
          ? implode("\n", json_decode($recipe['steps'], true))
          : htmlspecialchars($recipe['steps']);
      ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Nutrition Info</label>
      <textarea name="nutrition" class="form-control" rows="3" placeholder="e.g. Calories: 250 kcal\nProtein: 8g\nFat: 5g"><?= htmlspecialchars($recipe['nutrition'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Tags</label><br>
      <?php foreach ($allTags as $tag): ?>
        <div class="form-check form-check-inline">
          <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                 class="form-check-input"
                 <?= in_array($tag['id'], $recipeTagIds) ? 'checked' : '' ?>>
          <label class="form-check-label"><?= htmlspecialchars($tag['name']) ?></label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Existing Images</label><br>
      <?php
$basePath = '/MEAL-PLANNER-APP/';
foreach ($existingImages as $img): 
?>
  <div class="d-inline-block me-2 text-center">
    <img src="<?= $basePath . ltrim($img['image_url'], '/') ?>" class="image-thumb mb-1"><br>
    <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete
  </div>
<?php endforeach; ?>

    </div>

    <div class="mb-3">
      <label class="form-label">Upload New Images</label>
      <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    </div>

    <div class="form-check mb-4">
  <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" <?= $recipe['is_public'] ? 'checked' : '' ?>>
  <label class="form-check-label" for="is_public">
    Make recipe public
  </label>
</div>

    <div class="text-end">
      <button type="submit" class="btn btn-success">Update Recipe</button>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
