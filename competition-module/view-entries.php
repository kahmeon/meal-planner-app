<?php
include '../includes/db.php';
include '../includes/auth.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'all';

// Base query
$sql = "SELECT 
    ce.entry_id AS id,
    u.name AS name,
    u.email AS email,
    r.title AS recipe_name,
    ce.status,
    r.id AS recipe_id,
    ce.submitted_at
FROM competition_entries ce
JOIN users u ON ce.user_id = u.id
JOIN recipes r ON ce.recipe_id = r.id";

// Add search and filter conditions
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(u.name LIKE ? OR r.title LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= 'sss';
}

if ($status_filter !== 'all') {
    $where[] = "ce.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY ce.submitted_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Competition Entries | NomNomPlan</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #E63946;
            --primary-light: rgba(230, 57, 70, 0.1);
            --success-color: #2A9D8F;
            --success-light: rgba(42, 157, 143, 0.1);
            --danger-color: #E76F51;
            --danger-light: rgba(231, 111, 81, 0.1);
            --warning-color: #F4A261;
            --warning-light: rgba(244, 162, 97, 0.1);
            --info-color: #457B9D;
            --info-light: rgba(69, 123, 157, 0.1);
            --dark-color: #1D3557;
            --light-color: #F1FAEE;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-800: #343a40;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --transition: all 0.2s ease-in-out;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: var(--gray-800);
            line-height: 1.6;
        }
        
        .page-header {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary-color);
        }
        
        .page-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-title i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            border-radius: 8px;
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(230, 57, 70, 0.2);
        }
        
        .btn-primary:hover {
            background-color: #d62a1a;
            border-color: #d62a1a;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(230, 57, 70, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-light);
        }
        
        .search-container {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
        }
        
        .search-input {
            border-radius: 8px;
            padding: 0.625rem 1rem;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            font-size: 0.95rem;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(230, 57, 70, 0.15);
        }
        
        .search-btn {
            border-radius: 8px;
            padding: 0.625rem 1.5rem;
            background-color: var(--primary-color);
            border: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .search-btn:hover {
            background-color: #d62a1a;
            transform: translateY(-1px);
        }
        
        .clear-btn {
            border-radius: 8px;
            padding: 0.625rem 1.5rem;
            transition: var(--transition);
        }
        
        .filter-dropdown .dropdown-toggle {
            border-radius: 8px;
            padding: 0.625rem 1.25rem;
            background-color: white;
            color: var(--gray-800);
            border: 1px solid var(--gray-200);
            font-weight: 500;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition);
        }
        
        .filter-dropdown .dropdown-toggle:hover {
            border-color: var(--primary-color);
        }
        
        .filter-dropdown .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
        
        .dropdown-menu {
            border-radius: var(--border-radius);
            border: 1px solid var(--gray-200);
            box-shadow: var(--box-shadow);
            padding: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            font-weight: 500;
            color: var(--gray-800);
            border-radius: 6px;
            transition: var(--transition);
            margin: 0.125rem 0;
        }
        
        .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .dropdown-divider {
            border-color: var(--gray-200);
            margin: 0.5rem 0;
        }
        
        .table-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }
        
        .table {
            margin-bottom: 0;
            font-size: 0.925rem;
        }
        
        .table thead th {
            background-color: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            padding: 1rem 1.5rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--gray-200);
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-top: 1px solid var(--gray-200);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .table tbody tr:hover {
            background-color: var(--gray-100);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.5rem 0.875rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }
        
        .badge-approved {
            background-color: var(--success-light);
            color: var(--success-color);
            border: 1px solid rgba(42, 157, 143, 0.2);
        }
        
        .badge-pending {
            background-color: var(--warning-light);
            color: var(--warning-color);
            border: 1px solid rgba(244, 162, 97, 0.2);
        }
        
        .badge-rejected {
            background-color: var(--danger-light);
            color: var(--danger-color);
            border: 1px solid rgba(231, 111, 81, 0.2);
        }
        
        .badge-submitted {
            background-color: var(--info-light);
            color: var(--info-color);
            border: 1px solid rgba(69, 123, 157, 0.2);
        }
        
        .action-btn {
            border-radius: 6px;
            padding: 0.5rem 0.875rem;
            font-size: 0.825rem;
            font-weight: 500;
            margin-right: 0.5rem;
            transition: var(--transition);
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .view-btn {
            background-color: white;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .view-btn:hover {
            background-color: var(--primary-light);
        }
        
        .approve-btn {
            background-color: white;
            color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .approve-btn:hover {
            background-color: var(--success-light);
        }
        
        .reject-btn {
            background-color: white;
            color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .reject-btn:hover {
            background-color: var(--danger-light);
        }
        
        .edit-btn {
            background-color: white;
            color: var(--info-color);
            border-color: var(--info-color);
        }
        
        .edit-btn:hover {
            background-color: var(--info-light);
        }
        
        .delete-btn {
            background-color: white;
            color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .delete-btn:hover {
            background-color: var(--danger-light);
        }
        
        .no-results {
            padding: 3rem 2rem;
            text-align: center;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px dashed var(--gray-300);
        }
        
        .no-results-icon {
            font-size: 3.5rem;
            color: var(--gray-500);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }
        
        .no-results-title {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
        }
        
        .submission-date {
            font-size: 0.85rem;
            color: var(--gray-600);
            white-space: nowrap;
        }
        
        .recipe-name {
            font-weight: 500;
            color: var(--dark-color);
            transition: var(--transition);
        }
        
        .recipe-name:hover {
            color: var(--primary-color);
        }
        
        .participant-name {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .participant-email {
            font-size: 0.85rem;
            color: var(--gray-600);
            word-break: break-all;
        }
        
        .badge-competition {
            background-color: rgba(29, 53, 87, 0.1);
            color: var(--dark-color);
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 4px;
        }
        
        .action-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 1.25rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem;
            }
            
            .action-btns {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .action-btn {
                margin-right: 0;
                width: 100%;
                text-align: center;
                justify-content: center;
            }
        }
        
        /* Animation for status badges */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .status-badge:hover {
            animation: pulse 1s infinite;
        }
        
        /* Floating action button for mobile */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
            z-index: 1000;
            transition: var(--transition);
        }
        
        .fab:hover {
            transform: translateY(-3px) scale(1.1);
            color: white;
        }
        
        @media (min-width: 768px) {
            .fab {
                display: none;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include '../navbar.php'; ?>

<main class="container flex-grow-1 py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <div class="mb-3 mb-md-0">
                <h1 class="page-title">
                    <i class="bi bi-trophy"></i> Competition Entries
                </h1>
                <p class="text-muted mb-0">Manage and review all competition submissions</p>
            </div>
            <div class="d-flex gap-2">
                <a href="competition-list.php" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i> View Competitions
                </a>
                <a href="add-competition.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Competition
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-container">
        <div class="row g-3">
            <div class="col-md-8">
                <form method="GET" class="d-flex align-items-center">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control search-input border-start-0" 
                               placeholder="Search participants, recipes or emails..." 
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn search-btn text-white">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                    <?php if (!empty($search) || $status_filter !== 'all'): ?>
                        <a href="?" class="btn btn-outline-secondary clear-btn ms-2">
                            <i class="bi bi-x-lg me-1"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-4">
                <div class="filter-dropdown">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" 
                                id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-funnel me-1"></i> 
                            <span class="filter-text">Status: <?= ucfirst($status_filter) ?></span>
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="filterDropdown">
                            <li><a class="dropdown-item d-flex align-items-center" 
                                  href="?status=all<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
                                <i class="bi bi-list-ul me-2 text-muted"></i> All Entries
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item d-flex align-items-center" 
                                  href="?status=approved<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
                                <i class="bi bi-check-circle me-2 text-success"></i> Approved
                            </a></li>
                            <li><a class="dropdown-item d-flex align-items-center" 
                                  href="?status=pending<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
                                <i class="bi bi-hourglass me-2 text-warning"></i> Pending
                            </a></li>
                            <li><a class="dropdown-item d-flex align-items-center" 
                                  href="?status=rejected<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
                                <i class="bi bi-x-circle me-2 text-danger"></i> Rejected
                            </a></li>
                            <li><a class="dropdown-item d-flex align-items-center" 
                                  href="?status=submitted<?= !empty($search) ? '&search='.urlencode($search) : '' ?>">
                                <i class="bi bi-send me-2 text-info"></i> Submitted
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Participant</th>
                            <th>Recipe</th>
                            <th width="140">Status</th>
                            <th width="160">Submitted</th>
                            <th width="220">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-semibold text-muted">#<?= htmlspecialchars($row['id']) ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="participant-name"><?= htmlspecialchars($row['name']) ?></span>
                                        <span class="participant-email"><?= htmlspecialchars($row['email']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="../recipe-modules/view-recipe.php?id=<?= $row['recipe_id'] ?>" class="recipe-name text-decoration-none">
                                        <?= htmlspecialchars($row['recipe_name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge badge-<?= strtolower($row['status']) ?>">
                                        <i class="bi <?= 
                                            match(strtolower($row['status'])) {
                                                'approved' => 'bi-check-circle',
                                                'rejected' => 'bi-x-circle',
                                                'pending' => 'bi-hourglass',
                                                'submitted' => 'bi-send',
                                                default => 'bi-question-circle'
                                            }
                                        ?>"></i>
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td class="submission-date">
                                    <?= date("M j, Y", strtotime($row['submitted_at'])) ?>
                                    <br>
                                    <small class="text-muted"><?= date("g:i a", strtotime($row['submitted_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="../recipe-modules/view-recipe.php?id=<?= $row['recipe_id'] ?>" 
                                           class="btn action-btn view-btn"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="View Recipe">
                                            <i class="bi bi-eye"></i>
                                            <span class="d-none d-md-inline">View</span>
                                        </a>
                                        <?php if ($row['status'] == 'submitted' || $row['status'] == 'pending'): ?>
                                            <a href="approve_reject.php?id=<?= $row['id'] ?>&action=approve" 
                                               class="btn action-btn approve-btn"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Approve Entry">
                                                <i class="bi bi-check-lg"></i>
                                                <span class="d-none d-md-inline">Approve</span>
                                            </a>
                                            <a href="approve_reject.php?id=<?= $row['id'] ?>&action=reject" 
                                               class="btn action-btn reject-btn"
                                               data-bs-toggle="tooltip" data-bs-placement="top" title="Reject Entry">
                                                <i class="bi bi-x-lg"></i>
                                                <span class="d-none d-md-inline">Reject</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="bi bi-search no-results-icon"></i>
            <h4 class="no-results-title">No entries found</h4>
            <p class="text-muted mb-4">Try adjusting your search or filter criteria</p>
            <a href="?" class="btn btn-primary px-4">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
            </a>
        </div>
    <?php endif; ?>
</main>

<!-- Floating Action Button (Mobile Only) -->
<a href="add-competition.php" class="fab d-md-none">
    <i class="bi bi-plus fs-5"></i>
</a>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Enable Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Update filter text when selecting from dropdown
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function() {
                const text = this.textContent.trim();
                document.querySelector('.filter-text').textContent = 'Status: ' + text;
            });
        });
    });
</script>
</body>
</html>