<?php
session_start();

// Hardcode competition and winner details for testing purposes
$competition = [
    'title' => 'Best Recipe Challenge',
    'end_date' => '2025-04-20', // Set a future date for testing
    'prize' => 'A $1000 Cash Voucher',
    'announced_at' => '2025-04-15',
];

$winner = [
    'name' => 'Pow Kah Meon',
    'avatar_url' => 'https://randomuser.me/api/portraits/men/32.jpg', // Using random user API for demo
    'email' => 'pow.kahmeon@example.com', // Replace with actual email
    'recipe_title' => 'Spicy Mango Chicken Curry',
    'recipe_description' => 'A perfect blend of sweet and spicy with fresh mangoes and aromatic spices'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winner: <?= htmlspecialchars($competition['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #2a9d8f;
            --secondary-color: #264653;
            --accent-color: #FFD700;
            --dark-accent: #e9c46a;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .winner-modal .modal-content {
            border: 4px solid var(--accent-color);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .winner-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .winner-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
            animation: shine 3s infinite;
        }
        
        .winner-avatar {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--accent-color);
            margin: -70px auto 20px;
            background: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        
        .winner-avatar:hover {
            transform: scale(1.05) rotate(5deg);
        }
        
        .prize-badge {
            background: linear-gradient(to right, var(--accent-color), var(--dark-accent));
            color: #000;
            font-weight: bold;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            display: inline-block;
            margin: 1rem 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .prize-badge::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255,255,255,0) 45%,
                rgba(255,255,255,0.8) 50%,
                rgba(255,255,255,0) 55%
            );
            transform: rotate(30deg);
            animation: shine 3s infinite;
        }
        
        .winner-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: var(--accent-color);
            opacity: 0;
        }
        
        .btn-congrats {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-congrats:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-congrats::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        .btn-congrats:hover::after {
            animation: shine 1.5s infinite;
        }
        
        @keyframes shine {
            0% {
                transform: rotate(30deg) translate(-30%, -30%);
            }
            100% {
                transform: rotate(30deg) translate(30%, 30%);
            }
        }
        
        .modal-body {
            position: relative;
            overflow: hidden;
        }
        
        .winner-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }
        
        .winner-subtitle {
            color: #6c757d;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        
        .recipe-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .recipe-title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .recipe-description {
            color: #495057;
            font-style: italic;
        }
        
        .social-share {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        
        .social-icon:hover {
            transform: scale(1.1);
        }
        
        .facebook {
            background-color: #3b5998;
        }
        
        .twitter {
            background-color: #1da1f2;
        }
        
        .instagram {
            background: linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d);
        }
        
        .whatsapp {
            background-color: #25d366;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 mb-4 fw-bold text-gradient"><?= htmlspecialchars($competition['title']) ?></h1>
                <p class="lead mb-5">The results are in! Discover who won this amazing competition and their award-winning recipe.</p>
                
                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center mb-5">
                    <a href="#" class="btn btn-primary btn-lg px-4 gap-3" data-bs-toggle="modal" data-bs-target="#winnerModal">
                        <i class="bi bi-trophy"></i> View Winner Announcement
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="bi bi-arrow-left"></i> View Competition
                    </a>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-info-circle"></i> Competition Details</h5>
                        <p class="card-text">
                            <strong>End Date:</strong> <?= date('F j, Y', strtotime($competition['end_date'])) ?><br>
                            <strong>Prize:</strong> <?= htmlspecialchars($competition['prize']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Winner Modal -->
    <div class="modal fade winner-modal" id="winnerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="winner-header position-relative">
                    <div class="position-absolute top-0 end-0 m-3">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <h2 class="mb-0 animate__animated animate__fadeInDown"><i class="bi bi-trophy-fill"></i> WINNER ANNOUNCEMENT</h2>
                    <p class="mb-0 mt-2 animate__animated animate__fadeIn animate__delay-1s">
                        <i class="bi bi-calendar-check"></i> <?= date('F j, Y', strtotime($competition['announced_at'])) ?>
                    </p>
                </div>
                <div class="modal-body text-center py-4 px-5">
                    <img src="<?= htmlspecialchars($winner['avatar_url']) ?>" 
                         class="winner-avatar animate__animated animate__zoomIn" alt="Winner Avatar">
                    <h2 class="winner-title animate__animated animate__fadeIn animate__delay-1s"><?= htmlspecialchars($winner['name']) ?></h2>
                    <p class="winner-subtitle animate__animated animate__fadeIn animate__delay-1s">
                        Winner of <?= htmlspecialchars($competition['title']) ?>
                    </p>
                    
                    <?php if (!empty($competition['prize'])): ?>
                    <div class="prize-badge animate__animated animate__bounceIn animate__delay-2s">
                        <i class="bi bi-gift-fill"></i> Prize: <?= htmlspecialchars($competition['prize']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="winner-details animate__animated animate__fadeIn animate__delay-2s">
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <p class="mb-3"><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($winner['email']) ?></p>
                                
                                <?php if (!empty($winner['recipe_title'])): ?>
                                <div class="recipe-card text-start">
                                    <h4 class="recipe-title"><i class="bi bi-bookmark-star-fill"></i> Winning Recipe: <?= htmlspecialchars($winner['recipe_title']) ?></h4>
                                    <p class="recipe-description"><?= htmlspecialchars($winner['recipe_description']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-share animate__animated animate__fadeIn animate__delay-3s">
                        <a href="#" class="social-icon facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-icon twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="social-icon instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-icon whatsapp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                    
                    <button type="button" class="btn btn-congrats animate__animated animate__fadeInUp animate__delay-3s" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle-fill"></i> Congratulations!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create confetti effect when modal opens
        document.getElementById('winnerModal').addEventListener('shown.bs.modal', function() {
            const modalBody = document.querySelector('.modal-body');
            const colors = ['#FFD700', '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = -10 + 'px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 8 + 5 + 'px';
                confetti.style.height = confetti.style.width;
                confetti.style.opacity = Math.random() + 0.5;
                confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                confetti.style.animation = 'fall ' + (Math.random() * 3 + 2) + 's linear forwards';
                
                // Create keyframes for falling animation
                const style = document.createElement('style');
                style.innerHTML = `
                    @keyframes fall {
                        to {
                            top: 100%;
                            transform: rotate(' + Math.random() * 360 + 'deg);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
                
                modalBody.appendChild(confetti);
            }
            
            // Remove confetti after animation completes
            setTimeout(() => {
                const confettiElements = document.querySelectorAll('.confetti');
                confettiElements.forEach(el => el.remove());
            }, 3000);
        });
    </script>
</body>
</html>