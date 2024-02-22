<?php

$servername = "localhost"; 
$username = "c31harsanyiFR"; 
$password = "PefZwG9@3"; 
$dbname = "c31harsanyiFR_db"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Sikertelen kapcsolódás az adatbázishoz: " . mysqli_connect_error());
}

   
   
