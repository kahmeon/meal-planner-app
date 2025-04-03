<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'user';

include '../navbar.php';

// 1️⃣ Get competitions the user has joined
if ($user_role === 'admin') {
    $joinedQuery = $conn->query("SELECT * FROM competitions ORDER BY created_at DESC");
    $joinedCompetitions = $joinedQuery->fetch_all(MYSQLI_ASSOC);
    $availableCompetitions = []; // Admin doesn't need this section
} else {
    $joinedStmt = $conn->prepare("SELECT c.* FROM competitions c
                                  JOIN competition_entries e ON c.competition_id = e.competition_id
                                  WHERE e.user_id = ?
                                  GROUP BY c.competition_id
                                  ORDER BY c.created_at DESC");
    $joinedStmt->bind_param("i", $user_id);
    $joinedStmt->execute();
    $joinedResult = $joinedStmt->get_result();
    $joinedCompetitions = $joinedResult->fetch_all(MYSQLI_ASSOC);

    // 2️⃣ Get competitions the user has NOT joined and are ongoing
    $availableStmt = $conn->prepare("SELECT * FROM competitions 
                                     WHERE end_date >= CURDATE()
                                     AND competition_id NOT IN (
                                        SELECT competition_id FROM competition_entries WHERE user_id = ?
                                     )
                                     ORDER BY start_date ASC");
    $availableStmt->bind_param("i", $user_id);
    $availableStmt->execute();
    $availableResult = $availableStmt->get_result();
    $availableCompetitions = $availableResult->fetch_all(MYSQLI_ASSOC);
}
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

      <!-- 1️⃣ Joined Competitions Table -->
      <?php if (count($joinedCompetitions) > 0): ?>
        <h5 class="mt-4 mb-2 fw-bold">✅ Competitions You’ve Joined</h5>
        <div class="table-responsive">
          <table class="table table-bordered table-striped bg-white align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <?php if ($user_role === 'admin'): ?>
                  <th>Actions</th>
                <?php else: ?>
                  <th>Your Submission</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($joinedCompetitions as $index => $comp): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><strong><?= htmlspecialchars($comp['title']) ?></strong><br>
                  <small class="text-muted">Created: <?= date('Y-m-d', strtotime($comp['created_at'])) ?></small>
                </td>
                <td><?= $comp['start_date'] ?></td>
                <td><?= $comp['end_date'] ?></td>
                <td>
                  <?php
                  $now = date('Y-m-d');
                  echo ($comp['end_date'] < $now) 
                    ? '<span class="badge bg-danger">Ended</span>' 
                    : '<span class="badge bg-success">Ongoing</span>';
                  ?>
                </td>
                <?php if ($user_role === 'admin'): ?>
                  <td>
                    <a href="view-entries.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-secondary">Entries</a>
                    <a href="edit-competition.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="delete-competition.php?id=<?= $comp['competition_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
                  </td>
                <?php else: ?>
                  <td>
                    <?php
                    $entryStmt = $conn->prepare("SELECT r.title, e.status FROM competition_entries e 
                                                 JOIN recipes r ON e.recipe_id = r.id 
                                                 WHERE e.user_id = ? AND e.competition_id = ?");
                    $entryStmt->bind_param("ii", $user_id, $comp['competition_id']);
                    $entryStmt->execute();
                    $entryResult = $entryStmt->get_result();
                    if ($entryResult->num_rows > 0) {
                        while ($entry = $entryResult->fetch_assoc()) {
                            echo '<div><strong>' . htmlspecialchars($entry['title']) . '</strong><br>
                                  <span class="badge bg-warning text-dark">' . ucfirst($entry['status']) . '</span></div>';
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
        <p class="text-muted text-center">You haven’t joined any competitions yet.</p>
      <?php endif; ?>

      <!-- 2️⃣ Available Competitions to Join -->
      <?php if ($user_role !== 'admin' && count($availableCompetitions) > 0): ?>
        <h5 class="mt-5 mb-3 fw-bold">🔥 Available Competitions</h5>
        <div class="row">
          <?php foreach ($availableCompetitions as $comp): ?>
            <div class="col-md-6 mb-4">
              <div class="card shadow-sm h-100">
                <img src="<?= htmlspecialchars($comp['image_url']) ?>" class="card-img-top" alt="Competition Image" style="max-height: 180px; object-fit: cover;">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($comp['title']) ?></h5>
                  <p class="card-text small text-muted">From <?= $comp['start_date'] ?> to <?= $comp['end_date'] ?></p>
                  <p class="card-text"><?= htmlspecialchars($comp['description']) ?></p>
                  <a href="submit-competition-entry.php?id=<?= $comp['competition_id'] ?>" class="btn btn-outline-danger w-100">Join Competition</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- 🧠 Sidebar -->
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
