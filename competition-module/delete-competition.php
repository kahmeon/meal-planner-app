<?php
include '../includes/db.php';
include '../includes/auth.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $competition_id = $_GET['id'];
    $referer = $_SERVER['HTTP_REFERER'] ?? 'competitions.php'; // Get referring page
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // 1. Delete all entries for this competition
        $delete_entries = $conn->prepare("DELETE FROM competition_entries WHERE competition_id = ?");
        $delete_entries->bind_param("i", $competition_id);
        $delete_entries->execute();
        
        // 2. Get image path to delete the file
        $get_image = $conn->prepare("SELECT image_url FROM competitions WHERE competition_id = ?");
        $get_image->bind_param("i", $competition_id);
        $get_image->execute();
        $result = $get_image->get_result();
        $competition = $result->fetch_assoc();
        
        // 3. Delete the competition
        $delete_competition = $conn->prepare("DELETE FROM competitions WHERE competition_id = ?");
        $delete_competition->bind_param("i", $competition_id);
        $delete_competition->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Delete image file if it's not the default
        if ($competition && $competition['image_url'] !== 'assets/images/default-competition.jpg') {
            @unlink('../' . $competition['image_url']);
        }
        
        $_SESSION['success_message'] = "Competition and all related entries deleted successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting competition: " . $e->getMessage();
    }
    
    // Redirect back to the referring page
    header("Location: $referer");
    exit();
} else {
    $_SESSION['error_message'] = "No competition specified";
    header("Location: competitions.php");
    exit();
}
?>