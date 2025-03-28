<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recipe Management | NomNomPlan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .logo {
      font-family: 'Pacifico', cursive;
      font-size: 2rem;
      color: #e00000;
    }
    .page-header {
      background-color: #fff;
      padding: 2rem 1rem;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
  </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<!-- Page Header -->
<div class="page-header mb-4">
  <h1 class="logo">NomNomPlan</h1>
  <h2 class="mt-2">Recipe Management</h2>
  <p class="text-muted">View, organize, and manage your recipes</p>
</div>

<div class="container mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Your Recipes</h4>
    <a href="#" class="btn btn-danger">+ Add New Recipe</a>
  </div>

  <!-- Sample Static Table -->
  <table class="table table-bordered bg-white">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Recipe Name</th>
        <th>Category</th>
        <th>Created Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Spaghetti Bolognese</td>
        <td>Pasta</td>
        <td>2025-03-28</td>
        <td>
          <a href="#" class="btn btn-sm btn-outline-primary">View</a>
          <a href="#" class="btn btn-sm btn-outline-success">Edit</a>
          <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
        </td>
      </tr>
      <tr>
        <td>2</td>
        <td>Chicken Curry</td>
        <td>Main Dish</td>
        <td>2025-03-26</td>
        <td>
          <a href="#" class="btn btn-sm btn-outline-primary">View</a>
          <a href="#" class="btn btn-sm btn-outline-success">Edit</a>
          <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
        </td>
      </tr>
    </tbody>
  </table>
</div>

</body>
</html>
