<?php
session_start();
require_once '../includes/db.php';

// Validate competition ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger text-center mt-5'>Invalid competition ID.</div>";
    exit;
}

$competition_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'] ?? null;

// Fetch competition info
$compStmt = $conn->prepare("SELECT * FROM competitions WHERE competition_id = ?");
$compStmt->bind_param("i", $competition_id);
$compStmt->execute();
$compResult = $compStmt->get_result();

if ($compResult->num_rows === 0) {
    echo "<div class='alert alert-danger text-center mt-5'>Competition not found.</div>";
    exit;
}

$competition = $compResult->fetch_assoc();

$entries = null;
$entriesError = false;

try {
    $entriesStmt = $conn->prepare("SELECT r.*, u.name AS author_name, u.avatar_url AS author_avatar,
        (SELECT vote FROM recipe_votes WHERE recipe_id = r.id AND user_id = ?) AS user_vote,
        (SELECT COUNT(*) FROM recipe_votes WHERE recipe_id = r.id AND vote = 1) AS upvotes,
        (SELECT COUNT(*) FROM recipe_votes WHERE recipe_id = r.id AND vote = -1) AS downvotes,
        (SELECT image_url FROM recipe_images WHERE recipe_id = r.id LIMIT 1) AS image_url
        FROM competition_entries e
        JOIN recipes r ON e.recipe_id = r.id
        JOIN users u ON r.created_by = u.id
        WHERE e.competition_id = ? AND e.status = 'approved'");

    if (!$entriesStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $entriesStmt->bind_param("ii", $user_id, $competition_id);
    
    if (!$entriesStmt->execute()) {
        throw new Exception("Execute failed: " . $entriesStmt->error);
    }
    
    $entries = $entriesStmt->get_result();
    
    if (!$entries) {
        throw new Exception("Get result failed: " . $entriesStmt->error);
    }
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $entriesError = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote for Entries | NomNomPlan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e00000;
            --secondary-color: #4ECDC4;
            --light-bg: #F7FFF7;
            --dark-color: #292F36;
            --gold-color: #FFD700;
            --accent-color: #FF6B6B;
            --light-gray: #F8F9FA;
            --medium-gray: #E9ECEF;
            --dark-gray: #6C757D;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .page-header {
            background: linear-gradient(135deg, rgba(230,57,70,0.1), rgba(78,205,196,0.1));
            border-radius: 16px;
            padding: 3rem;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .recipe-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            background: white;
        }

        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card-img-top {
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .recipe-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .vote-section {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 1rem;
        }

        .btn-vote {
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-upvote {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }

        .btn-upvote:hover, .btn-upvote:active {
            background-color: #36b2a4;
            transform: translateY(-2px);
        }

        .btn-downvote {
            background-color: #f8f9fa;
            color: var(--dark-color);
            border: 1px solid #dee2e6;
        }

        .btn-downvote:hover, .btn-downvote:active {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .btn-view {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .btn-view:hover {
            background-color: #c00000;
            transform: translateY(-2px);
        }

        .empty-state {
            background-color: white;
            padding: 3rem;
            text-align: center;
            border: 2px dashed #D9D9D9;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        /* Modal styles */
        .modal-recipe-img {
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recipe-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: var(--light-gray);
            border-radius: 8px;
        }

        .recipe-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .recipe-detail-card {
            background-color: var(--light-gray);
            padding: 1.25rem;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .recipe-detail-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .recipe-detail-card i {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .recipe-detail-card h6 {
            color: var(--dark-gray);
            font-weight: 600;
        }

        .recipe-detail-card p {
            font-weight: 700;
            margin-bottom: 0;
            color: var(--dark-color);
        }

        .vote-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--gold-color);
            color: var(--dark-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.8rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 1;
        }

        .voted {
            opacity: 0.7;
            transform: scale(0.95);
        }
        
        /* Enhanced recipe view styles */
        .ingredient-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 0.75rem;
        }
        
        .ingredient-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            margin-bottom: 0;
            background-color: white;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: 1px solid var(--medium-gray);
            position: relative;
        }
        
        .ingredient-item:hover {
            background-color: var(--light-gray);
            transform: translateX(5px);
            border-color: var(--secondary-color);
        }
        
        .ingredient-checkbox {
            margin-right: 1rem;
            cursor: pointer;
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--secondary-color);
        }
        
        .ingredient-text {
            flex-grow: 1;
            font-size: 0.95rem;
        }
        
        .ingredient-checkbox:checked + .ingredient-text {
            text-decoration: line-through;
            color: var(--dark-gray);
        }
        
        .instruction-step {
            display: flex;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background-color: white;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: 1px solid var(--medium-gray);
            position: relative;
            overflow: hidden;
        }
        
        .instruction-step:hover {
            background-color: var(--light-gray);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .step-number {
            min-width: 36px;
            height: 36px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-weight: bold;
            flex-shrink: 0;
            font-size: 1.1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .step-content {
            flex-grow: 1;
            font-size: 0.95rem;
        }
        
        .timer-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            margin-left: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .timer-btn:hover {
            color: #c00000;
            transform: scale(1.1);
        }
        
        .tab-content {
            padding: 1.5rem 0;
        }
        
        .nav-tabs {
            border-bottom: 2px solid var(--medium-gray);
        }
        
        .nav-tabs .nav-link {
            color: var(--dark-gray);
            font-weight: 600;
            border: none;
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
            background-color: var(--light-gray);
        }
        
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: var(--primary-color);
            background-color: white;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .nutrition-badge {
            display: inline-block;
            padding: 0.5em 0.8em;
            font-size: 0.85em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50px;
            background-color: var(--secondary-color);
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .recipe-description {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--medium-gray);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            line-height: 1.7;
        }
        
        .recipe-description p:last-child {
            margin-bottom: 0;
        }
        
        .clear-checkboxes {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--dark-gray);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .clear-checkboxes:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .recipe-tag {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            line-height: 1;
            color: var(--dark-color);
            text-align: center;
            background-color: var(--light-gray);
            border-radius: 50px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .recipe-tag i {
            margin-right: 0.25rem;
            color: var(--dark-gray);
        }
        
        .recipe-header {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .recipe-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            color: white;
            padding: 2rem 1.5rem 1rem;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        @media print {
            .modal-header, .modal-footer, .print-btn {
                display: none !important;
            }
            
            body, .modal-content {
                padding: 0;
                margin: 0;
                box-shadow: none;
                border: none;
            }
            
            .modal-body {
                padding: 0 !important;
            }
            
            .recipe-meta, .recipe-details {
                margin-bottom: 1rem !important;
            }
            
            .recipe-detail-card {
                padding: 0.5rem !important;
            }
            
            .ingredient-list {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .ingredient-list {
                grid-template-columns: 1fr !important;
            }
            
            .recipe-details {
                grid-template-columns: 1fr !important;
            }
            
            .page-header {
                padding: 2rem 1rem;
            }
            
            .instruction-step {
                flex-direction: column;
            }
            
            .step-number {
                margin-right: 0;
                margin-bottom: 1rem;
            }
        }
        
        /* Animation for voting */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .vote-animation {
            animation: pulse 0.5s ease;
        }
    </style>
</head>
<body class="bg-light">

<?php include '../navbar.php'; ?>

<div class="container py-5">
    <div class="page-header">
        <h1 class="display-5 fw-bold">🏆 Vote for Entries</h1>
        <p class="lead">Help decide the winner for: <strong><?= htmlspecialchars($competition['title']) ?></strong></p>
        <p class="text-muted">Browse the recipes and vote for your favorites!</p>
    </div>

    <?php if (!$entriesError && $entries && $entries->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($entry = $entries->fetch_assoc()): ?>
                <?php
                $displayImage = (!empty($entry['image_url'])) 
                    ? '../uploads/recipes/' . htmlspecialchars(basename($entry['image_url']))
                    : '../assets/no-image.jpg';
                
                $upvoted = $entry['user_vote'] == 1;
                $downvoted = $entry['user_vote'] == -1;
                
                // Parse ingredients and instructions
                $ingredients = array_filter(explode("\n", $entry['ingredients'] ?? ''), function($item) {
                    return trim($item) !== '';
                });
                
                $instructions = array_filter(explode("\n", $entry['instructions'] ?? ''), function($item) {
                    return trim($item) !== '';
                });
                
                // Parse nutrition if available
                $nutrition = [];
                if (!empty($entry['nutrition_info'])) {
                    $nutrition = json_decode($entry['nutrition_info'], true) ?: [];
                }
                
                // Parse tags if available
                $tags = [];
                if (!empty($entry['tags'])) {
                    $tags = explode(',', $entry['tags']);
                }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="recipe-card card h-100">
                        <div class="position-relative">
                            <img src="<?= $displayImage ?>" class="card-img-top" alt="<?= htmlspecialchars($entry['title']) ?>">
                            <?php if ($upvoted || $downvoted): ?>
                                <span class="vote-badge">
                                    <?= $upvoted ? '👍 Voted' : '👎 Voted' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($entry['title']) ?></h5>
                            <div class="d-flex align-items-center mb-2">
                                <img src="<?= htmlspecialchars($entry['author_avatar'] ?? '../assets/default-avatar.jpg') ?>" 
                                     class="author-avatar me-2" 
                                     alt="<?= htmlspecialchars($entry['author_name']) ?>">
                                <span class="text-muted">By <?= htmlspecialchars($entry['author_name']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-hand-thumbs-up"></i> <?= $entry['upvotes'] ?>
                                </span>
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-hand-thumbs-down"></i> <?= $entry['downvotes'] ?>
                                </span>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-clock"></i> <?= date('M j, Y', strtotime($entry['created_at'])) ?>
                                </span>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-view flex-grow-1" data-bs-toggle="modal" data-bs-target="#recipeModal<?= $entry['id'] ?>">
                                    <i class="bi bi-eye"></i> View Recipe
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recipe Modal -->
                <div class="modal fade" id="recipeModal<?= $entry['id'] ?>" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="recipeModalLabel"><?= htmlspecialchars($entry['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="recipe-header">
                                    <img src="<?= $displayImage ?>" class="modal-recipe-img w-100" alt="<?= htmlspecialchars($entry['title']) ?>">
                                    <h2 class="recipe-title"><?= htmlspecialchars($entry['title']) ?></h2>
                                </div>
                                
                                <div class="recipe-meta">
                                    <img src="<?= htmlspecialchars($entry['author_avatar'] ?? '../assets/default-avatar.jpg') ?>" 
                                         class="author-avatar" 
                                         alt="<?= htmlspecialchars($entry['author_name']) ?>">
                                    <div>
                                        <h6>By <?= htmlspecialchars($entry['author_name']) ?></h6>
                                        <small class="text-muted">Submitted <?= date('F j, Y', strtotime($entry['created_at'])) ?></small>
                                    </div>
                                </div>
                                
                                <?php if (!empty($tags)): ?>
                                <div class="mb-4">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="recipe-tag">
                                            <i class="bi bi-tag"></i><?= htmlspecialchars(trim($tag)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="recipe-details">
                                    <div class="recipe-detail-card">
                                        <i class="bi bi-clock"></i>
                                        <h6>Prep Time</h6>
                                        <p><?= htmlspecialchars($entry['prep_time'] ?? 'N/A') ?> mins</p>
                                    </div>
                                    <div class="recipe-detail-card">
                                        <i class="bi bi-fire"></i>
                                        <h6>Cook Time</h6>
                                        <p><?= htmlspecialchars($entry['cook_time'] ?? 'N/A') ?> mins</p>
                                    </div>
                                    <div class="recipe-detail-card">
                                        <i class="bi bi-people"></i>
                                        <h6>Servings</h6>
                                        <p><?= htmlspecialchars($entry['servings'] ?? 'N/A') ?></p>
                                    </div>
                                    <div class="recipe-detail-card">
                                        <i class="bi bi-bar-chart"></i>
                                        <h6>Difficulty</h6>
                                        <p><?= htmlspecialchars($entry['difficulty'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                                
                                <div class="recipe-description">
                                    <h5 class="mb-3"><i class="bi bi-journal-text"></i> Description</h5>
                                    <p><?= nl2br(htmlspecialchars($entry['description'] ?? 'No description available')) ?></p>
                                </div>
                                
                                <?php if (!empty($nutrition)): ?>
                                <div class="mb-4 p-3 bg-white rounded border">
                                    <h5 class="mb-3"><i class="bi bi-nutrition"></i> Nutrition Information</h5>
                                    <div>
                                        <?php foreach ($nutrition as $key => $value): ?>
                                            <span class="nutrition-badge">
                                                <?= htmlspecialchars(ucfirst($key)) ?>: <?= htmlspecialchars($value) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <ul class="nav nav-tabs" id="recipeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="ingredients-tab" data-bs-toggle="tab" data-bs-target="#ingredients-<?= $entry['id'] ?>" type="button" role="tab" aria-controls="ingredients" aria-selected="true">
                                            <i class="bi bi-cart3"></i> Ingredients
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="instructions-tab" data-bs-toggle="tab" data-bs-target="#instructions-<?= $entry['id'] ?>" type="button" role="tab" aria-controls="instructions" aria-selected="false">
                                            <i class="bi bi-list-ol"></i> Instructions
                                        </button>
                                    </li>
                                    <?php if (!empty($entry['notes'])): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-<?= $entry['id'] ?>" type="button" role="tab" aria-controls="notes" aria-selected="false">
                                            <i class="bi bi-lightbulb"></i> Notes
                                        </button>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                                
                                <div class="tab-content" id="recipeTabContent">
                                    <!-- Ingredients Tab -->

<!-- Ingredients Tab -->
<!-- Ingredients Tab -->
<div class="tab-pane fade show active" id="ingredients-<?= $entry['id'] ?>" role="tabpanel" aria-labelledby="ingredients-tab">
    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle"></i> Ingredients list for your dish.
    </div>

    <?php 
    // Ingredients text array (example data provided)
    $ingredients = ["For the Blue Rice:", "2 cups white rice (Jasmine or Basmati)", "10 dried butterfly pea flowers (bunga telang)", "2 cups water", "1 pandan leaf (knotted)", "1/2 tsp salt", "For the Garnishes:", "1 cup bean sprouts (blanched)", "1/2 cup shredded cabbage", "1/2 cup finely sliced torch ginger flower (bunga kantan)", "1/2 cup fresh herbs (mint, basil, ulam raja, or daun kesum)", "1/4 cup kerisik (toasted grated coconut)", "1 salted egg (cut into halves)", "1 fried fish or grilled chicken", "For the Sambal Kelapa (Coconut Sambal):", "1 cup grated coconut (toasted)", "3 shallots (finely chopped)", "2 cloves garlic (minced)", "1 tbsp dried shrimp (pounded)", "1 tsp turmeric powder", "1/2 tsp salt", "1 tbsp sugar"];

    $groupedIngredients = [];
    $currentGroup = null;

    // Loop through the ingredients and group them
    foreach ($ingredients as $ingredient) {
        if (preg_match('/^For the (.+):$/', $ingredient, $matches)) {
            // It's a new group, so we create a new key in the array
            $currentGroup = $matches[1];
            $groupedIngredients[$currentGroup] = [];
        } else {
            // It's an ingredient, so we add it to the current group
            if ($currentGroup) {
                $groupedIngredients[$currentGroup][] = $ingredient;
            }
        }
    }
    ?>

    <!-- Display grouped ingredients -->
    <?php foreach ($groupedIngredients as $group => $groupIngredients): ?>
        <div class="ingredient-group mb-4">
            <h5 class="mb-3 text-primary">
                <?= htmlspecialchars($group) ?>
            </h5>
            <ul class="ps-3">
                <?php foreach ($groupIngredients as $ingredient): ?>
                    <li class="mb-2">
                        <?= htmlspecialchars($ingredient) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>

</div>


<!-- Instructions Tab -->
<div class="tab-pane fade" id="instructions-<?= $entry['id'] ?>" role="tabpanel" aria-labelledby="instructions-tab">
    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle"></i> Follow the steps to make your dish.
    </div>

    <?php 
    // Get instructions from 'steps' if available, otherwise fall back to 'instructions'
    $steps = [];
    if (!empty($entry['steps'])) {
        $steps = json_decode($entry['steps'], true) ?: [];
    } elseif (!empty($entry['instructions'])) {
        $steps = array_filter(explode("\n", $entry['instructions']), function($item) {
            return trim($item) !== '';
        });
    }
    
    if (empty($steps)) {
        echo '<div class="alert alert-warning">No instructions provided</div>';
    } else {
        echo '<div class="steps-container">';
        foreach ($steps as $index => $step) {
            // Handle both array format (with title/description) and simple string format
            $stepTitle = is_array($step) ? ($step['title'] ?? '') : '';
            $stepDescription = is_array($step) ? ($step['description'] ?? '') : $step;
            ?>
            <div class="step-card mb-3 p-3 border rounded bg-light">
                <div class="step-header d-flex align-items-center mb-2">
                    <span class="step-number badge bg-primary rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                        <?= $index + 1 ?>
                    </span>
                    <?php if ($stepTitle): ?>
                        <h5 class="step-title mb-0"><?= htmlspecialchars($stepTitle) ?></h5>
                    <?php endif; ?>
                </div>
                <div class="step-body ps-4">
                    <p class="mb-2"><?= nl2br(htmlspecialchars($stepDescription)) ?></p>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }
    ?>
</div>

                           
                                    <?php if (!empty($entry['notes'])): ?>
                                    <div class="tab-pane fade" id="notes-<?= $entry['id'] ?>" role="tabpanel" aria-labelledby="notes-tab">
                                        <div class="alert alert-info">
                                            <i class="bi bi-lightbulb"></i>
                                            <?= nl2br(htmlspecialchars($entry['notes'])) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <form method="POST" action="submit-vote.php" class="w-100">
                                    <input type="hidden" name="recipe_id" value="<?= $entry['id'] ?>">
                                    <input type="hidden" name="competition_id" value="<?= $competition_id ?>">
                                    <div class="vote-section">
                                        <button type="submit" name="vote" value="1" 
                                            class="btn btn-upvote <?= $upvoted ? 'voted' : '' ?>" 
                                            <?= $upvoted ? 'disabled' : '' ?>>
                                            <i class="bi bi-hand-thumbs-up"></i> Upvote (<?= $entry['upvotes'] ?>)
                                        </button>
                                        <button type="submit" name="vote" value="-1" 
                                            class="btn btn-downvote <?= $downvoted ? 'voted' : '' ?>" 
                                            <?= $downvoted ? 'disabled' : '' ?>>
                                            <i class="bi bi-hand-thumbs-down"></i> Downvote (<?= $entry['downvotes'] ?>)
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php elseif ($entriesError): ?>
        <div class="alert alert-danger">Error loading entries. Please try again later.</div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-emoji-frown"></i>
            <h4 class="mb-3">No entries submitted for this competition yet</h4>
            <p class="text-muted">Check back later or join the competition yourself!</p>
            <a href="submit-competition-entry.php?id=<?= $competition_id ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Submit Your Entry
            </a>
        </div>
    <?php endif; ?>
</div>

<button class="print-btn btn btn-primary" onclick="window.print()">
    <i class="bi bi-printer-fill"></i>
</button>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add animation to voting buttons
    document.querySelectorAll('.btn-vote').forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('voted');
            this.classList.add('vote-animation');
            setTimeout(() => {
                this.classList.remove('vote-animation');
            }, 500);
        });
    });

    // Show success message if vote was submitted
    <?php if (isset($_GET['vote_success'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = new bootstrap.Toast(document.getElementById('voteToast'));
            toast.show();
        });
    <?php endif; ?>
    
    // Save checked ingredients to localStorage
    document.querySelectorAll('.ingredient-checkbox').forEach(checkbox => {
        const recipeId = checkbox.id.split('-')[1];
        const storageKey = `recipe-${recipeId}-ingredients`;
        
        // Load saved state
        const savedState = JSON.parse(localStorage.getItem(storageKey) || '{}');
        if (savedState[checkbox.id]) {
            checkbox.checked = true;
            checkbox.nextElementSibling.style.textDecoration = 'line-through';
            checkbox.nextElementSibling.style.color = '#6c757d';
        }
        
        // Save state on change
        checkbox.addEventListener('change', function() {
            const savedState = JSON.parse(localStorage.getItem(storageKey) || '{}');
            savedState[this.id] = this.checked;
            localStorage.setItem(storageKey, JSON.stringify(savedState));
            
            this.nextElementSibling.style.textDecoration = this.checked ? 'line-through' : 'none';
            this.nextElementSibling.style.color = this.checked ? '#6c757d' : 'inherit';
        });
    });
    
    // Timer functionality for instructions
    document.querySelectorAll('.timer-btn').forEach(button => {
        button.addEventListener('click', function() {
            const minutes = parseInt(this.getAttribute('data-minutes'));
            const stepContent = this.closest('.step-content');
            const stepText = stepContent.firstChild.textContent.trim();
            
            if (confirm(`Set a timer for ${minutes} minutes for:\n"${stepText}"`)) {
                // In a real implementation, you would start a timer here
                // This is just a visual indication for the demo
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check-circle"></i> Timer set!';
                this.style.color = 'green';
                
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    this.style.color = '';
                    alert(`Timer for "${stepText}" is complete!`);
                }, minutes * 1000); // In a real app, this would be minutes * 60 * 1000
            }
        });
    });
    
    // Clear all checkboxes
    function clearAllCheckboxes(modalId) {
        const modal = document.getElementById(modalId);
        const checkboxes = modal.querySelectorAll('.ingredient-checkbox');
        const recipeId = modalId.replace('recipeModal', '');
        const storageKey = `recipe-${recipeId}-ingredients`;
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.nextElementSibling.style.textDecoration = 'none';
            checkbox.nextElementSibling.style.color = 'inherit';
        });
        
        localStorage.removeItem(storageKey);
        
        // Show feedback
        const feedback = document.createElement('div');
        feedback.className = 'alert alert-success alert-dismissible fade show mt-2';
        feedback.innerHTML = `
            <i class="bi bi-check-circle"></i> All ingredients unchecked!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = modal.querySelector('.ingredient-list').parentNode;
        container.appendChild(feedback);
        
        setTimeout(() => {
            feedback.classList.add('show');
        }, 10);
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
</body>
</html>