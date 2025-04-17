<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: text/html; charset=utf-8');

// Admin authentication check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$entriesError = false;

// Initialize all variables with default values
$totalRecipes = 0;
$activeUsersCount = 0;
$totalComments = 0;
$pendingUsers = 0;
$newSignups = 0;
$popularRecipes = [];
$latestRecipes = [];
$activeUsers = [];
$ongoingCompetitions = [];
$mealPlans = [];

// Fetch Recipe Overview
try {
    // Total recipes count
    $totalRecipesStmt = $conn->prepare("SELECT COUNT(*) AS total_recipes FROM recipes");
    $totalRecipesStmt->execute();
    $totalRecipes = $totalRecipesStmt->get_result()->fetch_assoc()['total_recipes'] ?? 0;

    // Most Popular Recipes
    $popularRecipesStmt = $conn->prepare("SELECT r.title, COUNT(rv.vote) AS upvotes 
                                         FROM recipes r 
                                         LEFT JOIN recipe_votes rv ON r.id = rv.recipe_id AND rv.vote = 1
                                         GROUP BY r.id ORDER BY upvotes DESC LIMIT 5");
    $popularRecipesStmt->execute();
    $popularRecipes = $popularRecipesStmt->get_result();

    // Latest Submissions
    $latestRecipesStmt = $conn->prepare("SELECT title, created_at FROM recipes ORDER BY created_at DESC LIMIT 5");
    $latestRecipesStmt->execute();
    $latestRecipes = $latestRecipesStmt->get_result();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $entriesError = true;
}

// Fetch Meal Planning Overview
try {
    $mealPlansStmt = $conn->prepare("SELECT * FROM meal_plans WHERE user_id = ? AND planned_date >= CURDATE() ORDER BY planned_date ASC");
    $mealPlansStmt->bind_param("i", $user_id);
    $mealPlansStmt->execute();
    $mealPlans = $mealPlansStmt->get_result();
} catch (Exception $e) {
    error_log("Meal plans error: " . $e->getMessage());
}

// Fetch Community Activity
try {
    $activeUsersStmt = $conn->prepare("SELECT u.name, COUNT(c.id) AS comment_count
                                     FROM users u
                                     LEFT JOIN comments c ON u.id = c.user_id
                                     GROUP BY u.id ORDER BY comment_count DESC LIMIT 5");
    $activeUsersStmt->execute();
    $activeUsers = $activeUsersStmt->get_result();

    $totalCommentsStmt = $conn->prepare("SELECT COUNT(*) AS total_comments FROM comments");
    $totalCommentsStmt->execute();
    $totalComments = $totalCommentsStmt->get_result()->fetch_assoc()['total_comments'] ?? 0;
} catch (Exception $e) {
    error_log("Community activity error: " . $e->getMessage());
}

$competitionsQuery = $conn->query("
    SELECT 
        c.competition_id, 
        c.title, 
        c.winner_id,
        COUNT(DISTINCT e.entry_id) AS participants,
        (
            SELECT COUNT(*) 
            FROM recipe_votes rv
            JOIN competition_entries ce ON rv.recipe_id = ce.recipe_id
            WHERE ce.competition_id = c.competition_id AND rv.vote = 1
        ) AS upvotes,
        (
            SELECT COUNT(*) 
            FROM recipe_votes rv
            JOIN competition_entries ce ON rv.recipe_id = ce.recipe_id
            WHERE ce.competition_id = c.competition_id AND rv.vote = 0
        ) AS downvotes,
        (
            SELECT r.id
            FROM competition_entries ce
            JOIN recipes r ON ce.recipe_id = r.id
            LEFT JOIN recipe_votes rv ON rv.recipe_id = r.id AND rv.vote = 1
            WHERE ce.competition_id = c.competition_id
            GROUP BY r.id
            ORDER BY COUNT(rv.vote) DESC
            LIMIT 1
        ) AS leading_recipe_id,
        (
            SELECT r.title
            FROM competition_entries ce
            JOIN recipes r ON ce.recipe_id = r.id
            LEFT JOIN recipe_votes rv ON rv.recipe_id = r.id AND rv.vote = 1
            WHERE ce.competition_id = c.competition_id
            GROUP BY r.id
            ORDER BY COUNT(rv.vote) DESC
            LIMIT 1
        ) AS leading_recipe_title,
        (
            SELECT u.id
            FROM competition_entries ce
            JOIN recipes r ON ce.recipe_id = r.id
            JOIN users u ON r.created_by = u.id
            LEFT JOIN recipe_votes rv ON rv.recipe_id = r.id AND rv.vote = 1
            WHERE ce.competition_id = c.competition_id
            GROUP BY r.id
            ORDER BY COUNT(rv.vote) DESC
            LIMIT 1
        ) AS leading_user_id,
        (
            SELECT u.name
            FROM competition_entries ce
            JOIN recipes r ON ce.recipe_id = r.id
            JOIN users u ON r.created_by = u.id
            LEFT JOIN recipe_votes rv ON rv.recipe_id = r.id AND rv.vote = 1
            WHERE ce.competition_id = c.competition_id
            GROUP BY r.id
            ORDER BY COUNT(rv.vote) DESC
            LIMIT 1
        ) AS leading_user_name
    FROM competitions c
    LEFT JOIN competition_entries e ON c.competition_id = e.competition_id
    WHERE c.end_date >= CURDATE()
    GROUP BY c.competition_id, c.title, c.winner_id
");

$competitionsData = [];
$winnerData = [];

if ($competitionsQuery !== false && $competitionsQuery->num_rows > 0) {
    while ($row = $competitionsQuery->fetch_assoc()) {
        $competitionsData[] = $row;
        
        if (!empty($row['leading_recipe_id'])) {
            $winnerData[$row['competition_id']] = [
                'recipe_id' => $row['leading_recipe_id'],
                'recipe_title' => $row['leading_recipe_title'],
                'user_name' => $row['leading_user_name'],
                'upvotes' => $row['upvotes']
            ];
        }
    }
}

// Fetch User Statistics
try {
    $activeUsersCountStmt = $conn->prepare("SELECT COUNT(*) AS active_users FROM users WHERE status = 'active'");
    $activeUsersCountStmt->execute();
    $activeUsersCount = $activeUsersCountStmt->get_result()->fetch_assoc()['active_users'] ?? 0;

    $pendingUsersStmt = $conn->prepare("SELECT COUNT(*) AS pending_users FROM users WHERE status = 'pending'");
    $pendingUsersStmt->execute();
    $pendingUsers = $pendingUsersStmt->get_result()->fetch_assoc()['pending_users'] ?? 0;

    $newSignupsStmt = $conn->prepare("SELECT COUNT(*) AS new_signups FROM users WHERE created_at > NOW() - INTERVAL 30 DAY");
    $newSignupsStmt->execute();
    $newSignups = $newSignupsStmt->get_result()->fetch_assoc()['new_signups'] ?? 0;
} catch (Exception $e) {
    error_log("User stats error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #457b9d;
            --accent-color: #a8dadc;
            --light-bg: #f1faee;
            --dark-color: #1d3557;
            --success-color: #2a9d8f;
            --warning-color: #e9c46a;
            --danger-color: #e76f51;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-color);
        }

        .sidebar {
            background-color: var(--dark-color);
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            margin-bottom: 5px;
            border-radius: 5px;
            padding: 10px 15px;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .stat-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            border: none;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            border-left: 4px solid var(--primary-color);
            background-color: white;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-card.secondary {
            border-left-color: var(--secondary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-card.danger {
            border-left-color: var(--danger-color);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 700;
            padding: 1rem 1.5rem;
        }

        .card-header h4 {
            font-weight: 700;
            color: var(--dark-color);
        }

        .card-header i {
            color: var(--primary-color);
            margin-right: 10px;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .stat-number.primary {
            color: var(--primary-color);
        }

        .stat-number.secondary {
            color: var(--secondary-color);
        }

        .stat-number.success {
            color: var(--success-color);
        }

        .stat-number.warning {
            color: var(--warning-color);
        }

        .stat-number.danger {
            color: var(--danger-color);
        }

        .stat-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 1rem 1.5rem;
            transition: background-color 0.2s ease;
        }

        .list-group-item:hover {
            background-color: rgba(241, 250, 238, 0.5);
        }

        .empty-state {
            background-color: white;
            padding: 3rem;
            text-align: center;
            border: 2px dashed #D9D9D9;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--accent-color);
            opacity: 0.7;
        }

        .empty-state h5 {
            margin-top: 1rem;
            color: var(--secondary-color);
        }

        .badge-primary {
            background-color: var(--primary-color);
        }

        .badge-secondary {
            background-color: var(--secondary-color);
        }

        .badge-success {
            background-color: var(--success-color);
        }

        .badge-warning {
            background-color: var(--warning-color);
        }

        .badge-danger {
            background-color: var(--danger-color);
        }

        .table th {
            background-color: var(--light-bg);
            color: var(--dark-color);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #c1121f;
            border-color: #c1121f;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .section-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-bg);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-color);
            color: var(--dark-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
        }

        .progress-bar {
            background-color: var(--primary-color);
        }

        .nav-tabs .nav-link {
            color: var(--dark-color);
            font-weight: 600;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }

        .activity-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
            border-left: 2px solid var(--accent-color);
        }

        .activity-item:last-child {
            margin-bottom: 0;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: var(--primary-color);
        }

        .activity-date {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .activity-content {
            background-color: var(--light-bg);
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 5px;
        }

        .winner-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(45deg, #ffc107, #ff9800);
            color: #000;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 10;
        }

        .winner-card {
            border: 2px solid var(--success-color);
            position: relative;
            overflow: hidden;
        }

        .winner-card::after {
            content: 'Winner';
            position: absolute;
            top: 10px;
            right: -25px;
            background: var(--success-color);
            color: white;
            padding: 3px 30px;
            transform: rotate(45deg);
            font-size: 12px;
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .stat-trend {
            font-size: 0.8rem;
            margin-left: 5px;
        }

        .trend-up {
            color: var(--success-color);
        }

        .trend-down {
            color: var(--danger-color);
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 20px;
            }
        }

    .sidebar.d-none {
        display: none;
    }

    </style>
</head>
<body>
    <div class="d-flex">
     <!-- Sidebar -->
<div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4">Admin Panel</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
        <a href="../home.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>
    </li>
    <li>
        <a href="../recipe-modules/list-recipes.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'recipes.php' ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark"></i>
            Recipes
        </a>
    </li>
    <li>
        <a href="../competition-module/competition.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'competition.php' ? 'active' : '' ?>">
            <i class="bi bi-trophy"></i>
            Competitions
        </a>
    </li>
</ul>

    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-avatar me-2">
                <?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?>
            </div>
            <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../logout.php">Sign out</a></li>
        </ul>
    </div>
</div>
        <!-- Main Content -->
        <div class="main-content">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-5 fw-bold mb-3">Admin Dashboard</h1>
                        <p class="lead mb-0">Welcome back! Here's what's happening with your platform today.</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dashboardDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-calendar3"></i> Last 30 Days
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dashboardDropdown">
                            <li><a class="dropdown-item" href="#">Today</a></li>
                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <?php if ($entriesError): ?>
                <div class="alert alert-danger">Some data couldn't be loaded. Please try again later.</div>
            <?php endif; ?>

            <!-- Quick Stats Row -->
            <div class="row mb-4 g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number primary"><?= htmlspecialchars($totalRecipes) ?></div>
                                    <div class="stat-label">Total Recipes</div>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-journal-bookmark-fill" style="font-size: 1.5rem; color: var(--primary-color);"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-arrow-up-circle text-success"></i> 
                                    <span class="trend-up">12% from last month</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card secondary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number secondary"><?= htmlspecialchars($activeUsersCount) ?></div>
                                    <div class="stat-label">Active Users</div>
                                </div>
                                <div class="bg-secondary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-people-fill" style="font-size: 1.5rem; color: var(--secondary-color);"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-arrow-up-circle text-success"></i> 
                                    <span class="trend-up">5% from last month</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-number warning"><?= htmlspecialchars($pendingUsers) ?></div>
                                    <div class="stat-label">Pending Users</div>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-exclamation" style="font-size: 1.5rem; color: var(--warning-color);"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-arrow-down-circle text-danger"></i> 
                                    <span class="trend-down">3% from last month</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row g-4">
                <!-- Recipe Overview -->
                <div class="col-lg-6">
                    <div class="card stat-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-journal-bookmark"></i> Recipe Overview</h4>
                            <a href="recipes.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-3" id="recipeTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="popular-tab" data-bs-toggle="tab" data-bs-target="#popular" type="button" role="tab">Popular</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab">Recent</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="trending-tab" data-bs-toggle="tab" data-bs-target="#trending" type="button" role="tab">Trending</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="recipeTabsContent">
                                <div class="tab-pane fade show active" id="popular" role="tabpanel">
                                    <?php if ($popularRecipes && $popularRecipes->num_rows > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php while ($recipe = $popularRecipes->fetch_assoc()): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div class="ms-2 me-auto">
                                                        <div class="fw-bold"><?= htmlspecialchars($recipe['title'] ?? '') ?></div>
                                                        <small class="text-muted">Posted 2 days ago</small>
                                                    </div>
                                                    <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($recipe['upvotes'] ?? 0) ?></span>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-emoji-frown"></i>
                                            <h5>No Popular Recipes</h5>
                                            <p>There are no popular recipes to display.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="tab-pane fade" id="recent" role="tabpanel">
                                    <?php if ($latestRecipes && $latestRecipes->num_rows > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php while ($latestRecipe = $latestRecipes->fetch_assoc()): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div class="ms-2 me-auto">
                                                        <div class="fw-bold"><?= htmlspecialchars($latestRecipe['title'] ?? '') ?></div>
                                                        <small class="text-muted"><?= isset($latestRecipe['created_at']) ? date('M j, Y', strtotime($latestRecipe['created_at'])) : '' ?></small>
                                                    </div>
                                                    <span class="badge bg-secondary rounded-pill">New</span>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-state">
                                            <i class="bi bi-emoji-frown"></i>
                                            <h5>No Recent Recipes</h5>
                                            <p>There are no recent recipe submissions.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="tab-pane fade" id="trending" role="tabpanel">
                                    <div class="empty-state">
                                        <i class="bi bi-graph-up-arrow"></i>
                                        <h5>Trending Data</h5>
                                        <p>Trending recipes analytics will appear here.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Statistics -->
                <div class="col-lg-6">
                    <div class="card stat-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-people-fill"></i> User Statistics</h4>
                            <a href="users.php" class="btn btn-sm btn-outline-primary">Manage Users</a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 g-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="bi bi-person-check-fill" style="font-size: 1.5rem; color: var(--success-color);"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($activeUsersCount) ?></div>
                                            <small class="text-muted">Active</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="bi bi-person-x-fill" style="font-size: 1.5rem; color: var(--warning-color);"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($pendingUsers) ?></div>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            <i class="bi bi-person-plus-fill" style="font-size: 1.5rem; color: var(--secondary-color);"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($newSignups) ?></div>
                                            <small class="text-muted">New (30d)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="mt-3 mb-3">Top Contributors</h5>
                            <?php if ($activeUsers && $activeUsers->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($activeUser = $activeUsers->fetch_assoc()): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($activeUser['name'] ?? '', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($activeUser['name'] ?? '') ?></div>
                                                        <small class="text-muted">Member since Jan 2023</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-info rounded-pill"><?= htmlspecialchars($activeUser['comment_count'] ?? 0) ?> comments</span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-emoji-frown"></i>
                                    <h5>No Active Users</h5>
                                    <p>There are no active users to display.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Competitions -->
                <div class="col-lg-12">
                    <div class="card stat-card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-trophy-fill"></i> Ongoing Competitions</h4>
                            <a href="competition.php" class="btn btn-sm btn-outline-primary">Manage Competitions</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($competitionsData)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Competition</th>
                                                <th>Participants</th>
                                                <th>Current Leader</th>
                                                <th>Votes</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Updated dashboard.php code -->
<?php
// Assuming this is part of your loop to display competitions
foreach ($competitionsData as $comp): ?>
    <tr>
        <td><strong><?= htmlspecialchars($comp['title']) ?></strong></td>
        <td><?= htmlspecialchars($comp['participants']) ?></td>
        <td>
            <?php if (!empty($comp['leading_user_name'])): ?>
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-2">
                        <?= strtoupper(substr($comp['leading_user_name'], 0, 1)) ?>
                    </div>
                    <?= htmlspecialchars($comp['leading_user_name']) ?>
                    (<?= htmlspecialchars($comp['leading_recipe_title']) ?>)
                </div>
            <?php else: ?>
                <span class="text-muted">No leader yet</span>
            <?php endif; ?>
        </td>
        <td>
            <div class="d-flex flex-column">
                <span class="text-success">↑ <?= htmlspecialchars($comp['upvotes'] ?? 0) ?></span>
                <span class="text-danger">↓ <?= htmlspecialchars($comp['downvotes'] ?? 0) ?></span>
                <small class="text-muted">Net: <?= ($comp['upvotes'] ?? 0) - ($comp['downvotes'] ?? 0) ?></small>
            </div>
        </td>
        <td>
            <?php if (!empty($comp['winner_id'])): ?>
                <span class="badge bg-success">
                    <i class="bi bi-check-circle-fill"></i> Announced
                </span>
            <?php else: ?>
                <span class="badge bg-warning">
                    <i class="bi bi-hourglass-split"></i> Ongoing
                </span>
            <?php endif; ?>
        </td>
        <td>
            <?php if (!empty($comp['winner_id'])): ?>
                <a href="view_winner.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View
                </a>
            <?php else: ?>
                <!-- Ensure competition_id is passed in URL for announce_winner.php -->
                <a href="announce_winner.php?id=<?= $comp['competition_id'] ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-megaphone"></i> Announce Winner
                </a>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>

                                                  
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state text-center">
                                    <i class="bi bi-emoji-frown" style="font-size: 3rem; color: var(--secondary-color);"></i>
                                    <h5>No Ongoing Competitions</h5>
                                    <p>There are currently no active competitions.</p>
                                    <a href="competitions.php?action=create" class="btn btn-primary mt-3">
                                        <i class="bi bi-plus-circle"></i> Create New Competition
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-lg-6">
                    <div class="card stat-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-activity"></i> Recent Activity</h4>
                            <a href="activity.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="activity-item">
                                <div class="activity-date">Today, 10:45 AM</div>
                                <div class="activity-content">
                                    <strong>John Doe</strong> submitted a new recipe <strong>Pasta Carbonara</strong>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-date">Today, 09:30 AM</div>
                                <div class="activity-content">
                                    <strong>Jane Smith</strong> commented on <strong>Chocolate Cake Recipe</strong>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-date">Yesterday, 4:15 PM</div>
                                <div class="activity-content">
                                    <strong>Mike Johnson</strong> joined the platform
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-date">Yesterday, 2:00 PM</div>
                                <div class="activity-content">
                                    <strong>Sarah Williams</strong> voted for <strong>Vegetable Lasagna</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Analytics -->
                <div class="col-lg-6">
                    <div class="card stat-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><i class="bi bi-graph-up"></i> Platform Analytics</h4>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="analyticsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Last 30 Days
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="analyticsDropdown">
                                    <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                    <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                    <li><a class="dropdown-item" href="#">Last 90 Days</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="analyticsChart"></canvas>
                            </div>
                            <div class="row mt-4 text-center">
                                <div class="col-md-4">
                                    <div class="p-2">
                                        <div class="stat-number primary">1,245</div>
                                        <div class="stat-label">Visits</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2">
                                        <div class="stat-number secondary">342</div>
                                        <div class="stat-label">New Users</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2">
                                        <div class="stat-number success">78</div>
                                        <div class="stat-label">Recipes Added</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Simple animation for stat cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Initialize analytics chart
            const ctx = document.getElementById('analyticsChart');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Visits',
                        data: [650, 590, 800, 810, 560, 550, 1245],
                        borderColor: '#e63946',
                        backgroundColor: 'rgba(230, 57, 70, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'New Users',
                        data: [120, 190, 130, 150, 220, 200, 342],
                        borderColor: '#457b9d',
                        backgroundColor: 'rgba(69, 123, 157, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Recipes Added',
                        data: [30, 45, 60, 50, 70, 65, 78],
                        borderColor: '#2a9d8f',
                        backgroundColor: 'rgba(42, 157, 143, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>