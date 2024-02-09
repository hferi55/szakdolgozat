<?php
require('sql.php'); // Adatbázis kapcsolódás

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A form adatok beolvasása
    $email = $_POST['email'];
    $jelszo = $_POST['jelszo'];

    // Biztonságosabbá tétele az SQL lekérdezéseknek
    $email = mysqli_real_escape_string($conn, $email);
    $jelszo = mysqli_real_escape_string($conn, $jelszo);

    // Bejelentkezési ellenőrzés
    $query = "SELECT * FROM felhasznalok WHERE email_cim='$email' AND jelszo='$jelszo'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        // Sikeres bejelentkezés
        echo "Sikeres bejelentkezés!";
    } else {
        // Sikertelen bejelentkezés
        echo "Hibás felhasználónév vagy jelszó!";
    }
}

// Adatbázis kapcsolat lezárása
mysqli_close($conn);

