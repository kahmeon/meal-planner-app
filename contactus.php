<?php
session_start();
require_once 'includes/db.php';

$success = '';
$user_name = $_SESSION['user_name'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'guest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            $success = "✅ Thank you for contacting us! We'll get back to you soon.";
        } else {
            $success = "❌ Failed to send your message. Please try again later.";
        }
        $stmt->close();
    } else {
        $success = "⚠️ Name, email, and message are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    .contact-hero {
      background: url('assets/images/background3.webp') no-repeat center center;
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      min-height: 100vh;
      padding: 100px 0;
      position: relative;
      color: white;
    }

    .contact-hero::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.35);
      backdrop-filter: blur(3px);
      z-index: 0;
    }

    .contact-hero .container {
      position: relative;
      z-index: 1;
    }

    .contact-form-box, .intro-box {
      background: rgba(255, 255, 255, 0.93);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(5px);
      transition: all 0.3s ease;
    }

    .contact-form-box:hover, .intro-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .contact-form-box input,
    .contact-form-box textarea {
      background-color: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(0, 0, 0, 0.1);
      color: #212529;
      font-size: 16px;
      border-radius: 10px;
      padding: 10px 15px;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .contact-form-box label {
      color: #333;
      font-weight: 500;
    }

    .contact-form-box .btn {
      border-radius: 10px;
      padding: 10px 20px;
      font-weight: 500;
    }

    h1.display-5 {
      color: #fff;
      text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }

    @media (max-width: 576px) {
      .contact-hero {
        padding: 40px 0;
      }
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="contact-hero">
  <div class="container">
    <h1 class="text-center mb-5 display-5 fw-bold">📬 Contact Us</h1>
    <div class="row g-5 justify-content-center">
      <!-- Left: Info -->
      <div class="col-lg-5">
        <div class="intro-box text-dark">
          <h4 class="mb-3">👋 Let's Get In Touch</h4>
          <p>We're here to help — whether it's feedback, ideas, support, or recipe tips! Expect a reply within 1–2 business days.</p>

          <hr class="my-3">

          <h6>📍 Address</h6>
          <p>123 Flavor Street, Food City, Malaysia</p>

          <h6>📞 Phone</h6>
          <p>+60 123-456-789 (Mon–Fri, 9AM–6PM)</p>

          <h6>✉️ Email</h6>
          <p>support@nomnomplan.com</p>

          <h6>🕐 Operating Hours</h6>
          <p>
            Mon – Fri: 9:00 AM – 6:00 PM<br>
            Sat: 10:00 AM – 2:00 PM<br>
            Sun: Closed
          </p>

          <?php if ($user_role === 'admin'): ?>
            <hr>
            <div class="alert alert-warning">
              <strong>Admin Access:</strong> <a href="export-feedback.php" class="btn btn-sm btn-outline-primary">View All Messages</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Form -->
      <div class="col-lg-6">
        <div class="contact-form-box">
          <?php if ($success): ?>
            <div class="alert alert-info text-center"><?= $success ?></div>
          <?php endif; ?>
          <form method="POST" action="contactus.php">
            <div class="mb-3">
              <label class="form-label">👤 Name *</label>
              <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $user_name) ?>" aria-label="Your Name">
            </div>
            <div class="mb-3">
              <label class="form-label">📧 Email *</label>
              <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" aria-label="Your Email">
            </div>
            <div class="mb-3">
              <label class="form-label">📝 Subject</label>
              <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" aria-label="Subject">
            </div>
            <div class="mb-3">
              <label class="form-label">✉️ Message *</label>
              <textarea name="message" class="form-control" rows="5" required aria-label="Your Message"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-danger shadow-sm px-4 py-2">Send Message</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
