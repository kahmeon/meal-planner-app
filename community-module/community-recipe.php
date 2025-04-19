<?php
include('../includes/db.php');
include('../includes/auth.php');

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Store current URL to redirect back after login
    header("Location: ../login.php");
    exit();
}

$post_status = "";
$update_status="";
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$banner_url = "";
$user_role = $_SESSION['user_role'];

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: ../login.php");
    exit();
}

// Get the community information
$community_id = isset($_GET['community_id']) ? (int) $_GET['community_id'] : 0;
$stmt = $conn->prepare("SELECT recipe_id, slogan, banner FROM community WHERE community_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $community_info = $result->fetch_assoc()) {
        $recipe_id = $community_info['recipe_id'];
        $community_slogan = htmlspecialchars($community_info['slogan']);
        $community_banner = htmlspecialchars($community_info['banner']);
    } else {
        // Handle no result
        echo "Community not found.";
    }

    $stmt->close();
} else {
    echo "Statement preparation failed: " . $conn->error;
}
$_GET['community_id'] = $community_id;

// Get the recipe information
$stmt = $conn->prepare("SELECT title, description, total_time, difficulty, created_by, created_at FROM recipes WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $recipe_info = $result->fetch_assoc()) {
        $recipe_title = htmlspecialchars($recipe_info['title']);
        $recipe_description = htmlspecialchars($recipe_info['description']);
        $recipe_total_time = $recipe_info['total_time'];
        $recipe_difficulty = htmlspecialchars($recipe_info['difficulty']);
        $recipe_author_id = $recipe_info['created_by'];
        $recipe_created_at = $recipe_info['created_at'];
    } else {
        // Handle no result found
        echo "Recipe not found.";
    }

    $stmt->close();
} else {
    echo "Statement preparation failed: " . $conn->error;
}

//Get the recipe image information
$smt = $conn->prepare("SELECT image_url FROM recipe_images WHERE recipe_id = ? ORDER BY id ASC LIMIT 1");
if($smt){
    $smt->bind_param("i",$recipe_id);
    $smt->execute();
    $smt->bind_result($recipe_url);

    if($smt->fetch()){
        $banner_url = $recipe_url;
    } else{
        $banner_url = "No url found";
    }
    $smt->close();
}

//Get the avatar information
$stmt = $conn->prepare("
    SELECT a.avatar_url 
    FROM user_community uc 
    JOIN avatar a ON uc.user_avatar_id = a.avatar_id 
    WHERE uc.user_id = ?
    ");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($avatar_url);

    if ($stmt->fetch()) {
        $avatar_url = "../uploads/community/avatar/" . $avatar_url;
    } else {
        $avatar_url =  "No avatar found for this user_comm_id.";
    }

    $stmt->close();
} else {
    echo "Statement preparation failed: " . $conn->error;
}

