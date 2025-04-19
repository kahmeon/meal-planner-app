<?php
session_start();
include('../includes/db.php');

// Get the community ID from the URL
$community_id = isset($_GET['community_id']) ? (int)$_GET['community_id'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch all ratings for this community initially
$stmt = $conn->prepare("SELECT r.rate_value, r.feedback_comment, r.created_at, u.name AS user_name, a.avatar_url
                        FROM rating r 
                        JOIN users u ON r.user_id = u.id 
                        JOIN user_community uc ON r.user_id = uc.user_id 
                        JOIN avatar a ON uc.user_avatar_id = a.avatar_id
                        WHERE r.community_id = ? 
                        ORDER BY r.created_at DESC LIMIT 5"); // Limiting the initial load to 5 ratings
if ($stmt) {
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $ratings = [];
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }

    $stmt->close();
} else {
    echo "Error fetching ratings: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Ratings for Community</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .rating-stars i {
            color: #FFD700;
        }

        .rating-card {
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
        }

        .rating-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .rating-header img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-right: 15px;
        }

        .rating-header h5 {
            margin-bottom: 0;
        }

        .rating-comment {
            font-size: 1rem;
            color: #555;
        }

        .rating-date {
            color: #888;
            font-size: 0.9rem;
        }

        .back-link {
            margin-top: 30px;
            text-align: center;
            display: block;
            font-size: 1.2rem;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .rating-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .view-all-btn {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">All Ratings for Community</h2>

    <div id="ratingsContainer">
        <?php if (count($ratings) > 0): ?>
            <?php foreach ($ratings as $rating): ?>
                <div class="rating-card">
                    <div class="rating-header">
                        <img src="../uploads/community/avatar/<?= $rating['avatar_url'] ?>" alt="User Avatar">
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($rating['user_name']) ?></h5>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= ($i <= $rating['rate_value']) ? '' : 'far' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <p class="rating-comment"><?= htmlspecialchars($rating['feedback_comment']) ?></p>
                    <div class="rating-date text-end">
                        <small>Posted on <?= date('M d, Y', strtotime($rating['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No ratings available for this community.
            </div>
        <?php endif; ?>
    </div>

    <!-- Button to load all ratings -->
    <div class="view-all-btn">
        <button id="viewAllRatingsBtn" class="btn btn-outline-primary btn-sm">View All Ratings</button>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('viewAllRatingsBtn').addEventListener('click', function() {
        // Fetch all ratings via AJAX when the button is clicked
        fetch('get-all-ratings.php?community_id=<?= $community_id ?>')
            .then(response => response.json())
            .then(data => {
                // Append all ratings to the page
                const ratingsContainer = document.getElementById('ratingsContainer');
                data.ratings.forEach(rating => {
                    const ratingElement = document.createElement('div');
                    ratingElement.classList.add('rating-card');
                    ratingElement.innerHTML = `
                        <div class="rating-header">
                            <img src="../uploads/community/avatar/${rating.avatar_url}" alt="User Avatar">
                            <div>
                                <h5>${rating.user_name}</h5>
                                <div class="rating-stars">${getStarRating(rating.rate_value)}</div>
                            </div>
                        </div>
                        <p>${rating.feedback_comment}</p>
                        <small>Posted on ${new Date(rating.created_at).toLocaleDateString()}</small>
                    `;
                    ratingsContainer.appendChild(ratingElement);
                });

                // Hide the button after clicking to avoid multiple fetches
                document.getElementById('viewAllRatingsBtn').style.display = 'none';
            })
            .catch(error => console.error('Error:', error));
    });

    // Function to generate stars for rating
    function getStarRating(rateValue) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += i <= rateValue ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
        }
        return stars;
    }
</script>

</body>
</html>
