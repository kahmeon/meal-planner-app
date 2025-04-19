<?php 
require_once '../includes/db.php';
session_start();

if (isset($_POST['post_submit']) && !empty($_POST['post_comment'])) {
    $user_id = $_SESSION['user_id'];
    $comm_id = $_GET['community_id'];
    $recipe_id = $_GET['recipe_id'];
    $post_status = "";
    $post_images = [];
    // Sanitize post content
    $post_content = htmlspecialchars(trim($_POST['post_comment']));

    // Check for uploaded images
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'][0] !== 4) {
        $post_images = $_FILES['post_image'];
    }

    // Check if the user is the creator of the recipe
    $stmt_check = $conn->prepare("SELECT created_by FROM recipes WHERE id = ?");
    $stmt_check->bind_param("i", $recipe_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $stmt_check->bind_result($created_by);

    if ($stmt_check->num_rows > 0) {
        $stmt_check->fetch();

        if ($user_id != $created_by) {
            $user_role = "User";
        } else {
            $user_role = "Admin";
        }
    }

    // Insert into the post database
    $stmt = $conn->prepare("INSERT INTO post (community_id, user_id, recipe_id, comment, user_role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $comm_id, $user_id, $recipe_id, $post_content, $user_role);
    if ($stmt->execute()) {
        $post_id = $stmt->insert_id; // Get the newly inserted post ID
        
        // Handle images if any
        if ($post_images && !empty($post_images['name'][0])) {
            $upload_dir = "uploads/community/post/";
        
            foreach ($post_images['tmp_name'] as $index => $tmp_name) {
                $file_name = basename($post_images['name'][$index]);
                $target_path = $upload_dir . $file_name;
        
                if (move_uploaded_file($tmp_name, $target_path)) {
                    $stmt_img = $conn->prepare("INSERT INTO post_image (post_id, photo_url) VALUES (?, ?)");
                    $stmt_img->bind_param("is", $post_id, $target_path);
                    $stmt_img->execute();
                    $stmt_img->close();
                }
            }
        }
        $stmt->close();
        $post_status = "Post and images uploaded successfully!";
        header("Location: community-recipe.php?community_id=" . urlencode($comm_id) . "&post_status=" . urlencode($post_status));
        exit;
    } else {
        $post_status = "Failed to insert post.";
        header("Location: community-recipe.php?community_id=" . urlencode($comm_id) . "&post_status=" . urlencode($post_status));
        exit;
    }
    
    exit();
}
?>
