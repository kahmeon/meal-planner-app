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
            $_POST = []; // clear form data after success
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
    .feedback-form {
      max-width: 600px;
    }
    .table td {
      white-space: pre-wrap;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
  <h1 class="text-center mb-3">💬 Feedback</h1>

  <?php if (!$isAdmin): ?>
    <p class="text-center text-muted mb-4">We'd love to hear your thoughts! Fill out the form below.</p>

    <?php if ($success): ?>
      <div class="alert alert-info alert-dismissible fade show text-center" role="alert">
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="feedback.php" class="p-4 border rounded-4 shadow bg-white mx-auto mb-5 feedback-form">
      <div class="mb-3">
        <label class="form-label">👤 Your Name *</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">📧 Email (optional)</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">📑 Your Feedback *</label>
        <textarea name="message" class="form-control" rows="4" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
      </div>
      <div class="text-end">
        <button type="submit" class="btn btn-danger">Submit Feedback</button>
      </div>
    </form>
  <?php else: ?>
    <p class="text-center text-muted mb-4">As an admin, you can view all submitted feedback below.</p>
  <?php endif; ?>

  <?php if ($isAdmin): ?>
    <div class="mb-4 text-end">
      <a href="export-feedback.php" class="btn btn-success">📄 Export CSV</a>
    </div>

    <div class="mt-3">
      <h3 class="text-center mb-4">📋 Submitted Feedback</h3>
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

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
