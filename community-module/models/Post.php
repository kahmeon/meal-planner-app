<?php 
require_once(__DIR__ . "/../config.php");
class Post{
    private $con;
    public function __construct($con){
        $this->con = $con;
    }

    public function getPosts(){
        if(!$this->con){
            die("Database connection is missing.");
        }
        $query = "SELECT * FROM post ORDER BY created_at DESC";
        $result = mysqli_query($this->con, $query);
        if(!$result){
            die("Database query failed: " . mysqli_error($this->con));
        }
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function addPost($community_id, $user_id, $recipe_id, $comment,$feeling_id, $photo_url, $user_role){
        if(!$this->con){
            die("Database connection is missing.");
        }
        $comm_id = (int)$community_id;
        $user_id = (int)$user_id;
        $recipe_id = (int)$recipe_id;
        $comment = mysqli_real_escape_string($this->con, $comment);
        $feeling_id = (int)$feeling_id;
        $photo_url = mysqli_real_escape_string($this->con, $photo_url);
        $user_role = mysqli_real_escape_string($this->con, $user_role);
        $query = "INSERT INTO post (community_id, user_id, recipe_id, comment, feeling_id, photo_url, user_role) VALUES ('$comm_id',
        '$user_id', '$recipe_id', '$comment','$feeling_id','$photo_url','$user_role')";
        if (!mysqli_query($this->con, $query)) {
        die("Insert query failed: " . mysqli_error($this->con));
        }
        return true;
        
    }
    public function checkImage($files){
        $photo = [];
        $errors = [];
        for($i=0; $i<count($files['name']);$i++){
            $file_err = $files['error'][$i];
            $file_name = $files['name'][$i];

            // Error detect
            if ($file_err !== UPLOAD_ERR_OK) {
                fileError($file_err, $file_name);
            }
            // No error detect
            else{
                $target_dir = "uploads/post";
                $target_tmp = ""; // Add tmp file name
                $target_file = $target_dir . basename($file_name);

                if(!move_uploaded_file($target_tmp, $target_file)){
                        $errors[] = "Failed to move $file_name";
                }else{
                    $photo[] = $file_name;
                }
            }	
        }
        return [$photo, $errors];
    }

}

?>