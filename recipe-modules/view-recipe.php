<?php
session_start();
require_once '../includes/db.php';

$loggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['user_role'] ?? 'user';
$user_id = $_SESSION['user_id'] ?? null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: recipe-management.php");
    exit;
}

$recipe_id = (int) $_GET['id'];

// Check if user has saved this recipe
$isSaved = false;
if ($loggedIn) {
    $saveCheck = $conn->prepare("SELECT 1 FROM saved_recipes WHERE user_id = ? AND recipe_id = ? LIMIT 1");
    $saveCheck->bind_param("ii", $user_id, $recipe_id);
    $saveCheck->execute();
    $isSaved = $saveCheck->get_result()->num_rows > 0;
    $saveCheck->close();
}

// Handle save/unsave action
if ($loggedIn && isset($_POST['save_action'])) {
    if ($_POST['save_action'] === 'save') {
        $saveStmt = $conn->prepare("INSERT INTO saved_recipes (user_id, recipe_id) VALUES (?, ?)");
        $saveStmt->bind_param("ii", $user_id, $recipe_id);
        $saveStmt->execute();
        $saveStmt->close();
        $isSaved = true;
    } elseif ($_POST['save_action'] === 'unsave') {
        $unsaveStmt = $conn->prepare("DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?");
        $unsaveStmt->bind_param("ii", $user_id, $recipe_id);
        $unsaveStmt->execute();
        $unsaveStmt->close();
        $isSaved = false;
    }
}

$query = $conn->prepare("SELECT recipes.*, users.name AS username FROM recipes LEFT JOIN users ON recipes.created_by = users.id WHERE recipes.id = ?");
$query->bind_param("i", $recipe_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger m-5'>Recipe not found.</div>";
    exit;
}
$recipe = $result->fetch_assoc();

$tagStmt = $conn->prepare("SELECT tags.name FROM tags JOIN recipe_tags ON tags.id = recipe_tags.tag_id WHERE recipe_tags.recipe_id = ?");
$tagStmt->bind_param("i", $recipe_id);
$tagStmt->execute();
$tagResult = $tagStmt->get_result();
$tags = [];
while ($row = $tagResult->fetch_assoc()) {
    $tags[] = $row['name'];
}
$tagStmt->close();

$imageStmt = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ?");
$imageStmt->bind_param("i", $recipe_id);
$imageStmt->execute();
$imageResult = $imageStmt->get_result();
$images = [];
while ($img = $imageResult->fetch_assoc()) {
    $images[] = $img['image_url'];
}
$imageStmt->close();

function displayIngredients($input) {
    if (empty(trim($input))) {
        echo '<div class="alert alert-info mb-0">No ingredients listed.</div>';
        return;
    }

    $decoded = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $sections = [];
        $current = [];
        foreach ($decoded as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;
            
            if (preg_match('/^[A-Z][^:]*:$/', $trimmed)) {
                if (!empty($current)) {
                    $sections[] = $current;
                }
                $current = [$trimmed];
            } else {
                $current[] = $trimmed;
            }
        }
        if (!empty($current)) {
            $sections[] = $current;
        }

        if (!empty($sections)) {
            echo '<div class="row">';
            foreach ($sections as $col) {
                echo '<div class="col-md-6 mb-3">';
                foreach ($col as $index => $item) {
                    $escaped = htmlspecialchars($item);
                    if ($index === 0 && preg_match('/^[A-Z][^:]*:$/', $item)) {
                        echo '<h5 class="ingredient-section-title mb-3">' . $escaped . '</h5><ul class="ingredient-list mb-4">';
                    } elseif ($index === count($col) - 1) {
                        echo '<li class="ingredient-item">' . $escaped . '</li></ul>';
                    } else {
                        echo '<li class="ingredient-item">' . $escaped . '</li>';
                    }
                }
                echo '</div>';
            }
            echo '</div>';
            return;
        }
    }
    
    echo '<div class="ingredients-plain">';
    echo nl2br(htmlspecialchars($input));
    echo '</div>';
}

