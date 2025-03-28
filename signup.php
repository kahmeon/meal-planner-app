<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'signup_process.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NomNomPlan - Register</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"/>

  <style>
    :root {
      --primary: #e00000;
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

    .btn-register {
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

    .btn-register:hover {
      background-color: #b30000;
      transform: translateY(-2px);
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

    .password-requirements {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: -0.5rem;
      margin-bottom: 1rem;
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
        <h1>Create Your Account</h1>
        <p>Join our platform to get started</p>
      </div>
    </div>

    <?php if (!empty($success)): ?>
  <div class="alert alert-success text-center"><?= $success ?></div>
<?php endif; ?>

    <div class="auth-body">

    <?php if (!empty($error)): ?>
  <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

      <form action="signup.php" method="POST">
        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required />

        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required />

        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
        <div class="password-requirements">Password must be at least 8 characters</div>

        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />

        <div class="form-check mb-4">
        <input type="checkbox" class="form-check-input" id="terms" name="terms" required />
          <label class="form-check-label" for="terms">I agree to the Terms & Conditions</label>
        </div>

        <button type="submit" class="btn-register">Register</button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Password Match Check -->
<script>
  document.querySelector('form').addEventListener('submit', function(e) {
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (pass !== confirm) {
      e.preventDefault();
      alert('Passwords do not match!');
      return false;
    }

    if (!document.getElementById('terms').checked) {
      e.preventDefault();
      alert('Please agree to the Terms & Conditions.');
      return false;
    }

    return true;
  });
</script>
</body>
</html>

