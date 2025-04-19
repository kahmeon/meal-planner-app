<?php
require_once '../includes/db.php';
require_once 'community-module/likeupdate.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: ../login.php");
    exit();
}

// Handle update post status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_toggle'])) {
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
    header("Location:community-recipe.php?community_id=$community_id");
    exit;
}
?>
