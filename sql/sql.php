<?php

$szervernev = "localhost"; 
$felhasznalonev = "c31harsanyiFR"; 
$jelszo = "PefZwG9@3"; 
$dbnev = "c31harsanyiFR_db"; 

$conn = mysqli_connect($szervernev, $felhasznalonev, $jelszo, $dbnev);

if (!$conn) {
    die("Sikertelen kapcsolódás az adatbázishoz: " . mysqli_connect_error());
}

   
   