function displayInstructions($input) {
    if (empty(trim($input))) {
        echo '<div class="alert alert-info mb-0">No instructions provided.</div>';
        return;
    }

    $decoded = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $stepCount = 1;
        $hasContent = false;
        
        foreach ($decoded as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;
            
            $hasContent = true;
            $escaped = htmlspecialchars($trimmed);
            $isSectionHeader = preg_match('/^([A-Z][^:]*:)/', $trimmed);
            
            if ($isSectionHeader) {
                echo '<h5 class="instruction-header mt-4 mb-3">'.$escaped.'</h5>';
            } else {
                echo '<div class="step-item d-flex mb-3">
                        <div class="step-number me-3">'.$stepCount.'</div>
                        <div class="step-content">'.$escaped.'</div>
                      </div>';
                $stepCount++;
            }
        }
        
        if ($hasContent) return;
    }
    
    echo '<div class="instructions-plain">';
    echo nl2br(htmlspecialchars($input));
    echo '</div>';
}

function formatNutritionFacts($nutrition) {
    if (empty(trim($nutrition))) {
        return '<div class="alert alert-info mb-0">No nutrition information provided.</div>';
    }

    $lines = array_filter(explode("\n", $nutrition), function($line) {
        return !empty(trim($line));
    });

    $formatted = '<div class="nutrition-facts">';
    foreach ($lines as $line) {
        $parts = explode(':', $line, 2);
        if (count($parts) === 2) {
            $label = trim($parts[0]);
            $value = trim($parts[1]);
            $formatted .= '<div class="nutrition-row d-flex justify-content-between">
                            <span class="nutrition-label">'.$label.':</span>
                            <span class="nutrition-value">'.$value.'</span>
                          </div>';
        } else {
            $formatted .= '<div class="nutrition-plain">'.htmlspecialchars($line).'</div>';
        }
    }
    $formatted .= '</div>';
    
    return $formatted;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($recipe['title']) ?> | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color:rgb(220, 18, 18);
      --secondary-color:rgb(255, 11, 137);
      --dark-color: #292f36;
      --light-color: #f7fff7;
      --accent-color: #ffd166;
    }
    
    body {
      font-family: 'Montserrat', sans-serif;
      color: var(--dark-color);
      background-color: #f8f9fa;
      line-height: 1.6;
    }
    
    .logo {
      font-family: 'Pacifico', cursive;
      color: var(--primary-color);
    }
    
    .recipe-header {
      margin-bottom: 2rem;
      text-align: center;
      padding-bottom: 1rem;
      border-bottom: 2px solid var(--primary-color);
    }
    
    .recipe-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .section-box {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .section-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: var(--primary-color);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .recipe-slider {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
    }
    
    .recipe-slider img {
      width: 100%;
      height: 400px;
      object-fit: contain;
      background-color: #f8f9fa;
      cursor: zoom-in;
    }
    
    .swiper-button-next, .swiper-button-prev {
      color: white;
      background: rgba(0, 0, 0, 0.3);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      backdrop-filter: blur(4px);
    }
    
    .swiper-button-next::after, .swiper-button-prev::after {
      font-size: 1.2rem;
    }
    
    .recipe-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .recipe-meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
      color: #6c757d;
    }
    
    .recipe-meta-item i {
      color: var(--primary-color);
    }
    
    .tag-badge {
      display: inline-block;
      background-color: #e9ecef;
      padding: 0.35rem 0.75rem;
      border-radius: 50px;
      font-size: 0.8rem;
      margin-right: 0.5rem;
      margin-bottom: 0.5rem;
      color: #495057;
    }
    
    .time-box {
      text-align: center;
      padding: 1rem;
      border-radius: 8px;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .time-box span {
      display: block;
      font-size: 1.5rem;
      font-weight: 700;
    }
    
    .ingredient-section-title {
      color: var(--secondary-color);
      font-weight: 600;
      font-size: 1.2rem;
    }
    
    .ingredient-list {
      list-style-type: none;
      padding-left: 0;
    }
    
    .ingredient-item {
      padding: 0.5rem 0;
      border-bottom: 1px dashed #dee2e6;
      position: relative;
      padding-left: 1.5rem;
    }
    
    .ingredient-item:before {
      content: "•";
      color: var(--primary-color);
      position: absolute;
      left: 0;
    }
    
    .step-item {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      transition: all 0.2s ease;
    }
    
    .step-item:hover {
      background: white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      transform: translateY(-2px);
    }
    
    .step-number {
      background: var(--primary-color);
      color: white;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }
    
    .instruction-header {
      font-weight: 600;
      color: var(--secondary-color);
      margin-top: 2rem;
      margin-bottom: 1rem;
      font-size: 1.2rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--secondary-color);
    }
    
    .nutrition-facts {
      background: white;
      border: 1px solid #dee2e6;
      padding: 1.5rem;
      border-radius: 8px;
      font-family: 'Montserrat', sans-serif;
    }
    
    .nutrition-row {
      padding: 0.5rem 0;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .nutrition-row:last-child {
      border-bottom: none;
    }
    
    .nutrition-label {
      font-weight: 600;
      color: var(--dark-color);
    }
    
    .nutrition-value {
      color: var(--primary-color);
      font-weight: 500;
    }
    
    .btn-hover-effect {
      transition: all 0.3s ease;
      border-width: 2px;
    }
    
    .btn-hover-effect:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-outline-primary {
      border-color: var(--primary-color);
      color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-outline-secondary {
      border-color: var(--dark-color);
      color: var(--dark-color);
    }
    
    .btn-outline-secondary:hover {
      background-color: var(--dark-color);
      border-color: var(--dark-color);
      color: white;
    }
    
    .btn-outline-success {
      border-color: var(--secondary-color);
      color: var(--secondary-color);
    }
    
    .btn-outline-success:hover {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
      color: white;
    }
    
    .btn-save-recipe {
      border-color: var(--accent-color);
      color: var(--dark-color);
    }
    
    .btn-save-recipe:hover, .btn-save-recipe.saved {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
      color: var(--dark-color);
    }
    
    .btn-save-recipe i {
      transition: all 0.3s ease;
    }
    
    .btn-save-recipe.saved i {
      transform: scale(1.2);
    }
    
    /* Fullscreen image viewer */
    .image-viewer-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      z-index: 9999;
      display: none;
      justify-content: center;
      align-items: center;
    }
    
    .image-viewer-container {
      position: relative;
      max-width: 90%;
      max-height: 90%;
    }
    
    .image-viewer-img {
      max-width: 100%;
      max-height: 80vh;
      object-fit: contain;
    }
    
    .image-viewer-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-size: 24px;
      cursor: pointer;
      z-index: 10000;
      transition: all 0.3s ease;
    }
    
    .image-viewer-nav:hover {
      background: rgba(255, 255, 255, 0.4);
    }
    
    .image-viewer-prev {
      left: 20px;
    }
    
    .image-viewer-next {
      right: 20px;
    }
    
    .image-viewer-close {
      position: absolute;
      top: 20px;
      right: 20px;
      color: white;
      font-size: 30px;
      cursor: pointer;
      z-index: 10000;
    }
    
    .image-viewer-counter {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      color: white;
      font-size: 16px;
      background: rgba(0, 0, 0, 0.5);
      padding: 5px 15px;
      border-radius: 20px;
    }
    
    @media print {
      .print-hidden {
        display: none !important;
      }
      
      .section-box {
        box-shadow: none;
        border: none;
        page-break-inside: avoid;
      }
      
      body {
        font-size: 12pt;
        background: none;
        color: black;
      }
      
      .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 2rem;
      }
      
      .print-meta {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 1rem;
      }
    }
    
    @media (max-width: 768px) {
      .recipe-header h1 {
        font-size: 2rem;
      }
      
      .recipe-slider img {
        height: 300px;
      }
      
      .time-box span {
        font-size: 1.2rem;
      }
      
      .image-viewer-nav {
        width: 40px;
        height: 40px;
        font-size: 20px;
      }
      
      .image-viewer-prev {
        left: 10px;
      }
      
      .image-viewer-next {
        right: 10px;
      }
    }
  </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<!-- Fullscreen Image Viewer -->
