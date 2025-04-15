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
      --primary-color: #f8f9fa;
      --secondary-color: #f5e942;
      --accent-color: #dd0505;
      --dark-color: #212529;
      --light-color: #f8f9fa;
      --container-bg: #ffffff;
      --sidebar-bg: #f8f9fa;
      --border-color: #dee2e6;
      --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--primary-color);
      color: var(--dark-color);
    }

    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2.5rem;
      color: var(--secondary-color);
      text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }

    .hero-section {
      background: linear-gradient(135deg, #c00000 0%, #e83e8c 100%);
      padding: 3rem 0;
      margin-bottom: 2rem;
      color: white;
    }

    .hero-title {
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .hero-subtitle {
      font-weight: 300;
      opacity: 0.9;
      max-width: 700px;
      margin: 0 auto;
    }

    .management-container {
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .sidebar {
      background: var(--sidebar-bg);
      border-radius: 0.5rem;
      padding: 1.25rem;
      box-shadow: var(--card-shadow);
      height: fit-content;
    }

    .main-content {
      background: var(--container-bg);
      border-radius: 0.5rem;
      padding: 1.5rem;
      box-shadow: var(--card-shadow);
    }

    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: var(--card-shadow);
      margin-bottom: 1.5rem;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0,0,0,0.1);
    }

    .card-header {
      background-color: var(--container-bg);
      border-bottom: 1px solid var(--border-color);
      font-weight: 600;
      padding: 1rem 1.25rem;
      border-radius: 0.5rem 0.5rem 0 0 !important;
    }

    .card-body {
      padding: 1.25rem;
    }

    .recipe-img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 0.375rem;
    }

    .status-badge {
      font-size: 0.75rem;
      padding: 0.35em 0.65em;
      font-weight: 500;
    }

    .badge-draft { background-color: #6c757d; }
    .badge-pending { background-color: #ffc107; color: #212529; }
    .badge-approved { background-color: #198754; }
    .badge-rejected { background-color: #dc3545; }

    .recipe-meta {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 0.5rem;
    }

    .recipe-meta-item {
      display: flex;
      align-items: center;
      font-size: 0.875rem;
      color: #6c757d;
    }

    .recipe-meta-item i {
      margin-right: 0.25rem;
    }

    .recipe-title {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
    }

    .recipe-description {
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 1rem;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .recipe-tags {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      margin-bottom: 1rem;
    }

    .recipe-tag {
      background-color: #e9ecef;
      color: #495057;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
    }

    .recipe-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: flex-end;
    }

    .recipe-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .sidebar-section {
      margin-bottom: 1.5rem;
    }

    .sidebar-section h5 {
      font-weight: 600;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid var(--border-color);
    }

    .sidebar-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-list li {
      margin-bottom: 0.5rem;
    }

    .sidebar-list a {
      display: flex;
      align-items: center;
      padding: 0.5rem 0.75rem;
      border-radius: 0.25rem;
      color: var(--dark-color);
      text-decoration: none;
      transition: all 0.2s;
    }

    .sidebar-list a:hover {
      background-color: #e9ecef;
    }

    .sidebar-list a.active {
      background-color: var(--accent-color);
      color: white;
    }

    .sidebar-list a i {
      margin-right: 0.5rem;
      width: 1.25rem;
      text-align: center;
    }

    .stats-card {
      background: white;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: var(--card-shadow);
    }

    .stats-card h6 {
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
    }

    .stats-card .count {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark-color);
    }

    .empty-state {
      text-align: center;
      padding: 3rem;
      background: white;
      border-radius: 0.5rem;
      box-shadow: var(--card-shadow);
    }

    .empty-state i {
      font-size: 3rem;
      color: #adb5bd;
      margin-bottom: 1rem;
    }

    @media (max-width: 992px) {
      .management-container {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .hero-section {
        padding: 2rem 0;
      }
      
      .recipe-grid {
        grid-template-columns: 1fr;
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
    <p class="hero-subtitle">Your personal recipe dashboard</p>
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

// Get stats for sidebar
$stats = [];
if ($role === 'admin') {
    $statsQuery = "SELECT status, COUNT(*) as count FROM recipes GROUP BY status";
    $statsResult = $conn->query($statsQuery);
    while ($stat = $statsResult->fetch_assoc()) {
        $stats[$stat['status']] = $stat['count'];
    }
} else {
    $statsQuery = "SELECT status, COUNT(*) as count FROM recipes WHERE created_by = ? GROUP BY status";
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->bind_param("i", $user_id);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    while ($stat = $statsResult->fetch_assoc()) {
        $stats[$stat['status']] = $stat['count'];
    }
}
?>

<div class="container">
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show text-center">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="management-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-section">
        <h5>Quick Actions</h5>
        <ul class="sidebar-list">
          <li><a href="add-recipe.php" class="active"><i class="fas fa-plus-circle"></i> Add New Recipe</a></li>
          <li><a href="list-recipes.php"><i class="fas fa-eye"></i> Browse Recipes</a></li>
          <?php if ($role === 'admin'): ?>
          <li><a href="?status=pending"><i class="fas fa-clock"></i> Pending Approvals</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="sidebar-section">
        <h5>Recipe Stats</h5>
        <div class="stats-card">
          <h6>Total Recipes</h6>
          <div class="count"><?= count($recipes) ?></div>
        </div>
        <?php foreach ($stats as $status => $count): ?>
          <div class="stats-card">
            <h6><?= ucfirst($status) ?></h6>
            <div class="count"><?= $count ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="sidebar-section">
        <h5>Status Legend</h5>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge status-badge badge-draft">Draft</span>
          <span class="badge status-badge badge-pending">Pending</span>
          <span class="badge status-badge badge-approved">Approved</span>
          <span class="badge status-badge badge-rejected">Rejected</span>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Filter Card -->
      <div class="card filter-card">
        <div class="card-header">
          <i class="fas fa-filter me-2"></i>Filter Recipes
        </div>
        <div class="card-body">
          <form method="GET">
            <div class="row g-3">
              <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="">All Statuses</option>
                  <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                  <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                  <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                  <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
              </div>
              <div class="col-md-4">
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
              <div class="col-md-4">
                <label for="difficulty" class="form-label">Difficulty</label>
                <select name="difficulty" class="form-select">
                  <option value="">All Levels</option>
                  <option value="easy" <?= $difficultyFilter === 'easy' ? 'selected' : '' ?>>Easy</option>
                  <option value="medium" <?= $difficultyFilter === 'medium' ? 'selected' : '' ?>>Medium</option>
                  <option value="hard" <?= $difficultyFilter === 'hard' ? 'selected' : '' ?>>Hard</option>
                </select>
              </div>
              <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                  <a href="?" class="btn btn-outline-secondary">Clear Filters</a>
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i> Apply Filters
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Recipe Cards -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="fas fa-list-ul me-2"></i>Your Recipes</span>
          <span class="badge bg-primary"><?= count($recipes) ?> recipes</span>
        </div>
        <div class="card-body">
          <?php if (empty($recipes)): ?>
            <div class="empty-state">
              <i class="fas fa-utensils"></i>
              <h5>No recipes found</h5>
              <p class="mb-4">Try adjusting your filters or add a new recipe</p>
              <a href="add-recipe.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Add Recipe
              </a>
            </div>
          <?php else: ?>
            <div class="recipe-grid">
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

                  // Get thumbnail
                  $thumbQuery = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ? LIMIT 1");
                  $thumbQuery->bind_param("i", $row['id']);
                  $thumbQuery->execute();
                  $thumbResult = $thumbQuery->get_result();
                  $thumbData = $thumbResult->fetch_assoc();
                  $thumb = ($thumbResult->num_rows > 0) ? '/meal-planner-app/' . ltrim($thumbData['image_url'], '/') : 'https://via.placeholder.com/400x300';

                  // Status badge
                  $statusBadgeClass = 'badge-' . $row['status'];
                ?>
                <div class="card recipe-card">
                  <img src="<?= $thumb ?>" class="recipe-img" alt="<?= htmlspecialchars($row['title']) ?>">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <span class="badge <?= $statusBadgeClass ?>"><?= ucfirst($row['status']) ?></span>
                      <?php if ($role === 'admin'): ?>
                        <small class="text-muted">By: <?= htmlspecialchars($row['username']) ?></small>
                      <?php endif; ?>
                    </div>
                    
                    <h5 class="recipe-title"><?= htmlspecialchars($row['title']) ?></h5>
                    
                    <div class="recipe-meta">
                      <span class="recipe-meta-item">
                        <i class="fas fa-utensils"></i> <?= htmlspecialchars($row['cuisine']) ?>
                      </span>
                      <span class="recipe-meta-item">
                        <i class="fas fa-tachometer-alt"></i> <?= ucfirst(htmlspecialchars($row['difficulty'])) ?>
                      </span>
                      <span class="recipe-meta-item">
                        <i class="fas fa-clock"></i> <?= $row['prep_time'] + $row['cook_time'] ?> mins
                      </span>
                    </div>
                    
                    <p class="recipe-description"><?= htmlspecialchars($row['description']) ?></p>
                    
                    <?php if (!empty($tags)): ?>
                      <div class="recipe-tags">
                        <?php foreach ($tags as $tag): ?>
                          <span class="recipe-tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                      <small class="text-muted">Created: <?= date('M j, Y', strtotime($row['created_at'])) ?></small>
                      <div class="recipe-actions">
                        <a href="view-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                          <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                          <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete-recipe.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this recipe?')">
                          <i class="fas fa-trash-alt"></i>
                        </a>
                      </div>
                    </div>
                    
                    <?php if ($row['status'] === 'rejected' && !empty($row['admin_note'])): ?>
                      <div class="alert alert-danger mt-2 p-2 small">
                        <strong>Rejection Reason:</strong> <?= htmlspecialchars($row['admin_note']) ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if ($role === 'admin'): ?>
                      <form method="POST" action="update-status.php" class="mt-2">
                        <input type="hidden" name="recipe_id" value="<?= $row['id'] ?>">
                        <select name="status" class="form-select form-select-sm mb-2" onchange="toggleNoteField(this, <?= $row['id'] ?>)">
                          <?php foreach (["draft", "pending", "approved", "rejected"] as $statusOption): ?>
                            <option value="<?= $statusOption ?>" <?= ($row['status'] === $statusOption) ? 'selected' : '' ?>>
                              <?= ucfirst($statusOption) ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <textarea name="admin_note" id="note-<?= $row['id'] ?>" class="form-control form-control-sm mb-2" placeholder="Rejection reason..." style="display: <?= $row['status'] === 'rejected' ? 'block' : 'none' ?>;"><?= htmlspecialchars($row['admin_note'] ?? '') ?></textarea>
                        <button type="submit" class="btn btn-sm btn-success w-100">Update Status</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
</script>
</body>
</html>