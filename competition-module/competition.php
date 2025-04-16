<?php
include '../includes/db.php';
session_start();

$current_date = date('Y-m-d H:i:s');

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// 1. MAIN COMPETITIONS QUERY (FIXED)
$sql = "
    SELECT 
        c.*,
        COUNT(e.entry_id) AS entry_count,
        u.name AS winner_name,  -- Changed from c.winner_name to get from users table
        u.avatar_url AS winner_avatar, -- Added
        r.title AS winning_recipe_title,
        r.image_url AS winning_recipe_image,
        r.id AS winning_recipe_id,
        MAX(CASE WHEN e.status = 'winner' THEN 1 ELSE 0 END) AS has_winner
    FROM competitions c
    LEFT JOIN competition_entries e ON c.competition_id = e.competition_id
    LEFT JOIN users u ON c.winner_id = u.id  -- Added join to users
    LEFT JOIN recipes r ON c.winning_recipe_id = r.id
    WHERE (c.status = 'active' AND c.end_date > NOW()) 
       OR c.status = 'completed'
    GROUP BY c.competition_id
    ORDER BY 
        CASE WHEN c.status = 'active' THEN 0 ELSE 1 END,
        c.end_date DESC  -- Changed to end_date for more logical ordering
";

$result = $conn->query($sql);

// 2. FEATURED WINNER QUERY (OPTIMIZED)
$featured_winner_sql = "
    SELECT 
        c.competition_id, 
        c.title AS competition_title,
        u.name AS winner_name,
        u.avatar_url AS winner_avatar,
        r.title AS winning_recipe_title,
        r.image_url AS winning_recipe_image,
        r.id AS winning_recipe_id,
        c.prize,
        c.winner_announced_at
    FROM competitions c
    JOIN users u ON c.winner_id = u.id
    JOIN recipes r ON c.winning_recipe_id = r.id
    WHERE c.status = 'completed' 
      AND c.winner_id IS NOT NULL
      AND c.winner_announced = 1  -- Ensure winner was officially announced
    ORDER BY c.winner_announced_at DESC 
    LIMIT 1
";

$featured_winner_result = $conn->query($featured_winner_sql);
$featured_winner = $featured_winner_result->num_rows > 0 ? $featured_winner_result->fetch_assoc() : null;

