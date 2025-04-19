<?php
include('../includes/db.php');
include('../includes/auth.php');

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Store current URL to redirect back after login
    header("Location: ../login.php");
    exit();
}

// Get the user id
$user_id = $_SESSION['user_id'];

// Fetch all the user’s favored communities
$stmt = $conn->prepare("
    SELECT 
        c.community_id,
        r.title AS recipe_title,
        c.slogan,
        (SELECT image_url FROM recipe_images WHERE recipe_id = c.recipe_id ORDER BY id ASC LIMIT 1) AS recipe_image
    FROM user_favor uf
    JOIN community c ON uf.community_id = c.community_id
    JOIN recipes r ON c.recipe_id = r.id
    WHERE uf.user_id = ?
    ORDER BY uf.created_at DESC
");

$my_communities = [];
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $my_communities[] = $row;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorite Communities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .community-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .community-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        .community-card img {
            object-fit: cover;
            height: 180px;
            width: 100%;
        }
        .community-card-body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .community-card-footer {
            background-color: #fff;
            text-align: center;
            padding: 15px;
        }
        .card-title {
            font-weight: bold;
        }
        .community-description {
            color: #6c757d;
        }
        .community-card .fa-heart {
            color: #e74c3c;
            font-size: 1.2em;
            cursor: pointer;
        }
        .community-card .fa-heart:hover {
            color: #c0392b;
        }
    </style>
</head>
<body>
    <?php include('../navbar.php'); ?>
    <div class="container mt-4">
        <h1 class="mb-4 text-center">My Favorite Communities</h1>
        
        <?php if (empty($my_communities)): ?>
            <p class="text-center text-muted">You haven’t favored any communities yet.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($my_communities as $com): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card community-card">
                            <?php if (!empty($com['recipe_image'])): ?>
                                <img src="../<?= htmlspecialchars($com['recipe_image']) ?>" alt="Community Image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/350x180/cccccc/000000?text=No+Image" alt="Placeholder Image">
                            <?php endif; ?>
                            <div class="community-card-body">
                                <h5 class="card-title"><?= htmlspecialchars($com['recipe_title']) ?> <i class="fas fa-heart text-danger ms-2"></i></h5> <!-- Love icon beside title -->
                                <p class="community-description"><?= htmlspecialchars($com['slogan']) ?></p>
                            </div>
                            <div class="community-card-footer">
                                <a href="community-recipe.php?community_id=<?= $com['community_id'] ?>" class="btn btn-outline-primary">Visit Community</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include('../includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
