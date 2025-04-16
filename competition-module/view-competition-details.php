<?php
session_start();
require_once '../includes/db.php';

// Fetch competition details (for user view)
if (isset($_GET['id'])) {
    $competition_id = $_GET['id'];

    // Fetch competition data
    $stmt = $conn->prepare("SELECT * FROM competitions WHERE competition_id = ?");
    $stmt->bind_param("i", $competition_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $competition = $result->fetch_assoc();

    if (!$competition) {
        $_SESSION['error_message'] = "Competition not found.";
        header("Location: competitions.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "No competition specified.";
    header("Location: competitions.php");
    exit();
}

$days_remaining = ceil((strtotime($competition['end_date']) - time()) / (60 * 60 * 24));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($competition['title']) ?> | NomNomPlan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .card-img-top {
            height: 300px;
            object-fit: cover;
        }
        .badge-time {
            background-color: #FFE66D;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container py-5">
    <div class="card shadow border-0">
        <img src="<?= htmlspecialchars($competition['image_url'] ?? '../assets/images/default-competition.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($competition['title']) ?>">
        <div class="card-body p-4">
            <h1 class="card-title fw-bold mb-3">🏆 <?= htmlspecialchars($competition['title']) ?></h1>
            <span class="badge-time mb-3 d-inline-block">🕒 <?= $days_remaining > 0 ? "$days_remaining days left" : "Ending soon" ?></span>
            <p class="text-muted mb-4">
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('M j, Y', strtotime($competition['start_date'])) ?> - <?= date('M j, Y', strtotime($competition['end_date'])) ?>
            </p>

            <h5 class="fw-semibold">📋 Description</h5>
            <p><?= nl2br(htmlspecialchars($competition['description'])) ?></p>

            <h5 class="fw-semibold mt-4">🎁 Prize</h5>
            <p><?= htmlspecialchars($competition['prize']) ?></p>

            <div class="text-end">
                <?php if ($competition['status'] === 'active'): ?>
                    <a href="submit-competition-entry.php?id=<?= $competition['competition_id'] ?>" class="btn btn-danger px-4 py-2 rounded-pill">
                        ✨ Submit My Recipe
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

