<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us | NomNomPlan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    :root {
      --primary: #e00000;
      --primary-light: #ff6b6b;
      --dark: #333333;
      --light: #f8f8f8;
      --cream: #fff9f5;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      line-height: 1.8;
      overflow-x: hidden;
    }
    
    h1, h2, h3, h4 {
      font-family: 'Playfair Display', serif;
      font-weight: 600;
    }
    
    .hero-section {
      min-height: 80vh;
      background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                  url('https://images.pexels.com/photos/5949884/pexels-photo-5949884.jpeg');
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      color: white;
    }
    
    .section-title {
      position: relative;
      margin-bottom: 3rem;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -15px;
      width: 60px;
      height: 3px;
      background: var(--primary);
    }
    
    .text-center .section-title:after {
      left: 50%;
      transform: translateX(-50%);
    }
    
    /* Story Section */
    .story-section {
      padding: 6rem 0;
      background-color: var(--cream);
    }
    
    .story-image {
      border-radius: 10px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    /* Mission Section */
    .mission-card {
      border: none;
      border-radius: 10px;
      padding: 2.5rem;
      height: 100%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      background: white;
    }
    
    .mission-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    
    .mission-icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 1.5rem;
    }
    
    /* Enhanced Timeline */
    .timeline-section {
      padding: 6rem 0;
      background: white;
      position: relative;
    }
    
    .timeline {
      position: relative;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .timeline::after {
      content: '';
      position: absolute;
      width: 4px;
      background: var(--primary);
      top: 0;
      bottom: 0;
      left: 50%;
      margin-left: -2px;
      border-radius: 2px;
    }
    
    .timeline-item {
      padding: 10px 40px;
      position: relative;
      width: 50%;
      box-sizing: border-box;
    }
    
    .timeline-item::after {
      content: '';
      position: absolute;
      width: 25px;
      height: 25px;
      background: white;
      border: 4px solid var(--primary);
      border-radius: 50%;
      top: 15px;
      z-index: 1;
    }
    
    .left {
      left: 0;
      text-align: right;
    }
    
    .right {
      left: 50%;
      text-align: left;
    }
    
    .left::after {
      right: -12px;
    }
    
    .right::after {
      left: -12px;
    }
    
    .timeline-content {
      padding: 20px;
      background: var(--cream);
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      position: relative;
    }
    
    .timeline-year {
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 0.5rem;
      font-size: 1.2rem;
    }
    
    /* Earth Section */
    .earth-section {
      padding: 6rem 0;
      background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
      text-align: center;
    }
    
    .earth-graphic {
      max-width: 500px;
      margin: 0 auto 3rem;
    }
    
    .earth-img {
      width: 100%;
      max-width: 400px;
      height: auto;
      border-radius: 50%;
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .hero-section {
        min-height: 60vh;
      }
      
      .timeline::after {
        left: 31px;
      }
      
      .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
      }
      
      .timeline-item::after {
        left: 18px;
      }
      
      .left, .right {
        left: 0;
        text-align: left;
      }
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center">
        <h1 class="display-3 fw-bold mb-4">Our Culinary Journey</h1>
        <p class="lead fs-4">Redefining meal planning for modern households</p>
      </div>
    </div>
  </div>
</section>

<!-- Story Section -->
<section class="story-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-5 mb-lg-0">
        <img src="https://images.pexels.com/photos/3186654/pexels-photo-3186654.jpeg" class="img-fluid story-image" alt="Our Story">
      </div>
      <div class="col-lg-6 ps-lg-5">
        <h2 class="section-title">Our Beginnings</h2>
        <p class="lead">In 2020, a team of food enthusiasts came together with a shared frustration - meal planning was too complicated.</p>
        <p>What began as a simple spreadsheet to organize our weekly meals evolved into NomNomPlan, a platform now used by thousands of households worldwide.</p>
        <p>We believe good food should be accessible to everyone, regardless of cooking skills or busy schedules. Our mission is to make meal planning effortless and enjoyable.</p>
      </div>
    </div>
  </div>
</section>

<!-- Mission Section -->
<section class="py-5 bg-white">
  <div class="container py-5">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="section-title">Our Mission</h2>
        <p class="lead">Transforming how families approach meal planning</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-md-4">
        <div class="mission-card text-center">
          <div class="mission-icon">
            <i class="fas fa-utensils"></i>
          </div>
          <h3 class="h4 mb-3">Simplify Cooking</h3>
          <p>Make meal planning intuitive and stress-free for home cooks of all levels</p>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="mission-card text-center">
          <div class="mission-icon">
            <i class="fas fa-heart"></i>
          </div>
          <h3 class="h4 mb-3">Promote Health</h3>
          <p>Help families make nutritious choices without compromising on taste</p>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="mission-card text-center">
          <div class="mission-icon">
            <i class="fas fa-globe"></i>
          </div>
          <h3 class="h4 mb-3">Global Flavors</h3>
          <p>Bring diverse cuisines and cooking traditions to your kitchen</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Enhanced Timeline Section -->
<section class="timeline-section">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="section-title">Our Journey</h2>
        <p class="lead">Milestones in our culinary story</p>
      </div>
    </div>
    
    <div class="timeline">
      <div class="timeline-item left">
        <div class="timeline-content">
          <div class="timeline-year">2020</div>
          <h3>Founding Days</h3>
          <p>Launched with just 50 recipes and basic meal planning tools</p>
        </div>
      </div>
      
      <div class="timeline-item right">
        <div class="timeline-content">
          <div class="timeline-year">2021</div>
          <h3>First Mobile App</h3>
          <p>Released our iOS and Android apps with smart grocery list features</p>
        </div>
      </div>
      
      <div class="timeline-item left">
        <div class="timeline-content">
          <div class="timeline-year">2022</div>
          <h3>Community Growth</h3>
          <p>Reached 100,000 active users and introduced social sharing</p>
        </div>
      </div>
      
      <div class="timeline-item right">
        <div class="timeline-content">
          <div class="timeline-year">2023</div>
          <h3>International Expansion</h3>
          <p>Launched localized versions for 5 countries with regional recipes</p>
        </div>
      </div>
      
      <div class="timeline-item left">
        <div class="timeline-content">
          <div class="timeline-year">2024</div>
          <h3>AI Integration</h3>
          <p>Introduced AI-powered meal recommendations based on dietary preferences</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Earth Section -->
<section class="earth-section">
  <div class="container">
    <div class="earth-graphic">
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/The_Earth_seen_from_Apollo_17.jpg/800px-The_Earth_seen_from_Apollo_17.jpg" 
           alt="Our Global Reach" 
           class="earth-img">
    </div>
    <h2 class="mb-3">Global Reach</h2>
    <p class="lead text-muted max-w-2xl mx-auto">
      Serving culinary enthusiasts across continents with our international community
    </p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>