// 3. USER'S JOINED COMPETITIONS (NEW)
// 3. USER'S JOINED COMPETITIONS (UPDATED)
$user_competitions = [];
if ($user_id) {
    $user_competitions_sql = "
        SELECT 
            c.competition_id,
            c.title,
            c.image_url,
            c.status AS competition_status,
            c.end_date,
            e.entry_id,
            e.status AS entry_status,
            e.submitted_at,
            (SELECT COUNT(*) FROM competition_votes WHERE entry_id = e.entry_id) AS vote_count,
            r.title AS recipe_title,
            r.image_url AS recipe_image
        FROM competition_entries e
        JOIN competitions c ON e.competition_id = c.competition_id
        LEFT JOIN recipes r ON e.recipe_id = r.id
        WHERE e.user_id = ?
        ORDER BY e.submitted_at DESC
    ";
    
    $stmt = $conn->prepare($user_competitions_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    while ($row = $user_result->fetch_assoc()) {
        $user_competitions[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competitions | NomNomPlan</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
       :root {
    --primary-color: #E63946;
    --primary-light: rgba(230, 57, 70, 0.1);
    --secondary-color: #457B9D;
    --secondary-light: rgba(69, 123, 157, 0.1);
    --accent-color: #FF7E33;
    --light-bg: #F8F9FA;
    --dark-color: #1D3557;
    --dark-light: #4A5568;
    --success-color: #38A169;
    --warning-color: #DD6B20;
    --danger-color: #E53E3E;
    --info-color: #3182CE;
    --gold-color: #D4AF37;
    --gold-light: rgba(212, 175, 55, 0.1);
    --silver-color: #C0C0C0;
    --bronze-color: #CD7F32;
    --text-color: #2D3748;
    --text-light: #718096;
    --border-radius: 12px;
    --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

body {
    font-family: 'Nunito', sans-serif;
    background-color: var(--light-bg);
    color: var(--text-color);
    line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: var(--dark-color);
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
    border-radius: var(--border-radius);
    padding: 3rem 1.5rem;
    margin-bottom: 3rem;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: var(--box-shadow);
    background-size: 200% 200%;
    animation: gradientBG 15s ease infinite;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.page-header-content {
    position: relative;
    z-index: 2;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-header p {
    font-size: 1.25rem;
    max-width: 700px;
    margin: 0 auto 2rem;
    opacity: 0.9;
}

.header-badge {
    background-color: rgba(255,255,255,0.15);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255,255,255,0.25);
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    font-weight: 600;
    margin: 0 0.5rem 0.5rem;
    display: inline-block;
    transition: var(--transition);
}

.header-badge:hover {
    transform: translateY(-2px);
    background-color: rgba(255,255,255,0.25);
}

/* Section Titles */
.section-title {
    position: relative;
    padding-bottom: 1rem;
    margin: 2.5rem 0 1.5rem;
    color: var(--dark-color);
}

.section-title:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: 2px;
}

/* Cards */
.competition-card {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    margin-bottom: 2rem;
    position: relative;
    background: white;
    font-size: 0.9375rem;
}

.competition-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.12);
}

.competition-card.active {
    border-left: 5px solid var(--primary-color);
}

.competition-card.completed {
    border-left: 5px solid var(--success-color);
}

.featured-competition {
    border: 2px solid var(--gold-color);
    position: relative;
    overflow: hidden;
}

.featured-competition:before {
    content: 'FEATURED';
    position: absolute;
    top: 15px;
    right: -30px;
    width: 120px;
    padding: 3px 0;
    background-color: var(--gold-color);
    color: white;
    font-size: 0.75rem;
    font-weight: bold;
    text-align: center;
    transform: rotate(45deg);
    z-index: 1;
}

.card-img-container {
    width: 100%;
    height: 220px;
    overflow: hidden;
    position: relative;
}

.card-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.competition-card:hover .card-img-container img {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 1.5rem;
    color: white;
    z-index: 2;
}

.card-overlay h3 {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.badge {
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 50px;
    background-color: rgba(255,255,255,0.2);
    backdrop-filter: blur(5px);
    font-size: 0.8rem;
}

.card-body {
    padding: 1.75rem;
}

.time-progress {
    height: 6px;
    background-color: var(--light-bg);
    border-radius: 3px;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.time-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: 3px;
    transition: width 1s ease;
}

.competition-dates {
    background-color: var(--light-bg);
    padding: 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.85rem;
    color: var(--text-light);
    display: flex;
    justify-content: space-between;
    border: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 1.25rem;
}

.competition-description {
    color: var(--text-light);
    margin-bottom: 1.5rem;
    line-height: 1.6;
    font-size: 0.9375rem;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-join, .btn-vote, .btn-details {
    padding: 0.75rem 1.25rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
}

.btn-join {
    background-color: var(--primary-color);
    color: white;
    border: none;
}

.btn-join:hover {
    background-color: #C1121F;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3);
}

.btn-vote {
    background-color: var(--secondary-color);
    color: white;
    border: none;
}

.btn-vote:hover {
    background-color: #315E7D;
    transform: translateY(-2px);
}

.btn-details {
    background-color: white;
    color: var(--dark-color);
    border: 1px solid rgba(0,0,0,0.1);
}

.btn-details:hover {
    background-color: var(--light-bg);
    transform: translateY(-2px);
}

/* Winner Cards */
.winner-card {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    height: 100%;
    background: white;
}

.winner-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.12);
}

.winner-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.winner-recipe-card {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: var(--transition);
    margin-top: 1.5rem;
}

.winner-recipe-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.winner-recipe-img {
    height: 150px;
    object-fit: cover;
    width: 100%;
}

/* Sidebar */
.sidebar-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.sidebar-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.sidebar-card h5 {
    font-size: 1.25rem;
    margin-bottom: 1.25rem;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar-card ul {
    padding-left: 1.25rem;
    margin-bottom: 0;
}

.sidebar-card li {
    margin-bottom: 0.75rem;
    position: relative;
    list-style-type: none;
    padding-left: 1.5rem;
}

.sidebar-card li:before {
    content: '•';
    color: var(--primary-color);
    font-weight: bold;
    position: absolute;
    left: 0;
}

/* Floating Action Button */
.fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 10px 25px rgba(230, 57, 70, 0.3);
    z-index: 100;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.fab:hover {
    transform: translateY(-5px) scale(1.1);
    box-shadow: 0 15px 30px rgba(230, 57, 70, 0.4);
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.empty-state i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    display: inline-block;
}

.empty-state h4 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* User Competitions Section */
.user-competitions {
    background-color: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 3rem;
    box-shadow: var(--box-shadow);
}

.user-competition-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    background-color: var(--light-bg);
    margin-bottom: 1rem;
    transition: var(--transition);
    border-left: 4px solid var(--primary-color);
}

.user-competition-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.user-competition-card.completed {
    border-left-color: var(--success-color);
}

.user-competition-card.winner {
    border-left-color: var(--gold-color);
    background-color: var(--gold-light);
}

.user-competition-image {
    width: 80px;
    height: 80px;
    border-radius: var(--border-radius);
    object-fit: cover;
    margin-right: 1.5rem;
    flex-shrink: 0;
}

.user-competition-info {
    flex-grow: 1;
}

.user-competition-status {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.status-pending {
    background-color: var(--warning-color);
    color: white;
}

.status-approved {
    background-color: var(--success-color);
    color: white;
}

.status-winner {
    background-color: var(--gold-color);
    color: white;
}

.status-finalist {
    background-color: var(--secondary-color);
    color: white;
}

.user-competition-actions {
    margin-left: 1.5rem;
    flex-shrink: 0;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .page-header h1 {
        font-size: 2rem;
    }
    
    .page-header p {
        font-size: 1.1rem;
    }
    
    .card-img-container {
        height: 180px;
    }
    
    .user-competition-card {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .user-competition-image {
        margin-right: 0;
        margin-bottom: 1rem;
        width: 100%;
        height: 120px;
    }
    
    .user-competition-actions {
        margin-left: 0;
        margin-top: 1rem;
        width: 100%;
    }
}

@media (max-width: 768px) {
    .page-header {
        padding: 2rem 1rem;
    }
    
    .page-header h1 {
        font-size: 1.75rem;
    }
    
    .header-badge {
        padding: 0.4rem 1rem;
        font-size: 0.8rem;
    }
    
    .card-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-join, .btn-vote, .btn-details {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .page-header {
        border-radius: 0;
        margin-left: -15px;
        margin-right: -15px;
    }
    
    .competition-dates {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .fab {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
        bottom: 20px;
        right: 20px;
    }
    
    .user-competitions {
        padding: 1.5rem;
    }
}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include '../navbar.php'; ?>

<main class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="page-header animate__animated animate__fadeIn">
                <div class="page-header-content">
                    <h1 class="display-5 fw-bold mb-3">🍳 Culinary Showdown</h1>
                    <p class="lead mb-4">Showcase your skills, compete with chefs, and win amazing prizes!</p>
                    <div>
                        <span class="header-badge"><i class="bi bi-trophy me-1"></i>Exciting Prizes</span>
                        <span class="header-badge"><i class="bi bi-people me-1"></i>Community Voting</span>
                        <span class="header-badge"><i class="bi bi-star me-1"></i>Featured Recipes</span>
                    </div>
                </div>
            </div>

            <!-- User's Competitions Section (NEW) -->
            <?php if ($user_id && !empty($user_competitions)): ?>
            <div class="user-competitions animate__animated animate__fadeIn">
                <h3 class="section-title"><i class="bi bi-person-check me-2"></i>Your Competition Entries</h3>
                
                <?php foreach ($user_competitions as $entry): 
                    $is_winner = $entry['entry_status'] == 'winner';
                    $is_completed = $entry['competition_status'] == 'completed';
                    $days_remaining = $is_completed ? 0 : ceil((strtotime($entry['end_date']) - time()) / (60 * 60 * 24));
                ?>
                <div class="user-competition-card <?= $is_winner ? 'winner' : '' ?> <?= $is_completed ? 'completed' : '' ?>">
                    <img src="<?= htmlspecialchars($entry['image_url'] ?? '../assets/images/default-competition.jpg') ?>" 
                         class="user-competition-image" 
                         alt="<?= htmlspecialchars($entry['title']) ?>">
                    
                    <div class="user-competition-info">
                        <span class="user-competition-status 
                            <?= $is_winner ? 'status-winner' : 
                               ($entry['entry_status'] == 'approved' ? 'status-approved' : 
                               ($entry['entry_status'] == 'finalist' ? 'status-finalist' : 'status-pending')) ?>">
                            <?= $is_winner ? 'Winner' : 
                               ($entry['entry_status'] == 'approved' ? 'Approved' : 
                               ($entry['entry_status'] == 'finalist' ? 'Finalist' : 'Pending')) ?>
                        </span>
                        
                        <h5 class="mb-1"><?= htmlspecialchars($entry['title']) ?></h5>
                        
                        <p class="mb-1 text-muted small">
                            <?php if ($is_completed): ?>
                                <i class="bi bi-calendar-check"></i> Ended <?= date('M j, Y', strtotime($entry['end_date'])) ?>
                            <?php else: ?>
                                <i class="bi bi-clock"></i> <?= $days_remaining ?> days remaining
                            <?php endif; ?>
                        </p>
                        
                        <?php if (!empty($entry['recipe_title'])): ?>
                        <p class="mb-0 small">
                            <i class="bi bi-bookmark-heart"></i> 
                            <strong>Your Recipe:</strong> <?= htmlspecialchars($entry['recipe_title']) ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($entry['vote_count'] > 0): ?>
                        <p class="mb-0 small text-success">
                            <i class="bi bi-heart-fill"></i> 
                            <strong><?= $entry['vote_count'] ?> votes</strong>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-competition-actions">
                        <a href="view-competition-details.php?id=<?= $entry['competition_id'] ?>" 
                           class="btn btn-sm btn-outline-primary mb-1 w-100">
                           <i class="bi bi-eye"></i> View
                        </a>
                        <?php if (!$is_completed): ?>
                        
                        <?php endif; ?>
                        <?php if ($is_winner): ?>
                        <span class="badge bg-warning text-dark w-100">
                            <i class="bi bi-trophy"></i> Winner!
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php elseif ($user_id): ?>
            <div class="empty-state animate__animated animate__fadeIn">
                <i class="bi bi-emoji-frown"></i>
                <h4 class="mb-3">No Competition Entries Yet</h4>
                <p class="text-muted">You haven't joined any competitions yet. Browse our current competitions and submit your entry!</p>
                <a href="#current-competitions" class="btn btn-primary rounded-pill px-4 mt-2">
                    <i class="bi bi-arrow-down me-1"></i> View Current Competitions
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Improved Recent Winners Section -->
            <h3 class="section-title animate__animated animate__fadeIn">
                <i class="bi bi-trophy-fill me-2" style="color: var(--gold-color);"></i>Recent Winners
            </h3>

            <div class="row g-4 mb-5">
                <?php 
                // Reset pointer and find completed competitions with winners
                $result->data_seek(0);
                $winner_shown = false;
                while ($competition = $result->fetch_assoc()): 
                    if ($competition['status'] == 'completed' && !empty($competition['winner_name'])): 
                        $winner_shown = true;
                        $days_since_ended = floor((time() - strtotime($competition['end_date'])) / (60 * 60 * 24));
                        $prize_value = isset($competition['prize']) ? 'Prize: $' . number_format($competition['prize'], 0) : 'Featured Recipe';
                ?>
                <div class="col-lg-6 animate__animated animate__fadeInUp">
                    <div class="winner-card h-100 position-relative">
                        <!-- Winner Crown Badge -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <div class="winner-crown bg-warning text-dark px-2 py-1 rounded-pill d-flex align-items-center">
                                <i class="bi bi-trophy-fill me-1"></i>
                                <small class="fw-bold">Winner</small>
                            </div>
                        </div>
                        
                        <!-- Recipe Image with Hover Effect -->
                        <div class="card-img-container position-relative overflow-hidden rounded-top" style="height: 200px;">
                            <img src="<?= htmlspecialchars($competition['winning_recipe_image'] ?? '../assets/images/default-recipe.jpg') ?>" 
                                class="card-img-top h-100 w-100 object-fit-cover transition-transform" 
                                alt="Winning Recipe">
                            
                            <!-- Competition Title Overlay -->
                            <div class="position-absolute bottom-0 start-0 end-0 p-3 text-white" 
                                style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                                <h5 class="mb-0 text-truncate"><?= htmlspecialchars($competition['title']) ?></h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Competition Meta -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                    <i class="bi bi-calendar me-1"></i><?= $days_since_ended ?> days ago
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                    <i class="bi bi-people me-1"></i><?= $competition['entry_count'] ?> entries
                                </span>
                            </div>
                            
                            <!-- Winner Profile -->
                            <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-3">
                                <img src="<?= htmlspecialchars($competition['winner_avatar'] ?? '../assets/default-avatar.jpg') ?>" 
                                    class="rounded-circle me-3 border border-3 border-warning" 
                                    width="60" height="60" alt="Winner"
                                    style="object-fit: cover;">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($competition['winner_name']) ?></h5>
                                    <small class="text-muted d-flex align-items-center">
                                        <i class="bi bi-award-fill me-1 text-warning"></i>
                                        <?= $prize_value ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Winning Recipe Details -->
                            <div class="winner-recipe bg-light p-3 rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 d-flex align-items-center">
                                        <i class="bi bi-bookmark-heart-fill me-2" style="color: var(--primary-color);"></i>
                                        Winning Recipe
                                    </h6>
                                    <div class="recipe-rating">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <i class="bi bi-star-half text-warning"></i>
                                    </div>
                                </div>
                                
                                <h5 class="mb-2"><?= htmlspecialchars($competition['winning_recipe_title']) ?></h5>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="#" class="text-muted small" data-bs-toggle="modal" data-bs-target="#winnerModal<?= $competition['competition_id'] ?>">
                                        <i class="bi bi-info-circle me-1"></i> Competition Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Winning Recipe Details Modal -->
                <div class="modal fade" id="winnerModal<?= $competition['competition_id'] ?>" tabindex="-1" aria-labelledby="winnerModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-danger" id="winnerModalLabel"><?= htmlspecialchars($competition['title']) ?> Winner</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="winner-recipe bg-light p-4 rounded-3">
                                    <h6 class="text-primary mb-3">Winning Recipe</h6>
                                    <h5 class="mb-3"><?= htmlspecialchars($competition['winning_recipe_title']) ?></h5>
                                    
                                    <p class="mb-2">By <strong><?= htmlspecialchars($competition['winner_name']) ?></strong></p>
                                    <p class="text-muted">Winner - <?= date('F j, Y', strtotime($competition['end_date'])) ?> (<?= floor((time() - strtotime($competition['end_date'])) / (60 * 60 * 24)) ?> days ago)</p>
                                    <p class="text-warning">
                                        <i class="bi bi-trophy me-1"></i> Won Featured Recipe among <?= htmlspecialchars($competition['entry_count']) ?> entries
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                    endif;
                endwhile; 
                
                if (!$winner_shown): 
                ?>
                <div class="col-12 animate__animated animate__fadeIn">
                    <div class="empty-state text-center p-5 bg-white rounded-3 shadow-sm">
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-4 mb-3 d-inline-block">
                            <i class="bi bi-trophy-fill fs-1 text-warning"></i>
                        </div>
                        <h4 class="mb-3">No Recent Winners Yet</h4>
                        <p class="text-muted mb-4">Our current competitions are still running. Submit your entry for a chance to win!</p>
                        <a href="#current-competitions" class="btn btn-warning rounded-pill px-4">
                            <i class="bi bi-arrow-right me-1"></i> View Current Competitions
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <h3 id="current-competitions" class="section-title animate__animated animate__fadeIn"><i class="bi bi-fire me-2"></i>Current Competitions</h3>

            <?php 
            // Reset pointer for active competitions
            $result->data_seek(0);
            $active_competitions = false;
            
            while ($competition = $result->fetch_assoc()): 
                $is_active = ($competition['status'] == 'active' && strtotime($competition['end_date']) > time());
                
                if ($is_active):
                    $active_competitions = true;
                    $days_remaining = ceil((strtotime($competition['end_date']) - time()) / (60 * 60 * 24));
                    $is_featured = $competition['is_featured'] ?? false;
                    $total_days = ceil((strtotime($competition['end_date']) - strtotime($competition['start_date'])) / (60 * 60 * 24));
                    $days_passed = $total_days - $days_remaining;
                    $progress_percent = ($days_passed / $total_days) * 100;
            ?>
                <div class="competition-card active animate__animated animate__fadeInUp <?= $is_featured ? 'featured-competition' : '' ?>">
                    <?php if ($is_featured): ?>
                        <div class="featured-banner">Featured</div>
                        <div class="prize-indicator">
                            <i class="bi bi-award"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-img-container">
                        <img src="<?= htmlspecialchars($competition['image_url'] ?? '../assets/images/default-competition.jpg') ?>" 
                            class="card-img-top" alt="<?= htmlspecialchars($competition['title']) ?>">
                        <div class="card-overlay">
                            <h3><?= htmlspecialchars($competition['title']) ?></h3>
                            <span class="badge"><i class="bi bi-people-fill me-1"></i><?= $competition['entry_count'] ?> entries</span>
                            <span class="badge"><i class="bi bi-clock me-1"></i><?= $days_remaining > 0 ? "$days_remaining days left" : "Ending today" ?></span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="time-progress">
                            <div class="time-progress-bar" style="width: <?= $progress_percent ?>%"></div>
                        </div>
                        
                        <div class="competition-dates">
                            <span><i class="bi bi-calendar-event me-1"></i> <?= date('M j, Y', strtotime($competition['start_date'])) ?></span>
                            <span><i class="bi bi-calendar-check me-1"></i> <?= date('M j, Y', strtotime($competition['end_date'])) ?></span>
                        </div>

                        <p class="competition-description"><?= htmlspecialchars($competition['description']) ?></p>

                        <div class="card-actions">
                            <a href="submit-competition-entry.php?id=<?= $competition['competition_id'] ?>" class="btn btn-join">
                                <i class="bi bi-trophy me-1"></i> Join Now
                            </a>
                            <a href="vote-entries.php?id=<?= $competition['competition_id'] ?>" class="btn btn-vote">
                                <i class="bi bi-star me-1"></i> Vote
                            </a>
                            <a href="view-competition-details.php?id=<?= $competition['competition_id'] ?>" class="btn btn-details">
                                <i class="bi bi-info-circle me-1"></i> Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endif;
            endwhile; 
            
            if (!$active_competitions): 
            ?>
                <div class="empty-state animate__animated animate__fadeIn">
                    <i class="bi bi-emoji-frown"></i>
                    <h4 class="mb-3">No Active Competitions</h4>
                    <p class="text-muted">We're preparing new exciting challenges for you! Check back soon or subscribe to be notified.</p>
                    <button class="btn btn-primary rounded-pill px-4 mt-2">
                        <i class="bi bi-bell me-1"></i> Notify Me
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px;">
                <div class="sidebar-card animate__animated animate__fadeInRight">
                    <h5><i class="bi bi-star-fill"></i> Why Participate?</h5>
                    <ul>
                        <li>Get recognition for your unique recipes</li>
                        <li>Win exciting monthly prizes and features</li>
                        <li>Gain exposure in the cooking community</li>
                        <li>Improve your skills with friendly competition</li>
                        <li>Get feedback from professional chefs</li>
                    </ul>
                </div>

                <div class="sidebar-card animate__animated animate__fadeInRight animate__delay-1s">
                    <h5><i class="bi bi-lightbulb"></i> Winning Tips</h5>
                    <ul>
                        <li><strong>Quality photos:</strong> Well-lit, high-resolution images</li>
                        <li><strong>Creative presentation:</strong> Make your dish visually appealing</li>
                        <li><strong>Unique flavors:</strong> Surprise the judges with innovation</li>
                        <li><strong>Storytelling:</strong> Share the inspiration behind your recipe</li>
                    </ul>
                </div>

                <div class="sidebar-card animate__animated animate__fadeInRight animate__delay-2s">
                    <h5><i class="bi bi-trophy"></i> Hall of Fame</h5>
                    <?php 
                    // Get top 3 winners
                    $top_winners_sql = "SELECT u.name, u.avatar_url, c.title as competition_title, 
                                      COUNT(*) as win_count
                                      FROM competitions c
                                      JOIN users u ON c.winner_id = u.id
                                      WHERE c.status = 'completed' AND c.winner_id IS NOT NULL
                                      GROUP BY u.id
                                      ORDER BY win_count DESC
                                      LIMIT 3";
                    $top_winners_result = $conn->query($top_winners_sql);
                    
                    if ($top_winners_result->num_rows > 0):
                        $place = 1;
                        while ($winner = $top_winners_result->fetch_assoc()):
                            $badge_color = $place == 1 ? 'var(--gold-color)' : 
                                          ($place == 2 ? 'var(--silver-color)' : 'var(--bronze-color)');
                    ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="position-relative me-3">
                            <img src="<?= htmlspecialchars($winner['avatar_url'] ?? '../assets/default-avatar.jpg') ?>" 
                                class="rounded-circle" width="50" height="50" alt="Top Winner">
                            <span class="position-absolute bottom-0 end-0 badge rounded-circle d-flex align-items-center justify-content-center" 
                                style="width: 24px; height: 24px; background-color: <?= $badge_color ?>; color: white;">
                                <?= $place ?>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($winner['name']) ?></h6>
                            <small class="text-muted"><?= $winner['win_count'] ?> win<?= $winner['win_count'] > 1 ? 's' : '' ?></small>
                        </div>
                    </div>
                    <?php 
                            $place++;
                        endwhile;
                    ?>
                    <a href="user-view-winner.php" class="btn btn-outline-primary w-100 mt-2">
                        View Winner
                    </a>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-hourglass-split me-1"></i>
                        No winners yet - be the first!
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Floating Action Button -->
<a href="#current-competitions" class="fab animate__animated animate__bounceIn animate__delay-2s">
    <i class="bi bi-trophy"></i>
</a>

<?php include '../includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Add confetti effect when winner is announced
    <?php if (isset($_GET['winner_announced'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Congratulations!',
            text: 'A new competition winner has been announced!',
            icon: 'success',
            confirmButtonColor: 'var(--primary-color)',
            background: 'white',
            backdrop: `
                rgba(0,0,0,0.4)
                url("https://media.giphy.com/media/xT0xezQGU5xCDJuCPe/giphy.gif")
                center top
                no-repeat
            `
        });
    });
    <?php endif; ?>

    // Animate elements when they come into view
    document.addEventListener('DOMContentLoaded', function() {
        const animateElements = document.querySelectorAll('.animate__animated');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add(entry.target.dataset.animation);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        animateElements.forEach(element => {
            observer.observe(element);
        });

        // Smooth scroll for FAB
        document.querySelector('.fab').addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 20,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
</body>
</html>