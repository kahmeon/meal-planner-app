<?php
// Start the session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

function isAdmin() {
  return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function isUser() {
  return isLoggedIn() && $_SESSION['user_role'] === 'user';
}

function redirectIfNotLoggedIn() {
  if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
  }
}

function redirectIfNotAdmin() {
  if (!isAdmin()) {
    header("Location: ../index.php");
    exit();
  }
}
?>
