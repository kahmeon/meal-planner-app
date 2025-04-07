<?php
session_start();
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>NomNomPlan | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    :root {
      --primary-color: #e00000;
      --primary-dark: #b30000;
      --secondary-color: #ff9a3c;
      --light-bg: #f8f9fa;
      --dark-text: #212529;
    }
    
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      overflow-x: hidden;
      font-family: 'Montserrat', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #f8f9fa 100%);
      position: relative;
      min-height: 100vh;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 30%, rgba(224, 0, 0, 0.08) 0%, transparent 20%),
                  radial-gradient(circle at 80% 70%, rgba(255, 154, 60, 0.08) 0%, transparent 20%);
      z-index: -1;
      pointer-events: none;
    }

    /* Modern Slider Styles */
    .hero-slider {
      position: relative;
      height: 90vh;
      overflow: hidden;
    }

    .slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      opacity: 0;
      transition: opacity 1.5s ease, transform 8s ease;
      transform: scale(1);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .slide.active {
      opacity: 1;
      z-index: 1;
    }

    .slide.active:hover {
      transform: scale(1.03);
    }

    .slide::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.7) 100%);
    }

    .slide-content {
      position: relative;
      z-index: 2;
      text-align: center;
      color: white;
      max-width: 800px;
      padding: 0 20px;
      transform: translateY(20px);
      transition: transform 1s ease;
      opacity: 0;
    }

    .slide.active .slide-content {
      transform: translateY(0);
      opacity: 1;
    }

    .slider-nav {
      position: absolute;
      bottom: 40px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      gap: 10px;
    }

    .slider-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255,255,255,0.5);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .slider-dot.active {
      background: var(--primary-color);
      transform: scale(1.3);
    }

    .slider-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      width: 50px;
      height: 50px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      transition: all 0.3s ease;
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255,255,255,0.1);
    }

    .slider-arrow:hover {
      background: var(--primary-color);
    }

    .slider-arrow.prev {
      left: 30px;
    }

    .slider-arrow.next {
      right: 30px;
    }

    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2rem;
      color: var(--primary-color);
      transition: all 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    .btn-red {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(224, 0, 0, 0.3);
    }

    .btn-red:hover {
      background-color: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(224, 0, 0, 0.4);
    }

    .btn-outline-red {
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
      background: transparent;
      padding: 10px 25px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-outline-red:hover {
      background-color: var(--primary-color);
      color: white;
      transform: translateY(-3px);
    }

    .section {
      padding: 5rem 0;
      position: relative;
    }

    .section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(216, 214, 214, 0.9);
      z-index: -1;
      border-radius: 20px;
    }

    .services .card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
      transition: all 0.4s ease;
      background: white;
    }

    .services .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .trust-badges {
      background-color: rgba(255, 255, 255, 0.8);
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      backdrop-filter: blur(5px);
    }

    .trust-badges img {
      transition: all 0.3s ease;
      opacity: 0.8;
      filter: grayscale(30%);
      margin: 0 1.5rem;
      height: 40px;
    }

    .trust-badges img:hover {
      opacity: 1;
      filter: grayscale(0);
      transform: scale(1.1);
    }

    .wrapper {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .hover-shadow {
      transition: all 0.3s ease;
    }

    .hover-shadow:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .feature-card {
      border-radius: 15px;
      overflow: hidden;
      transition: all 0.4s ease;
      border: none;
    }

    .feature-card:hover {
      transform: translateY(-5px) scale(1.02);
    }

    .feature-card .card-img-top {
      height: 200px;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .feature-card:hover .card-img-top {
      transform: scale(1.05);
    }

    .testimonial-card {
      border-radius: 15px;
      background: white;
      transition: all 0.3s ease;
      border: none;
    }

    .testimonial-card:hover {
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }

    footer {
      margin-top: auto;
      background: rgba(0, 0, 0, 0.9);
      color: white;
      padding: 3rem 0;
    }

    .scroll-effect {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.6s ease;
    }

    .scroll-effect.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .icon-circle {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin: 0 auto;
    }

    .icon-circle-sm {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
    }

    @media (max-width: 768px) {
      .hero-slider {
        height: 70vh;
      }
      
      .slider-arrow {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
      
      .slider-arrow.prev {
        left: 15px;
      }
      
      .slider-arrow.next {
        right: 15px;
      }
      
      .slider-nav {
        bottom: 20px;
      }
      
      .section {
        padding: 3rem 0;
      }

      .slide-content h1 {
        font-size: 2.5rem;
      }

      .slide-content .lead {
        font-size: 1.25rem;
      }
    }
  </style>
</head>
<body>
<div class="wrapper">

<?php include 'navbar.php'; ?>

<!-- Modern Hero Slider -->
<div class="hero-slider">
  <!-- Slide 1 -->
  <div class="slide active" style="background-image: url('https://images.pexels.com/photos/70497/pexels-photo-70497.jpeg');">
    <div class="slide-content">
      <h1 class="display-3 fw-bold mb-4">Welcome to NomNomPlan</h1>
      <p class="lead fs-3 mb-4">Your personal meal planner and recipe hub!</p>
      <div class="d-flex justify-content-center gap-3">
        <a href="signup.php" class="btn btn-red btn-lg">Get Started</a>
        <a href="aboutus.php" class="btn btn-outline-light btn-lg">Learn More</a>
      </div>
    </div>
  </div>
  
  <!-- Slide 2 -->
  <div class="slide" style="background-image: url('https://images.pexels.com/photos/461198/pexels-photo-461198.jpeg');">
    <div class="slide-content">
      <h1 class="display-3 fw-bold mb-4">Explore New Recipes</h1>
      <p class="lead fs-3 mb-4">Discover delicious recipes every day.</p>
      <a href="recipe-modules/list-recipes.php" class="btn btn-red btn-lg">Browse Recipes</a>
    </div>
  </div>
  
  <!-- Slide 3 -->
  <div class="slide" style="background-image: url('https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg');">
    <div class="slide-content">
      <h1 class="display-3 fw-bold mb-4">Connect & Share</h1>
      <p class="lead fs-3 mb-4">Join our vibrant cooking community.</p>
      <a href="community.php" class="btn btn-red btn-lg">Join Community</a>
    </div>
  </div>
  
  <!-- Slide 4 -->
  <div class="slide" style="background-image: url('https://images.pexels.com/photos/4253312/pexels-photo-4253312.jpeg');">
    <div class="slide-content">
      <h1 class="display-3 fw-bold mb-4">Cooking Challenges</h1>
      <p class="lead fs-3 mb-4">Participate and win exciting prizes!</p>
      <a href="challenges.php" class="btn btn-red btn-lg">View Challenges</a>
    </div>
  </div>

  <!-- Slider Navigation -->
  <div class="slider-arrow prev">
    <i class="fas fa-chevron-left"></i>
  </div>
  <div class="slider-arrow next">
    <i class="fas fa-chevron-right"></i>
  </div>
  
  <div class="slider-nav">
    <div class="slider-dot active" data-slide="0"></div>
    <div class="slider-dot" data-slide="1"></div>
    <div class="slider-dot" data-slide="2"></div>
    <div class="slider-dot" data-slide="3"></div>
  </div>
</div>

<!-- Services Section -->
<section class="section">
  <div class="container">
    <div class="text-center mb-5 scroll-effect">
      <h2 class="fw-bold display-5 mb-3">What You Can Do with NomNomPlan</h2>
      <p class="text-muted lead">Discover features designed to make your kitchen life easier and more joyful.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-3 scroll-effect" style="transition-delay: 0.1s">
        <div class="card h-100 feature-card">
          <div class="card-body text-center p-4">
            <div class="mb-4">
              <div class="icon-circle bg-danger bg-opacity-10 text-danger">
                <i class="fas fa-calendar-alt"></i>
              </div>
            </div>
            <h4 class="fw-semibold mb-3">Meal Planning</h4>
            <p class="text-muted">Plan your meals for the week with our intuitive drag-and-drop planner.</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 scroll-effect" style="transition-delay: 0.2s">
        <div class="card h-100 feature-card">
          <div class="card-body text-center p-4">
            <div class="mb-4">
              <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-book"></i>
              </div>
            </div>
            <h4 class="fw-semibold mb-3">Recipe Manager</h4>
            <p class="text-muted">Save, organize, and access your favorite recipes from any device.</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 scroll-effect" style="transition-delay: 0.3s">
        <div class="card h-100 feature-card">
          <div class="card-body text-center p-4">
            <div class="mb-4">
              <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-users"></i>
              </div>
            </div>
            <h4 class="fw-semibold mb-3">Community</h4>
            <p class="text-muted">Connect with other food lovers and share your culinary creations.</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 scroll-effect" style="transition-delay: 0.4s">
        <div class="card h-100 feature-card">
          <div class="card-body text-center p-4">
            <div class="mb-4">
              <div class="icon-circle bg-success bg-opacity-10 text-success">
                <i class="fas fa-trophy"></i>
              </div>
            </div>
            <h4 class="fw-semibold mb-3">Challenges</h4>
            <p class="text-muted">Participate in fun cooking challenges with exciting prizes.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About Us Section -->
<section class="section bg-light">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-5 mb-lg-0 scroll-effect">
        <div class="position-relative">
          <img src="https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg" class="img-fluid rounded-4 shadow-lg" alt="Delicious food cooking" style="width: 100%;">
          <div class="position-absolute bottom-0 start-0 bg-white p-3 rounded-end rounded-top shadow-sm" style="transform: translateY(20px);">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i class="fas fa-utensils fs-1 text-danger"></i>
              </div>
              <div>
                <h5 class="mb-0">25,000+ Recipes</h5>
                <small class="text-muted">And counting!</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 scroll-effect" style="transition-delay: 0.2s">
        <h2 class="fw-bold mb-4 display-5">Who We Are</h2>
        <p class="lead mb-4">
          <strong>NomNomPlan</strong> is your everyday kitchen companion — built to simplify meal planning, spark culinary creativity, and support healthy living.
        </p>
        <div class="mb-4">
          <div class="d-flex mb-3">
            <div class="me-3">
              <div class="icon-circle-sm bg-danger bg-opacity-10 text-danger">
                <i class="fas fa-check"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-1">Weekly Meal Planner</h5>
              <p class="text-muted mb-0">Smart suggestions based on your preferences and schedule.</p>
            </div>
          </div>
          <div class="d-flex mb-3">
            <div class="me-3">
              <div class="icon-circle-sm bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-check"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-1">Recipe Inspiration</h5>
              <p class="text-muted mb-0">Community-driven recipes from home cooks worldwide.</p>
            </div>
          </div>
          <div class="d-flex">
            <div class="me-3">
              <div class="icon-circle-sm bg-success bg-opacity-10 text-success">
                <i class="fas fa-check"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-1">Healthy Living</h5>
              <p class="text-muted mb-0">Nutritional insights and balanced meal suggestions.</p>
            </div>
          </div>
        </div>
        <a href="aboutus.php" class="btn btn-red px-4 me-2">Our Story</a>
        <a href="signup.php" class="btn btn-outline-danger px-4">Join Now</a>
      </div>
    </div>
  </div>
</section>

<!-- Featured Recipes -->
<section class="section">
  <div class="container">
    <div class="text-center mb-5 scroll-effect">
      <h2 class="fw-bold display-5 mb-3">Today's Featured Recipes</h2>
      <p class="text-muted lead">Handpicked selections from our culinary team</p>
    </div>

    <div class="row g-4">
      <?php
      $featuredQuery = $conn->query("SELECT r.*, (
        SELECT image_url FROM recipe_images WHERE recipe_id = r.id LIMIT 1
      ) AS image FROM recipes r WHERE is_public = 1 AND status = 'approved' ORDER BY created_at DESC LIMIT 3");

      $delays = [0, 0.1, 0.2];
      $i = 0;
      
      while ($recipe = $featuredQuery->fetch_assoc()):
        $badge = strtoupper($recipe['cuisine']) ?: 'RECIPE';
        $time = $recipe['total_time'] ?: ($recipe['prep_time'] + $recipe['cook_time']);
        $image = !empty($recipe['image']) ? '/meal-planner-app/' . ltrim($recipe['image'], '/') : '/meal-planner-app/assets/no-image.jpg';
      ?>
      <div class="col-md-4 scroll-effect" style="transition-delay: <?= $delays[$i++] ?>s">
        <div class="card h-100 feature-card">
          <div class="position-relative overflow-hidden" style="height: 200px;">
            <img src="<?= htmlspecialchars($image) ?>" class="card-img-top h-100 w-100" alt="<?= htmlspecialchars($recipe['title']) ?>" style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/400x250?text=No+Image';">
            <div class="position-absolute top-0 end-0 m-3">
              <span class="badge bg-danger"><?= htmlspecialchars($badge) ?></span>
            </div>
          </div>
          <div class="card-body">
            <h5 class="card-title fw-semibold"><?= htmlspecialchars($recipe['title']) ?></h5>
            <div class="d-flex align-items-center mb-3">
              <div class="text-warning small me-2">
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
              </div>
              <small class="text-muted">(24 reviews)</small>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted"><i class="far fa-clock me-1"></i> <?= $time ?> mins</span>
              <a href="recipe-modules/view-recipe.php?id=<?= $recipe['id'] ?>" class="btn btn-sm btn-danger">View Recipe</a>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <div class="text-center mt-5 scroll-effect">
      <a href="recipe-modules/list-recipes.php" class="btn btn-red px-4">Explore All Recipes <i class="fas fa-arrow-right ms-2"></i></a>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section class="section bg-light">
  <div class="container">
    <div class="text-center mb-5 scroll-effect">
      <h2 class="fw-bold display-5 mb-3">What Our Community Says</h2>
      <p class="text-muted lead">Join 25,000+ home cooks who love NomNomPlan</p>
    </div>

    <div class="row g-4">
      <!-- Testimonial 1 -->
      <div class="col-md-4 scroll-effect" style="transition-delay: 0.1s">
        <div class="card h-100 border-0 p-4 testimonial-card">
          <div class="d-flex align-items-center mb-4">
            <img src="https://randomuser.me/api/portraits/women/44.jpg"
                 class="rounded-circle me-3"
                 width="60"
                 height="60"
                 alt="Sarah K.">
            <div>
              <h5 class="mb-1 fw-semibold">Sarah K.</h5>
              <div class="text-warning small">
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-muted mb-4">"NomNomPlan cut my meal prep time in half! The weekly planner is a game-changer for my busy family. I love how it suggests recipes based on what's in my pantry."</p>
          <div class="mt-auto">
            <span class="badge bg-light text-dark small">
              <i class="fas fa-utensils me-1"></i> Meal Prep Enthusiast
            </span>
          </div>
        </div>
      </div>

      <!-- Testimonial 2 -->
      <div class="col-md-4 scroll-effect" style="transition-delay: 0.2s">
        <div class="card h-100 border-0 p-4 testimonial-card">
          <div class="d-flex align-items-center mb-4">
            <img src="https://randomuser.me/api/portraits/men/32.jpg"
                 class="rounded-circle me-3"
                 width="60"
                 height="60"
                 alt="Michael T.">
            <div>
              <h5 class="mb-1 fw-semibold">Michael T.</h5>
              <div class="text-warning small">
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-muted mb-4">"I've discovered so many new favorite recipes through NomNomPlan. The collections by cuisine type are incredibly helpful for expanding my cooking repertoire."</p>
          <div class="mt-auto">
            <span class="badge bg-light text-dark small">
              <i class="fas fa-home me-1"></i> Home Chef
            </span>
          </div>
        </div>
      </div>

      <!-- Testimonial 3 -->
      <div class="col-md-4 scroll-effect" style="transition-delay: 0.3s">
        <div class="card h-100 border-0 p-4 testimonial-card">
          <div class="d-flex align-items-center mb-4">
            <img src="https://randomuser.me/api/portraits/women/68.jpg"
                 class="rounded-circle me-3"
                 width="60"
                 height="60"
                 alt="Priya M.">
            <div>
              <h5 class="mb-1 fw-semibold">Priya M.</h5>
              <div class="text-warning small">
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-muted mb-4">"As a food blogger, I appreciate how easy it is to save and organize recipes from different sources. The community features help me connect with my audience."</p>
          <div class="mt-auto">
            <span class="badge bg-light text-dark small">
              <i class="fas fa-blog me-1"></i> Food Blogger
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Trust Badges -->
<div class="py-4 trust-badges">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center align-items-center">
      <img src="https://via.placeholder.com/150x60?text=Food+Magazine" alt="Featured in Food Magazine" class="img-fluid m-3">
      <img src="https://via.placeholder.com/150x60?text=Top+App+2023" alt="Top App 2023" class="img-fluid m-3">
      <img src="https://via.placeholder.com/150x60?text=Chef+Approved" alt="Chef Approved" class="img-fluid m-3">
      <img src="https://via.placeholder.com/150x60?text=Healthy+Choice" alt="Healthy Choice" class="img-fluid m-3">
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Modern Slider Functionality
  document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    const prevBtn = document.querySelector('.slider-arrow.prev');
    const nextBtn = document.querySelector('.slider-arrow.next');
    let currentSlide = 0;
    let slideInterval;

    // Initialize slider
    function showSlide(n) {
      // Reset all slides
      slides.forEach(slide => {
        slide.classList.remove('active');
        slide.style.opacity = 0;
      });
      dots.forEach(dot => dot.classList.remove('active'));
      
      // Handle wrap-around
      if (n >= slides.length) currentSlide = 0;
      if (n < 0) currentSlide = slides.length - 1;
      
      // Show new slide
      slides[currentSlide].classList.add('active');
      dots[currentSlide].classList.add('active');
      
      // Force reflow to enable transition
      void slides[currentSlide].offsetWidth;
      slides[currentSlide].style.opacity = 1;
    }

    // Next slide
    function nextSlide() {
      currentSlide++;
      showSlide(currentSlide);
    }

    // Previous slide
    function prevSlide() {
      currentSlide--;
      showSlide(currentSlide);
    }

    // Start autoplay
    function startSlider() {
      slideInterval = setInterval(nextSlide, 5000);
    }

    // Stop autoplay
    function stopSlider() {
      clearInterval(slideInterval);
    }

    // Event listeners
    nextBtn.addEventListener('click', () => {
      nextSlide();
      stopSlider();
      startSlider();
    });

    prevBtn.addEventListener('click', () => {
      prevSlide();
      stopSlider();
      startSlider();
    });

    dots.forEach(dot => {
      dot.addEventListener('click', function() {
        currentSlide = parseInt(this.getAttribute('data-slide'));
        showSlide(currentSlide);
        stopSlider();
        startSlider();
      });
    });

    // Pause on hover
    const slider = document.querySelector('.hero-slider');
    slider.addEventListener('mouseenter', stopSlider);
    slider.addEventListener('mouseleave', startSlider);

    // Initialize
    showSlide(currentSlide);
    startSlider();

    // Scroll animation
    const scrollElements = document.querySelectorAll('.scroll-effect');
    
    const elementInView = (el) => {
      const elementTop = el.getBoundingClientRect().top;
      return (
        elementTop <= (window.innerHeight || document.documentElement.clientHeight) - 100
      );
    };
    
    const displayScrollElement = (element) => {
      element.classList.add('visible');
    };
    
    const handleScrollAnimation = () => {
      scrollElements.forEach((el) => {
        if (elementInView(el)) {
          displayScrollElement(el);
        }
      });
    };
    
    window.addEventListener('scroll', () => {
      handleScrollAnimation();
    });
    
    handleScrollAnimation();

    // Dynamic background effect
    document.addEventListener('mousemove', function(e) {
      const x = e.clientX / window.innerWidth;
      const y = e.clientY / window.innerHeight;
      
      document.body.style.background = `
        radial-gradient(circle at ${x * 100}% ${y * 100}%, rgba(224, 0, 0, 0.08) 0%, transparent 20%),
        radial-gradient(circle at ${100 - (x * 100)}% ${100 - (y * 100)}%, rgba(255, 154, 60, 0.08) 0%, transparent 20%),
        linear-gradient(135deg, #f5f7fa 0%, #f8f9fa 100%)
      `;
    });
  });
</script>
</body>
</html>