// Get the recipe tag
$stmt = $conn->prepare("
    SELECT t.name
    FROM recipe_tags rt
    JOIN tags t ON rt.tag_id = t.id
    WHERE rt.recipe_id = ?
");
if($stmt){

    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['name'];
    }
    $stmt->close();
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

// Check if user has already favorited this community
$is_favorited = false;
$favor_stmt = $conn->prepare("SELECT id FROM user_favor WHERE community_id = ? AND user_id = ?");
if ($favor_stmt) {
    $favor_stmt->bind_param("ii", $community_id, $user_id);
    $favor_stmt->execute();
    $favor_result = $favor_stmt->get_result();
    
    if ($favor_result && $favor_result->num_rows > 0) {
        $is_favorited = true;
    }
    
    $favor_stmt->close();
}

$order = ""; // default order
if (isset($_POST['post_order']) && isset($_POST['sort'])) {
    $selected_sort = strtoupper($_POST['sort']);
    if ($selected_sort === "ASC" || $selected_sort === "DESC") {
        $order = $selected_sort;

    }
}else{
    $order = "DESC";
}

// Handle update post status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_toggle'])) {
    $post_id = $_POST['like_post_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $liked = getUserLike($user_id, $post_id);
    if ($liked) {
        // If already liked, delete the like
        $stmt = $conn->prepare("DELETE FROM like_post WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // If not liked, insert a like
        $stmt = $conn->prepare("INSERT INTO like_post (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();

    }
    getUserLike($user_id, $post_id);
    header("Location: " . $_SERVER['PHP_SELF'] . "?community_id=" . urlencode($community_id). "#post-section");
    exit();
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

// Update banner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bannerUpdate']) && isset($_FILES['bannerImage']) && isset($_POST['community_id'])) {
    $community_id = $_POST['community_id']; // Get community_id from the form submission
    $uploadDir = '../uploads/community/banner/'; // Set the directory to save the uploaded image
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate a unique filename to avoid overwriting
    $filename = basename($_FILES['bannerImage']['name']);
    $uploadFile = 'uploads/community/banner/' . $filename;

    // Check if the file is an image
    $check = getimagesize($_FILES['bannerImage']['tmp_name']);
    if ($check !== false) {
        if (move_uploaded_file($_FILES['bannerImage']['tmp_name'], $uploadFile)) {
            // Now update the banner URL in the database
            // Assuming you have a database connection $conn
            $sql = "UPDATE community SET banner = ? WHERE community_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $uploadFile, $community_id);

            if ($stmt->execute()) {
                $banner_url = ".." . $uploadFile;
                echo "<div class='alert alert-success'>Banner image successfully updated!</div>";
                // Refresh the page to show the new banner
                header("Location: community-recipe.php?community_id=" . $community_id);
            } else {
                echo "<div class='alert alert-danger'>Error updating banner image in database: " . $conn->error . "</div>";
            }

            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error uploading the image. Check file permissions.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Uploaded file is not a valid image.</div>";
    }
}

// View the post
function getCommunityPostsWithImages($comm_id, $order) {
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
        WHERE 
            post.community_id = ?
        ORDER BY 
            post.created_at $order
    ");

    $stmt->bind_param("i", $comm_id);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> NomNom | <?= $recipe_title ?>Community</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .community-banner {
            height: 240px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .community-info {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-top: -50px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .recipe-header {
            background-size: cover;
            background-position: center;
            height: 300px;
            position: relative;
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: flex-end;
        }
        .recipe-info {
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 30px 20px 20px;
            width: 100%;
        }
        .recipe-meta {
            font-size: 0.9rem;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: #e9f5ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .post-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.2s ease;
        }
        .post-image:hover {
            transform: scale(1.02);
            cursor: pointer;
        }
        .community-post {
            border-radius: 10px;
            margin-bottom: 30px; /* Increased spacing between posts */
            box-shadow: 0 3px 10px rgba(0,0,0,0.08); /* Subtle shadow for posts */
            border: 1px solid #f0f0f0;
            background-color: #ffffff;
        }
        .reaction-btn {
            color: #6c757d;
            transition: all 0.2s;
        }
        .reaction-btn:hover {
            color: #6c757d;
        }
        .reaction-btn.active {
            color: #6c757d;
        }

        /* Rating Stars */
        .rating-stars {
            font-size: 1.8rem;
            cursor: pointer;
            color: #FFD700;
            display: inline-block;
        }

        .rating-stars .rating-star {
            margin: 0 3px;
            transition: all 0.2s ease;
        }

        .rating-stars .rating-star:hover {
            transform: scale(1.1);
        }

        /* Rating Comment Section */
        .recipe-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

        /* Rating status message */
        .rating-status {
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; }
        }

        .tag-badge {
            font-size: 0.75rem;
            font-weight: normal;
            padding: 5px 10px;
            margin-right: 5px;
            margin-bottom: 5px;
            background-color: #f0f8ff;
            color: #0d6efd;
            border-radius: 20px;
        }
        .events-carousel .card {
            margin: 0 10px;
            transition: transform 0.3s;
        }
        .events-carousel .card:hover {
            transform: translateY(-5px);
        }
        .recipe-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .floating-join-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .badge-award {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
        .timeline-item {
            border-left: 2px solid #dee2e6;
            padding: 0 0 20px 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            width: 12px;
            height: 12px;
            background-color: #0d6efd;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 0;
        }
        .timeline-date {
            position: absolute;
            left: -110px;
            top: 0;
            width: 90px;
            text-align: right;
            color: #6c757d;
        }
        .image_container{
            height: 120px;
            width: 200px;
            border-radius: 6px;
            overflow: hidden;
        }
        .image_container img{
            height:100%;
            width:auto;
            object-fit: cover;
        }
        .image_container span{
            top:-8px;
            right:8px;
            color:black;
            font-size:28px;
            font-weight:normal;
            cursor:pointer;
        }

        /* Add these styles to your existing <style> section */
        .post-images-container img {
            object-fit: cover;
            border-radius: 8px;
        }

        .image_container {
            position: relative;
            margin: 5px;
            height: 100px;
            width: 150px;
            border-radius: 6px;
            overflow: hidden;
            display: inline-block;
        }

        .image_container img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        .image_container span {
            position: absolute;
            top: 5px;
            right: 8px;
            color: white;
            background-color: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .image_container span:hover {
            background-color: rgba(0,0,0,0.8);
        }

        .post-actions {
            display: flex;
            justify-content: space-around;
            padding: 12px 0; /* Increased padding */
            border-top: 1px solid #dee2e6;
            margin-top: 10px;
        }

        .post-actions button {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .post-actions button:hover {
            color: #0d6efd;
        }

        .post-actions button i {
            margin-right: 5px;
        }

        /* Emoji picker styles */
        .emoji-picker {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 10px;
            display: none;
        }

        .emoji-picker .emoji {
            font-size: 1.5rem;
            padding: 5px;
            cursor: pointer;
        }

        .emoji-picker .emoji:hover {
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .textarea-emoji-control {
            position: relative;
        }

        /* Add these styles to your existing styles */
        .textarea-controls {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
        }
        
        /* Enhanced Lightbox styles */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.92);
            overflow: hidden;
        }
        
        .lightbox-content {
            position: relative;
            width: 90%; /* Increased from 80% */
            max-width: 1200px; /* Increased from 1000px */
            margin: 20px auto; /* Reduced top margin to fit larger images */
            height: calc(100% - 40px);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .lightbox-img {
            max-width: 100%;
            max-height: 90vh; /* Increased from 80vh */
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        
        .lightbox-close {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1060;
            background: rgba(0,0,0,0.5);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .lightbox-close:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .lightbox-nav {
            position: absolute;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }
        
        .lightbox-prev, .lightbox-next {
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(0,0,0,0.4);
            width: 60px; /* Increased from 50px */
            height: 60px; /* Increased from 50px */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .lightbox-prev:hover, .lightbox-next:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }
        
        .post-image {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .post-image:hover {
            opacity: 0.95;
            transform: scale(1.02);
        }
        
        /* New styles for post enhancements */
        .card-header.bg-white {
            background-color: #f8f9fa !important; /* Light gray header */
            border-bottom: 1px solid #eaeaea;
            padding: 15px 20px; /* More padding */
        }
        
        .card-body {
            padding: 20px; /* More padding inside post body */
        }
        
        .card-footer.bg-white {
            background-color: #fafafa !important; /* Very light gray footer */
            border-top: 1px solid #eaeaea;
            padding: 15px 20px; /* More padding */
        }
        
        /* Style for post grid images */
        .post-images-container .row.g-2 {
            margin-bottom: 0;
        }
        
        /* Style for post with multiple images */
        .more-images-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .more-images-text {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-shadow: 0 1px 3px rgba(0,0,0,0.7);
        }
        
        /* Image counter in lightbox */
        .lightbox-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0,0,0,0.5);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        /* Comment section enhancements */
        .comment-section {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 0 0 10px 10px;
            margin-top: -1px;
        }
        
        .comment-input {
            background-color: white;
            border-radius: 30px;
            padding-left: 15px;
        }
        .btn-link:focus, .btn-link:active {
            outline: none; /* Remove the blue outline */
            color: inherit; 
        }

    </style>
</head>

<body>
<?php include('../navbar.php'); ?>
<div class="community-banner" style="background-image: url('../<?= $banner_url ?>');">
    <div class="container position-relative h-100">
        <div class="position-absolute bottom-0 end-0 mb-3 me-3">
            <?php if($user_role === "admin"){ ?>
                <button type="button" class="btn btn-light btn-sm rounded-pill" onclick="openFileDialog();">
                    <i class="fas fa-camera me-1"></i> Change Banner
                </button>
            <?php } ?>

        </div>
    </div>
</div>

<!-- Bootstrap Modal for Image Preview and Confirmation -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Preview and Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Image preview -->
                <img id="previewImage" src="" alt="Image preview" style="max-width: 100%; height: auto;">
            </div>
            <!-- Form inside the modal -->
            <form action="" method="POST" enctype="multipart/form-data" id="bannerForm">
                <!-- Hidden input fields -->
                <input type="hidden" name="community_id" value="<?= $community_id ?>">
                <input type="hidden" name="bannerUpdate" value="1">
                <!-- This is the key fix - add the file input to the form that submits -->
                <input type="file" id="bannerImageHidden" name="bannerImage" style="display: none;">
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden file input only used for selection -->
<input type="file" id="bannerImage" name="bannerImageSelect" style="display: none;" accept="image/*" onchange="previewImage();">


    <!-- Community Info Section -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="community-info mb-4">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <?php 
                            $stmt = $conn->prepare("SELECT COUNT(*) AS favor_count FROM user_favor WHERE community_id = ?");
                            if ($stmt) {
                                $stmt->bind_param("i", $community_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result && $user_favor = $result->fetch_assoc()) {
                                    $favor_count = $user_favor['favor_count'];
                                } else {
                                    $favor_count = 0;
                                }

                                $stmt->close();
                            } else {
                                echo "Statement preparation failed: " . $conn->error;
                            }
                            ?>
                            <h1 class="mb-2"><?= $recipe_info['title'] ?> Community</h1>
                            <p class="text-muted mb-2"><?= $community_slogan ?></p>
                            <div class="d-flex align-items-center text-muted small">
                                <div class="me-3">
                                    <?php 
                                    // Prepare the SQL query
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM post WHERE community_id = ?");
                                    $stmt->bind_param("i", $community_id);  // Bind the community_id parameter
                                    
                                    // Execute the query
                                    $stmt->execute();
                                    $stmt->bind_result($count);  // Bind the result to the $count variable
                                    $stmt->fetch();  // Fetch the result
                                    
                                    $stmt->close(); 
                                    ?>
                                    <i class="fas fa-users me-1"></i><?= $user_favor['favor_count'] ?> Favorite
                                </div>
                                <div class="me-3">

                                    <i class="fas fa-comment-alt me-1"></i>145 posts
                                </div>
                                <div>
                                    <i class="fas fa-star me-1"></i>3.0 rating
                                </div>
                            </div>
                        </div>
                        <!-- Favorite Button -->
                        <form action="" method="POST" name="update_favor">
                            <div class="d-flex flex-column">
                            <button type="button" class="btn <?= $is_favorited ? 'btn-danger' : 'btn-outline-danger' ?> mb-2" id="favoriteBtn" data-favorited="<?= $is_favorited ? 'true' : 'false' ?>" data-community-id="<?= $community_id ?>">
                                <i class="<?= $is_favorited ? 'fas' : 'far' ?> fa-heart me-1"></i><span id="favoriteLabel"><?= $is_favorited ? 'Favorited' : 'Favorite' ?></span>
                            </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Featured Recipe -->
                <div class="card mb-4 shadow-sm">
                    <div class="recipe-header" style="background-image: url('../<?= $recipe_url ?>');">
                        <div class="recipe-info">
                            <h3><?= $recipe_title ?></h3>
                            <div class="recipe-meta d-flex flex-wrap align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-clock me-1"></i><?= $recipe_total_time ?> mins
                                </div>
                                <div class="me-3">
                                    <i class="fas fa-fire me-1"></i><?= $recipe_difficulty ?>
                                </div>
                                <div class="me-3">
                                    <?php 
                                    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                                    if ($stmt) {
                                        $stmt->bind_param("i", $recipe_author_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
        
                                        if ($result && $author_name = $result->fetch_assoc()) {
                                            $author = $author_name['name'];
                                        } else {
                                            $author = "";
                                        }
        
                                        $stmt->close();
                                    } else {
                                        echo "Statement preparation failed: " . $conn->error;
                                    }
                                    ?>
                                    <i class="fas fa-user-edit me-1"></i><?= $author ?>
                                </div>
                                <?php 
                                    $stmt = $conn->prepare("SELECT rate_value FROM rating WHERE community_id = ?");
                                    if ($stmt) {
                                        $stmt->bind_param("i", $community_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                    
                                        $total = 0;
                                        $count = 0;
                                        $overall_rating = 0;
                                    
                                        if ($result) {
                                            while ($row = $result->fetch_assoc()) {
                                                $total += $row['rate_value'];
                                                $count++;
                                            }
                                    
                                            if ($count > 0) {
                                                $overall_rating = round($total / $count, 2); // Rounded to 2 decimal places
                                            }
                                        }
                                    
                                        $stmt->close();
                                    } else {
                                        echo "Statement preparation failed: " . $conn->error;
                                    }

                                    $full_stars = floor($overall_rating); // 4
                                    $half_star = ($overall_rating - $full_stars) >= 0.25 && ($overall_rating - $full_stars) < 0.75 ? 1 : 0;
                                    if (($overall_rating - $full_stars) >= 0.75) {
                                        $full_stars += 1;
                                        $half_star = 0;
                                    }
                                    $empty_stars = 5 - $full_stars - $half_star;
                                    ?>

                            <div class="ms-auto">
                                <span class="text-warning">
                                    <?php for ($i = 0; $i < $full_stars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>

                                    <?php if ($half_star): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php endif; ?>

                                    <?php for ($i = 0; $i < $empty_stars; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>

                                    <span class="text-white ms-1">(<?= number_format($overall_rating, 1) ?>)</span>
                                </span>
                            </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <?= $recipe_description ?>
                        </p>
                        <div class="mb-3">
                                <?php foreach ($tags as $tag) { ?>
                                    <span class="tag-badge"><i class="fas fa-tag me-1"></i><?= $tag ?></span>
                                <?php } ?>
                            </div>
                        <div class="d-flex justify-content-end">
                                <button class="btn btn-outline-secondary me-2" onclick="window.location.href='../recipe-modules/view-recipe.php?id=<?= $recipe_id ?>'">
                                    <i class="fas fa-book-open me-1"></i>View Recipe
                                </button>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#ratingModal">
                                <i class="fas fa-star me-1"></i>Rate Recipe
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Top Recipes -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Rating Comments</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            // Fetch ratings for this community/recipe
                            $stmt = $conn->prepare("
                                SELECT r.rate_value, r.feedback_comment, r.created_at, u.name AS user_name, a.avatar_url 
                                FROM rating r
                                JOIN users u ON r.user_id = u.id
                                JOIN user_community uc ON r.user_id = uc.user_id
                                JOIN avatar a ON uc.user_avatar_id = a.avatar_id
                                WHERE r.community_id = ? 
                                ORDER BY r.created_at DESC LIMIT 3
                            ");
                            
                            if ($stmt) {
                                $stmt->bind_param("i", $community_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    while ($rating = $result->fetch_assoc()) {
                                        $created_date = date('M d, Y', strtotime($rating['created_at']));
                                        ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card recipe-card h-100 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <img src="../uploads/community/avatar/<?= $rating['avatar_url'] ?>" class="rounded-circle me-2" width="30" height="30" alt="User">
                                                        <div>
                                                            <h6 class="mb-0"><?= htmlspecialchars($rating['user_name']) ?></h6>
                                                            <small class="text-muted"><?= $created_date ?></small>
                                                        </div>
                                                    </div>
                                                    <div class="small text-warning mb-2">
                                                        <?php
                                                        $rating_value = $rating['rate_value'];
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $rating_value) {
                                                                echo '<i class="fas fa-star"></i>';
                                                            } elseif ($i - 0.5 <= $rating_value) {
                                                                echo '<i class="fas fa-star-half-alt"></i>';
                                                            } else {
                                                                echo '<i class="far fa-star"></i>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <p class="card-text small"><?= htmlspecialchars($rating['feedback_comment']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo '<div class="col-12 text-center text-muted py-3">No ratings yet. Be the first to rate this recipe!</div>';
                                }
                                
                                $stmt->close();
                            }
                            ?>
                        </div>
                        
                        <!-- View All Ratings Button -->
                        <div class="text-center mt-3">
                            <a href="view-ratings.php?community_id=<?= $community_id ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list me-1"></i>View All Ratings
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Discussion Section -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center" id="post-section">
                        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Community Discussions</h5>
                        <div>
                        <form method="POST" class="d-inline-block">
                            <select name="sort" class="form-select form-select-sm me-2 d-inline-block w-auto">
                                <option value="DESC">Latest Posts</option>
                                <option value="ASC">Oldest Posts</option>
                            </select>
                            <button type="submit" name="post_order" class="btn btn-sm btn-primary">Sort</button>
                        </form>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <div class="d-flex text-center">
                            <p><?= $update_status ?></p>
                        </div>
                        
                    </div>
                    <div class="card-body bg-light">
                        <!-- New Post Form -->
                        <div class="mb-4 bg-white p-3 rounded-3 shadow-sm">
                            <div class="d-flex mb-3">
                                <img src="<?= $avatar_url ?>" class="user-avatar me-2" alt="User">
                                <div class="w-100">
                                     <!-- Replace your existing form HTML for posting with this -->
                                    <form action="post-add.php?community_id=<?= $community_id ?>&recipe_id=<?= $recipe_id ?>" method="POST" enctype="multipart/form-data">
                                        <div class="form-floating mb-2">
                                            <textarea class="form-control" name="post_comment" placeholder="Share your thoughts with the community..." id="postContent" style="height: 100px"></textarea>
                                            <label for="postContent">Share your thoughts with the community...</label>
                                        </div>
                                        
                                        <!-- Picture Display -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="document.getElementById('image').click()">
                                                    <i class="fas fa-image me-1"></i>Add Photo
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="emojiButton">
                                                    <i class="far fa-smile me-1"></i>Add Emoji
                                                </button>
                                            </div>
                                            <button type="submit" name="post_submit" class="btn btn-primary">Post</button>
                                        </div>
                                        
                                        <!-- Picture preview -->
                                        <div class="d-flex flex-wrap justify-content-start p-2 mt-2" id="container">
                                        </div>
                                        <input type="file" name="post_image[]" id="image" multiple class="d-none" onchange="image_select();">
                                        
                                        <div class="d-flex justify-content-center mb-2">
                                            <?= $post_status ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Discussion Posts -->
                        <?php
                         $all_posts = getCommunityPostsWithImages($community_id, $order);
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
                                            <small class="text-muted"><?= $post_hrs ?></small>
                                        </div>
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
                                                        } // End if for checking reply_id
                                                    } // End foreach for replies
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
                                                <?php $community_id = $_GET['community_id']; 
                                                $post_id = $row['post_id'];?>
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
                </div>

            </div>
        </div>
    </div>

    <?php if (!empty($rating_status)) { ?>
        <div class="alert alert-success rating-status"><?= $rating_status ?></div>
    <?php } ?>

    <?php 
    // Check if user has already rated this community/recipe
    $user_rating = null;
    $stmt = $conn->prepare("SELECT rate_value, feedback_comment FROM rating WHERE community_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $community_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $user_rating = $result->fetch_assoc();
        }
        
        $stmt->close();
    }

    // Display rating status message if exists
    $rating_status = "";
    if (isset($_SESSION['rating_status'])) {
        $rating_status = $_SESSION['rating_status'];
        unset($_SESSION['rating_status']); // Clear the message after displaying
    }
    ?>
    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ratingModalLabel">Rate <?= $recipe_title ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="rating-add.php" method="POST">
                    <div class="modal-body text-center">
                        <p>How would you rate your experience with this recipe?</p>
                        <div class="rating-stars mb-3">
                            <i class="far fa-star rating-star" data-rating="1"></i>
                            <i class="far fa-star rating-star" data-rating="2"></i>
                            <i class="far fa-star rating-star" data-rating="3"></i>
                            <i class="far fa-star rating-star" data-rating="4"></i>
                            <i class="far fa-star rating-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rate_value" id="ratingValue" value="0">
                        <input type="hidden" name="community_id" value="<?= $community_id ?>">
                        <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        <div class="mb-3">
                            <label for="feedback_comment" class="form-label">Share your feedback (optional)</label>
                            <textarea class="form-control" id="feedback_comment" name="feedback_comment" rows="3" placeholder="Tell us more about your experience..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_rating" class="btn btn-primary">Submit Rating</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced Image Lightbox -->
    <div id="imageLightbox" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <div class="lightbox-content">
            <div class="lightbox-nav">
                <span class="lightbox-prev"><i class="fas fa-chevron-left"></i></span>
                <span class="lightbox-next"><i class="fas fa-chevron-right"></i></span>
            </div>
            <img class="lightbox-img" id="lightboxImage">
            <div class="lightbox-counter">1 / 1</div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.reaction-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        this.classList.toggle('active');
                    });
                });
            // Image upload and preview handling
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
            
            // Make image_select function globally accessible
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
            
            // Make delete_image function globally accessible
            window.delete_image = function(index) {
                images.splice(index, 1); // Remove the image from array
                document.getElementById('container').innerHTML = image_show(); // Refresh display
            };
            
            // Initialize rating stars in modal
            const ratingStars = document.querySelectorAll('.rating-star');
            const ratingValue = document.getElementById('ratingValue');
            let selectedRating = 0;
            
            if (ratingStars.length > 0) {
                ratingStars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const rating = parseInt(this.getAttribute('data-rating'));
                        highlightStars(rating);
                    });
                    
                    star.addEventListener('mouseout', function() {
                        highlightStars(selectedRating);
                    });
                    
                    star.addEventListener('click', function() {
                        selectedRating = parseInt(this.getAttribute('data-rating'));
                        if (ratingValue) {
                            ratingValue.value = selectedRating;
                        }
                        highlightStars(selectedRating);
                    });
                });
            }
            
            function highlightStars(count) {
                ratingStars.forEach((star, index) => {
                    if (index < count) {
                        star.classList.remove('far');
                        star.classList.add('fas');
                    } else {
                        star.classList.remove('fas');
                        star.classList.add('far');
                    }
                });
            }
            
            // Comment toggle functionality
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
            
            // Emoji picker functionality
            const emojiButton = document.getElementById('emojiButton');
            const postContent = document.getElementById('postContent');
            
            if (emojiButton && postContent) {
                const emojis = ['😀', '😂', '😊', '😍', '🥰', '😎', '🤔', '👍', '❤️', '🎉', '🍕', '🍷', '🧁', '🍰', '🍩', '🥳', '😋', '🤩', '👨‍🍳', '👩‍🍳'];
                
                // Create emoji picker with improved styling
                const emojiPicker = document.createElement('div');
                emojiPicker.className = 'emoji-picker';
                emojiPicker.style.display = 'none';
                
                emojis.forEach(emoji => {
                    const span = document.createElement('span');
                    span.className = 'emoji';
                    span.textContent = emoji;
                    span.onclick = function() {
                        postContent.value += emoji;
                        postContent.focus();
                        emojiPicker.style.display = 'none';
                    };
                    emojiPicker.appendChild(span);
                });
                
                // Add emoji picker next to the button
                emojiButton.parentNode.insertBefore(emojiPicker, emojiButton.nextSibling);
                
                // Toggle emoji picker
                emojiButton.onclick = function(e) {
                    e.preventDefault();
                    if (emojiPicker.style.display === 'none') {
                        emojiPicker.style.display = 'grid';
                        emojiPicker.style.gridTemplateColumns = 'repeat(5, 1fr)';
                    } else {
                        emojiPicker.style.display = 'none';
                    }
                };
                
                // Close emoji picker when clicking outside
                document.addEventListener('click', function(event) {
                    if (!emojiButton.contains(event.target) && !emojiPicker.contains(event.target)) {
                        emojiPicker.style.display = 'none';
                    }
                });
            }
            
            // Enhanced Lightbox functionality
            const lightbox = document.getElementById('imageLightbox');
            if (lightbox) {
                const lightboxImg = document.getElementById('lightboxImage');
                const lightboxClose = document.querySelector('.lightbox-close');
                const lightboxPrev = document.querySelector('.lightbox-prev');
                const lightboxNext = document.querySelector('.lightbox-next');
                const lightboxCounter = document.querySelector('.lightbox-counter');
                
                let currentImageIndex = 0;
                let currentImages = [];
                let currentPostId = null;
                
                // Function to load all images from a post
                function loadPostImages(postId) {
                    const dataElement = document.querySelector(`.post-images-data[data-post-id="${postId}"]`);
                    if (dataElement) {
                        try {
                            return JSON.parse(dataElement.getAttribute('data-images'));
                        } catch (e) {
                            console.error('Error parsing images JSON:', e);
                            return [];
                        }
                    }
                    return [];
                }
                
                // Make all post images clickable to open lightbox
                document.querySelectorAll('.post-image, .more-images-overlay').forEach(element => {
                    element.addEventListener('click', function() {
                        // Get post ID and image index
                        const postId = this.getAttribute('data-post-id') || this.closest('[data-post-id]').getAttribute('data-post-id');
                        const imageIndex = parseInt(this.getAttribute('data-index') || 0);
                        
                        // Load all images from this post
                        currentImages = loadPostImages(postId);
                        currentPostId = postId;
                        currentImageIndex = imageIndex;
                        
                        if (currentImages.length > 0) {
                            openLightbox(currentImages[currentImageIndex]);
                        }
                    });
                });
                
                function openLightbox(imgSrc) {
                    lightboxImg.src = imgSrc;
                    lightbox.style.display = 'block';
                    document.body.style.overflow = 'hidden'; // Prevent scrolling
                    updateCounter();
                    
                    // Show/hide navigation buttons based on number of images
                    if (currentImages.length <= 1) {
                        lightboxPrev.style.display = 'none';
                        lightboxNext.style.display = 'none';
                        lightboxCounter.style.display = 'none';
                    } else {
                        lightboxPrev.style.display = 'flex';
                        lightboxNext.style.display = 'flex';
                        lightboxCounter.style.display = 'block';
                    }
                }
                
                function updateCounter() {
                    if (currentImages.length > 0) {
                        lightboxCounter.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
                    }
                }
                
                function closeLightbox() {
                    lightbox.style.display = 'none';
                    document.body.style.overflow = ''; // Restore scrolling
                }
                
                if (lightboxClose) {
                    lightboxClose.addEventListener('click', closeLightbox);
                }
                
                // Close lightbox when clicking outside the image
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        closeLightbox();
                    }
                });
                
                // Navigate to previous image
                if (lightboxPrev) {
                    lightboxPrev.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (currentImages.length > 1) {
                            currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
                            lightboxImg.src = currentImages[currentImageIndex];
                            updateCounter();
                        }
                    });
                }
                
                // Navigate to next image
                if (lightboxNext) {
                    lightboxNext.addEventListener('click', function(e) {
                        e.stopPropagation();
                        if (currentImages.length > 1) {
                            currentImageIndex = (currentImageIndex + 1) % currentImages.length;
                            lightboxImg.src = currentImages[currentImageIndex];
                            updateCounter();
                        }
                    });
                }
                
                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (lightbox.style.display === 'block') {
                        if (e.key === 'Escape') {
                            closeLightbox();
                        } else if (e.key === 'ArrowLeft' && lightboxPrev) {
                            lightboxPrev.click();
                        } else if (e.key === 'ArrowRight' && lightboxNext) {
                            lightboxNext.click();
                        }
                    }
                });
                
                // Add touch swipe support for lightbox
                let touchStartX = 0;
                let touchEndX = 0;
                
                lightbox.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, false);
                
                lightbox.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                }, false);
                
                function handleSwipe() {
                    if (touchEndX < touchStartX - 50) {
                        // Swipe left - next image
                        if (lightboxNext) lightboxNext.click();
                    }
                    if (touchEndX > touchStartX + 50) {
                        // Swipe right - previous image
                        if (lightboxPrev) lightboxPrev.click();
                    }
                }
                
                // Preload adjacent images for smoother navigation
                function preloadAdjacentImages() {
                    if (currentImages.length <= 1) return;
                    
                    const nextIndex = (currentImageIndex + 1) % currentImages.length;
                    const prevIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
                    
                    const nextImg = new Image();
                    nextImg.src = currentImages[nextIndex];
                    
                    const prevImg = new Image();
                    prevImg.src = currentImages[prevIndex];
                }
                
                // Add loading indicator for lightbox images
                if (lightboxImg) {
                    lightboxImg.addEventListener('load', function() {
                        this.style.opacity = '1';
                        preloadAdjacentImages();
                    });
                    
                    lightboxImg.addEventListener('loadstart', function() {
                        this.style.opacity = '0.5';
                    });
                }
            }
            
            // Favorite button toggle
            const favoriteBtn = document.getElementById('favoriteBtn');
            const favoriteStatus = document.getElementById('favoriteStatus');
            
            if (favoriteBtn && favoriteStatus) {
                favoriteBtn.addEventListener('click', function() {
                    const isFavorited = favoriteStatus.value === 'true';
                    
                    if (isFavorited) {
                        // Remove from favorites
                        this.innerHTML = '<i class="far fa-heart me-1"></i>Favorite';
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-outline-danger');
                        favoriteStatus.value = 'false';
                    } else {
                        // Add to favorites
                        this.innerHTML = '<i class="fas fa-heart me-1"></i>Favorited';
                        this.classList.remove('btn-outline-danger');
                        this.classList.add('btn-danger');
                        favoriteStatus.value = 'true';
                    }
                });
            }
            
            // jQuery favorite button functionality (if jQuery is available)
            if (typeof $ !== 'undefined') {
                $('#favoriteBtn').on('click', function() {
                    const btn = $(this);
                    const communityId = btn.data('community-id');
                    const isFavorited = btn.data('favorited') === true || btn.data('favorited') === 'true';
                    const action = isFavorited ? 'remove' : 'add';

                    $.ajax({
                        url: 'update-favor.php',
                        type: 'POST',
                        data: {
                            community_id: communityId,
                            action: action
                        },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                if (res.action === 'added') {
                                    btn.removeClass('btn-outline-danger').addClass('btn-danger');
                                    btn.find('i').removeClass('far').addClass('fas');
                                    $('#favoriteLabel').text('Favorited');
                                    btn.data('favorited', 'true');
                                } else if (res.action === 'removed') {
                                    btn.removeClass('btn-danger').addClass('btn-outline-danger');
                                    btn.find('i').removeClass('fas').addClass('far');
                                    $('#favoriteLabel').text('Favorite');
                                    btn.data('favorited', 'false');
                                }
                            } else {
                                alert(res.message || 'An error occurred.');
                            }
                        },
                        error: function() {
                            alert('Failed to update favorite. Please try again.');
                        }
                    });
                });
            }
            
            
            // Add view all ratings functionality
            const viewAllRatingsBtn = document.getElementById('viewAllRatingsBtn');
            if (viewAllRatingsBtn) {
                viewAllRatingsBtn.addEventListener('click', function() {
                    // You can implement a modal or redirect to a page showing all ratings
                    alert('This feature is coming soon!');
                });
            }
            
            // CRITICAL SECTION: Reply input handling
            console.log('DOM fully loaded - initializing reply functionality');
            
            // Find all reply inputs
            const allReplyInputs = document.querySelectorAll('.reply-input');
            console.log('Found ' + allReplyInputs.length + ' reply inputs');
            
            // Hide all reply inputs initially
            allReplyInputs.forEach(input => {
                input.style.display = 'none';
                console.log('Initially hiding a reply input');
            });
            
            // Find all reply toggle buttons
            const replyButtons = document.querySelectorAll('.reply-toggle-btn');
            console.log('Found ' + replyButtons.length + ' reply buttons');
            
            // Add click handlers to all reply buttons
            replyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Stop event bubbling
                    
                    const commentId = this.getAttribute('data-comment-id');
                    console.log('Reply button clicked for comment ID:', commentId);
                    
                    const replyInput = document.querySelector(`.reply-input[data-comment-id="${commentId}"]`);
                    console.log('Looking for reply input with selector: .reply-input[data-comment-id="' + commentId + '"]');
                    console.log('Found reply input:', replyInput);
                    
                    if (replyInput) {
                        // Check current computed style
                        const currentDisplay = window.getComputedStyle(replyInput).display;
                        console.log('Current display style for this reply input:', currentDisplay);
                        
                        // First hide all reply inputs
                        allReplyInputs.forEach(input => {
                            input.style.display = 'none';
                            console.log('Hiding a reply input');
                        });
                        
                        // Then conditionally show this one
                        if (currentDisplay === 'none') {
                            replyInput.style.display = 'flex';
                            console.log('Showing reply input for comment ID:', commentId);
                        }
                        // If it was visible before, it's now hidden by the forEach above
                    } else {
                        console.log('ERROR: Could not find reply input for comment ID:', commentId);
                    }
                });
            });
        });
        // Pre-select existing rating if any
        <?php if ($user_rating !== null) { ?>
            selectedRating = <?= $user_rating['rate_value'] ?>;
            ratingValue.value = selectedRating;
            highlightStars(selectedRating);
            
            // Pre-fill comment
            document.getElementById('feedback_comment').value = '<?= addslashes($user_rating['feedback_comment']) ?>';
        <?php } ?>

        $(document).ready(function() {
        // On button click, submit the form via AJAX
        $('#likeForm').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            $.ajax({
                url: '', // The current page URL (or any PHP file that processes the like action)
                method: 'POST',
                data: $(this).serialize(), // Send the form data
                success: function(response) {
                    // Update the button's HTML or color based on the new state
                    $('#likeButton').html(response);
                }
            });
        });
    });

    function openFileDialog() {
        document.getElementById('bannerImage').click(); // Trigger the file input when the button is clicked
    }

    function previewImage() {
        var file = document.getElementById('bannerImage').files[0];
        var reader = new FileReader();

        reader.onload = function(e) {
            var preview = document.getElementById('previewImage');
            preview.src = e.target.result; // Set the preview image to the selected file
            
            // Clone the selected file to the hidden input in the form
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('bannerImageHidden').files = dataTransfer.files;
            
            var myModal = new bootstrap.Modal(document.getElementById('confirmationModal')); // Get modal instance
            myModal.show(); // Show the modal
        };

        if (file) {
            reader.readAsDataURL(file); // Read and preview the image
        }
    }


    </script>
</body>
</html>