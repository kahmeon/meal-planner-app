<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle remove from saved action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    if (isset($_POST['recipe_id'])) {
        $recipe_id = $_POST['recipe_id'];
        
        // Delete from saved_recipes table
        $delete_query = "DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $user_id, $recipe_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Recipe removed from saved']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove recipe']);
        }
        exit;
    }
}

// Get all saved recipes for the current user
$query = "SELECT r.* FROM recipes r
          JOIN saved_recipes sr ON r.id = sr.recipe_id
          WHERE sr.user_id = ? AND r.is_public = 1 AND r.status = 'approved'
          ORDER BY r.title ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$saved_recipes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Saved Recipes | NomNomPlan</title>
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

    .remove-saved-btn {
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
      color: var(--secondary-color);
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .remove-saved-btn:hover {
      background: white;
      transform: scale(1.1);
      color: var(--accent-color);
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    }

    .toast {
      border: none;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      overflow: hidden;
    }

    .toast-success {
      background-color: #28a745;
      color: white;
    }

    .toast-error {
      background-color: #dc3545;
      color: white;
    }

    @media (max-width: 768px) {
      .hero-section {
        padding: 2rem 0;
      }
      
      .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="hero-section">
  <div class="container text-center">
    <h1 class="logo mb-3">NomNomPlan</h1>
    <h2 class="page-title">Your Saved Recipes</h2>
    <p class="text-white">All the recipes you've bookmarked in one place</p>
  </div>
</div>

<div class="container mb-5">
  <div class="section-header">
    <h3>My Saved Recipes</h3>
    <div>
      <a href="list-recipes.php" class="btn btn-primary">
        <i class="fas fa-search me-2"></i> Browse More Recipes
      </a>
    </div>
  </div>

  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if ($saved_recipes->num_rows > 0): ?>
      <?php while ($recipe = $saved_recipes->fetch_assoc()): ?>
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
            
            <!-- Remove from Saved Button -->
            <button class="remove-saved-btn" data-recipe-id="<?= $recipe['id'] ?>" title="Remove from saved">
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
          <i class="far fa-bookmark fa-3x mb-3" style="color: #ccc;"></i>
          <h3 class="mb-3">No saved recipes yet</h3>
          <p class="text-muted">Save recipes while browsing to see them here</p>
          <a href="list-recipes.php" class="btn btn-primary mt-3">
            <i class="fas fa-search me-2"></i> Browse Recipes
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Toast Notification -->
<div class="toast-container">
  <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body d-flex justify-content-between align-items-center">
      <span id="toastMessage"></span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap toast
    const toastEl = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    const toast = new bootstrap.Toast(toastEl, {
      autohide: true,
      delay: 3000
    });

    // Function to show toast notification
    function showToast(message, isSuccess) {
      toastEl.classList.remove('toast-success', 'toast-error');
      toastEl.classList.add(isSuccess ? 'toast-success' : 'toast-error');
      toastMessage.textContent = message;
      toast.show();
    }

    // Remove from saved functionality
    document.querySelectorAll('.remove-saved-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const recipeId = this.getAttribute('data-recipe-id');
        const card = this.closest('.col');
        
        if (!recipeId) {
          showToast('Error: Recipe ID is missing', false);
          return;
        }

        // Change icon to loading spinner
        const icon = this.querySelector('i');
        icon.classList.remove('fa-bookmark');
        icon.classList.add('fa-spinner', 'fa-spin');

        fetch('saved-recipes.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `recipe_id=${recipeId}&action=remove`
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            showToast('Recipe removed from saved', true);
            
            // Add fade out animation
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            
            // Remove card after animation
            setTimeout(() => {
              card.remove();
              
              // Show no results message if last card
              if (document.querySelectorAll('.col').length === 0) {
                const noResults = `
                  <div class="col-12">
                    <div class="no-results">
                      <i class="far fa-bookmark fa-3x mb-3" style="color: #ccc;"></i>
                      <h3 class="mb-3">No saved recipes left</h3>
                      <p class="text-muted">Save recipes while browsing to see them here</p>
                      <a href="list-recipes.php" class="btn btn-primary mt-3">
                        <i class="fas fa-search me-2"></i> Browse Recipes
                      </a>
                    </div>
                  </div>
                `;
                document.querySelector('.row').innerHTML = noResults;
              }
            }, 300);
          } else {
            showToast('Error: ' + data.message, false);
            // Revert icon if failed
            icon.classList.remove('fa-spinner', 'fa-spin');
            icon.classList.add('fa-bookmark');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred. Please try again.', false);
          // Revert icon if error
          icon.classList.remove('fa-spinner', 'fa-spin');
          icon.classList.add('fa-bookmark');
        });
      });
    });

    // Animation for recipe cards
    const cards = document.querySelectorAll('.recipe-card');
    cards.forEach((card, index) => {
      card.style.opacity = 0;
      card.style.transform = 'translateY(20px)';
      card.style.transition = `all 0.5s ease ${index * 0.1}s`;
      
      setTimeout(() => {
        card.style.opacity = 1;
        card.style.transform = 'translateY(0)';
      }, 100);
    });
  });
</script>
</body>
</html>