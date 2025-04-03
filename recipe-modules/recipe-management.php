<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recipe Management | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2rem;
      color: #e00000;
    }
    .page-header {
      background-color: #fff;
      padding: 2rem 1rem;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .table th, .table td {
      vertical-align: middle;
    }
    .thumbnail-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }
    .search-wrapper {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 1rem;
    }
    .search-wrapper input {
      flex-grow: 1;
    }
  </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="page-header mb-4">
  <h1 class="logo">NomNomPlan</h1>
  <h2 class="mt-2">Recipe Management</h2>
  <p class="text-muted">View, organize, and manage your recipes</p>
</div>

<?php if (!$loggedIn): ?>
  <script>
    alert("⚠️ You must log in to access the recipe management page.");
    window.location.href = "../login.php";
  </script>
<?php else: ?>

<?php
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'user';

if ($role === 'admin') {
    $query = $conn->prepare("SELECT recipes.*, IFNULL(users.name, 'Unknown') AS username FROM recipes LEFT JOIN users ON recipes.created_by = users.id");
} else {
    $query = $conn->prepare("SELECT recipes.*, users.name AS username FROM recipes LEFT JOIN users ON recipes.created_by = users.id WHERE created_by = ?");
    $query->bind_param("i", $user_id);
}

if (!$query) {
    die("Query preparation failed: " . $conn->error);
}

$query->execute();
$result = $query->get_result();

$pendingRejectedRecipes = [];
$approvedRecipes = [];

while ($row = $result->fetch_assoc()) {
    if (in_array($row['status'], ['pending', 'rejected'])) {
        $pendingRejectedRecipes[] = $row;
    } else {
        $approvedRecipes[] = $row;
    }
}
?>

<div class="container mb-5">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success text-center">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><?= ($role === 'admin') ? 'All Recipes' : 'Your Recipes' ?></h4>
    <a href="add-recipe.php" class="btn btn-danger">+ Add New Recipe</a>
  </div>

  <div class="search-wrapper">
    <span>🔍</span>
    <input type="text" id="searchInput" class="form-control" placeholder="Search recipes by title or cuisine...">
  </div>

  <table class="table table-bordered bg-white" id="recipeTable">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Thumbnail</th>
        <th>Recipe Title</th>
        <th>Cuisine</th>
        <th>Tags</th>
        <?php if ($role === 'admin'): ?><th>Owner</th><?php endif; ?>
        <th>Status</th>
        <th>Created Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $i = 1;
      function renderRecipeRow($row, $i, $conn, $role) {
          ob_start();
          $tagQuery = $conn->prepare("SELECT tags.name FROM tags JOIN recipe_tags ON tags.id = recipe_tags.tag_id WHERE recipe_tags.recipe_id = ?");
          $tagQuery->bind_param("i", $row['id']);
          $tagQuery->execute();
          $tagResult = $tagQuery->get_result();
          $tags = [];
          while ($tagRow = $tagResult->fetch_assoc()) {
              $tags[] = $tagRow['name'];
          }
          $tagList = implode(', ', $tags);

          $thumbQuery = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ? LIMIT 1");
          $thumbQuery->bind_param("i", $row['id']);
          $thumbQuery->execute();
          $thumbResult = $thumbQuery->get_result();
          $thumb = ($thumbResult->num_rows > 0) ? $thumbResult->fetch_assoc()['image_url'] : 'https://via.placeholder.com/60';

          $statusColor = match ($row['status']) {
              'approved' => 'success',
              'pending' => 'secondary',
              'rejected' => 'danger',
              'draft' => 'dark',
              default => 'warning'
          };
      ?>
      <tr>
        <td><?= $i ?></td>
        <td><img src="<?= $thumb ?>" alt="Thumb" class="thumbnail-img"></td>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><?= htmlspecialchars($row['cuisine']) ?></td>
        <td><?= htmlspecialchars($tagList) ?></td>
        <?php if ($role === 'admin'): ?>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td>
          <form method="POST" action="update-status.php" class="d-flex flex-column align-items-start">
            <input type="hidden" name="recipe_id" value="<?= $row['id'] ?>">
            <select name="status" class="form-select form-select-sm mb-1" onchange="toggleNoteField(this, <?= $row['id'] ?>)">
              <?php foreach (["draft", "pending", "approved", "rejected"] as $statusOption): ?>
                <option value="<?= $statusOption ?>" <?= ($row['status'] === $statusOption) ? 'selected' : '' ?>>
                  <?= ucfirst($statusOption) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <textarea name="admin_note" id="note-<?= $row['id'] ?>" class="form-control form-control-sm mb-1" placeholder="Rejection reason..." style="display: <?= $row['status'] === 'rejected' ? 'block' : 'none' ?>;">
<?= htmlspecialchars($row['admin_note'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-sm btn-success w-100">Update</button>
          </form>
        </td>
        <?php else: ?>
        <td>
          <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($row['status']) ?></span>
          <?php if ($row['status'] === 'rejected' && !empty($row['admin_note'])): ?>
            <div class="mt-1 small text-danger"><strong>Reason:</strong> <?= htmlspecialchars($row['admin_note']) ?></div>
          <?php endif; ?>
        </td>
        <?php endif; ?>
        <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
        <td>
          <a href="view-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
          <a href="delete-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this recipe?')">Delete</a>
        </td>
      </tr>
      <?php
          return ob_get_clean();
      }

      foreach ($pendingRejectedRecipes as $row) {
          echo renderRecipeRow($row, $i++, $conn, $role);
      }

      if (!empty($pendingRejectedRecipes) && !empty($approvedRecipes)) {
          echo '<tr><td colspan="' . (($role === 'admin') ? 9 : 8) . '"><hr></td></tr>';
      }

      foreach ($approvedRecipes as $row) {
          echo renderRecipeRow($row, $i++, $conn, $role);
      }

      if (empty($pendingRejectedRecipes) && empty($approvedRecipes)) {
          echo '<tr><td colspan="' . (($role === 'admin') ? 9 : 8) . '" class="text-center text-muted">No recipes found.</td></tr>';
      }
      ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('searchInput').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#recipeTable tbody tr');
    rows.forEach(row => {
      if (row.querySelector('td:nth-child(3)')) {
        let title = row.cells[2].textContent.toLowerCase();
        let cuisine = row.cells[3].textContent.toLowerCase();
        row.style.display = (title.includes(filter) || cuisine.includes(filter)) ? '' : 'none';
      }
    });
  });

  function toggleNoteField(select, id) {
    const noteBox = document.getElementById(`note-${id}`);
    if (select.value === 'rejected') {
      noteBox.style.display = 'block';
      noteBox.required = true;
    } else {
      noteBox.style.display = 'none';
      noteBox.required = false;
      noteBox.value = '';
    }
  }
</script>
</body>
</html>
