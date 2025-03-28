<?php
session_start();

require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);
  $error = "";

  if (empty($email) || empty($password)) {
    $error = "Email and password are required.";
  } else {
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($password, $user['password'])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_role"] = $user["role"];

        header("Location: home.php");
        exit();
      } else {
        $error = "Incorrect password.";
      }
    } else {
      $error = "No account found with that email.";
    }

    $stmt->close();
  }

  if (!empty($error)) {
    $_SESSION['login_error'] = $error;
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
  }

  $conn->close();
}
?>
