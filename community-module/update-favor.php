<?php
include('../includes/db.php');
include('../includes/auth.php');

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$response = ['success' => false];

if (isset($_POST['community_id']) && isset($_POST['action'])) {
    $community_id = (int)$_POST['community_id'];
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action']; // 'add' or 'remove'
    
    if ($action === 'add') {
        // Add favorite
        $stmt = $conn->prepare("INSERT INTO user_favor (community_id, user_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $community_id, $user_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'action' => 'added'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to add favorite'];
        }
        $stmt->close();
    } else {
        // Remove favorite
        $stmt = $conn->prepare("DELETE FROM user_favor WHERE community_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $community_id, $user_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'action' => 'removed'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove favorite'];
        }
        $stmt->close();
    }
}

echo json_encode($response);
?>