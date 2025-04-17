<?php
session_start();
require_once '../includes/db.php';

// Validate and sanitize competition ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid competition ID.";
    header("Location: competitions.php");
    exit();
}

$competition_id = intval($_GET['id']);

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

// Calculate time remaining
$start_date = strtotime($competition['start_date']);
$end_date = strtotime($competition['end_date']);
$current_time = time();

if ($current_time < $start_date) {
    $days_remaining = ceil(($start_date - $current_time) / (60 * 60 * 24));
    $time_status = "Starts in $days_remaining days";
    $status_class = "bg-info";
} elseif ($current_time > $end_date) {
    $time_status = "Competition ended";
    $status_class = "bg-secondary";
} else {
    $days_remaining = ceil(($end_date - $current_time) / (60 * 60 * 24));
    $time_status = "$days_remaining days remaining";
    $status_class = "bg-success";
}

// Handle image URL
$default_image = '../assets/images/default-competition.jpg';
$image_url = !empty($competition['image_url']) ? $competition['image_url'] : $default_image;

// Check if image exists (for local paths)
if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
    // Handle relative paths
    if (strpos($image_url, '../') === 0) {
        $full_path = $image_url;
    } else {
        $full_path = '../' . ltrim($image_url, '/');
    }
    
    // Use default if file doesn't exist
    if (!file_exists($full_path)) {
        $image_url = $default_image;
    } else {
        $image_url = $full_path;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($competition['title']) ?> | NomNomPlan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .competition-header {
            position: relative;
            height: 350px;
            overflow: hidden;
        }
        .competition-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .competition-image:hover {
            transform: scale(1.05);
        }
        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            color: white;
            padding: 2rem 1.5rem 1rem;
        }
        .time-badge {
            font-size: 1rem;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
        }
        .prize-card {
            border-left: 4px solid #FFD700;
            background-color: #FFF9E6;
        }
        .rules-card {
            border-left: 4px solid #6c757d;
        }
        .judging-criteria li {
            margin-bottom: 0.5rem;
        }
        .btn-submit {
            display: inline-block;
            width: 100%;
            padding: 15px 20px;
            text-align: center;
            background-color: #dc3545;
            color: white;
            font-size: 18px;
            border-radius: 50px;
            font-weight: bold;
            text-decoration: none;
        }
        .btn-submit:hover {
            background-color: #c82333;
        }
        .btn-submit:disabled {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container py-4">
    <div class="competition-header rounded-3 mb-4 shadow">
        <?php if (!empty($image_url)): ?>
            <img src="<?= htmlspecialchars($image_url) ?>" 
                 class="competition-image" 
                 alt="<?= htmlspecialchars($competition['title']) ?>"
                 onerror="this.onerror=null; this.src='<?= $default_image ?>';">
        <?php else: ?>
            <div class="img-placeholder">
                <i class="bi bi-trophy"></i>
            </div>
        <?php endif; ?>
        <div class="image-overlay">
            <h1 class="display-5 fw-bold text-white mb-2"><?= htmlspecialchars($competition['title']) ?></h1>
            <span class="time-badge <?= $status_class ?>"><?= $time_status ?></span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h4 mb-3"><i class="bi bi-info-circle"></i> About This Competition</h2>
                    <div class="mb-4">
                        <?= nl2br(htmlspecialchars($competition['description'])) ?>
                    </div>
                    
                    <div class="d-flex align-items-center text-muted mb-4">
                        <div class="me-4">
                            <i class="bi bi-calendar-event"></i>
                            <?= date('F j, Y', strtotime($competition['start_date'])) ?>
                        </div>
                        <div>
                            <i class="bi bi-calendar-check"></i>
                            <?= date('F j, Y', strtotime($competition['end_date'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h4 mb-3"><i class="bi bi-journal-text"></i> Competition Rules</h2>
                    <div class="rules-card p-3 rounded">
                        <?= nl2br(htmlspecialchars($competition['rules'] ?? 'No specific rules provided.')) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body prize-card">
                    <h2 class="card-title h4 mb-3"><i class="bi bi-award"></i> Prize</h2>
                    <div class="fs-5">
                        <?= htmlspecialchars($competition['prize']) ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="card-title h4 mb-3"><i class="bi bi-list-check"></i> Judging Criteria</h2>
                    <ul class="judging-criteria">
                        <li><i class="bi bi-check-circle text-success"></i> Creativity (30%)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Taste (40%)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Presentation (20%)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Originality (10%)</li>
                    </ul>
                </div>
            </div>

            <div class="d-grid gap-2">
                <?php if ($competition['status'] === 'active' && $current_time >= $start_date && $current_time <= $end_date): ?>
                    <a href="submit-competition-entry.php?id=<?= $competition['competition_id'] ?>" 
                       class="btn-submit">
                        <i class="bi bi-send-plus"></i> Submit Your Entry
                    </a>
                <?php elseif ($current_time < $start_date): ?>
                    <button class="btn-submit" disabled>
                        <i class="bi bi-clock"></i> Competition Not Started
                    </button>
                <?php else: ?>
                    <button class="btn-submit" disabled>
                        <i class="bi bi-calendar-x"></i> Competition Ended
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple animation for competition header
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('.competition-header');
        if (header) {
            setTimeout(() => {
                header.style.opacity = 1;
            }, 100);
        }
    });
</script>
</body>
</html>
