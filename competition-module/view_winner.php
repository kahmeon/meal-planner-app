<?php
error_reporting(E_ALL & ~E_NOTICE);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php"); // or redirect them to an error page
    exit;
}

require_once '../includes/db.php';
require_once '../includes/auth.php';

$competition_id = intval($_GET['id'] ?? 0);


error_log("Competition ID: $competition_id");
error_log("User ID: " . ($_SESSION['user_id'] ?? 'guest'));
$winner_data = $conn->query("
    SELECT 
        c.title AS competition_title,
        c.start_date,
        c.end_date,
        c.prize,  
        u.name AS winner_name,
        u.avatar_url,
        u.id AS winner_id
    FROM competitions c
    JOIN users u ON c.winner_id = u.id
    WHERE c.competition_id = $competition_id
")->fetch_assoc();

// Check if no winner data is found
if (!$winner_data) {
    $error = $conn->error ?: "No winner data found";
    error_log("Database error: $error");
    
    // Show the same message to everyone
    echo '<div class="container py-5">
            <div class="alert alert-danger text-center">
                <h2><i class="bi bi-exclamation-triangle"></i> Competition Data Unavailable</h2>
                <p>We cannot display the winner information at this time.</p>
                <a href="dashboard.php" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
          </div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Champion Announcement: <?= htmlspecialchars($winner_data['competition_title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #FFD700;
            --silver: #C0C0C0;
            --bronze: #CD7F32;
            --primary: #2a9d8f;
            --secondary: #264653;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f5f5;
            color: var(--dark);
            background-image: url('https://www.transparenttextures.com/patterns/45-degree-fabric-light.png');
        }
        
        .champion-header {
            background: linear-gradient(135deg, var(--gold), var(--primary));
            color: white;
            border-radius: 12px;
            padding: 4rem 2rem;
            margin: 0 0 3rem;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            border: 4px solid var(--gold);
            text-transform: uppercase;
            font-family: 'Bebas Neue', sans-serif;
        }
        
        .champion-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
        }
        
        .champion-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
        }
        
        .champion-header h1 {
            font-size: 4rem;
            letter-spacing: 3px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 1rem;
        }
        
        .champion-header h2 {
            font-size: 2rem;
            letter-spacing: 2px;
            font-weight: 300;
        }
        
        .champion-card {
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 3rem;
            border: none;
            background: white;
            transition: all 0.3s ease;
            position: relative;
            border-top: 5px solid var(--gold);
        }
        
        .champion-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            font-weight: 700;
            padding: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-family: 'Bebas Neue', sans-serif;
        }
        
        .user-avatar {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--gold);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            margin: -100px auto 2rem;
            background-color: white;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        
        .champion-name {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.5rem;
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 1px;
        }
        
        .champion-email {
            font-size: 1.1rem;
            color: var(--dark);
            margin-bottom: 2rem;
        }
        
        .btn-champion {
            background: linear-gradient(to right, var(--gold), #FFC000);
            color: var(--dark);
            border: none;
            font-weight: 700;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-champion:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.6);
            color: var(--dark);
        }
        
        .prize-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-left: 5px solid var(--gold);
            border-radius: 8px;
            padding: 2rem;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .prize-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23FFD700"><path d="M12 1L8 6l-6 .75 4.13 4.62L4 18l6-3 6 3-2.13-6.63L22 6.75 16 6l-4-5zM8.5 9.5l3 3-1 4-4-1 1-4 1-2zm7 0l1 2 1 4-4 1-1-4 3-3z"/></svg>');
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.1;
        }
        
        .prize-title {
            font-size: 1.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .prize-icon {
            font-size: 3rem;
            color: var(--gold);
            margin-bottom: 1rem;
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: var(--gold);
            opacity: 0.7;
            animation: fall 5s linear infinite;
        }
        
        @keyframes fall {
            0% { transform: translateY(-100vh) rotate(0deg); }
            100% { transform: translateY(100vh) rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .champion-header h1 {
                font-size: 2.5rem;
            }
            
            .champion-header h2 {
                font-size: 1.5rem;
            }
            
            .user-avatar {
                width: 150px;
                height: 150px;
                margin: -75px auto 1.5rem;
            }
            
            .champion-name {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container py-4 position-relative">
    <!-- Confetti animation -->
    <div id="confetti-container"></div>
    
    <!-- Champion Header -->
    <div class="champion-header position-relative">
        <div class="position-relative" style="z-index: 1;">
            <h1 class="display-2 fw-bold mb-3">
                <i class="bi bi-trophy-fill me-3"></i> CHAMPION
            </h1>
            <h2 class="fw-light mb-0"><?= htmlspecialchars($winner_data['competition_title']) ?></h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Champion Profile Column -->
        <div class="col-lg-8">
            <div class="champion-card text-center">
                <div class="card-header">
                    <h3 class="mb-0">THE WINNER</h3>
                </div>
                <div class="card-body pt-5 px-4 pb-4">
                    <img src="<?= htmlspecialchars($winner_data['avatar_url'] ?? '../assets/default-avatar.jpg') ?>" 
                         class="user-avatar" alt="Champion Avatar">
                    <h3 class="champion-name"><?= htmlspecialchars($winner_data['winner_name']) ?></h3>
                </div>
            </div>
            
            <!-- Prize Information Card -->
            <?php if (!empty($winner_data['prize'])): ?>
            <div class="prize-card">
                <div class="text-center prize-icon">
                    <i class="bi bi-gift-fill"></i>
                </div>
                <h4 class="prize-title text-center">Champion's Prize</h4>
                <div class="text-center fs-5">
                    <?= nl2br(htmlspecialchars($winner_data['prize'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Create confetti effect
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('confetti-container');
        const colors = ['#FFD700', '#FFA500', '#FF6347', '#87CEEB', '#98FB98', '#DA70D6'];
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            confetti.style.animationDelay = Math.random() * 5 + 's';
            confetti.style.width = (Math.random() * 10 + 5) + 'px';
            confetti.style.height = (Math.random() * 10 + 5) + 'px';
            container.appendChild(confetti);
        }
    });
</script>
</body>
</html>