<?php 
require_once(__DIR__ . "/../config.php");

class Tags{
    private $con;
    public function __construct($con){
        $this->con = $con;
    }

    public function getTags(){
        if(!$this->con){
            die("Database connection is missing.");
        }
        $query = "SELECT * FROM tags ORDER BY id DESC";
        $result = mysqli_query($this->con, $query);
        if(!$result){
            die("Database query failed: " . mysqli_error($this->con));
        }
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // public function getUserAvatar($user_comm_id){
    //     if(!$this->con){
    //         die("Database connection is missing.");
    //     }
    //     $comm_id = (int) $user_comm_id;
    //     $query = "SELECT user_avatar_id FROM user_community WHERE user_comm_id = '$comm_id'";
    //     $result = mysqli_query($this->con, $query) or die("Database query failed: " . mysqli_error($this->con));
    //     $avatar_id = mysqli_fetch_assoc($result);

    //     $avatar = $avatar_id['user_avatar_id'];
    //     $avatar_query = "SELECT avatar_url FROM avatar WHERE avatar_id = '$avatar'";
    //     $avatar_result = mysqli_query($this->con, $avatar_query);
    //     if(!$avatar_result){
    //         die("Database query failed: " . mysqli_error($this->con));
    //     }
    //     $avatar_result = mysqli_fetch_assoc($avatar_result);
    //     $avatar_img = "../uploads/community/avatar/" . $avatar_result['avatar_url'];
        
    //     return $avatar_img;
    // }

    // private function checkImage($files){
    //     $photo = [];
    //     $errors = [];
    //     for($i=0; $i<count($files['name']);$i++){
    //         $file_err = $files['error'][$i];
    //         $file_name = $files['name'][$i];

    //         // Error detect
    //         if ($file_err !== UPLOAD_ERR_OK) {
    //             fileError($file_err, $file_name);
    //         }
    //         // No error detect
    //         else{
    //             $target_dir = "uploads/post";
    //             $target_tmp = ""; // Add tmp file name
    //             $target_file = $target_dir . basename($file_name);

    //             if(!move_uploaded_file($target_tmp, $target_file)){
    //                     $errors[] = "Failed to move $file_name";
    //             }else{
    //                 $photo[] = $file_name;
    //             }
    //         }	
    //     }
    //     return [$photo, $errors];
    // }
}

?>