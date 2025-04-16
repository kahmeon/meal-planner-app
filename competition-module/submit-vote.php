<?php
session_start();
require_once '../includes/db.php';  // Include database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate the incoming vote data
if (isset($_POST['vote']) && isset($_POST['recipe_id']) && isset($_POST['competition_id'])) {
    $user_id = $_SESSION['user_id'];  // Get the logged-in user ID
    $vote = (int) $_POST['vote'];  // Either 1 (Upvote) or -1 (Downvote)
    $recipe_id = (int) $_POST['recipe_id'];
    $competition_id = (int) $_POST['competition_id'];

    // Check if the user has already voted
    $checkVoteStmt = $conn->prepare("SELECT * FROM recipe_votes WHERE recipe_id = ? AND user_id = ?");
    $checkVoteStmt->bind_param("ii", $recipe_id, $user_id);
    $checkVoteStmt->execute();
    $checkVoteResult = $checkVoteStmt->get_result();

    if ($checkVoteResult->num_rows > 0) {
        // Update the vote if the user has already voted
        $updateVoteStmt = $conn->prepare("UPDATE recipe_votes SET vote = ? WHERE recipe_id = ? AND user_id = ?");
        $updateVoteStmt->bind_param("iii", $vote, $recipe_id, $user_id);
        $updateVoteStmt->execute();
    } else {
        // Insert a new vote if the user hasn't voted yet
        $insertVoteStmt = $conn->prepare("INSERT INTO recipe_votes (recipe_id, user_id, vote) VALUES (?, ?, ?)");
        $insertVoteStmt->bind_param("iii", $recipe_id, $user_id, $vote);
        $insertVoteStmt->execute();
    }

    // Redirect back to the voting page with a success message
    header("Location: vote-entries.php?id=" . $competition_id . "&message=Vote Submitted Successfully");
    exit;
} else {
    // Redirect with an error if data is missing
    header("Location: vote-entries.php?id=" . $_POST['competition_id'] . "&error=Invalid Vote");
    exit;
}
?>
