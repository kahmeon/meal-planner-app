<?php
// recipe-search.php
// This file displays a popup search window to find recipes by title

// Get recipe data from controller
if (!isset($recipe) || empty($recipe)) {
    $recipe = []; // Ensure $recipe is defined even if empty
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Search</title>
    <style>
        /* Styling for the search modal */
        .search-modal {
            display: flex;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }

        .search-modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 80%;
            max-width: 600px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: transparent;
        }

        .search-container {
            position: relative;
            width: 100%;
        }

        #suggestions {
            position: absolute;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
            z-index: 1001;
            display: none;
        }

        #suggestions .item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        #suggestions .item:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <!-- Search Modal -->
    <div class="search-modal" id="recipeSearchModal">
        <div class="search-modal-content">
            <button class="close-btn" id="closeSearchBtn">&times;</button>
            <h3>Search Recipes</h3>
            <div class="search-container w-100 mt-3">
                <input id="searchInput" class="form-control" type="search" placeholder="Search for recipes by title..." autocomplete="off">
                <div id="suggestions"></div>
            </div>
        </div>
    </div>

    <script>
    // Make PHP $recipe data available to JavaScript
    window.recipeData = <?php echo json_encode($recipe); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById("searchInput");
        const suggestions = document.getElementById("suggestions");
        const closeBtn = document.getElementById("closeSearchBtn");
        const searchModal = document.getElementById("recipeSearchModal");

        // Close modal when clicking the X button
        closeBtn.addEventListener("click", function() {
            searchModal.style.display = "none";
        });

        // Close modal when clicking outside the modal content
        window.addEventListener("click", function(event) {
            if (event.target == searchModal) {
                searchModal.style.display = "none";
            }
        });

        // Focus search input when modal opens
        searchModal.addEventListener("click", function() {
            searchInput.focus();
        }, { once: true });

        // Handle search input
        searchInput.addEventListener("input", function() {
            const query = this.value.trim();
            if (query.length < 2) {
                suggestions.style.display = "none";
                return;
            }

            // Search in local data first if available
            if (window.recipeData && window.recipeData.length > 0) {
                displayLocalResults(query);
            } else {
                // Fall back to server search
                fetch("community-search.php?search=" + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        displayResults(data);
                    });
            }
        });

        // Display search results from local data
        function displayLocalResults(query) {
            query = query.toLowerCase();
            const results = window.recipeData.filter(item => 
                item.title.toLowerCase().includes(query)
            );
            displayResults(results);
        }

        // Display search results
        function displayResults(data) {
            suggestions.innerHTML = "";

            if (data.length === 0) {
                suggestions.innerHTML = "<div class='item'>No results found</div>";
            } else {
                data.forEach(item => {
                    const div = document.createElement("div");
                    div.className = "item";
                    div.style.cursor = "pointer";

                    // Get tags if available
                    let tagsHTML = "";
                    if (item.tags && Array.isArray(item.tags)) {
                        tagsHTML = item.tags.map(tag =>
                            `<span class="badge bg-success me-1">${tag}</span>`
                        ).join(" ");
                    }

                    div.innerHTML = `
                        <strong>${item.title}</strong><br>
                        ${tagsHTML ? `<div class="mt-1">${tagsHTML}</div>` : ""}
                    `;

                    div.addEventListener("click", () => {
                        if (item.community_id) {
                            window.location.href = `community-recipe.php?community_id=${item.community_id}`;
                        } else if (item.id) {
                            window.location.href = `community-recipe.php?community_id=${item.id}`;
                        }
                    });

                    suggestions.appendChild(div);
                });
            }

            suggestions.style.display = "block";
        }

        // Close suggestions when clicking outside
        document.addEventListener("click", function(e) {
            if (!suggestions.contains(e.target) && e.target !== searchInput) {
                suggestions.style.display = "none";
            }
        });

        // Focus search input when modal opens
        searchInput.focus();
    });
    </script>
</body>
</html>