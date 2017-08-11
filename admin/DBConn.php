<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = $_POST["host"];
    $user = $_POST["username"];
    $password = $_POST["password"];
    $secret = $_POST["secret"];
    
    
    if (empty($secret) || md5($secret) != "c45008212f7bdf6eab6050c2a564435a") {
        echo "Secret either empty or incorrect. Redirecting in 3 seconds...";
        header( "refresh:3;url=DBTesting.php" );
    } else {
        if (connect($host, $user, $password)) {
            echo "Connected. Click <a href='DBTesting.php'>here</a> to go back.";
        } else {
            echo "Failed to connect to db.";
            
            header( "refresh:5;url=DBTesting.php" );
        }
    }
    
    echo "<br><br>";
    echo "Debugging...<br>";
    echo "Host: \"" . $host . "\"<br>";
    echo "User: \"" . $user . "\"<br>";
    echo "Password: \"" . $password . "\"<br>";
    echo "Secret: \"" . $secret . "\"<br>";
      
} else {
    echo "You shouldn't be here. Redirecting in 3 seconds...";
    
    header( "refresh:3;url=index.php" );
}

function connect($host, $user, $password) {
    $dbname = "bb_teamsectioncamp_db";
    
    $conn = new mysqli($host, $user, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "Error: " . ($conn->connect_error) . "";
        return false;
    } else {
        return true;
    }
}
?>