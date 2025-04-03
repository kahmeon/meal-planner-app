<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

if ($user_role === 'admin') {
    $query = $conn->query("SELECT * FROM competitions ORDER BY created_at DESC");
} else {
    $query = $conn->prepare("SELECT c.* FROM competitions c
                             JOIN competition_entries e ON c.competition_id = e.competition_id
                             WHERE e.user_id = ?
                             GROUP BY c.competition_id
                             ORDER BY c.created_at DESC");
    $query->bind_param("i", $user_id);
    $query->execute();
    $query = $query->get_result();
}
$competitions = $query->fetch_all(MYSQLI_ASSOC);

include '../navbar.php';
?>

<div class="container py-5">
  <div class="row g-5">
    <div class="col-lg-8">
      <h2 class="mb-4">
        <?= ($user_role === 'admin') ? '📋 Manage Competitions' : '🏆 My Competition Entries' ?>
      </h2>
      <p class="lead text-muted">Welcome to the cooking competition portal! Here you can view ongoing and past competitions, showcase your culinary creations, and compete for exciting prizes.</p>

      <?php if ($user_role === 'admin'): ?>
        <div class="mb-4 text-end">
          <a href="add-competition.php" class="btn btn-danger">+ Add New Competition</a>
        </div>
      <?php endif; ?>

      <?php if (count($competitions) > 0): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped bg-white align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <?php if ($user_role === 'admin'): ?>
                  <th>Actions</th>
                <?php else: ?>
                  <th>Your Submission</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($competitions as $index => $comp): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td>
                  <strong><?= htmlspecialchars($comp['title']) ?></strong><br>
                  <small class="text-muted">Created: <?= date('Y-m-d', strtotime($comp['created_at'])) ?></small>
                </td>
                <td><?= date('Y-m-d', strtotime($comp['start_date'])) ?></td>
                <td><?= date('Y-m-d', strtotime($comp['end_date'])) ?></td>
                <td>
                  <?php
                  $now = date('Y-m-d');
                  if ($comp['end_date'] < $now) {
                      echo '<span class="badge bg-danger">Ended</span>';
                  } else {
                      echo '<span class="badge bg-success">Ongoing</span>';
                  }
                  ?>
                </td>
                <?php if ($user_role === 'admin'): ?>
                  <td>
                    <a href="view-entries.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-secondary">View Entries</a>
                    <a href="edit-competition.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="delete-competition.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
                  </td>
                <?php else: ?>
                  <td>
                    <?php
                      $entryStmt = $conn->prepare("SELECT r.title, e.status FROM competition_entries e JOIN recipes r ON e.recipe_id = r.id WHERE e.user_id = ? AND e.competition_id = ?");
                      $entryStmt->bind_param("ii", $user_id, $comp['competition_id']);
                      $entryStmt->execute();
                      $entryResult = $entryStmt->get_result();
                      if ($entryResult->num_rows > 0) {
                          while ($entry = $entryResult->fetch_assoc()) {
                              echo '<div><strong>' . htmlspecialchars($entry['title']) . '</strong><br><span class="badge bg-warning text-dark">' . ucfirst($entry['status']) . '</span></div>';
                          }
                      } else {
                          echo '<span class="text-muted">Not submitted</span>';
                      }
                    ?>
                  </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted text-center">No competitions found.</p>
      <?php endif; ?>
    </div>

    <div class="col-lg-4">
      <?php if ($user_role !== 'admin'): ?>
        <img src="../assets/images/competition_banner.jpg" class="img-fluid rounded shadow mb-3" alt="Competition Banner" style="object-fit: cover; max-height: 240px;">
        <div class="bg-light p-4 rounded shadow-sm">
          <h5 class="fw-bold mb-3">🍳 Why Participate?</h5>
          <ul class="list-unstyled">
            <li>🏅 Get recognition for your unique recipes</li>
            <li>🎁 Win exciting monthly prizes and features</li>
            <li>👨‍🍳 Gain exposure in the cooking community</li>
          </ul>
          <p class="text-muted small">Competitions refresh every month. Don’t miss your chance to shine!</p>
        </div>
      <?php else: ?>
        <div class="bg-light p-4 rounded shadow-sm">
          <h5 class="fw-bold mb-3">📢 Admin Tips</h5>
          <ul class="list-unstyled">
            <li>➕ Use <strong>Add New</strong> to create competitions</li>
            <li>🛠 Manage entries and track submissions</li>
            <li>📊 Analyze participation trends monthly</li>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>