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
    $query = "INSERT INTO `felhasznalo`(`nev`, `email_cim`, `jelszo`) VALUES ('$nev','$email','$jelszo')";
    $result = mysqli_query($conn, $query);

    if($result){
        //Sikeres a regisztráció
        //Dobjon át a bejelentkező ablakra
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
                    <form action="bejelentkezes.php" method="post">
                        <input type="text" placeholder="Adja meg az email címét" name="email">
                        <input type="password" placeholder="Adja meg a jelszavát" name="jelszo">
                        <input type="submit" class="button" value="Bejelentkezés" name="submit">
                    </form>
                    <div class="signup">
                        <span class="signup">Még nincs fiókja?
                            <label for="check">Regisztráció</label>
                        </span>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    /*if ($result) {
        // Sikeres regisztráció
        echo "Sikeres regisztráció!";
    } else {
        // Sikertelen regisztráció
        echo "Hiba a regisztráció során!";
    }*/
    
}
// Adatbázis kapcsolat lezárása
mysqli_close($conn);
?>
