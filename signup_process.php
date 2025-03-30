<?php
session_start();

require_once "includes/db.php";

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST["name"]);
  $email = trim($_POST["email"]);
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];
  $role = "user";
  $created_at = date("Y-m-d H:i:s");

  if (strlen($password) < 8) {
    $error = "Password must be at least 8 characters.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "Email is already registered.";
    } else {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $insert = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)");
      $insert->bind_param("sssss", $name, $email, $hashedPassword, $role, $created_at);

      if ($insert->execute()) {
        $_SESSION["signup_success"] = "Registration successful! You can now log in.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
      } else {
        $error = "Something went wrong. Please try again.";
      }

      $insert->close();
    }

    $stmt->close();
  }

  if (!empty($error)) {
    $_SESSION['signup_error'] = $error;
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
  }

  $conn->close();
}
?>