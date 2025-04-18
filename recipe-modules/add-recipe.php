<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
if (!$loggedIn) {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

// Fetch tags from DB
$tagQuery = $conn->query("SELECT id, name FROM tags");
$tags = $tagQuery->fetch_all(MYSQLI_ASSOC);

// Pre-fill old inputs if any error occurred
$title = $_SESSION['old_inputs']['title'] ?? '';
$description = $_SESSION['old_inputs']['description'] ?? '';
$cuisine = $_SESSION['old_inputs']['cuisine'] ?? '';
$prep_time = $_SESSION['old_inputs']['prep_time'] ?? '';
$cook_time = $_SESSION['old_inputs']['cook_time'] ?? '';
$total_time = $_SESSION['old_inputs']['total_time'] ?? '';
$difficulty = $_SESSION['old_inputs']['difficulty'] ?? '';
$status = $_SESSION['old_inputs']['status'] ?? 'pending';
$ingredients = $_SESSION['old_inputs']['ingredients'] ?? '';
$steps = $_SESSION['old_inputs']['steps'] ?? '';
$nutrition = $_SESSION['old_inputs']['nutrition'] ?? '';
$is_public = $_SESSION['old_inputs']['is_public'] ?? 1;
$tags_selected = $_SESSION['old_inputs']['tags'] ?? [];

unset($_SESSION['old_inputs']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Recipe | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2rem;
      color: #e00000;
    }
    .form-label {
      font-weight: 500;
    }
    .section-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #c0392b;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container my-5">
  <h1 class="logo text-center mb-3">NomNomPlan</h1>
  <h2 class="text-center mb-4">Add New Recipe</h2>

  <!-- Error/Success Flash Message -->
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['success']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <form action="save-recipe.php" method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">

    <div class="mb-3">
      <label class="form-label">Recipe Title *</label>
      <input type="text" name="title" class="form-control" placeholder="e.g. Nasi Lemak with Sambal" value="<?= htmlspecialchars($title) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description *</label>
      <textarea name="description" class="form-control" rows="3" placeholder="Brief summary of your recipe" required><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Cuisine *</label>
      <input type="text" name="cuisine" class="form-control" placeholder="e.g. Malaysian, Indian, Chinese" value="<?= htmlspecialchars($cuisine) ?>" required>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Prep Time (mins) *</label>
        <input type="number" name="prep_time" class="form-control" placeholder="e.g. 15" value="<?= htmlspecialchars($prep_time) ?>" required>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Cook Time (mins) *</label>
        <input type="number" name="cook_time" class="form-control" placeholder="e.g. 25" value="<?= htmlspecialchars($cook_time) ?>" required>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Total Time (mins)</label>
        <input type="number" name="total_time" class="form-control" placeholder="e.g. 40" value="<?= htmlspecialchars($total_time) ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Difficulty *</label>
      <select name="difficulty" class="form-select" required>
        <option value="">Select difficulty</option>
        <option value="easy" <?= $difficulty === 'easy' ? 'selected' : '' ?>>Easy – Great for beginners</option>
        <option value="medium" <?= $difficulty === 'medium' ? 'selected' : '' ?>>Medium – Some experience needed</option>
        <option value="hard" <?= $difficulty === 'hard' ? 'selected' : '' ?>>Hard – Advanced preparation</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft (save privately)</option>
        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending (awaiting approval)</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Ingredients *</label>
      <textarea name="ingredients" class="form-control" rows="4" placeholder="- 2 cups rice&#10;- 1 boiled egg" required><?= htmlspecialchars($ingredients) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Steps *</label>
      <textarea name="steps" class="form-control" rows="4" placeholder="1. Wash rice&#10;2. Boil egg" required><?= htmlspecialchars($steps) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Nutrition (optional)</label>
      <textarea name="nutrition" class="form-control" rows="3" placeholder="Calories: 250 | Protein: 8g | Carbs: 30g"><?= htmlspecialchars($nutrition) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Tags</label><br>
      <?php foreach ($tags as $tag): ?>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" id="tag<?= $tag['id'] ?>" <?= in_array($tag['id'], $tags_selected) ? 'checked' : '' ?>>
          <label class="form-check-label" for="tag<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Images</label>
      <input type="file" name="images[]" class="form-control" multiple accept="image/*">
      <small class="text-muted">You can select and upload multiple images (JPG, PNG, etc.)</small>
    </div>

    <div class="form-check mb-4">
      <input class="form-check-input" type="checkbox" name="is_public" id="is_public" <?= $is_public ? 'checked' : '' ?>>
      <label class="form-check-label" for="is_public">Make recipe public</label>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-danger">Save Recipe</button>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
