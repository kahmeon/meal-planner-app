<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>NomNomPlan | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">


  <style>
    body {
      background-color: #fff;
      font-family: 'Poppins', sans-serif;
    }

    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2rem;
      color: #e00000;
    }

    .hero {
      background: url('https://images.pexels.com/photos/70497/pexels-photo-70497.jpeg') no-repeat center center;
      background-size: cover;
      height: 80vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
    }

    .hero-overlay {
      background-color: rgba(0, 0, 0, 0.6);
      padding: 3rem;
      border-radius: 20px;
      max-width: 700px;
      margin: 0 auto;
    }

    .btn-red {
      background-color: #e00000;
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 8px;
      font-weight: 500;
    }

    .btn-red:hover {
      background-color: #b30000;
    }

    .services .card {
      border: none;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease;
    }

    .services .card:hover {
      transform: translateY(-5px);
    }

    .services h5 {
      font-weight: 600;
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
    }

    .text-muted {
      font-size: 0.95rem;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-overlay text-white">
    <h1 class="display-4 fw-bold">Welcome to NomNomPlan</h1>
    <p class="lead">Your personal meal planner and recipe hub!</p>
    <a href="signup.php" class="btn btn-red btn-lg mt-3">Get Started</a>
  </div>
</section>

<!-- About Preview -->
<section class="container py-5 text-center">
  <h2 class="mb-3 fw-bold">Smart, Simple, and Satisfying</h2>
  <p class="text-muted">NomNomPlan helps you plan meals, discover recipes, and enjoy a healthier lifestyle effortlessly.</p>
</section>

<!-- Services -->
<section class="container py-4 services">
  <div class="row text-center">
    <div class="col-md-3 mb-4">
      <div class="card p-4 h-100">
        <h5>📋 Meal Planning</h5>
        <p class="text-muted">Plan your meals for the week with ease.</p>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card p-4 h-100">
        <h5>🍳 Recipe Manager</h5>
        <p class="text-muted">Save, organize, and view your favorite recipes.</p>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card p-4 h-100">
        <h5>👨‍👩‍👧‍👦 Community</h5>
        <p class="text-muted">Join others and share your food journey.</p>
      </div>
    </div>
    <div class="col-md-3 mb-4">
      <div class="card p-4 h-100">
        <h5>🏆 Cooking Challenges</h5>
        <p class="text-muted">Show off your skills in monthly contests.</p>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
