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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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

    .carousel-item {
      height: 80vh;
      background-size: cover;
      background-position: center;
    }

    .hero-overlay {
      background-color: rgba(0, 0, 0, 0.6);
      padding: 3rem;
      border-radius: 20px;
      max-width: 700px;
      margin: auto;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
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

    .trust-badges {
    background-color: #f9f9f9;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.trust-badges img {
    transition: all 0.3s ease;
    opacity: 0.7;
    filter: grayscale(30%);
}

.trust-badges img:hover {
    opacity: 1;
    filter: grayscale(0);
    transform: scale(1.05);
}
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Hero Slider Section -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
  <div class="carousel-inner">
    <!-- First Slide -->
    <div class="carousel-item active" style="background-image: url('https://images.pexels.com/photos/70497/pexels-photo-70497.jpeg');">
      <div class="hero-overlay">
        <h1 class="display-4 fw-bold">Welcome to NomNomPlan</h1>
        <p class="lead">Your personal meal planner and recipe hub!</p>
        <a href="signup.php" class="btn btn-red btn-lg mt-3">Get Started</a>
      </div>
    </div>
    <!-- Second Slide -->
    <div class="carousel-item" style="background-image: url('https://images.pexels.com/photos/461198/pexels-photo-461198.jpeg');">
      <div class="hero-overlay">
        <h1 class="display-4 fw-bold">Explore New Recipes</h1>
        <p class="lead">Discover delicious recipes every day.</p>
      </div>
    </div>
    <!-- Third Slide -->
    <div class="carousel-item" style="background-image: url('https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg');">
      <div class="hero-overlay">
        <h1 class="display-4 fw-bold">Connect & Share</h1>
        <p class="lead">Join our vibrant cooking community.</p>
      </div>
    </div>
    <!-- Fourth Slide -->
    <div class="carousel-item" style="background-image: url('https://images.pexels.com/photos/4253312/pexels-photo-4253312.jpeg');">
      <div class="hero-overlay">
        <h1 class="display-4 fw-bold">Cooking Challenges</h1>
        <p class="lead">Participate and win exciting prizes!</p>
      </div>
    </div>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>


<!-- About Preview -->
<section class="container py-5 text-center">
  <h2 class="mb-3 fw-bold">Smart, Simple, and Satisfying</h2>
  <p class="text-muted">NomNomPlan helps you plan meals, discover recipes, and enjoy a healthier lifestyle effortlessly.</p>
</section>

<!-- Services Section -->
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

<!-- About Us Section -->
<section class="container py-5 text-center">
  <div class="row align-items-center">
    <div class="col-md-6 mb-4">
      <img src="https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg" class="img-fluid rounded" alt="Delicious food">
    </div>
    <div class="col-md-6">
      <h2 class="fw-bold">About Us</h2>
      <p class="text-muted">
        We are dedicated to providing delicious and nutritious meals to fuel your culinary adventures. From meal planning to cooking competitions, our platform is designed to inspire and support every home.
      </p>
      <!-- Link to aboutus.php -->
      <a href="aboutus.php" class="btn btn-red btn-lg">Find out more about us</a>
    </div>
  </div>
</section>

<!-- Featured Recipes -->
<section class="container py-5">
  <h2 class="text-center mb-4 fw-bold">Today's Featured Recipes</h2>
  <div class="row g-4">
    <!-- Recipe 1 -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <img src="https://images.pexels.com/photos/1279330/pexels-photo-1279330.jpeg" class="card-img-top rounded-top" alt="Rosemary Lemon Grilled Chicken" style="height: 200px; object-fit: cover;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-danger text-white">CHICKEN</span>
            <span class="text-warning">★★★★★ <span class="text-muted">(281)</span></span>
          </div>
          <h5 class="card-title">Rosemary Lemon Grilled Chicken</h5>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted"><i class="far fa-clock me-1"></i> 55 mins</span>
            <a href="recipe.php?id=1" class="btn btn-sm btn-danger">View Recipe</a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recipe 2 -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd" class="card-img-top rounded-top" alt="Roasted New Red Potatoes" style="height: 200px; object-fit: cover;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-danger text-white">VEGETARIAN</span>
            <span class="text-warning">★★★★★ <span class="text-muted">(1,424)</span></span>
          </div>
          <h5 class="card-title">Roasted New Red Potatoes</h5>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted"><i class="far fa-clock me-1"></i> 35 mins</span>
            <a href="recipe.php?id=2" class="btn btn-sm btn-danger">View Recipe</a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Recipe 3 -->
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <img src="https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2" class="card-img-top rounded-top" alt="Chef John's Fresh Salmon Cakes" style="height: 200px; object-fit: cover;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-danger text-white">SEAFOOD</span>
            <span class="text-warning">★★★★★ <span class="text-muted">(393)</span></span>
          </div>
          <h5 class="card-title">Chef John's Fresh Salmon Cakes</h5>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted"><i class="far fa-clock me-1"></i> 45 mins</span>
            <a href="recipe.php?id=3" class="btn btn-sm btn-danger">View Recipe</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="text-center mt-4">
    <a href="recipes.php" class="btn btn-danger px-4">Explore All Recipes →</a>
  </div>
</section>

<!-- Testimonials Section -->
<section class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold mb-3">What Our Community Says</h2>
    <p class="text-muted">Join 25,000+ home cooks who love NomNomPlan</p>
  </div>
  
  <div class="row g-4">
    <!-- Testimonial 1 -->
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm p-4">
        <div class="d-flex align-items-center mb-3">
          <img src="https://randomuser.me/api/portraits/women/44.jpg" 
               class="rounded-circle me-3" 
               width="60" 
               height="60" 
               alt="Sarah K.">
          <div>
            <h5 class="mb-1">Sarah K.</h5>
            <div class="text-warning small">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>
        <p class="text-muted mb-4">"NomNomPlan cut my meal prep time in half! The weekly planner is a game-changer for my busy family."</p>
        <div class="mt-auto">
          <span class="badge bg-light text-dark small">
            <i class="fas fa-utensils me-1"></i> Meal Prep Enthusiast
          </span>
        </div>
      </div>
    </div>
    
    <!-- Testimonial 2 -->
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm p-4">
        <div class="d-flex align-items-center mb-3">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" 
               class="rounded-circle me-3" 
               width="60" 
               height="60" 
               alt="Michael T.">
          <div>
            <h5 class="mb-1">Michael T.</h5>
            <div class="text-warning small">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>
        <p class="text-muted mb-4">"I've discovered so many new favorite recipes. The collections by cuisine type are incredibly helpful!"</p>
        <div class="mt-auto">
          <span class="badge bg-light text-dark small">
            <i class="fas fa-home me-1"></i> Home Chef
          </span>
        </div>
      </div>
    </div>
    
    <!-- Testimonial 3 -->
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm p-4">
        <div class="d-flex align-items-center mb-3">
          <img src="https://randomuser.me/api/portraits/women/68.jpg" 
               class="rounded-circle me-3" 
               width="60" 
               height="60" 
               alt="Priya M.">
          <div>
            <h5 class="mb-1">Priya M.</h5>
            <div class="text-warning small">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>
        <p class="text-muted mb-4">"As a food blogger, I appreciate how easy it is to save and organize recipes from different sources."</p>
        <div class="mt-auto">
          <span class="badge bg-light text-dark small">
            <i class="fas fa-blog me-1"></i> Food Blogger
          </span>
        </div>
      </div>
    </div>
  </div>
  
 <!-- Trust Badges Section -->
<section class="trust-badges py-4">
  <div class="container">
    <div class="row justify-content-center align-items-center g-4">
      <div class="col-auto">
        <img src="images/badges/food-magazine.png" 
             alt="Featured in Food Magazine" 
             class="img-fluid trust-badge"
             style="height: 30px;">
      </div>
      <div class="col-auto">
        <img src="images/badges/top-app-2023.png" 
             alt="Top App 2023" 
             class="img-fluid trust-badge"
             style="height: 30px;">
      </div>
      <div class="col-auto">
        <img src="images/badges/chef-approved.png" 
             alt="Chef Approved" 
             class="img-fluid trust-badge"
             style="height: 30px;">
      </div>
    </div>
  </div>
</section>

<!-- Footer - Outside all other sections -->
<?php include 'includes/footer.php'; ?>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
