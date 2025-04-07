<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recipe Management | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #f6efef;
      --secondary-color:rgb(241, 231, 97);
      --dark-color: #333;
      --light-color: #f30a0a;
      --accent-color: #dd0505;
      --card-bg: #ffffff;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: var(--dark-color);
    }

    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2.5rem;
      color: var(--secondary-color);
    }

    .hero-section {
      background: linear-gradient(135deg, rgb(176, 3, 3) 0%, rgba(253, 101, 197, 0.99) 100%);
      padding: 3rem 0;
      border-radius: 0 0 0px 0px;
      margin-bottom: 3rem;
      color: white;
    }

    .hero-title {
      font-weight: 700;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }

    .hero-subtitle {
      font-weight: 300;
      max-width: 700px;
      margin: 0 auto;
    }

    .thumbnail-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }

    .table-responsive {
      overflow-x: auto;
    }

    .table {
      min-width: 800px;
    }

    .status-badge {
      font-size: 0.8rem;
      padding: 0.35em 0.65em;
    }

    .badge-draft { background-color: #6c757d; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-approved { background-color: #198754; }
    .badge-rejected { background-color: #dc3545; }

    .filter-bar {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 2rem;
    }

    .action-buttons {
      display: flex;
      gap: 15px;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .action-buttons .btn {
      flex: 1;
      min-width: 200px;
    }

    .description-tooltip {
      cursor: pointer;
      position: relative;
    }

    .description-tooltip:hover::after {
      content: attr(data-description);
      position: absolute;
      bottom: 100%;
      left: 50%;
      transform: translateX(-50%);
      width: 300px;
      background: white;
      padding: 10px;
      border-radius: 5px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      z-index: 100;
      font-size: 0.9rem;
      color: var(--dark-color);
    }

    .legend {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      .hero-section {
        padding: 2rem 0;
        border-radius: 0 0 20px 20px;
      }
      
      .action-buttons .btn {
        flex: 100%;
      }
      
      .table th, .table td {
        padding: 0.5rem;
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="hero-section text-center">
  <div class="container">
    <h1 class="logo">NomNomPlan</h1>
    <h2 class="hero-title mt-3">Recipe Management</h2>
    <p class="hero-subtitle">View, organize, and manage your recipes</p>
  </div>
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

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$cuisineFilter = $_GET['cuisine'] ?? '';
$difficultyFilter = $_GET['difficulty'] ?? '';

// Base query construction
if ($role === 'admin') {
    $query = "SELECT recipes.*, IFNULL(users.name, 'Unknown') AS username FROM recipes LEFT JOIN users ON recipes.created_by = users.id";
    $whereClauses = [];
    $params = [];
    $types = '';
} else {
    $query = "SELECT recipes.*, users.name AS username FROM recipes LEFT JOIN users ON recipes.created_by = users.id WHERE recipes.created_by = ?";
    $whereClauses = [];
    $params = [$user_id];
    $types = 'i';
}

// Add filters
if (!empty($statusFilter)) {
    $whereClauses[] = "status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($cuisineFilter)) {
    $whereClauses[] = "cuisine = ?";
    $params[] = $cuisineFilter;
    $types .= 's';
}

if (!empty($difficultyFilter)) {
    $whereClauses[] = "difficulty = ?";
    $params[] = $difficultyFilter;
    $types .= 's';
}

// Combine where clauses
if (!empty($whereClauses)) {
    $query .= ($role === 'admin' ? " WHERE " : " AND ") . implode(" AND ", $whereClauses);
}

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$recipes = [];
while ($row = $result->fetch_assoc()) {
    $recipes[] = $row;
}
?>

<div class="container mb-5">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success text-center">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <!-- Action Buttons -->
  <div class="action-buttons">
    <a href="add-recipe.php" class="btn btn-danger">
      <i class="fas fa-plus-circle me-2"></i> Add New Recipe
    </a>
    <a href="list-recipes.php" class="btn btn-outline-danger">
      <i class="fas fa-eye me-2"></i> View Public Recipes
    </a>
    <?php if ($role === 'admin'): ?>
    <a href="?status=pending" class="btn btn-outline-warning">
      <i class="fas fa-clock me-2"></i> Pending Approvals
    </a>
    <?php endif; ?>
  </div>

  <!-- Status Legend -->
  <div class="legend">
    <div class="legend-item"><span class="badge status-badge badge-draft"></span> Draft</div>
    <div class="legend-item"><span class="badge status-badge badge-pending"></span> Pending</div>
    <div class="legend-item"><span class="badge status-badge badge-approved"></span> Approved</div>
    <div class="legend-item"><span class="badge status-badge badge-rejected"></span> Rejected</div>
  </div>

  <!-- Filter Bar -->
  <form method="GET" class="filter-bar">
    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="cuisine" class="form-label">Cuisine</label>
        <select name="cuisine" class="form-select">
          <option value="">All Cuisines</option>
          <option value="Malaysian" <?= $cuisineFilter === 'Malaysian' ? 'selected' : '' ?>>Malaysian</option>
          <option value="Chinese" <?= $cuisineFilter === 'Chinese' ? 'selected' : '' ?>>Chinese</option>
          <option value="Malay" <?= $cuisineFilter === 'Malay' ? 'selected' : '' ?>>Malay</option>
          <option value="Indian" <?= $cuisineFilter === 'Indian' ? 'selected' : '' ?>>Indian</option>
          <option value="Western" <?= $cuisineFilter === 'Western' ? 'selected' : '' ?>>Western</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="difficulty" class="form-label">Difficulty</label>
        <select name="difficulty" class="form-select">
          <option value="">All Levels</option>
          <option value="easy" <?= $difficultyFilter === 'easy' ? 'selected' : '' ?>>Easy</option>
          <option value="medium" <?= $difficultyFilter === 'medium' ? 'selected' : '' ?>>Medium</option>
          <option value="hard" <?= $difficultyFilter === 'hard' ? 'selected' : '' ?>>Hard</option>
        </select>
      </div>
      <div class="col-md-3 d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-filter me-2"></i> Apply Filters
        </button>
      </div>
    </div>
  </form>

  <!-- Recipe Table -->
  <div class="table-responsive">
    <table class="table table-bordered bg-white" id="recipeTable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Thumbnail</th>
          <th>Recipe Title</th>
          <th>Cuisine</th>
          <th>Difficulty</th>
          <th>Tags</th>
          <?php if ($role === 'admin'): ?><th>Owner</th><?php endif; ?>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recipes)): ?>
          <tr>
            <td colspan="<?= ($role === 'admin') ? 10 : 9 ?>" class="text-center text-muted py-4">
              No recipes found matching your criteria.
              <a href="?" class="d-block mt-2">Clear filters</a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($recipes as $i => $row): ?>
            <?php
              // Get tags
              $tagQuery = $conn->prepare("SELECT tags.name FROM tags JOIN recipe_tags ON tags.id = recipe_tags.tag_id WHERE recipe_tags.recipe_id = ?");
              $tagQuery->bind_param("i", $row['id']);
              $tagQuery->execute();
              $tagResult = $tagQuery->get_result();
              $tags = [];
              while ($tagRow = $tagResult->fetch_assoc()) {
                  $tags[] = $tagRow['name'];
              }
              $tagList = implode(', ', $tags);

              // Get image count
              $imgCountQuery = $conn->prepare("SELECT COUNT(*) as count FROM recipe_images WHERE recipe_id = ?");
              $imgCountQuery->bind_param("i", $row['id']);
              $imgCountQuery->execute();
              $imgCount = $imgCountQuery->get_result()->fetch_assoc()['count'];

              // Get thumbnail
              $thumbQuery = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ? LIMIT 1");
              $thumbQuery->bind_param("i", $row['id']);
              $thumbQuery->execute();
              $thumbResult = $thumbQuery->get_result();
              $thumbData = $thumbResult->fetch_assoc();
              $thumb = ($thumbResult->num_rows > 0) ? '/meal-planner-app/' . ltrim($thumbData['image_url'], '/') : 'https://via.placeholder.com/60';

              // Status badge
              $statusBadgeClass = 'badge-' . $row['status'];
            ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <img src="<?= $thumb ?>" alt="Thumb" class="thumbnail-img">
                <?php if ($imgCount > 1): ?>
                  <span class="badge bg-secondary position-relative" style="top: -10px; left: -10px;" title="<?= $imgCount ?> images">+<?= $imgCount-1 ?></span>
                <?php endif; ?>
              </td>
              <td>
                <span class="description-tooltip" data-description="<?= htmlspecialchars(substr($row['description'], 0, 200)) ?>">
                  <?= htmlspecialchars($row['title']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($row['cuisine']) ?></td>
              <td><?= ucfirst(htmlspecialchars($row['difficulty'])) ?></td>
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
                <span class="badge status-badge <?= $statusBadgeClass ?>"><?= ucfirst($row['status']) ?></span>
                <?php if ($row['status'] === 'rejected' && !empty($row['admin_note'])): ?>
                  <div class="mt-1 small text-danger"><strong>Reason:</strong> <?= htmlspecialchars($row['admin_note']) ?></div>
                <?php endif; ?>
              </td>
              <?php endif; ?>
              <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
              <td>
                <div class="d-flex flex-column gap-1">
                  <a href="view-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                  <a href="edit-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <a href="delete-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this recipe?')">Delete</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Search functionality
  document.getElementById('searchInput')?.addEventListener('keyup', function () {
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

  // Toggle rejection note field
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

  // Tooltip initialization
  document.querySelectorAll('.description-tooltip').forEach(el => {
    new bootstrap.Tooltip(el, {
      title: el.dataset.description,
      placement: 'top',
      trigger: 'hover'
    });
  });
</script>
</body>
</html>