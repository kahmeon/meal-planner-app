<?php 
require_once '../includes/db.php';
session_start();

if (isset($_POST['comment_submit']) && !empty($_POST['comment_txt'])) {
    $comment_status = "";
    $community_id = $_GET['community_id'] ?? null;
    $post_id = $_GET['post_id'] ?? null;
    $user_id = $_GET['user_id'] ?? null;
    $reply_user_id = $_GET['reply_user_id'] ?? null;
    $comment_txt = trim($_POST['comment_txt'] ?? '');

    if ($user_id && !empty($comment_txt)) {
        $stmt = $conn->prepare("INSERT INTO reply_comment (community_id, post_id, user_id, reply_id, content) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $community_id, $post_id, $user_id, $reply_user_id, $comment_txt);
        
        if ($stmt->execute()) {
            $comment_status = "Reply inserted successfully!";
        } else {
            $comment_status = "Failed to insert reply: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $comment_status = "Missing input or empty comment.";
        header("Location: community-recipe.php?community_id=" . urlencode($community_id) . "&comment_status=" . urlencode($comment_status));
        exit();
    }

    // Redirect back with message
    header("Location: community-recipe.php?community_id=" . urlencode($community_id) . "&comment_status=" . urlencode($comment_status));
    exit();
}
?>
