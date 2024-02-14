<?php
require('sql.php'); // Adatbázis kapcsolódás

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // A form adatok beolvasása
  $nev = isset($_POST['nev']) ? $_POST['nev'] : '';
  $email = isset($_POST['email']) ? $_POST['email'] : '';
  $jelszo = isset($_POST['jelszo']) ? $_POST['jelszo'] : '';
  $jelszoMegerosit = isset($_POST['jelszo_megerosit']) ? $_POST['jelszo_megerosit'] : '';


    // Ellenőrzés, hogy az összes mező ki van-e töltve
    if (!empty($nev) && !empty($email) && !empty($jelszo) && !empty($jelszoMegerosit)) {
        // Biztonságosabbá tétele az SQL lekérdezéseknek
        $nev = mysqli_real_escape_string($conn, $nev);
        $email = mysqli_real_escape_string($conn, $email);
        $jelszo = mysqli_real_escape_string($conn, $jelszo);

        // Regisztráció az adatbázisba
        $query = "INSERT INTO `felhasznalo`(`nev`, `email_cim`, `jelszo`) VALUES ('$nev','$email','$jelszo')";
        $result = mysqli_query($conn, $query);

        if ($result) {
          header("Location: bejelentkezes.php");
          exit(); // Fontos: Leállítjuk az aktuális kódfuttatást, hogy biztosan csak az átirányítás történjen
      } else {
          // Sikertelen regisztráció
          echo "Hiba a regisztráció során: " . mysqli_error($conn);
      }
    } else {
        // Ha valamelyik mező nincs kitöltve
        echo "Minden mező kitöltése kötelező!";
    }

    // Adatbázis kapcsolat lezárása
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étrendkészítő Weboldal Regisztráció</title>
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="regisztracio kartya">
            <header>Regisztráció</header>
            <form action="" method="post">
                <input type="text" placeholder="Adja meg a nevét" name="nev">
                <input type="text" placeholder="Adja meg az email címét" name="email">
                <input type="password" placeholder="Adja meg a jelszavát" name="jelszo">
                <input type="password" placeholder="Erősítse meg a jelszavát" name="jelszo_megerosit">
                <input type="submit" class="button" value="Regisztráció" name="submit">
            </form>
            <div class="signup">
                <span class="signup">Már van fiókja?
                    <a href="bejelentkezes.php">Bejelentkezés</a>
                </span>
            </div>
        </div>
    </div>
</body>
</html>