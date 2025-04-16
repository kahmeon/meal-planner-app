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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NomNomPlan - Sign In</title>

  <!-- Bootstrap & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    :root {
      --primary: #e00000;
      --primary-dark: #b30000;
      --gray: #6c757d;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: url('https://images.unsplash.com/photo-1546069901-ba9599a7e63c') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .auth-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 40px 20px;
      background-color: rgba(0, 0, 0, 0.6);
    }

    .auth-card {
      background: white;
      border-radius: 20px;
      max-width: 430px;
      width: 100%;
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      overflow: hidden;
    }

    .auth-header {
      background: var(--primary);
      color: white;
      padding: 2.2rem 1.5rem 1.7rem;
      text-align: center;
      position: relative;
    }

    .auth-header::before {
      content: '';
      position: absolute;
      top: -20px;
      right: -20px;
      width: 100px;
      height: 100px;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .auth-header::after {
      content: '';
      position: absolute;
      bottom: -30px;
      left: -20px;
      width: 70px;
      height: 70px;
      background-color: rgba(255, 255, 255, 0.08);
      border-radius: 50%;
    }

    .logo {
      font-size: 2.2rem;
      font-family: 'Pacifico', cursive;
      margin-bottom: 0.3rem;
    }

    .auth-header h1 {
      font-size: 1.5rem;
      font-weight: 600;
    }

    .auth-header p {
      font-size: 0.95rem;
      opacity: 0.95;
    }

    .auth-body {
      padding: 2rem 1.8rem;
    }

    .form-control {
      padding: 12px 15px;
      border-radius: 10px;
      border: 1px solid #dee2e6;
      margin-bottom: 1rem;
      font-size: 0.95rem;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(212, 0, 0, 0.15);
    }

    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .form-check-label {
      font-size: 0.9rem;
    }

    .forgot-password {
      color: var(--primary);
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-signin {
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
      font-size: 1rem;
    }

    .btn-signin:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }

    .auth-footer {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
      color: var(--gray);
    }

    .auth-footer a {
      color: var(--primary);
      font-weight: 500;
      text-decoration: none;
    }

    .auth-footer a:hover {
      text-decoration: underline;
    }

    .alert {
      border-radius: 10px;
      margin: 1rem auto;
      width: 100%;
      max-width: 500px;
      font-size: 0.95rem;
    }

    @media (max-width: 576px) {
      .auth-body {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div class="logo">NomNomPlan</div>
      <h1>Welcome Back!</h1>
      <p>Sign in to access your recipes and meal plans</p>
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

        <button type="submit" class="btn-signin">
          <i class="fas fa-sign-in-alt me-2"></i> Sign In
        </button>
      </form>

      <div class="auth-footer">
        Don’t have an account? <a href="signup.php">Create one now</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
