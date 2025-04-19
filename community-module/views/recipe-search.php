
<!-- Modal for Recipe Search -->
<div class="modal" id="recipeSearchModal" tabindex="-1" aria-labelledby="recipeSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="recipeSearchModalLabel">Search and Attach a Recipe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Search Bar -->
                <div class="search-container">
                    <input id="searchInput" class="form-control" type="search" placeholder="Search for recipes or tags..." autocomplete="off">
                    <div id="suggestions" class="mt-2"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

