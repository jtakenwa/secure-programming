<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "jaasiel";
$dbname = "jaas_shop";

$conn = new mysqli($servername, $username, $password, $dbname);
//echo "Connected successfully";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    //echo " DB Connected successfully \n ";
}
?>
