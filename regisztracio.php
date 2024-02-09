<?php
require('sql.php'); // Adatbázis kapcsolódás

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A form adatok beolvasása
    $nev = $_POST['nev'];
    $email = $_POST['email'];
    $jelszo = $_POST['jelszo'];

    // Biztonságosabbá tétele az SQL lekérdezéseknek
    $nev = mysqli_real_escape_string($conn, $nev);
    $email = mysqli_real_escape_string($conn, $email);
    $jelszo = mysqli_real_escape_string($conn, $jelszo);

    // Regisztráció az adatbázisba
    $query = "INSERT INTO felhasznalok (nev, email_cim, jelszo) VALUES ('$nev', '$email', '$jelszo')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        // Sikeres regisztráció
        echo "Sikeres regisztráció!";
    } else {
        // Sikertelen regisztráció
        echo "Hiba a regisztráció során!";
    }
}

// Adatbázis kapcsolat lezárása
mysqli_close($conn);
?>
