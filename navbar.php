<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Optional: get base URL for consistent links
$basePath = '/meal-planner-app/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>


  <style>
    :root {
      --primary: #e00000;
      --secondary: rgb(238, 140, 140);
    }
    .navbar {
      background-color: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      padding: 0.8rem 1.5rem;
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: #e00000;
      font-family: 'Pacifico', cursive;
    }
    .nav-link {
      margin-right: 15px;
      font-weight: 500;
      color: #333;
    }
    .nav-link:hover {
      color: #e00000;
    }
    .btn-signup {
      background-color: #e00000;
      color: white;
      border-radius: 8px;
      padding: 6px 16px;
      font-weight: 500;
      border: none;
    }
    .btn-signup:hover {
      background-color: #b30000;
    }
    .btn-outline-dark {
      border-radius: 8px;
      padding: 6px 16px;
      font-weight: 500;
    }
    .modal-content {
      border-radius: 20px;
      overflow: hidden;
    }
    .modal-header {
      background: #ffffff;
      color: #e00000;
      padding: 1.5rem;
      text-align: center;
    }
    .modal-title {
      font-family: 'Pacifico', cursive;
      font-size: 1.8rem;
      width: 100%;
      text-align: center;
      color: #e00000;
    }
    .modal-body {
      padding: 2rem;
    }
    .form-control {
      padding: 12px 15px;
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      margin-bottom: 1.2rem;
    }
    .btn-register, .btn-login {
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 12px;
      width: 100%;
      border-radius: 10px;
      font-weight: 600;
      margin-bottom: 1.5rem;
      transition: all 0.3s;
    }
    .btn-register:hover, .btn-login:hover {
      background-color: #b30000;
      transform: translateY(-2px);
    }
    .password-requirements {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: -0.5rem;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= $basePath ?>home.php">NomNomPlan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>aboutus.php">About Us</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Our Services
          </a>
          <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
            <li><a class="dropdown-item" href="<?= $basePath ?>recipe-modules/recipe-management.php">Recipe Management</a></li>
            <li><a class="dropdown-item" href="<?= $basePath ?>meal-planning-module/meal-plan.php">Meal Planning</a></li>
            <li><a class="dropdown-item" href="<?= $basePath ?>community-module/community.php">Community Engagement</a></li>
            <li><a class="dropdown-item" href="<?= $basePath ?>competition-module/competition.php">Cooking Competition</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="#">Tips & Blog</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>contactus.php">Contact Us</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>feedback.php">Feedback</a></li>


        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>admin/dashboard.php">Admin Panel</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center">
        <?php if (isset($_SESSION['user_id'])): ?>
          <span class="me-3">Hi, <?= htmlspecialchars($_SESSION['user_name']); ?></span>
          <a href="<?= $basePath ?>logout.php" class="btn btn-outline-dark">Logout</a>
        <?php else: ?>
          <button class="btn btn-outline-dark me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
          <button class="btn btn-signup" data-bs-toggle="modal" data-bs-target="#signupModal">Sign Up</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Modals -->
<?php if (!isset($_SESSION['user_id'])): ?>
  <!-- Login Modal -->
  <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">NomNomPlan</h5>
        </div>
        <div class="modal-body">
          <h6 class="text-center">Welcome to Our Platform</h6>
          <p class="text-center text-muted">Please sign in to continue</p>
          <form action="<?= $basePath ?>login_process.php" method="POST">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="remember">
              <label class="form-check-label" for="remember">Remember me</label>
              <a href="#" class="float-end small text-danger">Forgot password?</a>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
          </form>
          <p class="text-center">Don't have an account? <a href="#signupModal" data-bs-toggle="modal" data-bs-dismiss="modal" class="text-danger">Sign up</a></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Signup Modal -->
  <div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">NomNomPlan</h5>
        </div>
        <div class="modal-body">
          <h6 class="text-center">Create Your Account</h6>
          <p class="text-center text-muted">Join our platform to get started</p>
          <form action="<?= $basePath ?>signup.php" method="POST">
            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="password-requirements">Password must be at least 8 characters</div>
            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
              <label class="form-check-label" for="terms">I agree to the Terms & Conditions</label>
            </div>
            <button type="submit" class="btn-register">Register</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>