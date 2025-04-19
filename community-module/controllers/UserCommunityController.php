<?php 
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../models/UserCommunity.php");

class UserCommunityController {
    private $user_comm;

    public function __construct($con) {
        $this->user_comm = new UserCommunity($con);
    }

    public function displayAvatar() {
        if(isset($_SESSION['user_community_id']) && !empty($_SESSION['user_community_id'])){
            $user_comm_id = (int)$_SESSION['user_community_id'];
            $avatar = $this->user_comm->getUserAvatar($user_comm_id);
        }
        include(__DIR__ . "/../views/community-avatar.php");
    }

    // public function addPost() {
    //     $photo_url = [];
    //     if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['post_submit'])) {
    //         $comm_id = $_SESSION['community_id'];
    //         $user_id = $_SESSION['user_id'];
    //         $comment = $_POST['comment'] ?? '';
    //         $photo_url = $_FILES['photo_url'] ?? null;
    //         // If detect got photo
    //         if (count(array_filter($_FILES['post_photo']['name'])) !== 0){
    //             [$photos, $upload_errors] = $this->post->checkImage($photo_url);
    //             // If no error found
    //             if(count(array_filter($photos)) !== 0 && empty($upload_errors)){
    //                 $photo_url[] = $photos;
    //             }else{
    //                 Header("Location: community-recipe.php?community_id=" . urlencode($comm_id). "&post_success=0&post_errors=(implode(", ", $upload_errors))");
    //                 exit();
    //             }
    //         }
    //         $feeling_id = $_POST['feeling_id'] ?? null;
    //         $recipe_id = $_POST['recipe_id'] ?? null;
    //         $user_role = $_POST['user_role'] ?? '';

    //         // Add query
    //         if ($this->post->addPost($comm_id, $user_id, $recipe_id, $comment, $feeling_id, $photo_url, $user_role)) {
    //             header("Location: ../community-recipe.php?community_id=" . urlencode($comm_id) . "&post_success=1");
    //             exit();
    //         }
    //     }
    // }

}
?>
