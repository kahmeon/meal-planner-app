<?php
session_start();
session_unset();    // Clear session variables
session_destroy();  // Destroy session data

header("Location: home.php");
exit();
