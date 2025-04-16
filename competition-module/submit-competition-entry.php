<?php
session_start();
require_once '../includes/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit an entry.";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_competition_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Fetch ongoing competitions with additional details
$compStmt = $conn->prepare("
    SELECT c.*, 
    (SELECT COUNT(*) FROM competition_entries e WHERE e.user_id = ? AND e.competition_id = c.competition_id) as already_joined,
    DATEDIFF(c.end_date, NOW()) as days_remaining
    FROM competitions c 
    WHERE c.end_date > NOW()
    ORDER BY c.end_date ASC
");
$compStmt->bind_param("i", $user_id);
$compStmt->execute();
$competitions = $compStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user's recipes with additional details
$recipeStmt = $conn->prepare("
    SELECT r.id, r.title, 
    (SELECT COUNT(*) FROM competition_entries ce WHERE ce.recipe_id = r.id) as times_submitted
    FROM recipes r 
    WHERE r.created_by = ?
    ORDER BY r.title ASC
");
$recipeStmt->bind_param("i", $user_id);
$recipeStmt->execute();
$recipes = $recipeStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission result
$submission_message = '';
$message_type = '';
if (isset($_SESSION['submission_message'])) {
    $submission_message = $_SESSION['submission_message'];
    $message_type = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['submission_message'], $_SESSION['message_type']);
}

include '../navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit to Competition | NomNomPlan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', sans-serif;
        }
        
        .competition-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .competition-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .competition-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
        }
        
        .recipe-option {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .recipe-option:hover {
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        .recipe-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .days-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--primary-color);
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .submission-form {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        .select2-container .select2-selection--single {
            height: 45px;
            border-radius: 8px;
            padding: 10px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
        }
        
        @media (max-width: 768px) {
            .recipe-option {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .recipe-image {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3">🍽️ Submit Your Recipe to a Competition</h2>
                <p class="text-muted">Choose from ongoing competitions and select your best recipe to participate</p>
            </div>

            <!-- Display success or error messages -->
            <?php if ($submission_message): ?>
                <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                    <?= htmlspecialchars($submission_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (count($recipes) === 0): ?>
                <div class="alert alert-warning text-center py-4">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-journal-text fs-1 text-warning mb-3"></i>
                        <h4 class="mb-3">You haven't added any recipes yet</h4>
                        <p class="mb-3">Create a recipe first before submitting to competitions</p>
                        <a href="../recipe-modules/recipe-add.php" class="btn btn-danger px-4">
                            <i class="bi bi-plus-circle me-2"></i>Add New Recipe
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="submission-form">
                    <form action="process_entry.php" method="post">
                        <!-- Competition Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">1. Select Competition</label>
                            <select name="competition_id" class="form-select form-select-lg" required id="competitionSelect">
                                <option value="">-- Choose a competition --</option>
                                <?php foreach ($competitions as $comp): ?>
                                    <option value="<?= $comp['competition_id'] ?>" 
                                        <?= ($selected_competition_id == $comp['competition_id']) ? 'selected' : '' ?>
                                        <?= ($comp['already_joined'] > 0) ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($comp['title']) ?> 
                                        <?php if ($comp['already_joined'] > 0): ?>
                                            (Already submitted)
                                        <?php else: ?>
                                            (Closes in <?= $comp['days_remaining'] ?> days)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">You can only submit to each competition once</small>
                        </div>

                        <!-- Recipe Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">2. Select Your Recipe</label>
                            <select name="recipe_id" class="form-select form-select-lg" required id="recipeSelect">
                                <option value="">-- Choose your recipe --</option>
                                <?php foreach ($recipes as $recipe): ?>
                                    <option value="<?= $recipe['id'] ?>" data-image="<?= htmlspecialchars($recipe['image_url'] ?? '') ?>">
                                        <?= htmlspecialchars($recipe['title']) ?>
                                        <?php if ($recipe['times_submitted'] > 0): ?>
                                            (Submitted <?= $recipe['times_submitted'] ?> times)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Preview Section -->
                        <div class="mb-4" id="recipePreview" style="display: none;">
                            <label class="form-label fw-bold mb-3">Recipe Preview</label>
                            <div class="border rounded p-3 d-flex align-items-center">
                                <img id="previewImage" src="" class="recipe-image me-3" alt="Recipe image">
                                <div>
                                    <h5 id="previewTitle" class="mb-1"></h5>
                                    <small class="text-muted" id="previewSubmissions"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-danger btn-lg px-4" id="submitButton" disabled>
                                <i class="bi bi-send-check me-2"></i>Submit Entry
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Recipe preview functionality
document.querySelector('select[name="recipe_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const previewDiv = document.getElementById('recipePreview');
    
    if (this.value) {
        previewDiv.style.display = 'block';
        document.getElementById('previewImage').src = selectedOption.getAttribute('data-image') || 
            'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"%3E%3Crect fill="%23ddd" width="64" height="64"/%3E%3Ctext fill="%23aaa" font-family="sans-serif" font-size="10" dy="10.5" font-weight="bold" x="50%" y="50%" text-anchor="middle"%3ENo Image%3C/text%3E%3C/svg%3E';
        document.getElementById('previewTitle').textContent = selectedOption.text.split(' (')[0];
        
        const submissionsText = selectedOption.text.match(/\(Submitted (\d+) times\)/);
        document.getElementById('previewSubmissions').textContent = submissionsText ? 
            `Submitted ${submissionsText[1]} times to competitions` : 'Never submitted to competitions';
    } else {
        previewDiv.style.display = 'none';
    }
});

// Get elements for competition, recipe, and submit button
const competitionSelect = document.querySelector('#competitionSelect');
const recipeSelect = document.querySelector('#recipeSelect');  // Declare recipeSelect once here
const submitButton = document.querySelector('#submitButton');

// Function to toggle the submit button based on selections
function toggleSubmitButton() {
    if (competitionSelect.value && recipeSelect.value) {
        submitButton.disabled = false;  // Enable button if both are selected
    } else {
        submitButton.disabled = true;   // Disable button if either is not selected
    }
}

// Add event listeners to both select elements to monitor changes
competitionSelect.addEventListener('change', toggleSubmitButton);
recipeSelect.addEventListener('change', toggleSubmitButton);

// Call the function initially to set the button state correctly when the page loads
toggleSubmitButton();
</script>
</body>
</html>
