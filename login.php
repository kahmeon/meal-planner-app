<?php
session_start();
if (isset($_SESSION['signup_success'])) {
  echo '<div class="alert alert-success text-center">' . $_SESSION['signup_success'] . '</div>';
  unset($_SESSION['signup_success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NomNomPlan - Sign In</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #e00000; /* Updated to match navbar brand color */
      --secondary: rgb(238, 140, 140);
      --dark: #292F36;
      --light: #F7FFF7;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%,rgb(251, 251, 251) 100%);
      min-height: 100vh;
      margin: 0;
      padding: 0;
    }

    .auth-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }

    .auth-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 450px;
    }

    .auth-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 2.5rem;
      text-align: center;
      border-top-left-radius: 20px;
      border-top-right-radius: 20px;
    }

    .logo {
      font-size: 2.2rem;
      font-family: 'Pacifico', cursive;
      margin-bottom: 0.5rem;
    }

    .welcome-text h1 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .auth-body {
      padding: 2.5rem;
    }

    .form-control {
      padding: 12px 15px;
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      margin-bottom: 1.2rem;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(224, 0, 0, 0.25);
    }

    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .forgot-password {
      color: var(--primary);
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-signin {
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 10px;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .btn-signin:hover {
      background-color: #b30000;
    }

    .auth-footer {
      text-align: center;
      color: #6c757d;
      font-size: 0.9rem;
    }

    .auth-footer a {
      color: var(--primary);
      font-weight: 500;
      text-decoration: none;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div class="logo">NomNomPlan</div>
      <div class="welcome-text">
        <h1>Welcome to Our Platform</h1>
        <p>Please sign in to continue</p>
      </div>
    </div>

    <div class="auth-body">
      <form action="login_process.php" method="POST">
        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>

        <div class="form-options">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <a href="#" class="forgot-password">Forgot password?</a>
        </div>

        <button type="submit" class="btn-signin">Sign In</button>
      </form>

      <div class="auth-footer">
        Don't have an account? <a href="signup.php">Sign up</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
