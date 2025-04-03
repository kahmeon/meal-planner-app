<?php
require_once '../includes/db.php';
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Use correct variable: $conn instead of $pdo
$conn = $conn ?? null;

// Get active competitions (update: no is_active column)
$compStmt = $conn->prepare("SELECT * FROM competitions WHERE end_date > NOW()");
if ($compStmt && $compStmt->execute()) {
    $competitions = $compStmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error fetching competitions: " . $conn->error);
}

// Get user's recipes (correct column: created_by)
$recipeStmt = $conn->prepare("SELECT id, title FROM recipes WHERE created_by = ?");
if ($recipeStmt) {
    $recipeStmt->bind_param("i", $_SESSION['user_id']);
    if ($recipeStmt->execute()) {
        $recipes = $recipeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Error executing recipe query: " . $recipeStmt->error);
    }
} else {
    die("Error preparing recipe query: " . $conn->error);
}

include '../navbar.php';
?>

<div class="container py-5">
  <h2 class="mb-4 text-center">Submit Recipe to Competition</h2>
  <form action="process_entry.php" method="post" class="border p-4 rounded shadow bg-white">
    <div class="mb-3">
      <label class="form-label">Competition</label>
      <select name="competition_id" class="form-select" required>
        <?php foreach ($competitions as $comp): ?>
          <option value="<?= $comp['competition_id'] ?>">
            <?= htmlspecialchars($comp['title']) ?> (Ends: <?= date('M j, Y', strtotime($comp['end_date'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Your Recipe</label>
      <select name="recipe_id" class="form-select" required>
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
</div>

<?php include '../includes/footer.php'; ?>
