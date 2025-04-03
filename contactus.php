<?php
session_start();
require_once 'includes/db.php';

$success = '';

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
    .contact-form {
      max-width: 600px;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
  <h1 class="text-center mb-4">📬 Contact Us</h1>

  <?php if ($success): ?>
    <div class="alert alert-info text-center"> <?= $success ?> </div>
  <?php endif; ?>

  <form method="POST" action="contactus.php" class="bg-white p-4 shadow rounded mx-auto contact-form">
    <div class="mb-3">
      <label class="form-label">👤 Name *</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">📧 Email *</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">📝 Subject</label>
      <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">✉️ Message *</label>
      <textarea name="message" class="form-control" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
    </div>
    <div class="text-end">
      <button type="submit" class="btn btn-danger">Send Message</button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
