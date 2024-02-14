<?php
require('sql.php'); // Adatbázis kapcsolódás

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A form adatok beolvasása
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $jelszo = isset($_POST['jelszo']) ? $_POST['jelszo'] : '';

    // Biztonságosabbá tétele az SQL lekérdezéseknek
    $email = mysqli_real_escape_string($conn, $email);
    $jelszo = mysqli_real_escape_string($conn, $jelszo);

    // Bejelentkezési ellenőrzés
    if (!empty($email) && !empty($jelszo) ) {
    $query = "SELECT * FROM felhasznalo WHERE email_cim='$email' AND jelszo='$jelszo'";
    $result = mysqli_query($conn, $query);

        if ($result) {
            // Sikeres bejelentkezés
            header("Location: index.php");
            exit(); // Fontos: Leállítjuk az aktuális kódfuttatást, hogy biztosan csak az átirányítás történjen
        } else {
            // Sikertelen bejelentkezés
            echo "Hibás felhasználónév vagy jelszó!" . mysqli_error($conn);
        }
    } else {
      // Ha valamelyik mező nincs kitöltve
      echo "Minden mező kitöltése kötelező!";
  }
}
?>
        <!DOCTYPE html>
        <html lang="hu">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE-edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Étrendkészítő Weboldal Bejelentkezés</title>
            <!-- CSS -->
            <link rel="stylesheet" href="style.css">
        </head>
        <body>

            <div class="container">
                <div class="bejelentkezes kartya">
                    <header>Bejelentkezés</header>
                    <form action="" method="post">
                        <input type="text" placeholder="Adja meg az email címét" name="email">
                        <input type="password" placeholder="Adja meg a jelszavát" name="jelszo">
                        <input type="submit" class="button" value="Bejelentkezés" name="submit">
                    </form>
                    <div class="signup">
                        <span class="signup">Még nincs fiókja?
                            <a href="index.php">Regisztráció</a>
                        </span>
                    </div>
                </div>
            </div>
        </body>
        </html>
<?php

// Adatbázis kapcsolat lezárása
mysqli_close($conn);