<div class="image-viewer-overlay" id="imageViewer">
  <div class="image-viewer-container">
    <span class="image-viewer-close" id="closeViewer">&times;</span>
    <div class="image-viewer-nav image-viewer-prev" id="prevImage"><i class="bi bi-chevron-left"></i></div>
    <img class="image-viewer-img" id="viewerImage" src="">
    <div class="image-viewer-nav image-viewer-next" id="nextImage"><i class="bi bi-chevron-right"></i></div>
    <div class="image-viewer-counter" id="imageCounter"></div>
  </div>
</div>

<div class="container py-5">
  <div class="print-header print-hidden" style="display: none;">
    <h1 class="logo"><?= htmlspecialchars($recipe['title']) ?></h1>
    <div class="print-meta">
      <?= date('F j, Y') ?> | Printed from NomNomPlan
    </div>
  </div>

  <div class="recipe-header print-hidden">
    <h1 class="logo"><?= htmlspecialchars($recipe['title']) ?></h1>
    <p class="text-muted"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
  </div>

  <div class="row recipe-top-section mb-5">
    <div class="col-lg-8">
      <?php if (!empty($images)): ?>
        <div class="swiper recipe-slider print-hidden">
          <div class="swiper-wrapper">
            <?php foreach ($images as $index => $img): ?>
              <div class="swiper-slide">
                <img src="/meal-planner-app/<?= ltrim($img, '/') ?>" 
                     alt="<?= htmlspecialchars($recipe['title']) ?>" 
                     class="recipe-image" 
                     data-index="<?= $index ?>"
                     data-src="/meal-planner-app/<?= ltrim($img, '/') ?>">
              </div>
            <?php endforeach; ?>
          </div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
        </div>
        <div class="d-none d-print-block">
          <img src="<?= $images[0] ?? '' ?>" alt="<?= htmlspecialchars($recipe['title']) ?>" style="max-width: 100%; height: auto;">
        </div>
      <?php else: ?>
        <div class="bg-light rounded print-hidden" style="height: 400px; display: flex; align-items: center; justify-content: center;">
          <div class="text-muted">No images available</div>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-4 recipe-details mt-4 mt-lg-0">
      <div class="section-box h-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="recipe-meta-item">
            <i class="bi bi-person"></i> <?= htmlspecialchars($recipe['username']) ?>
          </div>
          <div class="recipe-meta-item">
            <i class="bi bi-clock"></i> <?= $recipe['total_time'] ?> mins
          </div>
        </div>
        
        <div class="mb-4">
          <div class="recipe-meta-item mb-2">
            <i class="bi bi-globe"></i> <strong>Cuisine:</strong> <?= ucfirst($recipe['cuisine']) ?>
          </div>
          <div class="recipe-meta-item">
            <i class="bi bi-speedometer2"></i> <strong>Difficulty:</strong> <?= ucfirst($recipe['difficulty']) ?>
          </div>
        </div>

        <?php if (!empty($tags)): ?>
        <div class="mb-4">
          <h6 class="mb-2"><i class="bi bi-tags"></i> Tags</h6>
          <div>
            <?php foreach ($tags as $tag): ?>
              <span class="tag-badge">#<?= htmlspecialchars($tag) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="row g-3 mt-4">
          <div class="col-4">
            <div class="time-box">
              <span><?= $recipe['prep_time'] ?></span>
              Prep
            </div>
          </div>
          <div class="col-4">
            <div class="time-box">
              <span><?= $recipe['cook_time'] ?></span>
              Cook
            </div>
          </div>
          <div class="col-4">
            <div class="time-box">
              <span><?= $recipe['servings'] ?? 'N/A' ?></span>
              Serves
            </div>
          </div>
        </div>
        
        <?php if ($loggedIn): ?>
        <form method="post" class="mt-4">
          <button type="submit" name="save_action" value="<?= $isSaved ? 'unsave' : 'save' ?>" 
                  class="btn btn-save-recipe btn-hover-effect w-100 <?= $isSaved ? 'saved' : '' ?>">
            <i class="bi bi-bookmark<?= $isSaved ? '-fill' : '' ?>"></i> 
            <?= $isSaved ? 'Saved' : 'Save Recipe' ?>
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="section-box">
        <h2 class="section-title"><i class="bi bi-list-check"></i> Ingredients</h2>
        <?php displayIngredients($recipe['ingredients']); ?>
      </div>

      <div class="section-box">
        <h2 class="section-title"><i class="bi bi-list-ol"></i> Instructions</h2>
        <?php displayInstructions($recipe['steps']); ?>
      </div>
    </div>
    
    <div class="col-lg-4">
      <?php if (!empty($recipe['notes'])): ?>
      <div class="section-box">
        <h2 class="section-title"><i class="bi bi-lightbulb"></i> Chef's Notes</h2>
        <div class="p-3 bg-light rounded">
          <?= nl2br(htmlspecialchars($recipe['notes'])) ?>
        </div>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($recipe['nutrition'])): ?>
      <div class="section-box">
        <h2 class="section-title"><i class="bi bi-clipboard2-pulse"></i> Nutrition</h2>
        <?= formatNutritionFacts($recipe['nutrition']) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex justify-content-between print-hidden mt-4">
    <a href="recipe-management.php" class="btn btn-outline-secondary btn-hover-effect">
      <i class="bi bi-arrow-left"></i> Back to Recipes
    </a>
    <div>
    <?php if ($loggedIn && ($_SESSION['user_id'] == $recipe['created_by'] || $role === 'admin')): ?>
        <a href="edit-recipe.php?id=<?= $recipe_id ?>" class="btn btn-outline-success me-2 btn-hover-effect">
          <i class="bi bi-pencil"></i> Edit Recipe
        </a>
      <?php endif; ?>
      <button onclick="window.print()" class="btn btn-outline-primary btn-hover-effect">
        <i class="bi bi-printer"></i> Print Recipe
      </button>
    </div>
  </div>
