<?php
include('../includes/db.php');
include('../includes/auth.php');
include('../community-module/community-function.php');

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Store current URL to redirect back after login
    header("Location: ../login.php");
    exit();
}

// Get the user community information
$user_id = $_SESSION['user_id'];
$user_info = mysqli_fetch_assoc(mysqliOperation("SELECT u.name, uc.user_comm_id, a.avatar_id, a.avatar_url
            FROM users u JOIN user_community uc ON u.id = uc.user_id JOIN avatar a ON uc.user_avatar_id = a.avatar_id
            WHERE (u.id = '$user_id')"));
$user_name = $user_info['name'];    //get user name
$user_community_id = $user_info['user_comm_id'];    //get community id of users
$user_avatar_id = $user_info['avatar_id'];  //get avatar id
$user_avatar_url = htmlspecialchars($user_info['avatar_url']);  //get avatar url
$_SESSION['user_community_id'] = $user_community_id;

$user_fav_info = getUserFavorInfo(); 

// Fetch Top Rated Recipes
$top_rated_stmt = $conn->prepare("
    SELECT 
        r.id AS recipe_id,
        r.title AS recipe_title,
        AVG(rt.rate_value) AS average_rating,
        COUNT(rt.id) AS rating_count,
        (SELECT image_url FROM recipe_images WHERE recipe_id = r.id ORDER BY id ASC LIMIT 1) AS recipe_image,
        c.community_id
    FROM rating rt
    JOIN community c ON rt.community_id = c.community_id
    JOIN recipes r ON c.recipe_id = r.id
    GROUP BY r.id
    ORDER BY average_rating DESC
    LIMIT 5
");

$top_rated_recipes = [];
if ($top_rated_stmt) {
    $top_rated_stmt->execute();
    $result = $top_rated_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $top_rated_recipes[] = $row;
    }
    $top_rated_stmt->close();
}


// Query: Top 5 Most Favored Communities (all users)
$top_fav_stmt = $conn->prepare("
    SELECT 
        c.community_id,
        r.title AS recipe_title,
        c.slogan,
        COUNT(uf.id) AS favor_count,
        (SELECT image_url FROM recipe_images WHERE recipe_id = c.recipe_id ORDER BY id ASC LIMIT 1) AS recipe_image
    FROM user_favor uf
    JOIN community c ON uf.community_id = c.community_id
    JOIN recipes r ON c.recipe_id = r.id
    GROUP BY c.community_id
    ORDER BY favor_count DESC
    LIMIT 5
");

$top_communities = [];
if ($top_fav_stmt) {
    $top_fav_stmt->execute();
    $result = $top_fav_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $top_communities[] = $row;
    }
    $top_fav_stmt->close();
}

// Query: My Favored Communities (current user)
$my_fav_stmt = $conn->prepare("
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
    LIMIT 6
");

$my_communities = [];

if ($my_fav_stmt) {
    $my_fav_stmt->bind_param("i", $user_id);
    $my_fav_stmt->execute();
    $result = $my_fav_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $my_communities[] = $row;
    }
    $my_fav_stmt->close();
}

// Query: Top 5 Trending Discussions (based on post count)
$trending_discussions_stmt = $conn->prepare("
 SELECT 
        c.community_id,
        r.title AS recipe_title
    FROM 
        post p
    JOIN 
        community c ON p.community_id = c.community_id
    JOIN 
        recipes r ON p.user_id = r.id  -- Match user_id in post to id in recipes
    GROUP BY 
        c.community_id, r.title
    ORDER BY 
        COUNT(p.id) DESC  -- Order by highest post count
    LIMIT 3  -- Get only the community with the highest number of posts
");


$trending_discussions = [];

if ($trending_discussions_stmt) {
    $trending_discussions_stmt->execute();
    $result = $trending_discussions_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $trending_discussions[] = $row;
    }
    $trending_discussions_stmt->close();
}

// Get the amount of user recipes
$user_recipe = mysqli_fetch_assoc(mysqliOperation("SELECT COUNT(*) AS recipe_count FROM recipes 
                                                    WHERE created_by = '$user_id'"));
$recipe_amount = $user_recipe['recipe_count'];

// Update the user avatar
if(isset($_POST['update_avatar'])){
    $new_avatar = $_POST['avatar_selected'];
    $update_new_avatar = mysqliOperation("UPDATE user_community SET user_avatar_id = '$new_avatar' 
    WHERE user_id = '$user_id'");

    header("Location: community-mainpage.php?avatar=updated");
    exit;
}

// Get the community own info
function getAmountCommunity() {
    $user_id = $_SESSION['user_id'];
    global $conn;
    // Prepare the statement
    $stmt = $conn->prepare("
        SELECT c.community_id
        FROM community c
        JOIN recipes r ON c.recipe_id = r.id
        WHERE r.created_by = ?
    ");

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all community_ids into an array
        $community_ids = [];
        while ($row = $result->fetch_assoc()) {
            $community_ids[] = $row['community_id'];
        }

        // Total number (including duplicates)
        $total_comm = count($community_ids);
        $stmt->close();
        // Return both total and IDs
        return [
            'total' => $total_comm,
            'ids' => $community_ids,
        ];
    } else {
        $stmt->close();
        // Handle error if statement failed
        return false;
    }
}

function getUserFavorInfo(){
    $user_id = $_SESSION['user_id'];
    global $conn;
    $stmt = $conn->prepare("
        SELECT community_id
        FROM user_favor
        WHERE user_id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $community_ids = [];
        while ($row = $result->fetch_assoc()) {
            $community_ids[] = $row['community_id'];
        }

        $total_favorites = count($community_ids);
        
        $stmt->close();

        return [
            'total' => $total_favorites,
            'ids' => $community_ids,
        ];
    } else {
        return false;
    }
}

// View the post
function getAllCommunityPostsWithImages() {
    global $conn;

    $posts = [];

    // First, fetch all posts from the given community
    $stmt = $conn->prepare("
        SELECT 
            post.id, 
            post.community_id, 
            post.user_id, 
            post.recipe_id,
            post.comment, 
            post.created_at AS post_created_at, 
            post.user_role,
            users.name AS user_name,
            avatar.avatar_url
        FROM 
            post
        INNER JOIN 
            users ON post.user_id = users.id
        INNER JOIN 
            user_community ON post.user_id = user_community.user_id
        INNER JOIN 
            avatar ON user_community.user_avatar_id = avatar.avatar_id
        ORDER BY 
            post.created_at DESC
    ");

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $post_id = $row['id'];

        // Now, get all images related to this post
        $img_stmt = $conn->prepare("SELECT photo_url FROM post_image WHERE post_id = ?");
        $img_stmt->bind_param("i", $post_id);
        $img_stmt->execute();
        $img_result = $img_stmt->get_result();

        $image_urls = [];
        while ($img = $img_result->fetch_assoc()) {
            $image_urls[] = $img['photo_url'];
        }
        $img_stmt->close();

        // Store post with its images
        $row['post_id'] = $post_id;
        $row['images'] = $image_urls;
        $posts[] = $row;
    }

    $stmt->close();

    return $posts;
}

// Get post comment
function getPostComments($post_id) {
    global $conn;
    $stmt = $conn->prepare("
    SELECT 
        rc.id, rc.community_id, rc.post_id, rc.user_id, rc.reply_id, rc.content, rc.created_at,
        u.name AS user_name,
        a.avatar_url
    FROM 
        reply_comment rc
    JOIN 
        users u ON rc.user_id = u.id
    JOIN 
        user_community uc ON uc.user_id = u.id
    JOIN 
        avatar a ON uc.user_avatar_id = a.avatar_id
    WHERE 
        rc.post_id = ?
    ORDER BY 
        rc.created_at ASC
    ");

    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    $stmt->close();
    return $comments;
}

// Delete post 
if (isset($_POST['delete_post']) && isset($_POST['delete_post_id'])) {
    $post_id = $_POST['delete_post_id'];
    $community_id = $_GET['community_id'];

    // Delete related data first to avoid foreign key constraint issues
    $delete_image = $conn->prepare("DELETE FROM post_image WHERE post_id = ?");
    $delete_image->bind_param("i", $post_id);
    $delete_image->execute();
    $delete_image->close();

    $delete_reply = $conn->prepare("DELETE FROM reply_comment WHERE post_id = ?");
    $delete_reply->bind_param("i", $post_id);
    $delete_reply->execute();
    $delete_reply->close();

    $delete_like = $conn->prepare("DELETE FROM like_post WHERE post_id = ?");
    $delete_like->bind_param("i", $post_id);
    $delete_like->execute();
    $delete_like->close();

    // Now delete the actual post
    $stmt = $conn->prepare("DELETE FROM post WHERE id = ?");
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        $update_status = "Post deleted successfully!";
    } else {
        $update_status = "Failed to delete post.";
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?community_id=" . urlencode($community_id). "#post-section");
    exit();
}

// Build structure for comments
function buildCommentThread($comments) {
    $thread = [];
    $lookup = [];

    // First, index all comments by ID
    foreach ($comments as $comment) {
        $comment['replies'] = [];
        $lookup[$comment['id']] = $comment;
    }

    // Now, build the tree
    foreach ($lookup as $id => $comment) {
        if ($comment['reply_id']) {
            // It's a reply to another comment
            $parent_id = $comment['reply_id'];
            if (isset($lookup[$parent_id])) {
                $lookup[$parent_id]['replies'][] = &$lookup[$id];
            }
        } else {
            // It's a top-level comment
            $thread[] = &$lookup[$id];
        }
    }
    return $thread;
}

function getLikeCount($post_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*)  FROM reply_comment WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($comment_count);
    $stmt->fetch();
    $stmt->close();

    return $comment_count;
}

function getCommentCount($post_id){
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM like_post WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($like_count);
    $stmt->fetch();
    $stmt->close();

    return $like_count;
}

// Check if the user has liked the post
function getUserLike($user_id, $post_id){
    global $conn;
    $stmt = $conn->prepare("SELECT is_liked FROM like_post WHERE user_id = ? AND post_id = ?");
    if($stmt){
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->store_result();
        
        $liked = false; // Assume the user has not liked the post
        if ($stmt->num_rows > 0) {
            $liked = true; // The like exists
        }
    }
    $stmt->close();
    return $liked;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Hub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Animate.css for smooth animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Main color scheme */
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
            --accent-color: #ffd166;
            --light-bg: #f8f9fa;
            --dark-text: #343a40;
            --light-text: #f8f9fa;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Nunito', sans-serif;
        }

        /* Enhanced card styling */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .card-header {
            border-bottom: none;
            background-color: white;
            padding: 20px;
        }

        .card-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            background-color: white;
            padding: 15px 20px;
        }

        /* Recipe group card enhancements */
        .recipe-group-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 15px;
            border: none;
        }

        .recipe-group-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        /* Community post styling */
        .community-post {
            border-radius: 15px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        /* Avatar styling */
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Smaller avatars for comments */
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        }

        /* Post image enhancement */
        .post-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-top: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Trendy sidebar styling */
        .trending-group {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
        }

        .sidebar {
            position: sticky;
            top: 20px;
        }

        /* Enhanced buttons */
        .reaction-btn {
            color: #6c757d;
            transition: all 0.3s;
            border-radius: 20px;
            padding: 8px 15px;
        }

        .reaction-btn:hover {
            color: var(--primary-color);
            background-color: rgba(255,107,107,0.1);
        }

        .reaction-btn.active {
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Custom buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #ff5252;
            border-color: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.3);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.3);
        }

        /* Custom badges */
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 20px;
        }

        .badge-primary {
            background-color: var(--primary-color);
        }

        /* Improved search bar */
        #searchInput {
            border-radius: 25px;
            padding: 12px 20px;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        #searchInput:focus {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        #suggestions {
            border-radius: 15px;
            margin-top: 5px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        #suggestions .item {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: all 0.2s;
        }

        #suggestions .item:hover {
            background-color: rgba(255,107,107,0.1);
        }

        /* Profile stats cards */
        .stat-icon {
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            margin: 0 auto;
            background-color: #f8f9fa;
            color: var(--primary-color);
            font-size: 18px;
            transition: all 0.3s;
        }

        .stat-icon:hover {
            background-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .community-post {
            animation: fadeIn 0.5s ease-out forwards;
        }

        /* Improved list groups */
        .list-group-item {
            border: none;
            padding: 15px 20px;
            transition: all 0.3s;
        }

        .list-group-item:hover {
            background-color: rgba(255,107,107,0.05);
            transform: translateX(5px);
        }

        .list-group-item-action:focus, .list-group-item-action:hover {
            color: var(--primary-color);
        }

        /* Rating stars */
        .text-warning {
            color: #ffd166 !important;
        }

        /* Avatar selection modal enhancements */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
        }

        /* Bottom box shadow for sticky nav */
        .navbar {
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }    
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include('../navbar.php'); ?>
    <div class="container mt-4">
        <div class="row">
            <!-- Left Sidebar - Recipe Groups -->
            <div class="col-lg-3 mb-5">
                <div class="sidebar">
                    <!-- User Profile Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body text-center pt-4 pb-3">
                            <div class="position-relative d-inline-block mb-3">
                                <!-- Replace with dynamic user avatar from database -->
                                <img src="../uploads/community/avatar/<?= $user_avatar_url ?>" alt="User Avatar" class="user-avatar rounded-circle">
                            </div>
                            <!-- User details -->
                            <h5 class="mb-1">
                                <!-- Replace with dynamic username from database -->
                                <?= htmlspecialchars($user_info['name']) ?>
                            </h5>
                            
                            <!-- Edit profile button -->
                            <a href="#" class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#avatarModal">
                                <i class="fas fa-edit me-1"></i> Edit Avatar
                            </a>                         
                        </div>
                        <!-- not yet complete -->
                        <!-- User stats -->
                        <div class="card-footer bg-white pt-0 pb-3">
                            <div class="row text-center user-stats g-0">
                                <div class="col-6 p-2">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="stat-icon bg-light mb-1">
                                            <i class="fas fa-utensils text-primary"></i>
                                        </div>
                                        <!-- Replace with dynamic post count from database -->
                                         <?php 
                                        $user_comm_info = getAmountCommunity(); 
                                        ?>
                                        <div class="fw-bold"><?=$user_comm_info['total'] ?></div>
                                        <div class="text-muted small">Own Comm</div>
                                    </div>
                                </div>
                                <div class="col-4 p-2">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="stat-icon bg-light mb-1">
                                            <i class="fas fa-users text-success"></i>
                                        </div>
                                        <!-- Replace with dynamic communities count from database -->
                                        <?php 
                                        $user_fav_info = getUserFavorInfo(); 
                                        ?>
                                        <div class="fw-bold"><?= $user_fav_info['total'] ?></div>
                                        <div class="text-muted small">Fav Comm</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Favorite Community -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Favored Communities</h5>
                        </div>
                        <div class="list-group list-group-flush">
                        <?php if (empty($top_communities)): ?>
                        <div class="list-group-item text-muted text-center">No data yet</div>
                            <?php else: ?>
                                <?php foreach ($top_communities as $com): ?>
                                    <a href="community-recipe.php?community_id=<?= $com['community_id'] ?>" 
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($com['recipe_image'])): ?>
                                                <img src="../<?= htmlspecialchars($com['recipe_image']) ?>" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-users text-secondary me-2"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($com['recipe_title']) ?>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?= $com['favor_count'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- My Favorite Communities -->
                    <div class="card mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-heart me-2 text-danger"></i> My Favored Communities
                        </h5>
                        <?php if (count($my_communities) > 5): ?>
                            <a href="view-favorite.php" class="small text-decoration-none ms-3">View All</a>
                        <?php endif; ?>
                    </div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($my_communities)): ?>
                                <div class="list-group-item text-muted text-center">You haven't favored any communities yet.</div>
                            <?php else: ?>
                                <?php foreach (array_slice($my_communities, 0, 5) as $com): ?>
                                    <a href="community-recipe.php?community_id=<?= $com['community_id'] ?>" 
                                    class="list-group-item list-group-item-action d-flex align-items-center">
                                        <?php if (!empty($com['recipe_image'])): ?>
                                            <img src="../<?= htmlspecialchars($com['recipe_image']) ?>" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                        <?php else: ?>
                                            <i class="fas fa-users text-secondary me-2"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($com['recipe_title']) ?>
                                        <i class="fas fa-heart text-danger ms-2"></i> 
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center Content - Community Posts Feed -->
            <div class="col-lg-6 mb-5">
                <!-- Search Bar -->
                <div class="card mb-3 d-flex justify-content-center align-items-center" style="border:none; box-shadow:none;">
                    <div class="search-container w-100">
                        <input id="searchInput" class="form-control" type="search" placeholder="Search for recipes or tags..." autocomplete="off">
                        <div id="suggestions"></div>
                    </div>
                </div>

                <!-- Discussion Posts -->
                <?php
                    $all_posts = getAllCommunityPostsWithImages();
                    foreach($all_posts as $row){
                    // Show the container
                    $created_at = new DateTime($row['post_created_at']);
                    $now = new DateTime();
                    
                    // Get total seconds difference
                    $diff_in_seconds = $now->getTimestamp() - $created_at->getTimestamp();
                    
                    if ($diff_in_seconds < 60) {
                        $total_seconds = max(1, $diff_in_seconds); // show at least "1 second ago"
                        $post_hrs = $total_seconds . " seconds ago";
                    } elseif ($diff_in_seconds < 3600) {
                        $total_mins = floor($diff_in_seconds / 60);
                        $post_hrs = $total_mins . " mins ago";
                    } elseif ($diff_in_seconds < 86400) {
                        $total_hours = floor($diff_in_seconds / 3600);
                        $post_hrs = $total_hours . " hours ago";
                    } else {
                        $post_hrs = $created_at->format('d:m:Y'); // format as day:month:year
                    }
                    ?>
                    <!-- Show post data -->
                    <div class="card community-post mb-4 shadow-sm">
                        <!-- Show user info -->
                        <div class="card-header bg-white">
                            <div class="d-flex align-items-center">
                                <img src="../uploads/community/avatar/<?= $row['avatar_url'] ?>" class="user-avatar me-2" alt="User Avatar">
                                <div>
                                    <h6 class="mb-0"><?= $row['user_name'] ?></h6>
                                    <span>Post on </span>
                                    <small class="text-muted"><?= $post_hrs ?></small>
                                </div>
                                <?php if ($_SESSION['user_role'] === "admin" || $row['user_id'] == $_SESSION['user_id']) {?>
                                <div class="dropdown ms-auto">
                                    <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>                                             
                                        <form action="" method="POST" class="dropdown-item" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                            <input type="hidden" name="delete_post_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="community_id" value="<?= $community_id ?>">
                                            <button type="submit" name="delete_post" class="btn btn-link text-danger p-0 m-0" style="text-decoration: none;">
                                                <i class="fas fa-trash-alt me-2"></i>Delete Post
                                            </button>
                                        </form>
                                        </li>
                                    </ul>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- Show post info -->
                        <div class="card-body">
                            <p class="card-text"><?= $row['comment'] ?></p>
                            <?php if (!empty($row['images'])) { ?>
                                <div class="post-images-container mb-3" data-post-id="<?= $row['id'] ?>">
                                    <?php 
                                    $imageCount = count($row['images']);
                                    if ($imageCount == 1) { ?>
                                        <img src="<?= htmlspecialchars($row['images'][0]) ?>" class="img-fluid post-image w-100 rounded" alt="Post Image" data-index="0" data-post-id="<?= $row['id'] ?>">
                                    <?php } else if ($imageCount == 2) { ?>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <img src="<?= htmlspecialchars($row['images'][0]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="0" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                            <div class="col-6">
                                                <img src="<?= htmlspecialchars($row['images'][1]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="1" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                        </div>
                                    <?php } else if ($imageCount == 3) { ?>
                                        <div class="row g-2">
                                            <div class="col-12 mb-2">
                                                <img src="<?= htmlspecialchars($row['images'][0]) ?>" class="img-fluid post-image w-100 rounded" style="object-fit: cover; max-height: 300px;" alt="Post Image" data-index="0" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                            <div class="col-6">
                                                <img src="<?= htmlspecialchars($row['images'][1]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="1" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                            <div class="col-6">
                                                <img src="<?= htmlspecialchars($row['images'][2]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="2" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="row g-2">
                                            <div class="col-6 mb-2">
                                                <img src="<?= htmlspecialchars($row['images'][0]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="0" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <img src="<?= htmlspecialchars($row['images'][1]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="1" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                            <div class="col-6">
                                                <img src="<?= htmlspecialchars($row['images'][2]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover;" alt="Post Image" data-index="2" data-post-id="<?= $row['id'] ?>">
                                            </div>
                                            <div class="col-6 position-relative">
                                                <img src="<?= htmlspecialchars($row['images'][3]) ?>" class="img-fluid post-image w-100 h-100 rounded" style="object-fit: cover; filter: <?= $imageCount > 4 ? 'brightness(0.7)' : 'none'; ?>" alt="Post Image" data-index="3" data-post-id="<?= $row['id'] ?>">
                                                <?php if ($imageCount > 4) { ?>
                                                    <div class="more-images-overlay" data-post-id="<?= $row['id'] ?>" data-index="3">
                                                        <span class="more-images-text">+<?= $imageCount - 4 ?> more</span>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } 
                                    // Store all images for this post in a data attribute for lightbox
                                    $imagesJson = htmlspecialchars(json_encode($row['images'])); ?>
                                    <div class='post-images-data' data-post-id='{<?= $row['id'] ?>}' data-images='{$imagesJson}' style='display:none;'>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="card-footer bg-white">

                            <!-- Like and comment details -->
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <?php 
                                    $user_likes = 0;
                                    $user_comment = 0;
                                    $user_likes = getLikeCount($row['id']);
                                    $user_comment = getCommentCount($row['id']);
                                    ?>
                                    <span class="me-3"><i class="fas fa-thumbs-up text-primary me-1"></i><?= $user_likes?> likes</span>
                                    <span><i class="fas fa-comment text-muted me-1"></i><?= $user_comment ?> comments</span>
                                </div>
                            </div>

                            <hr class="my-2">
                            <!-- Like and comment button -->
                            <div class="d-flex justify-content-around">
                                <?php  ?>
                                <form method="POST" action="">
                                    <?php $liked = getUserLike($user_id, $row['id']); ?>
                                    <input type="hidden" name="like_post_id" value="<?= $row['id'] ?>" >
                                    <button type="submit" name="like_toggle" class="btn btn-link text-decoration-none reaction-btn" style="color: <?= $liked ? '#6c757d' : '#0d6efd'; ?>">
                                        <i class="fas fa-thumbs-up me-1"></i> <?= $liked ? 'Liked' : 'Like' ?>
                                    </button>
                                </form>
                                <button class="btn btn-link text-decoration-none reaction-btn comment-toggle-btn">
                                    <i class="fas fa-comment me-1"></i>Comment
                                </button>
                            </div>
                            
                            <!-- Comments Section - Initially Hidden -->
                            <div class="comments-section mt-3" style="display: none;">
                                <?php 
                                $comments = getPostComments($row['post_id']);
                                foreach($comments as $comment){
                                    $created_at = new DateTime($comment['created_at']);
                                    $now = new DateTime();
                                    
                                    // Get total seconds difference
                                    $diff_in_seconds = $now->getTimestamp() - $created_at->getTimestamp();
                                    
                                    if ($diff_in_seconds < 60) {
                                        $total_seconds = max(1, $diff_in_seconds); // show at least "1 second ago"
                                        $comment_time = $total_seconds . " seconds ago";
                                    } elseif ($diff_in_seconds < 3600) {
                                        $total_mins = floor($diff_in_seconds / 60);
                                        $comment_time = $total_mins . " mins ago";
                                    } elseif ($diff_in_seconds < 86400) {
                                        $total_hours = floor($diff_in_seconds / 3600);
                                        $comment_time = $total_hours . " hours ago";
                                    } else {
                                        $comment_time = $created_at->format('d:m:Y'); // format as day:month:year
                                    } ?>
                                    
                                    <!-- Comment item -->
                                    <div class="d-flex mb-2">
                                        <img src="../uploads/community/avatar/<?= $comment['avatar_url'] ?>" class="rounded-circle me-2" width="32" height="32" alt="Commenter">
                                        <div class="bg-light rounded-3 p-2 flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= $comment['user_name'] ?></strong>
                                                <small class="text-muted"><?= $comment_time ?></small>
                                            </div>
                                            <p class="mb-0"><?= $comment['content'] ?></p>
                                            
                                            <?php
                                            // Display any replies to this comment
                                            foreach($comments as $reply) {
                                                if ($reply['reply_id'] == $comment['id']) {
                                                    $reply_created_at = new DateTime($reply['created_at']);
                                                    $reply_now = new DateTime();
                                                    
                                                    // Get total seconds difference for reply
                                                    $reply_diff_in_seconds = $reply_now->getTimestamp() - $reply_created_at->getTimestamp();
                                                    
                                                    if ($reply_diff_in_seconds < 60) {
                                                        $reply_total_seconds = max(1, $reply_diff_in_seconds); 
                                                        $reply_time = $reply_total_seconds . " seconds ago";
                                                    } elseif ($reply_diff_in_seconds < 3600) {
                                                        $reply_total_mins = floor($reply_diff_in_seconds / 60);
                                                        $reply_time = $reply_total_mins . " mins ago";
                                                    } elseif ($reply_diff_in_seconds < 86400) {
                                                        $reply_total_hours = floor($reply_diff_in_seconds / 3600);
                                                        $reply_time = $reply_total_hours . " hours ago";
                                                    } else {
                                                        $reply_time = $reply_created_at->format('d:m:Y');
                                                    }
                                            ?>
                                            <!-- Reply Comment Input -->
                                            <div class="reply-input" data-comment-id="<?= $comment['id'] ?>" style="display: none;">
                                                <div class="d-flex mt-3 align-items-center">
                                                    <img src="<?= $avatar_url ?>" class="rounded-circle me-2 d-flex align-items-center" width="32" height="32" alt="Your Avatar">
                                                    <div class="flex-grow-1">
                                                        <?php $community_id = $_GET['community_id']; 
                                                        $post_id = $row['post_id'];
                                                        $reply_id = $comment['id']; ?>
                                                        <form action="comment-add.php?community_id=<?= urlencode($community_id) ?>&user_id=<?= urlencode($user_id) ?>&user_reply_id=<?= urlencode($reply_id) ?>&post_id=<?= urlencode($post_id) ?>" method="POST" class="d-flex align-items-center">
                                                            <div class="input-group">
                                                                <input type="text" name="comment_txt" class="form-control form-control-sm rounded-pill" placeholder="Reply to <?= $comment['user_name'] ?>...">
                                                                <button type="submit" name="comment_submit" class="btn btn-sm btn-outline-primary rounded-pill ms-2" style="height:100%;">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                                }
                                            }
                                            ?>
                                            
                                            <!-- Reply Comment Input -->
                                            <div class="reply-input" style="display: none;">
                                                <div class="d-flex mt-3 align-items-center" data-comment-id="<?= $comment['id'] ?>">
                                                    <img src="<?= $avatar_url ?>" class="rounded-circle me-2 d-flex align-items-center" width="32" height="32" alt="Your Avatar">
                                                    <div class="flex-grow-1">
                                                        <?php $community_id = $_GET['community_id']; 
                                                        $post_id = $row['post_id'];
                                                        $reply_id = $comment['id']; ?>
                                                        <form action="comment-add.php?community_id=<?= urlencode($community_id) ?>&user_id=<?= urlencode($user_id) ?>&user_reply_id=<?= urlencode($reply_id) ?>&post_id=<?= urlencode($post_id) ?>" method="POST" class="d-flex align-items-center">
                                                            <div class="input-group">
                                                                <input type="text" name="comment_txt" class="form-control form-control-sm rounded-pill" placeholder="Reply to <?= $comment['user_name'] ?>...">
                                                                <button type="submit" name="comment_submit" class="btn btn-sm btn-outline-primary rounded-pill ms-2" style="height:100%;">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                
                                <!-- Comment Input -->
                                <div class="d-flex mt-3 align-items-center">
                                    <img src="<?= $avatar_url ?>" class="rounded-circle me-2 d-flex align-items-center" width="32" height="32" alt="Your Avatar">
                                    <div class="flex-grow-1">
                                        <form action="comment-add.php?community_id=<?= urlencode($community_id) ?>&user_id=<?= urlencode($user_id) ?>&user_reply_id=&post_id=<?= urlencode($post_id)?>" method="POST" class="d-flex align-items-center">
                                            <div class="input-group">
                                                <input type="text" name="comment_txt" class="form-control form-control-sm rounded-pill" placeholder="Write a comment...">
                                                <button type="submit" name="comment_submit" class="btn btn-sm btn-outline-primary rounded-pill ms-2" style="height:100%;">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>

            <!-- Right Sidebar - Trending & Suggested -->
            <div class="col-lg-3 mb-5">
                <div class="sidebar">
                    <!-- Trending Discussions Sidebar Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Trending Discussions</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($trending_discussions)): ?>
                                <div class="list-group-item text-muted text-center">No trending discussions yet</div>
                            <?php else: ?>
                                <?php foreach ($trending_discussions as $discussion): ?>
                                    <a href="community-recipe.php?community_id=<?= htmlspecialchars($discussion['community_id']) ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($discussion['recipe_title']) ?></h6>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Highest Rated Recipes -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Rated Recipes</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($top_rated_recipes)): ?>
                                <div class="list-group-item text-muted text-center">No rated recipes yet</div>
                            <?php else: ?>
                                <?php foreach ($top_rated_recipes as $recipe): ?>
                                    <a href="community-recipe.php?community_id=<?= $recipe['community_id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <!-- Display recipe image -->
                                            <img src="..<?= htmlspecialchars($recipe['recipe_image']) ?>" class="rounded me-2" width="50" height="50" alt="Recipe Image">
                                            <div>
                                                <!-- Display recipe title -->
                                                <h6 class="mb-0"><?= htmlspecialchars($recipe['recipe_title']) ?></h6>
                                                <div class="text-warning">
                                                    <!-- Display the rating stars -->
                                                    <?php 
                                                    $rating = round($recipe['average_rating']); // Round to the nearest integer
                                                    for ($i = 0; $i < 5; $i++): 
                                                    ?>
                                                        <i class="fas fa-star <?= $i < $rating ? 'text-warning' : 'text-muted' ?>"></i>
                                                    <?php endfor; ?>
                                                    <!-- Display the number of ratings -->
                                                    <small class="text-muted ms-1">(<?= $recipe['rating_count'] ?> ratings)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avatar Selection Model -->
            <div class="modal fade" id="avatarModal" tabindex="1" aria-labelledby="avatarModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <!-- Header -->
                            <h5 class="modal-title" id="avatarModalLabel">
                                Choose Your Avatar
                            </h5>
                            <!-- Close the page -->
                            <button type="button" id="closeBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">   
                            <form id="avatarForm" method="post">
                                <input type="hidden" name="avatar_selected" id="selected_avatar_id" value="<?= $current_avatar_id ?>">

                                <div class="row g-3">
                                    <?php 
                                    $result = mysqli_query($conn, "SELECT * FROM avatar") or die("Query failed");
                                    $amount = 0;
                                    $current_avatar_id = $user_info['avatar_id'];
                                    while($row = mysqli_fetch_assoc($result)){ 
                                        $selected_class = ($row['avatar_id'] == $current_avatar_id) ? 'border-primary border-3' : '';
                                        $amount += 1;
                                    ?>
                                        <div class="col-3 text-center">
                                            <button type="button" 
                                                class="btn p-0 border <?= $selected_class ?>" 
                                                style="border-radius: 50%; width: 60px; height: 60px; overflow: hidden;"
                                                onclick="selectAvatar(this, <?= $row['avatar_id'] ?>)">
                                                <img src="../uploads/community/avatar/<?= htmlspecialchars($row['avatar_url']) ?>" 
                                                    alt="avatar-<?= $amount ?>" 
                                                    class="img-fluid rounded-circle"
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" name="update_avatar" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('../includes/footer.php'); ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="community-function.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    // Add event listeners to reaction buttons (like, comment, etc.)
    document.querySelectorAll('.reaction-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.classList.toggle('active');
        });
    });

    // Handling image preview and upload
    var images = [];
    function image_select() {
        var image = document.getElementById('image').files;
        for (let i = 0; i < image.length; i++) {
            images.push({
                "name": image[i].name,
                "url": URL.createObjectURL(image[i]),
                "file": image[i],
            });
        }
        document.getElementById('container').innerHTML = image_show();
    }

    window.image_select = image_select;

    function image_show() {
        var image = "";
        images.forEach((i) => {
            image += `<div class="image_container d-flex justify-content-center position-relative">
                        <img src="${i.url}" alt="image">
                        <span class="position-absolute" onclick="delete_image(${images.indexOf(i)})">&times;</span>
                    </div>`;
        });
        return image;
    }

    // Delete image preview
    window.delete_image = function(index) {
        images.splice(index, 1);
        document.getElementById('container').innerHTML = image_show();
    };

    // Add event listeners for comment toggling
    const commentToggleBtns = document.querySelectorAll('.comment-toggle-btn');
    commentToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const commentsSection = this.closest('.card-footer').querySelector('.comments-section');
            if (commentsSection) {
                if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
                    commentsSection.style.display = 'block';
                } else {
                    commentsSection.style.display = 'none';
                }
            }
        });
    });

    // Handle comment replies
    const replyButtons = document.querySelectorAll('.reply-toggle-btn');
    replyButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const commentId = this.getAttribute('data-comment-id');
            const replyInput = document.querySelector(`.reply-input[data-comment-id="${commentId}"]`);

            if (replyInput) {
                const currentDisplay = window.getComputedStyle(replyInput).display;

                // Hide all reply inputs first
                document.querySelectorAll('.reply-input').forEach(input => input.style.display = 'none');

                // Show or hide this reply input
                if (currentDisplay === 'none') {
                    replyInput.style.display = 'flex';
                }
            }
        });
    });

    // Handle the favorite button toggle
    const favoriteBtn = document.getElementById('favoriteBtn');
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function() {
            const isFavorited = favoriteBtn.classList.contains('active');
            if (isFavorited) {
                favoriteBtn.classList.remove('active');
            } else {
                favoriteBtn.classList.add('active');
            }
        });
    }

    // Handle the search functionality
    const searchInput = document.getElementById("searchInput");
    const suggestions = document.getElementById("suggestions");

    searchInput.addEventListener("input", function () {
        const query = this.value.trim();
        if (query.length < 2) {
            suggestions.style.display = "none";
            return;
        }

        fetch("community-search.php?search=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                suggestions.innerHTML = "";
                if (data.length === 0) {
                    suggestions.innerHTML = "<div class='item'>No results found</div>";
                } else {
                    data.forEach(item => {
                        const div = document.createElement("div");
                        div.className = "item";
                        div.style.cursor = "pointer";
                        div.innerHTML = `<strong>${item.title} Community</strong>`;
                        div.addEventListener("click", () => {
                            window.location.href = `community-recipe.php?community_id=${item.community_id}`;
                        });

                        suggestions.appendChild(div);
                    });
                }
                suggestions.style.display = "block";
            });
    });

    document.addEventListener("click", function (e) {
        if (!suggestions.contains(e.target) && e.target !== searchInput) {
            suggestions.style.display = "none";
        }
    });
});
    </script>
    <script>https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js</script>
    
</body>
</html>