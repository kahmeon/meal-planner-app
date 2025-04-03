<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_GET['id'] ?? null;

if (!$recipe_id) {
    die("Recipe ID is required.");
}

// Fetch recipe
$recipeStmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$recipeStmt->bind_param("i", $recipe_id);
$recipeStmt->execute();
$recipeResult = $recipeStmt->get_result();

if ($recipeResult->num_rows === 0) {
    die("Recipe not found.");
}

$recipe = $recipeResult->fetch_assoc();

// Fetch all tags
$tagsResult = $conn->query("SELECT * FROM tags");
$allTags = $tagsResult->fetch_all(MYSQLI_ASSOC);

// Fetch recipe's tags
$recipeTagsResult = $conn->prepare("SELECT tag_id FROM recipe_tags WHERE recipe_id = ?");
$recipeTagsResult->bind_param("i", $recipe_id);
$recipeTagsResult->execute();
$tagIds = array_column($recipeTagsResult->get_result()->fetch_all(MYSQLI_ASSOC), 'tag_id');

// Fetch images
$imageQuery = $conn->prepare("SELECT id, image_url FROM recipe_images WHERE recipe_id = ?");
$imageQuery->bind_param("i", $recipe_id);
$imageQuery->execute();
$images = $imageQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Convert JSON back to text for textarea
function jsonToTextarea($json) {
    $arr = json_decode($json, true);
    return is_array($arr) ? implode("\n", $arr) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Recipe | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .logo { font-family: 'Pacifico', cursive; font-size: 2rem; color: #e00000; }
    .img-thumb { width: 100px; border-radius: 8px; margin-right: 10px; }
  </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">
  <h1 class="logo text-center">NomNomPlan</h1>
  <h2 class="text-center mb-4">Edit Recipe</h2>

  <form action="update-recipe.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">
    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($recipe['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control"><?= htmlspecialchars($recipe['description']) ?></textarea>
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
        <option value="">Select</option>
        <option value="easy" <?= $recipe['difficulty'] == 'easy' ? 'selected' : '' ?>>Easy</option>
        <option value="medium" <?= $recipe['difficulty'] == 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="hard" <?= $recipe['difficulty'] == 'hard' ? 'selected' : '' ?>>Hard</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="draft" <?= $recipe['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        <option value="pending" <?= $recipe['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Ingredients</label>
      <textarea name="ingredients" class="form-control" rows="4"><?= jsonToTextarea($recipe['ingredients']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Steps</label>
      <textarea name="steps" class="form-control" rows="4"><?= jsonToTextarea($recipe['steps']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Nutrition</label>
      <textarea name="nutrition" class="form-control"><?= htmlspecialchars($recipe['nutrition']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Tags</label><br>
      <?php foreach ($allTags as $tag): ?>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
            <?= in_array($tag['id'], $tagIds) ? 'checked' : '' ?>>
          <label class="form-check-label"><?= htmlspecialchars($tag['name']) ?></label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Current Images</label><br>
      <?php foreach ($images as $img): ?>
        <div class="d-inline-block text-center me-2">
          <img src="<?= $img['image_url'] ?>" class="img-thumb">
          <br>
          <a href="delete-image.php?id=<?= $img['id'] ?>&recipe_id=<?= $recipe['id'] ?>" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Delete this image?')">Delete</a>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Add More Images</label>
      <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    </div>

    <div class="form-check mb-4">
      <input class="form-check-input" type="checkbox" name="is_public" value="1" <?= $recipe['is_public'] ? 'checked' : '' ?>>
      <label class="form-check-label">Make recipe public</label>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-danger">Update Recipe</button>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
