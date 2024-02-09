<?php

$servername = "localhost"; 
$username = "admin"; 
$password = "qCvE_CfYzgZoMX4t"; 
$dbname = "szakdolgozat"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Sikertelen kapcsolódás az adatbázishoz: " . mysqli_connect_error());
}

   
   
