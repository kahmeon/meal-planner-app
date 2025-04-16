<?php
session_start();
require_once '../includes/db.php';

// Handle search/filter
$search = $_GET['search'] ?? '';
$cuisine = isset($_GET['cuisine']) ? (array)$_GET['cuisine'] : [];
$difficulty = isset($_GET['difficulty']) ? (array)$_GET['difficulty'] : [];

$query = "SELECT r.*, 
          (SELECT COUNT(*) FROM saved_recipes sr WHERE sr.recipe_id = r.id AND sr.user_id = ?) as is_saved
          FROM recipes r 
          WHERE r.is_public = 1 AND r.status = 'approved'";
$params = [$_SESSION['user_id'] ?? 0];

if (!empty($search)) {
    $query .= " AND r.title LIKE ?";
    $params[] = "%$search%";
}

if (!empty($cuisine)) {
    $placeholders = implode(',', array_fill(0, count($cuisine), '?'));
    $query .= " AND r.cuisine IN ($placeholders)";
    $params = array_merge($params, $cuisine);
}

if (!empty($difficulty)) {
    $placeholders = implode(',', array_fill(0, count($difficulty), '?'));
    $query .= " AND r.difficulty IN ($placeholders)";
    $params = array_merge($params, $difficulty);
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
      --primary-color: rgb(235, 227, 117);
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
      position: relative;
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

    .save-recipe-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
      z-index: 2;
    }

    .save-recipe-btn:hover {
      background: white;
      transform: scale(1.1);
    }

    .save-recipe-btn.saved {
      color: var(--accent-color);
    }

    .filter-section {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .filter-section h5 {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: var(--text-dark);
    }

    .form-check-label {
      color: var(--text-light);
      cursor: pointer;
    }

    .form-check-input:checked {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
    }

    .filter-header {
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .filter-header::after {
      content: '\f078';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      transition: transform 0.3s;
    }

    .filter-header.collapsed::after {
      transform: rotate(-90deg);
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

      .filter-section {
        padding: 0.75rem;
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
    <a href="saved-recipes.php" class="btn btn-outline-primary">
      <i class="fas fa-bookmark me-2"></i> Saved Recipes
    </a>
  </div>

  <div class="row">
    <!-- Filter Column -->
    <div class="col-lg-3 mb-4">
      <form method="GET" class="filter-bar">
        <div class="search-container mb-3">
          <label for="search" class="form-label">Search Recipes</label>
          <input type="text" name="search" class="form-control ps-4" placeholder="What are you craving today?" value="<?= htmlspecialchars($search) ?>">
          <i class="fas fa-search search-icon"></i>
        </div>
        
        <!-- Cuisine Filter -->
        <div class="filter-section">
          <div class="filter-header" data-bs-toggle="collapse" data-bs-target="#cuisineFilter" aria-expanded="true">
            <h5>Cuisine</h5>
          </div>
          <div class="collapse show" id="cuisineFilter">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="cuisine[]" value="Malaysian" id="cuisineMalaysian" <?= in_array('Malaysian', $cuisine) ? 'checked' : '' ?>>
              <label class="form-check-label" for="cuisineMalaysian">Malaysian</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="cuisine[]" value="Chinese" id="cuisineChinese" <?= in_array('Chinese', $cuisine) ? 'checked' : '' ?>>
              <label class="form-check-label" for="cuisineChinese">Chinese</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="cuisine[]" value="Malay" id="cuisineMalay" <?= in_array('Malay', $cuisine) ? 'checked' : '' ?>>
              <label class="form-check-label" for="cuisineMalay">Malay</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="cuisine[]" value="Indian" id="cuisineIndian" <?= in_array('Indian', $cuisine) ? 'checked' : '' ?>>
              <label class="form-check-label" for="cuisineIndian">Indian</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="cuisine[]" value="Western" id="cuisineWestern" <?= in_array('Western', $cuisine) ? 'checked' : '' ?>>
              <label class="form-check-label" for="cuisineWestern">Western</label>
            </div>
          </div>
        </div>
        
        <!-- Difficulty Filter -->
        <div class="filter-section">
          <div class="filter-header" data-bs-toggle="collapse" data-bs-target="#difficultyFilter" aria-expanded="true">
            <h5>Difficulty</h5>
          </div>
          <div class="collapse show" id="difficultyFilter">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="difficulty[]" value="easy" id="difficultyEasy" <?= in_array('easy', $difficulty) ? 'checked' : '' ?>>
              <label class="form-check-label" for="difficultyEasy">Easy</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="difficulty[]" value="medium" id="difficultyMedium" <?= in_array('medium', $difficulty) ? 'checked' : '' ?>>
              <label class="form-check-label" for="difficultyMedium">Medium</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="difficulty[]" value="hard" id="difficultyHard" <?= in_array('hard', $difficulty) ? 'checked' : '' ?>>
              <label class="form-check-label" for="difficultyHard">Hard</label>
            </div>
          </div>
        </div>
        
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-2"></i> Apply Filters
          </button>
          <a href="?" class="btn btn-outline-secondary">
            <i class="fas fa-times me-2"></i> Clear Filters
          </a>
        </div>
      </form>
    </div>
    
    <!-- Recipe Cards Column -->
    <div class="col-lg-9">
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
                
                <!-- Save Recipe Button -->
                <button class="save-recipe-btn <?= $recipe['is_saved'] ? 'saved' : '' ?>" 
                        data-recipe-id="<?= $recipe['id'] ?>"
                        title="<?= $recipe['is_saved'] ? 'Remove from saved' : 'Save recipe' ?>">
                  <i class="fas fa-bookmark"></i>
                </button>
                
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
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Animation for recipe cards
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

    // Save recipe functionality
    document.addEventListener('DOMContentLoaded', function() {
  // Save recipe functionality
  document.querySelectorAll('.save-recipe-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Get the recipe ID from the button's data attribute
      const recipeId = this.getAttribute('data-recipe-id');
      const isSaved = this.classList.contains('saved'); // Check if the recipe is already saved
      const icon = this.querySelector('i');
      
      // Debug: Log recipeId and action to the console
      console.log('Recipe ID:', recipeId);
      console.log('Action:', isSaved ? 'remove' : 'add');
      
      if (!recipeId || !isSaved) {
        console.error('Missing recipe_id or invalid action');
        return;
      }
      
      const action = isSaved ? 'remove' : 'add'; // If saved, remove; otherwise, add
      
      fetch('saved-recipe.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `recipe_id=${recipeId}&action=${action}`  // Ensure recipe_id and action are passed
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (isSaved) {
            this.classList.remove('saved');
            icon.style.color = '';
            this.setAttribute('title', 'Save recipe');
          } else {
            this.classList.add('saved');
            icon.style.color = 'var(--accent-color)';
            this.setAttribute('title', 'Remove from saved');
          }
        } else {
          console.error('Error in saving/removing recipe: ', data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
    });
  });
});
  });
</script>
</body>
</html>