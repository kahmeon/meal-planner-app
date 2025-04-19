<?php
session_start();
include('../includes/db.php');
include('../includes/auth.php');

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['submit_rating'])) {
    $community_id = isset($_POST['community_id']) ? (int) $_POST['community_id'] : 0;
    $recipe_id = isset($_POST['recipe_id']) ? (int) $_POST['recipe_id'] : 0;
    $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $rate_value = isset($_POST['rate_value']) ? (int) $_POST['rate_value'] : 0;
    $feedback_comment = isset($_POST['feedback_comment']) ? $_POST['feedback_comment'] : '';
    
    // Validate inputs
    if ($community_id <= 0 || $user_id <= 0 || $rate_value <= 0 || $rate_value > 5) {
        $_SESSION['rating_status'] = "Invalid rating data!";
        header("Location: community-recipe.php?community_id=" . $community_id);
        exit();
    }
    
    // Check if user has already rated this community/recipe
    $check_stmt = $conn->prepare("SELECT id FROM rating WHERE community_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $community_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing rating
        $rating_id = $result->fetch_assoc()['id'];
        $update_stmt = $conn->prepare("UPDATE rating SET rate_value = ?, feedback_comment = ?, created_at = NOW() WHERE id = ?");
        $update_stmt->bind_param("isi", $rate_value, $feedback_comment, $rating_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['rating_status'] = "Your rating has been updated!";
        } else {
            $_SESSION['rating_status'] = "Error updating rating: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        // Insert new rating
        $insert_stmt = $conn->prepare("INSERT INTO rating (community_id, user_id, rate_value, feedback_comment) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("iiis", $community_id, $user_id, $rate_value, $feedback_comment);
        
        if ($insert_stmt->execute()) {
            $_SESSION['rating_status'] = "Thank you for your rating!";
        } else {
            $_SESSION['rating_status'] = "Error adding rating: " . $conn->error;
        }
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    
    // Redirect back to community page
    header("Location: community-recipe.php?community_id=" . $community_id);
    exit();
}

// If no form submission, redirect back
header("Location: ../index.php");
exit();
?>