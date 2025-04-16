<?php
include '../includes/db.php';
include '../includes/auth.php';

// Fetch competition entries
$sql = "SELECT * FROM competition_entries ORDER BY submitted_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Competition Entries | NomNomPlan</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Fonts & Custom Styles (if any) -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style>
      body {
        font-family: 'Segoe UI', sans-serif;
      }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include '../navbar.php'; ?>

<main class="container flex-grow-1 mt-5 mb-4">
    <h2 class="mb-4 text-center">Competition Entries</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
              <thead class="table-light">
                  <tr>
                      <th>#</th>
                      <th>Participant Name</th>
                      <th>Email</th>
                      <th>Recipe Name</th>
                      <th>Submission Date</th>
                  </tr>
              </thead>
              <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                          <td><?= $row['id']; ?></td>
                          <td><?= htmlspecialchars($row['name']); ?></td>
                          <td><?= htmlspecialchars($row['email']); ?></td>
                          <td><?= htmlspecialchars($row['recipe_name']); ?></td>
                          <td><?= date("d M Y, H:i", strtotime($row['submitted_at'])); ?></td>
                      </tr>
                  <?php endwhile; ?>
              </tbody>
          </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No entries found yet.</div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
