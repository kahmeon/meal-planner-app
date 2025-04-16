<?php
error_reporting(E_ALL & ~E_NOTICE);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../includes/auth.php';

// Admin authentication check
if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$competition_id = intval($_GET['id'] ?? 0);

// Fetch competition and winner data
$competition_data = $conn->query("
    SELECT 
        c.title,
        c.winner_id,
        c.prize,
        u.name AS winner_name,
        u.email AS winner_email
    FROM competitions c
    LEFT JOIN users u ON c.winner_id = u.id
    WHERE c.competition_id = $competition_id
")->fetch_assoc();

if (!$competition_data) {
    die("Competition not found");
}

// Check if already has a winner
if (empty($competition_data['winner_id'])) {
    die("This competition has no winner selected yet");
}

// Process announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate unique token for winner link
    $token = bin2hex(random_bytes(32));
    $update_sql = "UPDATE competitions SET winner_token = '$token' WHERE competition_id = $competition_id";
    $conn->query($update_sql);
    
    // Send email to winner
    $to = $competition_data['winner_email'];
    $subject = "🏆 You Won: " . $competition_data['title'];
    
    $message = '
    <html>
    <head>
        <style>
            .button {
                background: #4CAF50;
                color: white;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 5px;
                display: inline-block;
                font-weight: bold;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <h2>Congratulations, ' . $competition_data['winner_name'] . '! 🎉</h2>
        <p>You have been selected as the winner of our competition: <strong>' . $competition_data['title'] . '</strong></p>
        <p>Your prize: <strong>' . $competition_data['prize'] . '</strong></p>
        
        <p>Click below to view your winner page:</p>
        <a href="https://yourdomain.com/view_winner.php?token=' . $token . '" class="button">
            View Your Winner Page
        </a>
        
        <p>We will contact you shortly with prize redemption details.</p>
        <p>Thank you for participating!</p>
    </body>
    </html>
    ';
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: competitions@yourdomain.com\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        $_SESSION['success'] = "Winner announced and email sent successfully!";
        header("Location: view_winner.php?id=$competition_id");
        exit;
    } else {
        $_SESSION['error'] = "Failed to send email";
    }
}
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Announce Competition Winner</h3>
                    </div>
                    <div class="card-body">
                        <h4>Competition: <?= htmlspecialchars($competition_data['title']) ?></h4>
                        <p>Winner: <?= htmlspecialchars($competition_data['winner_name']) ?></p>
                        <p>Prize: <?= htmlspecialchars($competition_data['prize']) ?></p>
                        <p>Email: <?= htmlspecialchars($competition_data['winner_email']) ?></p>
                        
                        <div class="alert alert-warning mt-4">
                            <h5><i class="bi bi-exclamation-triangle-fill"></i> Important</h5>
                            <p>This action will:</p>
                            <ul>
                                <li>Generate a unique link for the winner</li>
                                <li>Send an email notification to the winner</li>
                                <li>Make the winner page publicly accessible</li>
                            </ul>
                        </div>
                        
                        <form method="POST">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-megaphone-fill"></i> Announce Winner
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>