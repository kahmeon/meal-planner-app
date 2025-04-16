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

$entries = null; // Initialize variable
$entriesError = false;

try {
    $entriesStmt = $conn->prepare("SELECT r.*, u.name AS author_name,
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
    echo "<div class='alert alert-danger'>Error loading entries. Please try again later.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote for Entries | NomNomPlan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e00000;
            --secondary-color: #4ECDC4;
            --light-bg: #F7FFF7;
            --dark-color: #292F36;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
        }

        .page-header {
            background: linear-gradient(135deg, rgba(230,57,70,0.1), rgba(78,205,196,0.1));
            border-radius: 16px;
            padding: 3rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
        }

        .vote-section {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn-vote {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            background-color: var(--secondary-color);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-vote:hover {
            background-color: #36b2a4;
        }

        .card-title {
            font-weight: bold;
        }

        .empty-state {
            background-color: white;
            padding: 3rem;
            text-align: center;
            border: 2px dashed #D9D9D9;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--secondary-color);
        }
    </style>
</head>
<body class="bg-light">

<?php include '../navbar.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-4">🏆 Vote for Entries - <?= htmlspecialchars($competition['title']) ?></h2>

    <?php if (!$entriesError && $entries && $entries->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($entry = $entries->fetch_assoc()): ?>
                <?php
                $displayImage = (!empty($entry['image_url'])) 
                    ? '../uploads/' . htmlspecialchars(basename($entry['image_url']))
                    : '../assets/no-image.jpg';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $displayImage ?>" class="card-img-top" alt="<?= htmlspecialchars($entry['title']) ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title">🍽️ <?= htmlspecialchars($entry['title']) ?></h5>
                            <p class="text-muted">👨‍🍳 By <?= htmlspecialchars($entry['author_name']) ?></p>
                            <p class="small">👍 <?= $entry['upvotes'] ?> | 👎 <?= $entry['downvotes'] ?></p>
                            <form method="POST" action="submit-vote.php">
                                <input type="hidden" name="recipe_id" value="<?= $entry['id'] ?>">
                                <input type="hidden" name="competition_id" value="<?= $competition_id ?>">
                                <div class="vote-section">
                                    <button type="submit" name="vote" value="1" class="btn btn-vote" <?= $entry['user_vote'] == 1 ? 'disabled' : '' ?>>👍 Upvote</button>
                                    <button type="submit" name="vote" value="-1" class="btn btn-vote" <?= $entry['user_vote'] == -1 ? 'disabled' : '' ?>>👎 Downvote</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php elseif ($entriesError): ?>
        <div class="alert alert-danger">Error loading entries. Please try again later.</div>
    <?php else: ?>
        <div class="empty-state mt-5">
            <i class="bi bi-emoji-frown"></i>
            <h4 class="mb-3">No entries submitted for this competition yet.</h4>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>