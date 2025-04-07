<?php
session_start();
require_once '../includes/db.php';

// Handle search/filter
$search = $_GET['search'] ?? '';
$cuisine = $_GET['cuisine'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';

$query = "SELECT * FROM recipes WHERE is_public = 1 AND status = 'approved'";
$params = [];

if (!empty($search)) {
    $query .= " AND title LIKE ?";
    $params[] = "%$search%";
}

if (!empty($cuisine)) {
    $query .= " AND cuisine = ?";
    $params[] = $cuisine;
}

if (!empty($difficulty)) {
    $query .= " AND difficulty = ?";
    $params[] = $difficulty;
}

$stmt = $conn->prepare($query);

if ($params) {
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$recipes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Recipes | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color:rgb(235, 227, 117);
      --secondary-color: #ea0a02;
      --dark-color: #fef9f9;
      --light-color: #f30a0a;
      --accent-color: #dd0505;
      --card-bg: #ffffff;
      --text-dark: #333;
      --text-light: #777;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: var(--text-dark);
    }

    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2.5rem;
      color: var(--primary-color);
      text-shadow: 1px 1px 3px rgba(253, 0, 0, 0.5);
    }

    .hero-section {
      background: linear-gradient(135deg, rgb(228, 46, 46) 0%, rgba(241, 103, 202, 0.99) 100%);
      padding: 4rem 0 3rem;
      border-radius: 0 0 0px 0px;
      margin-bottom: 3rem;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .page-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: white;
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }

    .page-title:after {
      content: '';
      position: absolute;
      width: 50%;
      height: 4px;
      background: var(--primary-color);
      bottom: -10px;
      left: 25%;
      border-radius: 2px;
    }

    .hero-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.2rem;
      color: white;
      margin-bottom: 1rem;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }

    .hero-subtitle {
      font-family: 'Poppins', sans-serif;
      font-weight: 300;
      font-size: 1.1rem;
      color: white;
      max-width: 700px;
      margin: 0 auto;
    }

    .filter-bar {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 2rem;
    }

    .form-control, .form-select {
      border-radius: 8px;
      padding: 0.75rem 1rem;
      border: 1px solid #e0e0e0;
      transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 0 0.25rem rgba(221, 5, 5, 0.25);
    }

    .btn-primary {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background-color: #c00404;
      border-color: #c00404;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .btn-outline-primary {
      color: var(--accent-color);
      border-color: var(--accent-color);
    }

    .btn-outline-primary:hover {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
      color: white;
    }

    .recipe-card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      height: 100%;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      background: var(--card-bg);
    }

    .recipe-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .card-img-top {
      height: 220px;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .recipe-card:hover .card-img-top {
      transform: scale(1.05);
    }

    .card-title {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.75rem;
    }

    .card-text {
      color: var(--text-light);
      margin-bottom: 1rem;
    }

    .badge-cuisine {
      background-color: var(--secondary-color);
      color: white;
      font-weight: 500;
    }

    .badge-difficulty {
      background-color: var(--primary-color);
      color: var(--accent-color);
      font-weight: 500;
    }

    .view-btn {
      background-color: var(--accent-color);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 1.25rem;
      font-weight: 500;
      transition: all 0.3s;
    }

    .view-btn:hover {
      background-color: #c00404;
      color: white;
      transform: translateY(-2px);
    }

    .no-results {
      text-align: center;
      padding: 4rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .no-results i {
      font-size: 3rem;
      color: #ccc;
      margin-bottom: 1rem;
    }

    .search-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
    }

    .search-container {
      position: relative;
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

    @media (max-width: 768px) {
      .hero-section {
        padding: 2rem 0;
        border-radius: 0 0 20px 20px;
      }
      
      .hero-title {
        font-size: 1.8rem;
      }
      
      .hero-subtitle {
        font-size: 1rem;
      }
      
      .action-buttons .btn {
        flex: 100%;
      }
    }
  </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="hero-section">
  <div class="container text-center">
    <h1 class="logo mb-3">NomNomPlan</h1>
    <h2 class="hero-title">Discover Delicious Recipes</h2>
    <p class="hero-subtitle">Find your next culinary adventure from our community of food lovers</p>
  </div>
</div>

<div class="container mb-5">
  <!-- Action Buttons for Create and Manage Recipes -->
  <div class="action-buttons">
    <a href="add-recipe.php" class="btn btn-primary">
      <i class="fas fa-plus-circle me-2"></i> Create New Recipe
    </a>
    <a href="recipe-management.php" class="btn btn-outline-primary">
      <i class="fas fa-clipboard-list me-2"></i> Manage My Recipes
    </a>
  </div>

  <!-- Search and Filter Form -->
  <form method="GET" class="filter-bar">
    <div class="row g-3 align-items-end">
      <div class="col-md-4 search-container">
        <label for="search" class="form-label">Search Recipes</label>
        <input type="text" name="search" class="form-control ps-4" placeholder="What are you craving today?" value="<?= htmlspecialchars($search) ?>">
        <i class="fas fa-search search-icon"></i>
      </div>
      <div class="col-md-3">
        <label for="cuisine" class="form-label">Cuisine</label>
        <select name="cuisine" class="form-select">
          <option value="">All Cuisines</option>
          <option value="Malaysian" <?= $cuisine == 'Malaysian' ? 'selected' : '' ?>>Malaysian</option>
          <option value="Chinese" <?= $cuisine == 'Chinese' ? 'selected' : '' ?>>Chinese</option>
          <option value="Malay" <?= $cuisine == 'Malay' ? 'selected' : '' ?>>Malay</option>
          <option value="Indian" <?= $cuisine == 'Indian' ? 'selected' : '' ?>>Indian</option>
          <option value="Western" <?= $cuisine == 'Western' ? 'selected' : '' ?>>Western</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="difficulty" class="form-label">Difficulty</label>
        <select name="difficulty" class="form-select">
          <option value="">All Levels</option>
          <option value="easy" <?= $difficulty == 'easy' ? 'selected' : '' ?>>Easy</option>
          <option value="medium" <?= $difficulty == 'medium' ? 'selected' : '' ?>>Medium</option>
          <option value="hard" <?= $difficulty == 'hard' ? 'selected' : '' ?>>Hard</option>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-filter me-2"></i> Apply
        </button>
      </div>
    </div>
  </form>

  <!-- Recipe Cards Grid -->
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if ($recipes->num_rows > 0): ?>
      <?php while ($recipe = $recipes->fetch_assoc()): ?>
        <div class="col">
          <div class="recipe-card card h-100">
            <?php
              $imgStmt = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ? LIMIT 1");
              $imgStmt->bind_param("i", $recipe['id']);
              $imgStmt->execute();
              $imgResult = $imgStmt->get_result()->fetch_assoc();
              $imageUrl = $imgResult && !empty($imgResult['image_url']) 
  ? '/MEAL-PLANNER-APP/' . ltrim($imgResult['image_url'], '/') 
  : '/MEAL-PLANNER-APP/assets/no-image.jpg';


            ?>
            <img src="<?= $imageUrl ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge badge-cuisine rounded-pill"><?= htmlspecialchars($recipe['cuisine']) ?></span>
                <span class="badge badge-difficulty rounded-pill"><?= ucfirst($recipe['difficulty']) ?></span>
              </div>
              <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
              <p class="card-text"><?= htmlspecialchars(substr($recipe['description'], 0, 120)) ?>...</p>
              <div class="d-flex justify-content-between align-items-center mt-auto">
                <small class="text-muted"><i class="far fa-clock me-1"></i> <?= $recipe['prep_time'] + $recipe['cook_time'] ?> mins</small>
                <a href="view-recipe.php?id=<?= $recipe['id'] ?>" class="view-btn">
                  View <i class="fas fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="no-results">
          <i class="far fa-frown-open"></i>
          <h3 class="mb-3">No recipes found</h3>
          <p class="text-muted">Try adjusting your search filters or come back later for new recipes!</p>
          <a href="?" class="btn btn-primary mt-3">Clear Filters</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.recipe-card');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = 1;
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    cards.forEach(card => {
      card.style.opacity = 0;
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.5s ease';
      observer.observe(card);
    });
  });
</script>
</body>
</html>