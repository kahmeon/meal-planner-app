<?php
session_start();
include '../includes/db.php';
include '../navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../home.php");
    exit();
}

$username = $_SESSION['user_name']; 
$role = $_SESSION['user_role'];     


// Get recipe stats
$user_id = $_SESSION['user_id'];
$statsQuery = ($role === 'admin') ?
    "SELECT 
        COUNT(*) AS total, 
        SUM(status = 'pending') AS pending, 
        SUM(status = 'approved') AS approved 
     FROM recipes" :
    "SELECT 
        COUNT(*) AS total, 
        SUM(status = 'pending') AS pending, 
        SUM(status = 'approved') AS approved 
     FROM recipes WHERE user_id = $user_id";

$result = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recipe Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>
<div class="container mt-4">

    <div class="text-center mb-4">
        <h1>🍽️ Recipe Management Dashboard</h1>
        <p class="lead">Welcome, <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo $role; ?>)</p>
    </div>

    <!-- Recipe Stats -->
    <div class="row text-center mb-4">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Total Recipes</h5>
                    <p class="fs-4"><?php echo $stats['total']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Pending Recipes</h5>
                    <p class="fs-4 text-warning"><?php echo $stats['pending']; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Approved Recipes</h5>
                    <p class="fs-4 text-success"><?php echo $stats['approved']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Links -->
    <div class="text-center">
        <a href="recipe-add.php" class="btn btn-primary m-2">➕ Add New Recipe</a>
        <a href="recipe-list.php" class="btn btn-secondary m-2">📃 View All Recipes</a>
        <a href="recipe-search.php" class="btn btn-info m-2">🔍 Search Recipes</a>
    </div>

    <?php if ($role === 'admin'): ?>
        <div class="alert alert-info mt-4 text-center">
            👑 As an admin, you can manage all users' recipes.
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