</div>

<div class="print-hidden">
  <?php include '../includes/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  // Initialize Swiper
  const swiper = new Swiper('.recipe-slider', {
    loop: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    autoplay: {
      delay: 5000,
    },
  });
  
  // Add animation to step items when they come into view
  const stepItems = document.querySelectorAll('.step-item');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = 1;
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });
  
  stepItems.forEach(item => {
    item.style.opacity = 0;
    item.style.transform = 'translateY(20px)';
    item.style.transition = 'all 0.4s ease';
    observer.observe(item);
  });
  
  // Add animation to save button
  const saveBtn = document.querySelector('.btn-save-recipe');
  if (saveBtn) {
    saveBtn.addEventListener('click', function() {
      this.classList.toggle('saved');
      const icon = this.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-bookmark');
        icon.classList.toggle('bi-bookmark-fill');
      }
    });
  }
  
  // Fullscreen Image Viewer
  const imageViewer = document.getElementById('imageViewer');
  const viewerImage = document.getElementById('viewerImage');
  const closeViewer = document.getElementById('closeViewer');
  const prevImage = document.getElementById('prevImage');
  const nextImage = document.getElementById('nextImage');
  const imageCounter = document.getElementById('imageCounter');
  
  let currentImageIndex = 0;
  const recipeImages = Array.from(document.querySelectorAll('.recipe-image'));
  const imageUrls = recipeImages.map(img => img.dataset.src);
  
  // Open image viewer when clicking on an image
  recipeImages.forEach((img, index) => {
    img.addEventListener('click', () => {
      currentImageIndex = index;
      updateViewerImage();
      imageViewer.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    });
  });
  
  // Close image viewer
  closeViewer.addEventListener('click', () => {
    imageViewer.style.display = 'none';
    document.body.style.overflow = 'auto';
  });
  
  // Navigate between images
  prevImage.addEventListener('click', (e) => {
    e.stopPropagation();
    currentImageIndex = (currentImageIndex - 1 + imageUrls.length) % imageUrls.length;
    updateViewerImage();
  });
  
  nextImage.addEventListener('click', (e) => {
    e.stopPropagation();
    currentImageIndex = (currentImageIndex + 1) % imageUrls.length;
    updateViewerImage();
  });
  
  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (imageViewer.style.display === 'flex') {
      if (e.key === 'Escape') {
        imageViewer.style.display = 'none';
        document.body.style.overflow = 'auto';
      } else if (e.key === 'ArrowLeft') {
        currentImageIndex = (currentImageIndex - 1 + imageUrls.length) % imageUrls.length;
        updateViewerImage();
      } else if (e.key === 'ArrowRight') {
        currentImageIndex = (currentImageIndex + 1) % imageUrls.length;
        updateViewerImage();
      }
    }
  });
  
  // Update the viewer image and counter
  function updateViewerImage() {
    viewerImage.src = imageUrls[currentImageIndex];
    imageCounter.textContent = `${currentImageIndex + 1} / ${imageUrls.length}`;
    
    // Preload adjacent images
    const prevIndex = (currentImageIndex - 1 + imageUrls.length) % imageUrls.length;
    const nextIndex = (currentImageIndex + 1) % imageUrls.length;
    
    const preloadPrev = new Image();
    preloadPrev.src = imageUrls[prevIndex];
    
    const preloadNext = new Image();
    preloadNext.src = imageUrls[nextIndex];
  }
  
  // Close when clicking outside the image
  imageViewer.addEventListener('click', (e) => {
    if (e.target === imageViewer) {
      imageViewer.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });
</script>
</body>
</html>