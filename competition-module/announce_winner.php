<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php"); // or redirect them to an error page
    exit;
}

// Get POST data from the form
$competition_id = isset($_POST['competition_id']) ? intval($_POST['competition_id']) : 0;
$winner_id = isset($_POST['winner_id']) ? intval($_POST['winner_id']) : 0;
$recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;

// Validate the data
if ($competition_id <= 0 || $winner_id <= 0 || $recipe_id <= 0) {
    die("Invalid competition, winner, or recipe ID.");
}

// Update the competition data (announcing the winner)
$stmt = $conn->prepare("
    UPDATE competitions 
    SET winner_id = ?, winner_token = ?, winning_recipe_id = ?, announced_at = NOW() 
    WHERE competition_id = ?
");
$token = bin2hex(random_bytes(32));  // Generate a unique token
$stmt->bind_param("isis", $winner_id, $token, $recipe_id, $competition_id);

if ($stmt->execute()) {
    echo "Winner announced successfully!";
    header("Location: view_winner.php?id=$competition_id&token=$token");
    exit;
} else {
    echo "Failed to announce winner.";
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announce Winner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container py-5">
        <h2>Announce Winner</h2>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Competition: <?= htmlspecialchars($competition_data['title']) ?></h3>
            </div>
            <div class="card-body">
                <p>Prize: <?= htmlspecialchars($competition_data['prize']) ?></p>
                
                <?php if (!empty($competition_data['winner_name'])): ?>
                    <p>Winner: <?= htmlspecialchars($competition_data['winner_name']) ?></p>
                    <p>Email: <?= htmlspecialchars($competition_data['winner_email']) ?></p>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $competition_id ?>">
                        <button type="submit" class="btn btn-success">
                            Announce Winner
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>