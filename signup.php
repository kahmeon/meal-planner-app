<?php
if (session_status() === PHP_SESSION_NONE) {
  
}
include 'signup_process.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NomNomPlan - Sign Up</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    :root {
      --primary: #a80000;
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

    .auth-header h5 {
      font-size: 1.4rem;
      font-weight: 600;
      margin: 0.3rem 0;
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

    .form-check-label {
      font-size: 0.9rem;
    }

    .btn-register {
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
      font-size: 1rem;
      margin-top: 1rem;
    }

    .btn-register:hover {
      background-color: #a80000;
      transform: translateY(-2px);
    }

    .auth-footer {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
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
      margin-bottom: 1rem;
      font-size: 0.9rem;
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
      <h5>Welcome Aboard!</h5>
      <p>Create your account to get started</p>
    </div>

    <div class="auth-body">
      <?php if (!empty($success)): ?>
        <div class="alert alert-success text-center"><?= $success ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="signup.php" method="POST">
        <input type="text" class="form-control" name="name" placeholder="Full Name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"/>

        <input type="email" class="form-control" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>

        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />

        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="terms" name="terms" required <?= isset($_POST['terms']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="terms">I agree to the <a href="#">Terms & Conditions</a></label>
        </div>

        <button type="submit" class="btn-register">
          <i class="fas fa-user-plus me-2"></i> Create Account
        </button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  document.querySelector('form').addEventListener('submit', function(e) {
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (pass !== confirm) {
      e.preventDefault();
      alert('Passwords do not match!');
    }

    if (!document.getElementById('terms').checked) {
      e.preventDefault();
      alert('Please agree to the Terms & Conditions.');
    }
  });
</script>

</body>
</html>
