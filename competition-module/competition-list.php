<?php
session_start();
require_once '../includes/db.php';

// Fetch competitions with entry counts
$sql = "SELECT c.*, COUNT(ce.entry_id) as entry_count 
        FROM competitions c
        LEFT JOIN competition_entries ce ON c.competition_id = ce.competition_id
        GROUP BY c.competition_id
        ORDER BY c.start_date DESC";
$result = $conn->query($sql);

// Handle flash messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Competition List | NomNomPlan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #E63946;
            --success-color: #2A9D8F;
            --warning-color: #F4A261;
            --secondary-color: #457B9D;
            --dark-color: #1D3557;
            --light-color: #F1FAEE;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        
        .page-header {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: rgba(241, 250, 238, 0.5);
        }
        
        .badge-status {
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-view-entries {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }
        
        .btn-view-entries:hover {
            background-color: #3a6a8a;
            color: white;
        }
        
        .btn-view-details {
            background-color: var(--success-color);
            color: white;
            border: none;
        }
        
        .btn-view-details:hover {
            background-color: #238175;
            color: white;
        }
        
        .empty-state {
            background-color: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 0;
            }
            
            .page-header {
                border-radius: 0;
                margin-left: -1rem;
                margin-right: -1rem;
            }
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<main class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0"><i class="bi bi-trophy me-2"></i> Competition Management</h1>
                <p class="text-muted mb-0">Manage all cooking competitions</p>
            </div>
            <a href="add-competition.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add New
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Competition List -->
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Dates</th>
                        <th>Entries</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $today = date('Y-m-d');
                        $status = ($today < $row['start_date']) ? 'Upcoming' : 
                                 (($today > $row['end_date']) ? 'Ended' : 'Active');
                    ?>
                        <tr>
                            <td class="fw-semibold">#<?= $row['competition_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['title']) ?></strong>
                                <div class="text-muted small mt-1"><?= htmlspecialchars($row['prize']) ?></div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span><?= date('M j, Y', strtotime($row['start_date'])) ?></span>
                                    <span class="text-muted small">to <?= date('M j, Y', strtotime($row['end_date'])) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $row['entry_count'] ?> entries</span>
                            </td>
                            <td>
                                <span class="badge-status bg-<?= 
                                    $status === 'Active' ? 'success' : 
                                    ($status === 'Ended' ? 'secondary' : 'warning')
                                ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="view-entries.php?id=<?= $row['competition_id'] ?>" 
                                       class="btn btn-sm btn-view-entries btn-action">
                                        <i class="bi bi-people"></i> View Entries
                                    </a>
                                    <a href="view-competition-details.php?id=<?= $row['competition_id'] ?>" 
                                       class="btn btn-sm btn-view-details btn-action">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <a href="delete-competition.php?id=<?= $row['competition_id'] ?>" 
                                       class="btn btn-sm btn-danger btn-action"
                                       onclick="return confirm('Are you sure you want to delete this competition? All entries will be permanently removed.');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-trophy text-muted" style="font-size: 3rem;"></i>
            <h4 class="mt-3">No competitions found</h4>
            <p class="text-muted">Get started by creating your first competition</p>
            <a href="add-competition.php" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-1"></i> Create Competition
            </a>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
