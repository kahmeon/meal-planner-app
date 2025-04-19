<?php 
require_once(__DIR__ . "/../config.php");

class Recipe{
    private $con;
    public function __construct($con){
        $this->con = $con;
    }

    public function getRecipeInfo(){
        if(!$this->con){
            die("Database connection is missing.");
        }
        $query = "SELECT * FROM recipes ORDER BY created_at DESC";
        $result = mysqli_query($this->con, $query);
        if(!$result){
            die("Database query failed: " . mysqli_error($this->con));
        }
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

?>