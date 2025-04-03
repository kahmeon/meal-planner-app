<?php
require_once '../includes/db.php';
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_competition_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Fetch ongoing competitions
$compStmt = $conn->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM competition_entries e WHERE e.user_id = ? AND e.competition_id = c.competition_id) as already_joined 
    FROM competitions c 
    WHERE c.end_date > NOW()
");
$compStmt->bind_param("i", $user_id);
$compStmt->execute();
$competitions = $compStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user's recipes
$recipeStmt = $conn->prepare("SELECT id, title FROM recipes WHERE created_by = ?");
$recipeStmt->bind_param("i", $user_id);
$recipeStmt->execute();
$recipes = $recipeStmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../navbar.php';
?>

<div class="container py-5">
  <h2 class="mb-4 text-center">🍽️ Submit Your Recipe to a Competition</h2>

  <?php if (count($recipes) === 0): ?>
    <div class="alert alert-warning text-center">
      You haven’t added any recipes yet. <a href="../recipe-modules/recipe-add.php" class="btn btn-sm btn-outline-primary ms-2">Add Recipe</a>
    </div>
  <?php else: ?>
    <form action="process_entry.php" method="post" class="border p-4 rounded shadow bg-white">
      <!-- Competition Dropdown -->
      <div class="mb-3">
        <label class="form-label">Select Competition</label>
        <select name="competition_id" class="form-select" required>
          <option value="">-- Choose a competition --</option>
          <?php foreach ($competitions as $comp): ?>
            <option value="<?= $comp['competition_id'] ?>" 
              <?= ($selected_competition_id == $comp['competition_id']) ? 'selected' : '' ?>
              <?= ($comp['already_joined'] > 0) ? 'disabled' : '' ?>>
              <?= htmlspecialchars($comp['title']) ?> 
              <?= ($comp['already_joined'] > 0) ? '(Already joined)' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Recipe Dropdown -->
      <div class="mb-3">
        <label class="form-label">Select Your Recipe</label>
        <select name="recipe_id" class="form-select" required>
          <option value="">-- Choose a recipe --</option>
          <?php foreach ($recipes as $recipe): ?>
            <option value="<?= $recipe['id'] ?>">
              <?= htmlspecialchars($recipe['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="text-end">
        <button type="submit" class="btn btn-danger">Submit Entry</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
