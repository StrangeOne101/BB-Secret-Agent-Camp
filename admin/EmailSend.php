<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = $_POST["emailaddr"];
    $message = $_POST["message"];
    $subject = $_POST["subject"];
    $secret = $_POST["secret"];
    
    $headers = "From: info@secretagentcamp.co.nz";
    
    if (empty($secret) || md5($secret) != "c45008212f7bdf6eab6050c2a564435a") {
        echo "Secret either empty or incorrect. Redirecting in 3 seconds...";
        header( "refresh:3;url=EmailTesting.php" );
    } else {
        if (mail($address, $subject, $message, $headers)) {
            echo "Mail send successfully. Click <a href='MailTesting.php'>here</a> to go back.";
        } else {
            echo "Failed to send mail";
        }
    }
    
    echo "<br><br>";
    echo "Debugging...<br>";
    echo "Address: \"" . $address . "\"<br>";
    echo "Message: \"" . $message . "\"<br>";
    echo "Subject: \"" . $subject . "\"<br>";
    echo "Secret: \"" . $secret . "\"<br>";
      
} else {
    echo "You shouldn't be here. Redirecting in 3 seconds...";
    
    header( "refresh:3;url=index.php" );
}
?>