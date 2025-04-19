<?php

// Undergo mysqli connection for query
function mysqliOperation($query) {

    static $conn;
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "mealplanner";
    
    if($conn === null){
        $conn = new mysqli($host, $user, $password, $database) or die("Connection failed: " . mysqli_connect_error());
    }
    
    $result = mysqli_query($conn, $query) or die("Query failed: " . mysqli_error($conn));
    return $result;
}   

?>