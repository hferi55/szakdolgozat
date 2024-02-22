<?php
require('sql.php'); // Adatbázis kapcsolódás

$errorMessage = ''; // Hibaüzenet inicializálása
$testsuly = $magassag = 0; // Alapértelmezett értékek
$cel = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A form adatok beolvasása
    $nev = isset($_POST['nev']) ? $_POST['nev'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $jelszo = isset($_POST['jelszo']) ? $_POST['jelszo'] : '';
    $jelszoMegerosit = isset($_POST['jelszo_megerosit']) ? $_POST['jelszo_megerosit'] : '';

    // Ellenőrzés, hogy az összes mező ki van-e töltve
    if (!empty($nev) && !empty($email) && !empty($jelszo) && !empty($jelszoMegerosit)) {
        // Ellenőrzés, hogy a név megfelelő hosszúságú
        if (strlen($nev) >= 3) {
            // Ellenőrzés, hogy a jelszó megfelelő hosszúságú, tartalmaz-e nagybetűt, és nem tartalmaz-e speciális karaktert
            if (strlen($jelszo) >= 4 && preg_match('/[A-Z]/', $jelszo) && !preg_match('/[^A-Za-z0-9]/', $jelszo)) {
                // Biztonságosabbá tétele az SQL lekérdezéseknek
                $nev = mysqli_real_escape_string($conn, $nev);
                $email = mysqli_real_escape_string($conn, $email);

                // Ellenőrzés, hogy az email cím már létezik-e az adatbázisban
                $ellenorzoQuery = "SELECT * FROM felhasznalo WHERE email_cim='$email'";
                $ellenorzoResult = mysqli_query($conn, $ellenorzoQuery);

                if ($ellenorzoResult && mysqli_num_rows($ellenorzoResult) > 0) {
                    // Sikertelen regisztráció - az email cím már foglalt
                    $errorMessage = "Ez az email cím már regisztrálva van!";
                } else {
                    // Jelszó titkosítása
                    $titkositottJelszo = password_hash($jelszo, PASSWORD_DEFAULT);

                    // Regisztráció az adatbázisba
                    $query = "INSERT INTO `felhasznalo`(`nev`, `email_cim`, `jelszo`, `testsuly`, `magassag`, `cel`) VALUES ('$nev','$email','$titkositottJelszo','$testsuly','$magassag','$cel')";
                    $result = mysqli_query($conn, $query);

                    if ($result) {
                        header("Location: bejelentkezes.php");
                        exit();
                    } else {
                        // Sikertelen regisztráció
                        $errorMessage = "Hiba a regisztráció során: " . mysqli_error($conn);
                    }
                }
            } else {
                // Sikertelen regisztráció - a jelszó nem felel meg a követelményeknek
                $errorMessage = "A jelszónak legalább 4 karakter hosszúnak kell lennie, tartalmaznia kell egy nagybetűt, és nem tartalmazhat speciális karaktereket!";
            }
        } else {
            // Sikertelen regisztráció - a név nem felel meg a követelményeknek
            $errorMessage = "A névnek legalább 3 karakter hosszúnak kell lennie!";
        }
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

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <a href="index.php">Főoldal</a> 
    </nav>
</header>

    <div class="container">
        <div class="regisztracio kartya">
            <header>Regisztráció</header>
            
            <?php if ($errorMessage): ?>
                <p><?php echo $errorMessage; ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <input type="text" placeholder="Adja meg a nevét" name="nev" required>
                <input type="email" placeholder="Adja meg az email címét" name="email" required>
                <input type="password" placeholder="Adja meg a jelszavát" name="jelszo" required>
                <input type="password" placeholder="Erősítse meg a jelszavát" name="jelszo_megerosit" required>
                <input type="submit" class="button" value="Regisztráció" name="submit" required>
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
