<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit an entry.";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate POST data
if (!isset($_POST['competition_id']) || !isset($_POST['recipe_id'])) {
    $_SESSION['error'] = "Please select a competition and a recipe.";
    header("Location: submit-competition-entry.php");
    exit();
}

$competition_id = (int) $_POST['competition_id'];
$recipe_id = (int) $_POST['recipe_id'];

// Step 1: Check if competition exists and is still active
$checkCompStmt = $conn->prepare("SELECT * FROM competitions WHERE competition_id = ? AND end_date > NOW()");
$checkCompStmt->bind_param("i", $competition_id);
$checkCompStmt->execute();
$compResult = $checkCompStmt->get_result();

if ($compResult->num_rows === 0) {
    $_SESSION['error'] = "Invalid or expired competition.";
    header("Location: submit-competition-entry.php");
    exit();
}

// Step 2: Check if user already submitted an entry to this competition
$checkEntryStmt = $conn->prepare("SELECT * FROM competition_entries WHERE user_id = ? AND competition_id = ?");
$checkEntryStmt->bind_param("ii", $user_id, $competition_id);
$checkEntryStmt->execute();
$entryResult = $checkEntryStmt->get_result();

if ($entryResult->num_rows > 0) {
    $_SESSION['info'] = "You've already submitted an entry to this competition.";
    header("Location: competition.php");
    exit();
}

// Step 3: Insert new entry
$insertStmt = $conn->prepare("INSERT INTO competition_entries (user_id, competition_id, recipe_id, status, submitted_at) VALUES (?, ?, ?, 'submitted', NOW())");
$insertStmt->bind_param("iii", $user_id, $competition_id, $recipe_id);

if ($insertStmt->execute()) {
    $_SESSION['success'] = "🎉 Your entry has been submitted successfully!";
} else {
    $_SESSION['error'] = "Something went wrong. Please try again.";
}

header("Location: competition.php");
exit();
