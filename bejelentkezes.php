<?php
require('sql.php'); // Adatbázis kapcsolódás

$errorMessage = ''; // Hibaüzenet inicializálása

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // A form adatok beolvasása
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $jelszo = isset($_POST['jelszo']) ? $_POST['jelszo'] : '';

    // Biztonságosabbá tétele az SQL lekérdezéseknek
    $email = mysqli_real_escape_string($conn, $email);
    $jelszo = mysqli_real_escape_string($conn, $jelszo);

    // Bejelentkezési ellenőrzés
    if (!empty($email) && !empty($jelszo)) {
        $query = "SELECT * FROM felhasznalo WHERE email_cim='$email'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $titkositottJelszoDB = $row['jelszo'];

            // Jelszó ellenőrzése
            if (password_verify($jelszo, $titkositottJelszoDB)) {
                // Sikeres bejelentkezés
                header("Location: loggedin.php");
                exit();
            } else {
                // Sikertelen bejelentkezés - hibás jelszó
                $errorMessage = "Hibás felhasználónév vagy jelszó!";
            }
        } else {
            // Sikertelen bejelentkezés - felhasználó nem található
            $errorMessage = "Hibás felhasználónév vagy jelszó!";
        }
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

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <a href="index.php">Főoldal</a> 
    </nav>
</header>

    <div class="container">
        <div class="bejelentkezes kartya">
            <header>Bejelentkezés</header>
            
            <?php if ($errorMessage): ?>
                <p><?php echo $errorMessage; ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <input type="text" placeholder="Adja meg az email címét" name="email" required>
                <input type="password" placeholder="Adja meg a jelszavát" name="jelszo" required>
                <input type="submit" class="button" value="Bejelentkezés" name="submit">
            </form>
            <div class="signup">
                <span class="signup">Még nincs fiókja?
                    <a href="regisztracio.php">Regisztráció</a>
                </span>
            </div>
        </div>
    </div>
</body>
</html>
<?php

// Adatbázis kapcsolat lezárása
mysqli_close($conn);
?>
