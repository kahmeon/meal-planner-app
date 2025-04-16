<?php
include('../includes/db.php');
include('../includes/auth.php');

    // Check if user is logged in
    if (!isset($_SESSION["user_id"])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Store current URL to redirect back after login
        header("Location: ../login.php");
        exit();
    }

    include('../navbar.php');

    // Initialize message
    $message = "";
    $formError = false; // Add this flag to track validation errors
    $redirectAfterSubmit = false; // Flag to trigger JavaScript redirect
    $redirectUrl = ""; // URL for JavaScript redirect

    // Get all recipes from the database for the dropdown
    $recipesQuery = "SELECT id, title FROM recipes WHERE status = 'approved'";
    $recipesResult = $conn->query($recipesQuery);
    $recipes = [];
    if ($recipesResult->num_rows > 0) {
        while ($row = $recipesResult->fetch_assoc()) {
            $recipes[$row['id']] = $row['title'];
        }
    }

    // Ensure we always have a filter value, defaulting to 'week'
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'week';

    // Handle image upload for custom meal
    $uploadedImagePath = "";
    if(isset($_FILES['customMealImage']) && $_FILES['customMealImage']['error'] == 0) {
        $target_dir = "../uploads/meal-plan/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["customMealImage"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . uniqid('meal_') . "." . $imageFileType;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["customMealImage"]["tmp_name"]);
        if($check !== false) {
            // Check file size (5MB max)
            if ($_FILES["customMealImage"]["size"] <= 5000000) {
                // Allow certain file formats
                if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                    if (move_uploaded_file($_FILES["customMealImage"]["tmp_name"], $target_file)) {
                        $uploadedImagePath = $target_file;
                    } else {
                        $message = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                $message = "Sorry, your file is too large. Maximum size is 5MB.";
            }
        } else {
            $message = "File is not an image.";
        }
    }

    // Handle image upload for preset
    $presetUploadedImagePath = "";
    if(isset($_FILES['presetMealImage']) && $_FILES['presetMealImage']['error'] == 0) {
        $target_dir = "../uploads/meal-plan/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["presetMealImage"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . uniqid('preset_') . "." . $imageFileType;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["presetMealImage"]["tmp_name"]);
        if($check !== false) {
            // Check file size (5MB max)
            if ($_FILES["presetMealImage"]["size"] <= 5000000) {
                // Allow certain file formats
                if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                    if (move_uploaded_file($_FILES["presetMealImage"]["tmp_name"], $target_file)) {
                        $presetUploadedImagePath = $target_file;
                    } else {
                        $message = "Sorry, there was an error uploading your preset file.";
                    }
                } else {
                    $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for preset images.";
                }
            } else {
                $message = "Sorry, your preset file is too large. Maximum size is 5MB.";
            }
        } else {
            $message = "Preset file is not an image.";
        }
    }

    // Handle form submission for meal planning
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['savePreset']) && !isset($_POST['updatePreset']) && !isset($_POST['applyPreset'])) {
        $mealDate = $conn->real_escape_string($_POST['mealDate']);
        $mealTime = $conn->real_escape_string($_POST['mealTime']);
        $recipeId = isset($_POST['recipe']) ? intval($_POST['recipe']) : 0;
        $customMeal = $conn->real_escape_string($_POST['customMeal']);
        $userId = $_SESSION["user_id"];
        
        $formError = false;
        
        // Enhanced validation
        if (empty($mealDate)) {
            $message = "Please select a meal date.";
            $formError = true;
        } elseif (empty($mealTime)) {
            $message = "Please select a meal time.";
            $formError = true;
        } elseif ($recipeId == 0) {
            $message = "Please select a recipe or custom meal option.";
            $formError = true;
        } elseif (empty($customMeal)) {
            // Custom meal description is required regardless of recipe selection
            $message = "Please provide a description for your meal. You can use '-' if you don't want to add details.";
            $formError = true;
        }
        
        if (!$formError) {
            // Get recipe image path
            $recipeImage = "";
            
            if ($recipeId > 0) {
                // Get image from database for existing recipe
                $imageQuery = "SELECT image_url FROM recipe_images WHERE recipe_id = $recipeId LIMIT 1";
                $imageResult = $conn->query($imageQuery);
                if ($imageResult && $imageResult->num_rows > 0) {
                    $imageRow = $imageResult->fetch_assoc();
                    $recipeImage = $imageRow['image_url'];
                }
            } else if ($recipeId == -1 || $customMeal != "") {
                // This is a custom meal
                if (!empty($uploadedImagePath)) {
                    // User uploaded an image
                    $recipeImage = $uploadedImagePath;
                } else {
                    // Use default image
                    $recipeImage = "../uploads/meal-plan/default_meal.jpg";
                }
            }

            // Check if updating or inserting a new meal plan
            if (isset($_POST['updateId'])) {
                $updateId = intval($_POST['updateId']);
                $sql = "UPDATE meal_plans SET 
                        meal_date='$mealDate', 
                        meal_time='$mealTime', 
                        recipe_id=" . ($recipeId > 0 ? $recipeId : "NULL") . ", 
                        custom_meal='$customMeal', 
                        recipe_image='$recipeImage', 
                        updated_at=NOW() 
                        WHERE id=$updateId AND user_id=$userId";
                        
                if ($conn->query($sql)) {
                    $message = "Meal plan updated successfully!";
                    // Replace redirect with JavaScript
                    $redirectAfterSubmit = true;
                    $redirectUrl = "meal-plan.php?filter=$filter&success=update";
                } else {
                    $message = "Error updating meal plan: " . $conn->error;
                }
            } else {
                // Insert new meal plan
                $sql = "INSERT INTO meal_plans (meal_date, meal_time, recipe_id, custom_meal, recipe_image, user_id, created_at, updated_at) 
                        VALUES ('$mealDate', '$mealTime', " . ($recipeId > 0 ? $recipeId : "NULL") . ", '$customMeal', '$recipeImage', $userId, NOW(), NOW())";
                        
                if ($conn->query($sql)) {
                    $message = "Meal plan saved successfully!";
                    // Replace redirect with JavaScript
                    $redirectAfterSubmit = true;
                    $redirectUrl = "meal-plan.php?filter=$filter&success=insert";
                } else {
                    $message = "Error: " . $conn->error;
                }
            }
        }
    }

    // Handle edit meal plan
    $editing = false;
    if (isset($_GET['edit'])) {
        $mealPlanId = intval($_GET['edit']);
        $userId = $_SESSION["user_id"];
        $result = $conn->query("SELECT * FROM meal_plans WHERE id = $mealPlanId AND user_id = $userId");
        if ($result && $result->num_rows > 0) {
            $mealPlan = $result->fetch_assoc();
            $editing = true;
        }
    }

    // Handle delete meal plan
    if (isset($_GET['delete'])) {
        $deleteId = intval($_GET['delete']);
        $userId = $_SESSION["user_id"];
        $conn->query("DELETE FROM meal_plans WHERE id = $deleteId AND user_id = $userId");
        $message = "Meal plan deleted successfully!";
        // Replace redirect with JavaScript
        $redirectAfterSubmit = true;
        $redirectUrl = "meal-plan.php?filter=$filter&success=delete";
    }

    // Filter logic for week/month
    $userId = $_SESSION["user_id"];

    if ($filter == 'week') {
        // Display meal plans for this week only
        $plansQuery = "SELECT mp.*, r.title as recipe_title, r.id as recipe_id
                    FROM meal_plans mp
                    LEFT JOIN recipes r ON mp.recipe_id = r.id
                    WHERE mp.user_id = $userId 
                    AND YEARWEEK(mp.meal_date, 1) = YEARWEEK(CURDATE(), 1)
                    ORDER BY mp.meal_date, FIELD(mp.meal_time, 'breakfast', 'lunch', 'dinner')";
    } elseif ($filter == 'month') {
        // Display meal plans for this month only
        $plansQuery = "SELECT mp.*, r.title as recipe_title, r.id as recipe_id
                    FROM meal_plans mp
                    LEFT JOIN recipes r ON mp.recipe_id = r.id
                    WHERE mp.user_id = $userId 
                    AND MONTH(mp.meal_date) = MONTH(CURDATE()) 
                    AND YEAR(mp.meal_date) = YEAR(CURDATE())
                    ORDER BY mp.meal_date, FIELD(mp.meal_time, 'breakfast', 'lunch', 'dinner')";
    } elseif ($filter == 'all') {
        // Show all meal plans regardless of date
        $plansQuery = "SELECT mp.*, r.title as recipe_title, r.id as recipe_id
                    FROM meal_plans mp
                    LEFT JOIN recipes r ON mp.recipe_id = r.id
                    WHERE mp.user_id = $userId
                    ORDER BY mp.meal_date DESC, FIELD(mp.meal_time, 'breakfast', 'lunch', 'dinner')";
    } else {
        // Default fallback
        $plansQuery = "SELECT mp.*, r.title as recipe_title, r.id as recipe_id
                    FROM meal_plans mp
                    LEFT JOIN recipes r ON mp.recipe_id = r.id
                    WHERE mp.user_id = $userId
                    ORDER BY mp.meal_date DESC, FIELD(mp.meal_time, 'breakfast', 'lunch', 'dinner')
                    LIMIT 10";
    }

    $plans = $conn->query($plansQuery);

    // Handle 'Save as Preset'
    if (isset($_POST['savePreset'])) {
        $presetMealTime = $conn->real_escape_string($_POST['presetMealTime']);
        $presetRecipeId = isset($_POST['presetRecipe']) ? intval($_POST['presetRecipe']) : 0;
        $presetCustomMeal = $conn->real_escape_string($_POST['presetCustomMeal']);
        $userId = $_SESSION["user_id"];
        
        $formError = false;
        
        // Enhanced validation for preset form
        if (empty($presetMealTime)) {
            $message = "Please select a meal time for the preset.";
            $formError = true;
        } elseif ($presetRecipeId == 0) {
            $message = "Please select a recipe or custom meal option for the preset.";
            $formError = true;
        } elseif (empty($presetCustomMeal)) {
            // Custom meal description is required regardless of recipe selection
            $message = "Please provide a description for your preset meal. You can use '-' if you don't want to add details.";
            $formError = true;
        }
        
        if (!$formError) {
            // Get recipe image for the preset
            $presetImage = "";
            
            if ($presetRecipeId > 0) {
                // Get image from database for existing recipe
                $imageQuery = "SELECT image_url FROM recipe_images WHERE recipe_id = $presetRecipeId LIMIT 1";
                $imageResult = $conn->query($imageQuery);
                if ($imageResult && $imageResult->num_rows > 0) {
                    $imageRow = $imageResult->fetch_assoc();
                    $presetImage = $imageRow['image_url'];
                }
            } else if ($presetRecipeId == -1) {
                // This is a custom meal
                if (!empty($presetUploadedImagePath)) {
                    // User uploaded an image
                    $presetImage = $presetUploadedImagePath;
                } else {
                    // Use default image
                    $presetImage = "../uploads/meal-plan/default_meal.jpg";
                }
            }

            // Insert the preset meal plan into the database
            $insertPresetQuery = "INSERT INTO preset_meal_plans (meal_time, recipe_id, custom_meal, recipe_image, user_id, created_at) 
                                VALUES ('$presetMealTime', " . ($presetRecipeId > 0 ? $presetRecipeId : "NULL") . ", '$presetCustomMeal', '$presetImage', $userId, NOW())";
            if ($conn->query($insertPresetQuery)) {
                $message = "Meal plan saved as preset successfully!";
                // Replace redirect with JavaScript
                $redirectAfterSubmit = true;
                $redirectUrl = "meal-plan.php?filter=$filter&success=preset_saved";
            } else {
                $message = "Error saving preset meal plan: " . $conn->error;
            }
        }
    }

    // Handle updating preset
    if (isset($_POST['updatePreset'])) {
        $presetId = intval($_POST['presetId']);
        $presetMealTime = $conn->real_escape_string($_POST['presetMealTime']);
        $presetRecipeId = isset($_POST['presetRecipe']) ? intval($_POST['presetRecipe']) : 0;
        $presetCustomMeal = $conn->real_escape_string($_POST['presetCustomMeal']);
        $userId = $_SESSION["user_id"];
        
        $formError = false;
        
        // Enhanced validation for update preset form
        if (empty($presetMealTime)) {
            $message = "Please select a meal time for the preset.";
            $formError = true;
        } elseif ($presetRecipeId == 0) {
            $message = "Please select a recipe or custom meal option for the preset.";
            $formError = true;
        } elseif (empty($presetCustomMeal)) {
            // Custom meal description is required regardless of recipe selection
            $message = "Please provide a description for your preset meal. You can use '-' if you don't want to add details.";
            $formError = true;
        }
        
        if (!$formError) {
            // Get recipe image for the preset
            $presetImage = "";
            
            if ($presetRecipeId > 0) {
                // Get image from database for existing recipe
                $imageQuery = "SELECT image_url FROM recipe_images WHERE recipe_id = $presetRecipeId LIMIT 1";
                $imageResult = $conn->query($imageQuery);
                if ($imageResult && $imageResult->num_rows > 0) {
                    $imageRow = $imageResult->fetch_assoc();
                    $presetImage = $imageRow['image_url'];
                }
            } else if ($presetRecipeId == -1) {
                // This is a custom meal
                if (!empty($presetUploadedImagePath)) {
                    // User uploaded an image
                    $presetImage = $presetUploadedImagePath;
                } else {
                    // Check if there's an existing image in the database
                    $existingImageQuery = "SELECT recipe_image FROM preset_meal_plans WHERE id = $presetId AND user_id = $userId";
                    $existingImageResult = $conn->query($existingImageQuery);
                    if ($existingImageResult && $existingImageResult->num_rows > 0) {
                        $existingImageRow = $existingImageResult->fetch_assoc();
                        $presetImage = $existingImageRow['recipe_image'];
                    } else {
                        // Use default image
                        $presetImage = "../uploads/meal-plan/default_meal.jpg";
                    }
                }
            }

            // Update the preset meal plan in the database
            $updatePresetQuery = "UPDATE preset_meal_plans SET 
                                meal_time='$presetMealTime', 
                                recipe_id=" . ($presetRecipeId > 0 ? $presetRecipeId : "NULL") . ", 
                                custom_meal='$presetCustomMeal'";
                                
            // Only update image if a new one was uploaded or recipe changed
            if (!empty($presetImage)) {
                $updatePresetQuery .= ", recipe_image='$presetImage'";
            }
            
            $updatePresetQuery .= " WHERE id=$presetId AND user_id=$userId";
            
            if ($conn->query($updatePresetQuery)) {
                $message = "Preset meal plan updated successfully!";
                // Replace redirect with JavaScript
                $redirectAfterSubmit = true;
                $redirectUrl = "meal-plan.php?filter=$filter&success=preset_updated";
            } else {
                $message = "Error updating preset meal plan: " . $conn->error;
            }
        }
    }

    // Get presets for the modal
    $presetsQuery = "SELECT pmp.*, r.title as recipe_title 
                    FROM preset_meal_plans pmp
                    LEFT JOIN recipes r ON pmp.recipe_id = r.id
                    WHERE pmp.user_id = {$_SESSION["user_id"]}
                    ORDER BY pmp.created_at DESC";
    $presets = $conn->query($presetsQuery);

    // Handle edit preset
    $editingPreset = false;
    $presetData = null;
    if (isset($_GET['editPreset'])) {
        $presetId = intval($_GET['editPreset']);
        $userId = $_SESSION["user_id"];
        $result = $conn->query("SELECT * FROM preset_meal_plans WHERE id = $presetId AND user_id = $userId");
        if ($result && $result->num_rows > 0) {
            $presetData = $result->fetch_assoc();
            $editingPreset = true;
        }
    }

    // Handle applying preset to meal plan
    if (isset($_POST['applyPreset'])) {
        $presetId = intval($_POST['presetId']);
        $userId = $_SESSION["user_id"];
        
        // Get preset details
        $presetResult = $conn->query("SELECT * FROM preset_meal_plans WHERE id = $presetId AND user_id = $userId");
        if ($presetResult && $presetResult->num_rows > 0) {
            $preset = $presetResult->fetch_assoc();
            $presetMealTime = $preset['meal_time'];
            $presetRecipeId = $preset['recipe_id'];
            $presetCustomMeal = $preset['custom_meal'];
            $presetRecipeImage = $preset['recipe_image'];
            
            // Apply preset to a new meal plan
            $mealDate = date('Y-m-d'); // Default to today
            
            // Insert new meal plan with preset values
            $sql = "INSERT INTO meal_plans (meal_date, meal_time, recipe_id, custom_meal, recipe_image, user_id, created_at, updated_at) 
                    VALUES ('$mealDate', '$presetMealTime', " . ($presetRecipeId ? $presetRecipeId : "NULL") . ", '$presetCustomMeal', '$presetRecipeImage', $userId, NOW(), NOW())";
                    
            if ($conn->query($sql)) {
                $message = "Preset meal plan applied successfully!";
                // Redirect after showing message
                $redirectAfterSubmit = true;
                $redirectUrl = "meal-plan.php?filter=$filter&success=preset_applied";
            } else {
                $message = "Error applying preset meal plan: " . $conn->error;
            }
        } else {
            $message = "Error: Preset not found.";
        }
    }

    // Handle delete preset
    if (isset($_GET['deletePreset'])) {
        $deletePresetId = intval($_GET['deletePreset']);
        $userId = $_SESSION["user_id"];
        $conn->query("DELETE FROM preset_meal_plans WHERE id = $deletePresetId AND user_id = $userId");
        $message = "Preset deleted successfully!";
        // Replace redirect with JavaScript
        $redirectAfterSubmit = true;
        $redirectUrl = "meal-plan.php?filter=$filter&success=preset_deleted";
    }

    // Check for success messages from redirects
    if (isset($_GET['success'])) {
        switch ($_GET['success']) {
            case 'update':
                $message = "Meal plan updated successfully!";
                break;
            case 'insert':
                $message = "Meal plan saved successfully!";
                break;
            case 'delete':
                $message = "Meal plan deleted successfully!";
                break;
            case 'preset_saved':
                $message = "Meal plan saved as preset successfully!";
                break;
            case 'preset_updated':
                $message = "Preset meal plan updated successfully!";
                break;
            case 'preset_deleted':
                $message = "Preset deleted successfully!";
                break;
            case 'preset_applied':
                $message = "Preset meal plan applied successfully!";
                break;
        }
    }

    function limit_words($text, $limit = 30, $by_char = true) {
        if (empty($text) || $text === '-') return $text;
        
        // Strip any HTML tags for consistent counting
        $clean_text = strip_tags($text);
        
        if ($by_char) {
            // Limit by characters
            if (mb_strlen($clean_text) > $limit) {
                return htmlspecialchars(mb_substr($clean_text, 0, $limit)) . '...';
            }
        } else {
            // Limit by words
            $words = explode(' ', $clean_text);
            if (count($words) > $limit) {
                return htmlspecialchars(implode(' ', array_slice($words, 0, $limit))) . '...';
            }
        }
        
        return htmlspecialchars($text);
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Meal Planning</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
        <link rel="stylesheet" type="text/css" href="meal-plan.css">
    </head>

<body>
    <div class="container">
        <div class="page-header">
            <h1 style="color: #b30000;">Meal Planning System</h1>
            <p>Plan your meals with ease and eat healthier every day</p>
        </div>

        <?php if (!empty($message)) : ?>
            <div class="message">
                <i class="fas fa-check-circle"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- JavaScript redirection handler -->
        <?php if ($redirectAfterSubmit): ?>
        <script>
            window.location.href = "<?= $redirectUrl ?>";
        </script>
        <?php endif; ?>

        <!-- Apply Filter -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <label for="filter"><i class="fas fa-filter"></i> Filter by:</label>
                <select name="filter" id="filter">
                    <option value="all" <?= ($filter == 'all') ? 'selected' : '' ?>>All</option>
                    <option value="week" <?= ($filter == 'week') ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= ($filter == 'month') ? 'selected' : '' ?>>This Month</option> 
                </select>
                <button type="submit" class="btn btn-apply-filter">
                    <i class="fas fa-search"></i> Apply Filter
                </button>
            </form>

            <div class="button-group">
                <button type="button" class="btn btn-accent" onclick="toggleFormVisibility()">
                    <i class="fas fa-plus"></i> New Meal Plan
                </button>

                <button type="button" class="btn btn-outline" onclick="openPresetModal()">
                    <i class="fas fa-eye"></i> View Presets
                </button>
            </div>
        </div>

        <!-- New/Edit Meal Plan -->
        <div class="card" id="mealPlanForm" <?= (!$editing && !$formError && (empty($_POST) || (isset($_GET['success']) && $_GET['success'] != 'error'))) ? 'style="display:none"' : '' ?>>
            <h2><i class="fas fa-utensils"></i> <?= $editing ? 'Edit Meal Plan' : 'Create New Meal Plan' ?></h2>
            <form method="POST" action="?filter=<?= $filter ?>" enctype="multipart/form-data">
                <?php if ($editing): ?>
                    <input type="hidden" name="updateId" value="<?= $mealPlan['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="mealDate"><i class="fas fa-calendar-alt"></i> Date:</label>
                    <input type="date" id="mealDate" name="mealDate" required value="<?= $editing ? $mealPlan['meal_date'] : date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label for="mealTime"><i class="fas fa-clock"></i> Meal Time:</label>
                    <select name="mealTime" id="mealTime" required>
                        <option value="breakfast" <?= ($editing && $mealPlan['meal_time'] == 'breakfast') ? 'selected' : '' ?>>Breakfast</option>
                        <option value="lunch" <?= ($editing && $mealPlan['meal_time'] == 'lunch') ? 'selected' : '' ?>>Lunch</option>
                        <option value="dinner" <?= ($editing && $mealPlan['meal_time'] == 'dinner') ? 'selected' : '' ?>>Dinner</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="recipe"><i class="fas fa-book-open"></i> Recipe:</label>
                    <select name="recipe" id="recipe" onchange="toggleCustomMealImage()">
                        <option value="0">Select a recipe</option>
                        <option value="-1" <?= ($editing && $mealPlan['recipe_id'] === null) ? 'selected' : '' ?>>Custom Meal</option>
                        <?php foreach($recipes as $id => $title): ?>
                            <option value="<?= $id ?>" <?= ($editing && $mealPlan['recipe_id'] == $id) ? 'selected' : '' ?>><?= $title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="customMeal"><i class="fas fa-pencil-alt"></i> Meal Description: <span class="required">*</span></label>
                    <textarea name="customMeal" id="customMeal" placeholder="Enter your meal description or '-' if selecting a recipe (nothing to add)..." rows="3" class="form-control"><?= $editing ? $mealPlan['custom_meal'] : '' ?></textarea>
                    <small class="form-text text-muted">Required field! If you do not have anything you want to fill in, kindly put “-”.</small>
                </div>

                <!-- Custom meal image upload section -->
                <div class="form-group image-upload-container" id="customMealImageContainer">
                    <label><i class="fas fa-image"></i> Custom Meal Image:</label>
                    <div class="image-preview" id="imagePreview">
                        <img src="<?= $editing && !empty($mealPlan['recipe_image']) ? $mealPlan['recipe_image'] : '../uploads/meal-plan/default_meal.jpg' ?>" alt="Default Meal" id="previewImg">
                    </div>
                    <label for="customMealImage" class="custom-file-upload">
                        <i class="fas fa-upload"></i> Upload Image
                    </label>
                    <input type="file" name="customMealImage" id="customMealImage" accept="image/*" onchange="previewImage(this)">
                    <p class="small text-muted">Upload your own image or use the default one</p>
                </div>
                    <div class="button-row" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" class="btn" style="flex: 1;">
                        <i class="fas <?= $editing ? 'fa-save' : 'fa-plus-circle' ?>"></i>
                        <?= $editing ? 'Update Meal Plan' : 'Save Meal Plan' ?>
                    </button>

                    <?php if (!$editing): ?>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="openSavePresetModal()">
                        <i class="fas fa-bookmark"></i> Save as Preset
                    </button>
                    <?php endif; ?>

                    <?php if ($editing): ?>
                        <a href="meal-plan.php?filter=<?= $filter ?>" class="btn btn-danger" style="width: auto">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- View Presets Modal -->
        <div id="presetModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close-modal" onclick="closePresetModal()">&times;</span>
                <div class="modal-header">
                    <h3><i class="fas fa-book-open"></i> Preset Meals</h3>
                </div>
                <div class="modal-body">
                    <?php if ($presets && $presets->num_rows > 0) : ?>
                        <div class="preset-cards-container">
                            <?php 
                            // Reset the pointer to the beginning
                            $presets->data_seek(0);
                            while($preset = $presets->fetch_assoc()): 
                            ?>
                                <div class="preset-card">
                                    <div class="preset-card-image">
                                        <img src="<?= !empty($preset['recipe_image']) ? $preset['recipe_image'] : '../uploads/meal-plan/default_meal.jpg' ?>" 
                                            alt="<?= !empty($preset['recipe_title']) ? $preset['recipe_title'] : 'Custom Meal' ?>"
                                            onerror="this.src='../uploads/meal-plan/default_meal.jpg'" />
                                    </div>
                                    <div class="preset-card-content">
                                        <h4>
                                            <span class="meal-badge <?= $preset['meal_time'] ?>"><?= ucfirst($preset['meal_time']) ?></span>
                                        </h4>
                                        <p><strong>Recipe:</strong> 
                                            <?php if (!empty($preset['recipe_title'])): ?>
                                                <?= $preset['recipe_title'] ?>
                                            <?php else: ?>
                                                <span class="no-meal">Custom Meal</span>
                                            <?php endif; ?>
                                        </p>
                                        <?php if (!empty($preset['custom_meal'])): ?>
                                        <p><strong>Description:</strong> 
                                            <?php if ($preset['custom_meal'] === '-' || mb_strlen(strip_tags($preset['custom_meal'])) <= 30): ?>
                                                <span class="description-preview"><?= htmlspecialchars($preset['custom_meal']) ?></span>
                                            <?php else: ?>
                                                <span class="description-preview"><?= limit_words($preset['custom_meal'], 30, true) ?></span>
                                                <a href="javascript:void(0);" class="read-more-link" data-full-text="<?= htmlspecialchars($preset['custom_meal'], ENT_QUOTES) ?>">Read More</a>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    </div>
                                    <div class="preset-card-actions">
                                        <?php if (!empty($preset['recipe_id'])): ?>
                                            <a href="../recipe-modules/view-recipe.php?id=<?= $preset['recipe_id'] ?>" class="btn btn-green" title="View Recipe">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-accent" style="background-color: #9370DB; border-color: #9370DB;" onclick="applyPreset(<?= $preset['id'] ?>)">
                                            <i class="fas fa-plus"></i> Apply
                                        </button>
                                        <a href="?editPreset=<?= $preset['id'] ?>&filter=<?= $filter ?>" class="btn btn-green" title="Edit Preset">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-danger" title="Delete" onclick="confirmDeletePreset(<?= $preset['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No presets saved yet.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closePresetModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Preset Modal -->
        <div id="editPresetModal" class="modal" <?= $editingPreset ? 'style="display:block"' : 'style="display:none"' ?>>
            <div class="modal-content">
            <span class="close-modal" onclick="window.location.href='meal-plan.php?filter=<?= $filter ?>'">&times;</span>
                <div class="modal-header">
                    <h3><i class="fas fa-edit"></i> Edit Preset</h3>
                </div>
                <div class="modal-body">
                <form method="POST" action="meal-plan.php?filter=<?= $filter ?>" enctype="multipart/form-data">
                        <input type="hidden" name="updatePreset" value="1">
                        <input type="hidden" name="presetId" value="<?= $editingPreset ? $presetData['id'] : '' ?>">
                        
                        <div class="form-group">
                            <label for="presetMealTime"><i class="fas fa-clock"></i> Meal Time:</label>
                            <select name="presetMealTime" id="editPresetMealTime" required>
                                <option value="breakfast" <?= ($editingPreset && $presetData['meal_time'] == 'breakfast') ? 'selected' : '' ?>>Breakfast</option>
                                <option value="lunch" <?= ($editingPreset && $presetData['meal_time'] == 'lunch') ? 'selected' : '' ?>>Lunch</option>
                                <option value="dinner" <?= ($editingPreset && $presetData['meal_time'] == 'dinner') ? 'selected' : '' ?>>Dinner</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="presetRecipe"><i class="fas fa-book-open"></i> Recipe:</label>
                            <select name="presetRecipe" id="editPresetRecipe" onchange="togglePresetCustomMealImage()">
                                <option value="0">Select a recipe</option>
                                <option value="-1" <?= ($editingPreset && $presetData['recipe_id'] === null) ? 'selected' : '' ?>>Custom Meal</option>
                                <?php foreach($recipes as $id => $title): ?>
                                    <option value="<?= $id ?>" <?= ($editingPreset && $presetData['recipe_id'] == $id) ? 'selected' : '' ?>><?= $title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="editPresetCustomMeal"><i class="fas fa-pencil-alt"></i> Meal Description: <span class="required">*</span></label>
                            <textarea name="presetCustomMeal" id="editPresetCustomMeal" placeholder="Enter your meal description or '-' if selecting a recipe (nothing to add)..." rows="3" class="form-control" required><?= $editingPreset ? $presetData['custom_meal'] : '' ?></textarea>
                            <small class="form-text text-muted">Required field! If you do not have anything you want to fill in, kindly put “-”.</small>
                        </div>
                        
                        <!-- Custom meal image upload section for preset -->
                        <div class="form-group image-upload-container" id="editPresetCustomMealImageContainer" <?= ($editingPreset && $presetData['recipe_id'] === null) ? 'style="display:block"' : '' ?>>
                            <label><i class="fas fa-image"></i> Custom Meal Image:</label>
                            <div class="image-preview" id="presetImagePreview">
                                <img src="<?= $editingPreset && !empty($presetData['recipe_image']) ? $presetData['recipe_image'] : '../uploads/meal-plan/default_meal.jpg' ?>" alt="Default Meal" id="presetPreviewImg">
                            </div>
                            <label for="presetMealImage" class="custom-file-upload">
                                <i class="fas fa-upload"></i> Upload Image
                            </label>
                            <input type="file" name="presetMealImage" id="presetMealImage" accept="image/*" onchange="previewPresetImage(this)">
                            <p class="small text-muted">Upload your own image or use the default one</p>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Update Preset
                            </button>
                            <button type="button" class="btn btn-danger" onclick="window.location.href='meal-plan.php?filter=<?= $filter ?>'">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Save as Preset Modal -->
        <div id="savePresetModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close-modal" onclick="closeSavePresetModal()">&times;</span>
                <div class="modal-header">
                    <h3><i class="fas fa-bookmark"></i> Save as Preset</h3>
                </div>
                <div class="modal-body">
                    <form method="POST" action="meal-plan.php?filter=<?= $filter ?>" enctype="multipart/form-data">
                        <input type="hidden" name="savePreset" value="1">
                        
                        <div class="form-group">
                            <label for="presetMealTime"><i class="fas fa-clock"></i> Meal Time:</label>
                            <select name="presetMealTime" id="presetMealTime" required>
                                <option value="breakfast">Breakfast</option>
                                <option value="lunch">Lunch</option>
                                <option value="dinner">Dinner</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="presetRecipe"><i class="fas fa-book-open"></i> Recipe:</label>
                            <select name="presetRecipe" id="presetRecipe" onchange="togglePresetCustomMealImage()">
                                <option value="0">Select a recipe</option>
                                <option value="-1">Custom Meal</option>
                                <?php foreach($recipes as $id => $title): ?>
                                    <option value="<?= $id ?>"><?= $title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="presetCustomMeal"><i class="fas fa-pencil-alt"></i> Meal Description: <span class="required">*</span></label>
                            <textarea name="presetCustomMeal" id="presetCustomMeal" placeholder="Enter your meal description or '-' if selecting a recipe (nothing to add)..." rows="3" class="form-control" required></textarea>
                            <small class="form-text text-muted">Required field! If you do not have anything you want to fill in, kindly put “-”.</small>
                        </div>
                        
                        <!-- Custom meal image upload section for preset -->
                        <div class="form-group image-upload-container hidden" id="presetCustomMealImageContainer">
                            <label><i class="fas fa-image"></i> Custom Meal Image:</label>
                            <div class="image-preview" id="presetImagePreview">
                                <img src="../uploads/meal-plan/default_meal.jpg" alt="Default Meal" id="presetPreviewImg">
                            </div>
                            <label for="presetMealImage" class="custom-file-upload">
                                <i class="fas fa-upload"></i> Upload Image
                            </label>
                            <input type="file" name="presetMealImage" id="presetMealImage" accept="image/*" onchange="previewPresetImage(this)">
                            <p class="small text-muted">Upload your own image or use the default one</p>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Save Preset
                            </button>
                            
                            <button type="button" class="btn btn-danger" onclick="closeSavePresetModal()" style="background-color: #dc3545; border-color: #dc3545; color: white;">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hidden form for apply preset -->
        <form id="applyPresetForm" method="POST" action="meal-plan.php?filter=<?= $filter ?>" style="display:none;">
            <input type="hidden" name="applyPreset" value="1">
            <input type="hidden" name="presetId" id="selectedPresetId" value="">
        </form>

        <!-- Meal Plans Section -->
        <div class="meal-plans-wrapper">
            <h2><i class="fas fa-calendar-alt"></i> Meal Plans for <?= ucfirst($filter) ?></h2>

            <?php if ($plans && $plans->num_rows > 0): ?>
                <div class="meal-plans-container">
                    <?php while ($mealPlan = $plans->fetch_assoc()): ?>
                        <div class="meal-card">
                            <div class="meal-card-image">
                                <!-- Display the recipe image with error handling -->
                                <img src="<?= !empty($mealPlan['recipe_image']) ? $mealPlan['recipe_image'] : '../uploads/meal-plan/default_meal.jpg' ?>" 
                                    alt="<?= !empty($mealPlan['recipe_title']) ? $mealPlan['recipe_title'] : 'Custom Meal' ?>"
                                    onerror="this.src='../uploads/meal-plan/default_meal.jpg'" />
                            </div>

                            <div class="meal-card-content">
                                <h3>
                                    <i class="fas fa-clock"></i> 
                                    <span class="meal-badge <?= $mealPlan['meal_time'] ?>"><?= ucfirst($mealPlan['meal_time']) ?></span> - 
                                    <?= date('F j, Y', strtotime($mealPlan['meal_date'])) ?>
                                </h3>

                                <p><i class="fas fa-book"></i> <strong>Recipe:</strong> 
                                    <?php if (!empty($mealPlan['recipe_title'])): ?>
                                        <?= $mealPlan['recipe_title'] ?>
                                    <?php else: ?>
                                        <span class="no-meal">Custom Meal</span>
                                    <?php endif; ?>
                                </p>
                                
                                <?php if (!empty($mealPlan['custom_meal'])): ?>
                                    <p><i class="fas fa-sticky-note"></i> <strong>Description:</strong> 
                                        <?php if ($mealPlan['custom_meal'] === '-' || mb_strlen(strip_tags($mealPlan['custom_meal'])) <= 30): ?>
                                            <span class="description-preview"><?= htmlspecialchars($mealPlan['custom_meal']) ?></span>
                                        <?php else: ?>
                                            <span class="description-preview"><?= limit_words($mealPlan['custom_meal'], 30, true) ?></span>
                                            <a href="javascript:void(0);" class="read-more-link" data-full-text="<?= htmlspecialchars($mealPlan['custom_meal'], ENT_QUOTES) ?>">Read More</a>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="meal-card-actions">
                                    <?php if (!empty($mealPlan['recipe_id'])): ?>
                                        <a href="../recipe-modules/view-recipe.php?id=<?= $mealPlan['recipe_id'] ?>" class="btn btn-green" title="View Recipe">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php endif; ?>
                                    <a href="?edit=<?= $mealPlan['id'] ?>&filter=<?= $filter ?>" class="btn btn-green" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn btn-danger" title="Delete" onclick="confirmDelete(<?= $mealPlan['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-meal">No meal plans available for this period. <a href="#" onclick="toggleFormVisibility(); return false;">Create one now</a>.</p>
            <?php endif; ?>
        </div>
    
        <!-- Full Description Modal -->
        <div id="descriptionModal" class="description-modal">
            <div class="description-modal-content">
                <span class="description-close">&times;</span>
                <h4>Full Description</h4>
                <div id="fullDescriptionText" style="white-space: pre-wrap;"></div>
            </div>
        </div>
    </div>

    <script>
        const redirectUrl = "<?= $redirectUrl ?>";
        const filter = "<?= $filter ?>";
        // Function to toggle form visibility
        function toggleFormVisibility() {
            const form = document.getElementById('mealPlanForm');
            
            // If we're in edit mode, redirect to base URL first to reset the form
            if (window.location.href.includes('edit=')) {
                window.location.href = 'meal-plan.php?filter=<?= $filter ?>';
                return;
            }
            
            // Show the form if it's hidden
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                // Scroll to the form
                form.scrollIntoView({ behavior: 'smooth' });
            } else {
                form.style.display = 'none';
            }
        }

        // Function to toggle custom meal image upload section
        function toggleCustomMealImage() {
            const recipeSelect = document.getElementById('recipe');
            const customMealImageContainer = document.getElementById('customMealImageContainer');
            
            if (recipeSelect.value === "-1") {
                customMealImageContainer.style.display = 'block';
            } else {
                customMealImageContainer.style.display = 'none';
            }
        }
        
        // Function to toggle custom meal image upload in preset modal
        function togglePresetCustomMealImage() {
            // For Save Modal
            const saveSelect = document.getElementById('presetRecipe');
            const saveImageContainer = document.getElementById('presetCustomMealImageContainer');
            if (saveSelect && saveImageContainer) {
                saveImageContainer.style.display = (saveSelect.value === "-1") ? "block" : "none";
            }

            // For Edit Modal
            const editSelect = document.getElementById('editPresetRecipe');
            const editImageContainer = document.getElementById('editPresetCustomMealImageContainer');
            if (editSelect && editImageContainer) {
                editImageContainer.style.display = (editSelect.value === "-1") ? "block" : "none";
            }
        }


        // Function to preview uploaded image
        function previewImage(input) {
            const preview = document.getElementById('previewImg');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Function to preview uploaded preset image
        function previewPresetImage(input) {
            const preview = document.getElementById('presetPreviewImg');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Modal functions
        function openPresetModal() {
            document.getElementById('presetModal').style.display = 'block';
        }

        function closePresetModal() {
            document.getElementById('presetModal').style.display = 'none';
        }
        
        function openSavePresetModal() {
            document.getElementById('savePresetModal').style.display = 'block';
            
            // Copy current form values to preset form
            const mealTime = document.getElementById('mealTime').value;
            const recipe = document.getElementById('recipe').value;
            const customMeal = document.getElementById('customMeal').value;
            
            document.getElementById('presetMealTime').value = mealTime;
            document.getElementById('presetRecipe').value = recipe;
            document.getElementById('presetCustomMeal').value = customMeal;
            
            // Force the display property directly
            const presetCustomMealImageContainer = document.getElementById('presetCustomMealImageContainer');
            
            if (recipe === "-1") {
                presetCustomMealImageContainer.style.display = 'block';
                presetCustomMealImageContainer.classList.remove('hidden');
            } else {
                presetCustomMealImageContainer.style.display = 'none';
                presetCustomMealImageContainer.classList.add('hidden');
            }
            
            // Also update the toggle function immediately
            togglePresetCustomMealImage();
        }
        
        function closeSavePresetModal() {
            document.getElementById('savePresetModal').style.display = 'none';
        }
        
        // Function to confirm and delete a meal plan
        function confirmDelete(mealPlanId) {
            if (confirm('Are you sure you want to delete this meal plan?')) {
                window.location.href = 'meal-plan.php?delete=' + mealPlanId + '&filter=<?= $filter ?>';
            }
        }
        
        // Function to confirm and delete a preset
        function confirmDeletePreset(presetId) {
            if (confirm('Are you sure you want to delete this preset?')) {
                window.location.href = 'meal-plan.php?deletePreset=' + presetId + '&filter=<?= $filter ?>';
            }
        }
        
        // Function to apply a preset
        function applyPreset(presetId) {
            document.getElementById('selectedPresetId').value = presetId;
            document.getElementById('applyPresetForm').submit();
        }
        
        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            const presetModal = document.getElementById('presetModal');
            const savePresetModal = document.getElementById('savePresetModal');
            const editPresetModal = document.getElementById('editPresetModal');
            
            if (event.target == presetModal) {
                presetModal.style.display = 'none';
            }
            if (event.target == savePresetModal) {
                savePresetModal.style.display = 'none';
            }
            if (event.target == editPresetModal) {
                window.location.href = 'meal-plan.php?filter=<?= $filter ?>';
            }
        }
        
        // Highlight newly added meal plan
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_GET['success']) && ($_GET['success'] == 'insert')): ?>
                const mealCards = document.querySelectorAll('.meal-card');
                if (mealCards.length > 0) {
                    mealCards[0].classList.add('meal-row-new');
                }
            <?php endif; ?>
            
            // Initialize custom meal image container visibility
            toggleCustomMealImage();
            
            // Also initialize preset custom meal image container if it exists
            const presetRecipe = document.getElementById('presetRecipe');
            if (presetRecipe) {
                const presetCustomMealImageContainer = document.getElementById('presetCustomMealImageContainer');
                if (presetRecipe.value === "-1") {
                    presetCustomMealImageContainer.style.display = 'block';
                    presetCustomMealImageContainer.classList.remove('hidden');
                } else {
                    presetCustomMealImageContainer.style.display = 'none';
                    presetCustomMealImageContainer.classList.add('hidden');
                }
            }
            
            // Initialize edit preset custom meal image container if it exists
            const editPresetRecipeSelect = document.getElementById('editPresetRecipe');
            if (editPresetRecipeSelect) {
                const editPresetCustomMealImageContainer = document.getElementById('presetCustomMealImageContainer');
                if (editPresetRecipeSelect.value === "-1") {
                    editPresetCustomMealImageContainer.style.display = 'block';
                } else {
                    editPresetCustomMealImageContainer.style.display = 'none';
                }
            }
        });

        // Read More functionality
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('read-more-link')) {
                const fullText = e.target.getAttribute('data-full-text');
                const descriptionElement = document.getElementById('fullDescriptionText');
                descriptionElement.innerText = fullText; // Use innerText to prevent HTML execution but preserve line breaks
                document.getElementById('descriptionModal').style.display = 'block';
                
                // Ensure proper scrolling for very long content
                descriptionElement.scrollTop = 0;
            }
        });

        // Close full description modal
        document.querySelector('.description-close').addEventListener('click', function() {
            document.getElementById('descriptionModal').style.display = 'none';
        });

        // Close the modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('descriptionModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        // Dynamically adjust textarea height
        function adjustTextareaHeight(selector) {
            const textarea = document.querySelector(selector);
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        }

        // Call the function for each textarea
        document.addEventListener('DOMContentLoaded', function() {
            adjustTextareaHeight('#customMeal');
            adjustTextareaHeight('#presetCustomMeal');
            adjustTextareaHeight('#editPresetCustomMeal');
            
            // Also adjust initial height for textareas with content
            const textareas = document.querySelectorAll('textarea');
            textareas.forEach(function(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            });
            
            // Enhance textarea when Custom Meal is selected
            function updateCustomMealTextarea() {
                const recipeSelect = document.getElementById('recipe');
                const customMealTextarea = document.getElementById('customMeal');
                
                if (recipeSelect && customMealTextarea) {
                    if (recipeSelect.value === "-1") {
                        customMealTextarea.rows = 5;
                        customMealTextarea.style.maxHeight = '300px';
                    } else {
                        customMealTextarea.rows = 3;
                        customMealTextarea.style.maxHeight = '100px';
                    }
                }
            }
            
            // Initialize textarea size
            updateCustomMealTextarea();
            
            // Add event listener to recipe selection
            const recipeSelect = document.getElementById('recipe');
            if (recipeSelect) {
                recipeSelect.addEventListener('change', updateCustomMealTextarea);
            }
            
            // Do the same for preset forms
            function updatePresetTextarea() {
                const presetRecipeSelect = document.getElementById('presetRecipe');
                const presetTextarea = document.getElementById('presetCustomMeal');
                
                if (presetRecipeSelect && presetTextarea) {
                    if (presetRecipeSelect.value === "-1") {
                        presetTextarea.rows = 5;
                        presetTextarea.style.maxHeight = '300px';
                    } else {
                        presetTextarea.rows = 3;
                        presetTextarea.style.maxHeight = '100px';
                    }
                }
            }
            
            const presetRecipeSelect = document.getElementById('presetRecipe');
            if (presetRecipeSelect) {
                updatePresetTextarea();
                presetRecipeSelect.addEventListener('change', updatePresetTextarea);
            }
            
            // For edit preset form
            function updateEditPresetTextarea() {
                const editPresetRecipeSelect = document.getElementById('editPresetRecipe');
                const editPresetTextarea = document.getElementById('editPresetCustomMeal');
                
                if (editPresetRecipeSelect && editPresetTextarea) {
                    if (editPresetRecipeSelect.value === "-1") {
                        editPresetTextarea.rows = 5;
                        editPresetTextarea.style.maxHeight = '300px';
                    } else {
                        editPresetTextarea.rows = 3;
                        editPresetTextarea.style.maxHeight = '100px';
                    }
                }
            }
            
            const editPresetRecipeSelect = document.getElementById('editPresetRecipe');
            if (editPresetRecipeSelect) {
                updateEditPresetTextarea();
                editPresetRecipeSelect.addEventListener('change', updateEditPresetTextarea);
            }
        });
    </script>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>