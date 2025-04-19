<?php 
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../models/Recipe.php");

class RecipeController {
    private $user_comm;

    public function __construct($con) {
        $this->user_comm = new Recipe($con);
    }

    public function displayRecipe() {
        $recipe = $this->user_comm->getRecipeInfo();
        require_once(__DIR__ . "/TagController.php");
        
        include(__DIR__ . "/../views/recipe-search.php");
    }
    private function getRecipeTags($recipeId) {
        if (!$recipeId) return [];
        
        // This would be replaced with your actual tag retrieval logic
        // For example, querying the recipe_tags join table
        $query = "SELECT t.tag_name 
                  FROM recipe_tags rt 
                  JOIN tags t ON rt.tag_id = t.tag_id 
                  WHERE rt.recipe_id = $recipeId";
                  
        $result = mysqli_query($this->con, $query);
        if (!$result) return [];
        
        $tags = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tags[] = $row['tag_name'];
        }
        
        return $tags;
    }
}

?>
