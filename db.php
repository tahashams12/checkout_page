<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "payment_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    echo "Connection failed: ";
}
else{
    echo "Connection successful";
}




?>