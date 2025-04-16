<?php
session_start();
require_once '../includes/db.php';

$loggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['user_role'] ?? 'user';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: recipe-management.php");
    exit;
}

$recipe_id = (int) $_GET['id'];

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

function displayIngredientsInTwoColumns($input) {
    $decoded = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $sections = [];
        $current = [];
        foreach ($decoded as $line) {
            if (preg_match('/^[A-Z][^:]*:$/', $line)) {
                if (!empty($current)) {
                    $sections[] = $current;
                }
                $current = [$line];
            } else {
                $current[] = $line;
            }
        }
        if (!empty($current)) {
            $sections[] = $current;
        }

        echo '<div class="row">';
        foreach ($sections as $col) {
            echo '<div class="col-md-6 mb-3">';
            foreach ($col as $index => $item) {
                $escaped = htmlspecialchars($item);
                if ($index === 0 && preg_match('/^[A-Z][^:]*:$/', $item)) {
                    echo '<strong>' . $escaped . '</strong><ul class="mb-2">';
                } elseif ($index === count($col) - 1) {
                    echo '<li>' . $escaped . '</li></ul>';
                } else {
                    echo '<li>' . $escaped . '</li>';
                }
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<pre class="mb-0">' . nl2br(htmlspecialchars($input)) . '</pre>';
    }
}

function displayTextList($input) {
    $decoded = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        foreach ($decoded as $line) {
            $escaped = htmlspecialchars($line);
            $isSectionHeader = preg_match('/^([A-Z][^:]*:)/', $line);
            echo '<div style="margin-bottom: 4px; white-space: pre-wrap;">' .
                ($isSectionHeader ? "<strong>$escaped</strong>" : $escaped) .
                '</div>';
        }
    } else {
        echo '<pre class="mb-0">' . nl2br(htmlspecialchars($input)) . '</pre>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($recipe['title']) ?> | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background-color: #f9fbfd;
      font-family: 'Segoe UI', sans-serif;
    }
    .logo {
      font-family: 'Pacifico', cursive;
      color: #e00000;
    }
    .time-box-group {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .time-box {
      background: #f0f4fa;
      text-align: center;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      color: #c0392b;
      min-width: 150px;
    }
    .gallery {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 10px;
      margin-bottom: 2rem;
    }
    .gallery-img {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 12px;
    }
    .section-title {
      font-weight: 600;
      font-size: 1.2rem;
      margin-bottom: 1rem;
      color: #c0392b;
    }
    .section-box {
      background: #ffffff;
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      margin-bottom: 1.5rem;
    }
    .nutrition-label {
      font-size: 0.95rem;
      background-color: #f9f9f9;
      border: 1px solid #ddd;
      padding: 1rem;
      border-radius: 6px;
    }
    @media print {
      .btn, .navbar, .print-hidden, footer {
        display: none !important;
      }
    }
  </style>
</head>
<>
<?php include '../navbar.php'; ?>
<div class="container py-5">
  <h1 class="logo text-center mb-3"><?= htmlspecialchars($recipe['title']) ?></h1>
  <p class="text-center text-muted mb-1">Chef <?= htmlspecialchars($recipe['username']) ?> | <?= ucfirst($recipe['cuisine']) ?> | <?= ucfirst($recipe['difficulty']) ?></p>

  <div class="text-center mb-4">
    <?php foreach ($tags as $tag): ?>
      <span class="badge bg-primary me-1">#<?= htmlspecialchars($tag) ?></span>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($images)): ?>
  <div class="gallery">
    <?php foreach ($images as $img): ?>
      <img src="<?= $img ?>" class="gallery-img" alt="Recipe Image">
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="section-box">
    <div class="section-title">📝 Description</div>
    <p class="mb-0"><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
  </div>

  <div class="time-box-group">
    <div class="time-box">Prep<br><?= $recipe['prep_time'] ?> mins</div>
    <div class="time-box">Cook<br><?= $recipe['cook_time'] ?> mins</div>
    <div class="time-box">Total<br><?= $recipe['total_time'] ?> mins</div>
  </div>

  <div class="section-box">
    <div class="section-title">🍅 Ingredients</div>
    <div class="mb-0">
      <?php displayIngredientsInTwoColumns($recipe['ingredients']); ?>
    </div>
  </div>

  <div class="section-box">
    <div class="section-title">📝 Instructions</div>
    <div class="mb-0">
      <?php displayTextList($recipe['steps']); ?>
    </div>
  </div>

  <?php if (!empty($recipe['nutrition'])): ?>
  <div class="section-box">
    <div class="section-title">💡 Nutrition Facts</div>
    <div class="nutrition-label">
      <div style="white-space: pre-wrap;"><?= htmlspecialchars($recipe['nutrition']) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between print-hidden">
    <a href="javascript:history.back()" class="btn btn-secondary" style="border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <div>
        <?php if ($loggedIn && ($role === 'admin' || $_SESSION['user_id'] == $recipe['created_by'])): ?>
            <a href="edit-recipe.php?id=<?= $recipe_id ?>" class="btn btn-outline-success me-2" style="border-radius: 8px; padding: 8px 16px;">
                <i class="bi bi-pencil"></i> Edit
            </a>
        <?php endif; ?>
        <button onclick="window.print()" class="btn btn-outline-primary" style="border-radius: 8px; padding: 8px 16px;">
            <i class="bi bi-printer"></i> Print / PDF
        </button>
    </div>
</div>
</div>


<div class="print-hidden">
  <?php include '../includes/footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>