<?php
require('../sql/sql.php'); // Adatbázis kapcsolódás

$hibaUzenet = ''; // Hibaüzenet inicializálása
$testsuly = $magassag = $cel = $nem = $eletkor = $aktivitas = 0; // Alapértelmezett értékek
$kivalasztott_kepek = NULL;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A form adatok beolvasása
    $nev = isset($_POST['nev']) ? $_POST['nev'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $jelszo = isset($_POST['jelszo']) ? $_POST['jelszo'] : '';
    $jelszoMegerosit = isset($_POST['jelszo_megerosit']) ? $_POST['jelszo_megerosit'] : '';

    // Ellenőrzés, hogy az összes mező ki van-e töltve
    if (!empty($nev) && !empty($email) && !empty($jelszo) && !empty($jelszoMegerosit)) {
        // Ellenőrzés, hogy a név megfelelő hosszúságú
        if (strlen($nev) >= 3 && !preg_match('/[^A-Za-z0-9]/', $nev && strlen($nev)) != 0 && strlen($nev) <= 8 && !empty($nev)) {
            // Ellenőrzés, hogy a jelszó megfelelő hosszúságú, tartalmaz-e nagybetűt, és nem tartalmaz-e speciális karaktert
            if (strlen($jelszo) >= 4 && preg_match('/[A-Z]/', $jelszo) && !preg_match('/[^A-Za-z0-9]/', $jelszo) && !empty($jelszo)) {
                // Biztonságosabbá tétele az SQL lekérdezéseknek
                $nev = mysqli_real_escape_string($conn, $nev);
                $email = mysqli_real_escape_string($conn, $email);

                // Ellenőrzés, hogy az email cím már létezik-e az adatbázisban
                $ellenorzoKeres = "SELECT * FROM felhasznalo WHERE email_cim='$email'";
                $ellenorzoValasz = mysqli_query($conn, $ellenorzoKeres);

                if ($ellenorzoValasz && mysqli_num_rows($ellenorzoValasz) > 0) {
                    // Sikertelen regisztráció - az email cím már foglalt
                    $hibaUzenet = "Ez az email cím már regisztrálva van!";
                } else {
                    // Jelszó titkosítása
                    $titkositottJelszo = password_hash($jelszo, PASSWORD_DEFAULT);

                    // Regisztráció az adatbázisba
                    $keres = "INSERT INTO `felhasznalo`(`nev`, `email_cim`, `jelszo`, `testsuly`, `magassag`, `cel`, `nem`, `eletkor`, `aktivitas`, `kivalasztott_kepek`) VALUES ('$nev','$email','$titkositottJelszo','$testsuly','$magassag','$cel', '$nem', '$eletkor', '$aktivitas', '$kivalasztott_kepek')";
                    $valasz = mysqli_query($conn, $keres);

                    if ($valasz) {
                        header("Location: bejelentkezes.php");
                        exit();
                    } else {
                        // Sikertelen regisztráció
                        $hibaUzenet = "Hiba a regisztráció során: " . mysqli_error($conn);
                    }
                }
            } else {
                // Sikertelen regisztráció - a jelszó nem felel meg a követelményeknek
                $hibaUzenet = "A jelszónak legalább 4 karakter hosszúnak kell lennie, tartalmaznia kell egy nagybetűt, és nem tartalmazhat speciális karaktereket!";
            }
        } else {
            // Sikertelen regisztráció - a név nem felel meg a követelményeknek
            $hibaUzenet = "A névnek legalább 3 karakter hosszúnak kell lennie!";
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
    <title>Étrend Készítő Weboldal</title>
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <a href="../index.php">Főoldal</a> 
    </nav>
</header>

    <div class="regisztracio_kontener">
        <div class="regisztracio kartya">
            <header>Regisztráció</header>
            
            <?php if ($hibaUzenet): ?>
                <p><?php echo $hibaUzenet; ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <input type="text" placeholder="Adja meg a nevét" name="nev" required>
                <label>A név minimum 3, maximum 8 karakter lehet, nem tartalmazhat speciális karaktert.</label>
                <br>
                <br>
                <input type="email" placeholder="Adja meg az email címét" name="email" required>
                <input type="password" placeholder="Adja meg a jelszavát" name="jelszo" required>
                <label>A jelszó minimum 4 karakter lehet, nem tartalmazhat speciális karaktert, minimum 1 nagy karaktert kell tartalmaznia.</label>
                <br>
                <br>
                <input type="password" placeholder="Erősítse meg a jelszavát" name="jelszo_megerosit" required>
                <input type="submit" class="button" value="Regisztráció" name="kuldes" required>
            </form>
            <div class="regisztralas">
                <span class="regisztralas">Már van fiókja?
                    <a href="../view/bejelentkezes.php">Bejelentkezés</a>
                </span>
            </div>
        </div>
    </div>
</body>
</html>
