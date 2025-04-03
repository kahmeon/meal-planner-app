<?php
session_start();
require_once 'includes/db.php';

$success = '';
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

// Handle form submission (only if not admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isAdmin) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, message, status) VALUES (?, ?, ?, 'new')");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $success = "✅ Thank you! Your feedback has been received.";
            $_POST = [];
        } else {
            $success = "❌ Failed to submit feedback. Please try again.";
        }

        $stmt->close();
    } else {
        $success = "⚠️ Name and message are required.";
    }
}

// If admin, fetch all feedback entries
$feedbackList = [];
if ($isAdmin) {
    $res = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
    $feedbackList = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Feedback | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    .feedback-hero {
      background: url('assets/images/background3.webp') no-repeat center center;
      background-size: cover;
      background-attachment: fixed;
      background-position: center;
      min-height: 100vh;
      padding: 100px 0;
      position: relative;
    }

    .feedback-hero::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(3px);
      z-index: 0;
    }

    .feedback-hero .container {
      position: relative;
      z-index: 1;
    }

    .glass-box {
      background: rgba(255, 255, 255, 0.93);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(5px);
    }

    .glass-box:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
      transition: 0.3s;
    }

    input, textarea {
      background-color: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(0, 0, 0, 0.1);
      color: #212529;
      font-size: 16px;
      border-radius: 10px;
      padding: 10px 15px;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    label {
      font-weight: 500;
      color: #333;
    }

    h1, h3 {
      color: #fff;
      text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }

    .admin-table-box {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      margin-top: 30px;
    }

    .table td {
      white-space: pre-wrap;
    }

    @media (max-width: 576px) {
      .feedback-hero {
        padding: 50px 0;
      }
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<section class="feedback-hero">
  <div class="container">
    <h1 class="text-center mb-5 display-5 fw-bold">💬 Feedback</h1>

    <?php if (!$isAdmin): ?>
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="glass-box">
            <p class="text-center text-muted mb-3">We'd love to hear your thoughts! Fill out the form below.</p>

            <?php if ($success): ?>
              <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="feedback.php">
              <div class="mb-3">
                <label class="form-label">👤 Your Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">📧 Email (optional)</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">📝 Your Feedback *</label>
                <textarea name="message" class="form-control" rows="4" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-danger px-4">Submit Feedback</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php else: ?>
      <p class="text-center text-light mb-4">As an admin, you can view all submitted feedback below.</p>

      <div class="admin-table-box">
        <div class="mb-3 text-end">
          <a href="export-feedback.php" class="btn btn-success">📄 Export CSV</a>
        </div>

        <h3 class="text-center mb-4 text-dark">📋 Submitted Feedback</h3>

        <?php if (!empty($feedbackList)): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Message</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($feedbackList as $index => $entry): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($entry['name']) ?></td>
                    <td><?= htmlspecialchars($entry['email']) ?></td>
                    <td><?= nl2br(htmlspecialchars($entry['message'])) ?></td>
                    <td><span class="badge bg-secondary text-capitalize"><?= htmlspecialchars($entry['status'] ?? 'new') ?></span></td>
                    <td><?= date('Y-m-d H:i', strtotime($entry['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted text-center">No feedback submitted yet.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
