<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$tab = $_GET['tab'] ?? 'all';
$current_date = date('Y-m-d');

switch ($tab) {
    case 'upcoming':
        $filter = "WHERE start_date > '$current_date'";
        break;
    case 'ongoing':
        $filter = "WHERE start_date <= '$current_date' AND end_date >= '$current_date'";
        break;
    case 'completed':
        $filter = "WHERE end_date < '$current_date'";
        break;
    default:
        $filter = "";
        break;
}

$query = "SELECT *, 
          DATEDIFF(end_date, start_date) AS duration_days,
          (SELECT COUNT(*) FROM participants WHERE competition_id = competitions.competition_id) AS participants
          FROM competitions 
          $filter
          ORDER BY start_date DESC";
$result = mysqli_query($conn, $query);
$competitions = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

function isActive($tabName, $currentTab) {
    return $tabName === $currentTab ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Culinary Challenges | Cooking Competitions</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B6B;
            --secondary: #4ECDC4;
            --dark: #292F36;
            --light: #F7FFF7;
            --accent: #FFE66D;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: var(--dark);
            font-weight: 300;
            font-size: 1.2rem;
        }
        
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 2rem 0;
        }
        
        .filter-tab {
            padding: 8px 16px;
            background: #eee;
            border-radius: 20px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s ease;
        }
        
        .filter-tab.active,
        .filter-tab:hover {
            background: var(--primary);
            color: white;
        }
        
        .competition-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .competition-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .competition-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .card-meta {
            display: flex;
            justify-content: space-between;
            color: #7f8c8d;
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }
        
        .card-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .status-upcoming {
            background-color: rgba(255, 107, 107, 0.1);
            color: var(--primary);
        }
        
        .status-ongoing {
            background-color: rgba(78, 205, 196, 0.1);
            color: var(--secondary);
        }
        
        .status-completed {
            background-color: rgba(41, 47, 54, 0.1);
            color: var(--dark);
        }
        
        .card-description {
            color: #666;
            margin: 1rem 0;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-prize {
            font-weight: 600;
            color: var(--primary);
            margin: 1rem 0;
        }
        
        .view-btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .view-btn:hover {
            background: var(--dark);
        }
        
        .empty-state {
            text-align: center;
            grid-column: 1 / -1;
            padding: 4rem 0;
        }
        
        .empty-icon {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-title {
            font-size: 1.5rem;
            color: #7f8c8d;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .competition-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>
<main class="container">
    <header>
        <h1>Culinary Challenges</h1>
        <p class="subtitle">Showcase your skills in these exciting cooking competitions</p>
    </header>

    <nav class="filter-tabs">
        <a href="?tab=all" class="filter-tab <?= isActive('all', $tab) ?>">All</a>
        <a href="?tab=upcoming" class="filter-tab <?= isActive('upcoming', $tab) ?>">Upcoming</a>
        <a href="?tab=ongoing" class="filter-tab <?= isActive('ongoing', $tab) ?>">Ongoing</a>
        <a href="?tab=completed" class="filter-tab <?= isActive('completed', $tab) ?>">Completed</a>
    </nav>

    <div class="competition-grid">
        <?php if (empty($competitions)): ?>
            <div class="empty-state">
                <div class="empty-icon">🍳</div>
                <h3 class="empty-title">No competitions available</h3>
                <p>We're preparing new challenges. Check back soon!</p>
            </div>
        <?php else: ?>
            <?php foreach ($competitions as $comp):
                if ($current_date < $comp['start_date']) {
                    $status = 'upcoming';
                    $status_class = 'status-upcoming';
                } elseif ($current_date > $comp['end_date']) {
                    $status = 'completed';
                    $status_class = 'status-completed';
                } else {
                    $status = 'ongoing';
                    $status_class = 'status-ongoing';
                }

                $default_image = 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80';
                $image = !empty($comp['image_url']) ? $comp['image_url'] : $default_image;
            ?>
                <div class="competition-card">
                    <img src="<?= htmlspecialchars($image) ?>" 
                         alt="<?= htmlspecialchars($comp['title']) ?> cooking competition image"
                         class="card-image" loading="lazy">
                    <div class="card-content">
                        <span class="card-status <?= $status_class ?>"><?= ucfirst($status) ?></span>
                        <h3 class="card-title"><?= htmlspecialchars($comp['title']) ?></h3>
                        <div class="card-meta">
                            <span>📅 <?= $comp['duration_days'] ?> days</span>
                            <span>👥 <?= $comp['participants'] ?> participants</span>
                        </div>
                        <?php if (!empty($comp['description'])): ?>
                            <p class="card-description"><?= htmlspecialchars($comp['description']) ?></p>
                        <?php endif; ?>
                        <p class="card-prize">🏆 Prize: <?= htmlspecialchars($comp['prize']) ?></p>
                        <a href="competition_details.php?id=<?= $comp['competition_id'] ?>" class="view-btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
