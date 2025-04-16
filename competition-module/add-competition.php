<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $prize = trim($_POST['prize']);

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/competitions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['image']['name']));
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        } else {
            $error = "Image upload failed.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO competitions (title, description, start_date, end_date, prize, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $description, $start_date, $end_date, $prize, $imagePath);
        if ($stmt->execute()) {
            $success = "✅ Competition created successfully!";
        } else {
            $error = "❌ Failed to create competition.";
        }
        $stmt->close();
    }
}

include '../navbar.php';
?>

<div class="container py-5">
  <h2 class="mb-4">➕ Add New Competition</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
    <div class="mb-3">
      <label class="form-label">Competition Title *</label>
      <input type="text" name="title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description *</label>
      <textarea name="description" class="form-control" rows="4" required></textarea>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Start Date *</label>
        <input type="date" name="start_date" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">End Date *</label>
        <input type="date" name="end_date" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Prize Details</label>
      <input type="text" name="prize" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Banner Image</label>
      <input type="file" name="image" class="form-control" accept="image/*">
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-danger">Create Competition</button>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>