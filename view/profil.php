<?php
require("../sql/sql.php");
session_start();

// Ellenőrzés, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalo_id'])) {
    header("Location: ../view/bejelentkezes.php");
    exit();
}

// Változók inicializálása
$nev = $email = $jelszo = $jelszoMegerosit = ""; // Alapértelmezett értékek

// Hibaüzenetek tárolására használt tömb
$errors = array();

// Ellenőrzés, hogy a form elküldésre került-e
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Az űrlap adatainak feldolgozása

    // Név
    $nev = $_POST["nev"];

    // Név ellenőrzése
    if (empty($nev) || strlen($nev) < 3 || strlen($nev) > 8 || preg_match("/[^a-zA-Z0-9]/", $nev)) {
        $errors[] = "A névnek legalább 3 karakter hosszúnak, maximum 8 karakter lehet, és nem tartalmazhat speciális karaktert.";
    }

    // Email-cím
    $email = $_POST["email"];

    // Email-cím ellenőrzése
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen email cím formátum.";
    }

    // Jelszó
    $jelszo = isset($_POST["jelszo"]) ? $_POST["jelszo"] : "";

    // Jelszó megerősítése
    $jelszoMegerosit = isset($_POST['jelszo_megerosit']) ? $_POST['jelszo_megerosit'] : '';

    // Jelszó erősítés ellenőrzése
    if ($jelszo !== $jelszoMegerosit) {
        $errors[] = "A jelszó és a jelszó megerősítése nem egyezik meg.";
    }

    // Jelszó erősségének ellenőrzése 
    if (empty($jelszo) || strlen($jelszo) < 4 || !preg_match("/[A-Z]/", $jelszo) || preg_match("/[^a-zA-Z0-9]/", $jelszo)) {
        $errors[] = "A jelszónak legalább 4 karakter hosszúnak, tartalmaznia kell legalább 1 nagybetűt, és nem tartalmazhat speciális karaktert.";
    }

    // Csak akkor frissítjük az adatokat, ha nincsenek hibaüzenetek
    if (empty($errors)) {
        // Jelszó hashelése
        $hashelt_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

        // Az adatok frissítése a session-ben
        $_SESSION['nev'] = $nev;
        $_SESSION['email'] = $email;

        // Az adatok frissítése az adatbázisban
        $felhasznalo_id = $_SESSION['felhasznalo_id']; // Hozzuk létre a felhasználó azonosítóját  
        $sqlUpdate = "UPDATE felhasznalo SET nev=?, email_cim=?, jelszo=? WHERE felhasznalo_id=?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("sssi", $nev, $email, $hashelt_jelszo, $felhasznalo_id);

        if ($stmtUpdate->execute()) {
            // Sikeres adatmódosítás esetén egyéb teendők
        } else {
            $errors[] = "Hiba történt az adatok frissítése során.";
        }
    }
} else {
    // Lekérdezés a jelenlegi név és email-cím megjelenítéséhez
    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    $sqlQuery = "SELECT nev, email_cim FROM felhasznalo WHERE felhasznalo_id=?";
    $stmtQuery = $conn->prepare($sqlQuery);
    $stmtQuery->bind_param("i", $felhasznalo_id);
    $stmtQuery->execute();
    $stmtQuery->store_result();
    $stmtQuery->bind_result($nev, $email);

    if (!$stmtQuery->fetch()) {
        $errors[] = "Hiba történt az adatok lekérdezése során.";
    }

    $stmtQuery->close();
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Étrend Készítő Weboldal</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <?php
        // Ha a felhasználó nincs bejelentkezve
        if (!isset($_SESSION['felhasznalo_id'])) {
            echo '
            <a href="../view/rolunk.php">Rólunk</a> |
            <a href="../view/bejelentkezes.php">Bejelentkezés</a> |
            <a href="../view/regisztracio.php">Regisztráció</a>
            ';
        } else { // Ha a felhasználó be van jelentkezve
            require("../sql/sql.php");
            $felhasznalo_id = $_SESSION['felhasznalo_id'];

            // Ellenőrizzük az adatbázisban, hogy van-e kiválasztott kép
            $query = "SELECT kivalasztott_kepek FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $kivalasztott_kepek = $row['kivalasztott_kepek'];

            if (!empty($kivalasztott_kepek)) {
                // Ha van kiválasztott kép, megjelenítjük az "Étrendem" linket
                echo '
                <a href="../view/rolunk.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrendem.php">Étrendem</a> |
                <a href="../logout.php">Kijelentkezés</a>
                ';
            } else {
                // Ha nincs kiválasztott kép, a korábbi logikához hasonlóan jelenítjük meg a linkeket
                // Ellenőrizzük, hogy van-e már étrendje
                $query = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '' OR cel = 'nincs cel' OR nem = 'valasszon' OR aktivitas = 'valasszon')";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_row($result);
                $etrendVan = $row[0] == 0;

                if ($etrendVan) {
                    echo '
                    <a href="../view/rolunk.php">Rólunk</a> |
                    <a href="../view/profil.php">Profil</a> |
                    <a href="../view/etrend.php">Étrend</a> |
                    <a href="../logout.php">Kijelentkezés</a>
                    ';
                } else {
                    echo '
                    <a href="../view/rolunk.php">Rólunk</a> |
                    <a href="../view/profil.php">Profil</a> |
                    <a href="../view/etrendkeszitese.php">Étrendkészítés</a> |
                    <a href="../logout.php">Kijelentkezés</a>
                    ';
                }
            }
        }
        ?>
    </nav>
</header>



<div class="profil_lap">
    <div class="kartya">
    <header>Profil</header>

    <?php
    // Hibaüzenetek megjelenítése
    if (!empty($errors) && isset($_POST["adatokmodositasa"])) {
        echo '<div class="hiba-uzenetek">';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    // Megjelenítés csak akkor, ha nincsenek hibaüzenetek és POST kérésből érkezett adatok
    if(empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["adatokmodositasa"])) {
        // Sikeres adatmódosítás esetén üzenet
        echo '<div class="sikeres-uzenet">';
            echo "Adataid sikeresen frissítve.";
        echo '</div>';
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regebbietrendek'])) {
        header("Location: regebbietrendek.php");
        exit();
    }

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Hiba: " . $e->getMessage();
    }

    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    // SQL lekérdezés
    $sql = "SELECT * FROM etkezes WHERE felhasznalo_id = :felhasznalo_id LIMIT 1"; // Ellenőrizzük, hogy van-e bármilyen rekord a felhasználóhoz tartozó étkezések között
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':felhasznalo_id', $felhasznalo_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $stmt->rowCount(); // Ellenőrizzük, hogy van-e találat

    ?>

    <form action="" method="post">

        <!-- Név -->
        <h3>Név:</h3>
        <label>Jelenlegi név: <?php echo htmlspecialchars($nev); ?></label>
        <br>
        <input type="text" placeholder="Név" name="nev" value="">
        <br>
        <label>A név minimum 3, maximum 8 karakter lehet, <br>
                nem tartalmazhat speciális karaktert.</label>
        <br>

        <!-- Email-cím -->
        <h3>Email-cím</h3>
        <label>Jelenlegi email-cím: <?php echo htmlspecialchars($email); ?></label>
        <br>
        <input type="text" placeholder="Email cím" name="email" value="">

        <!-- Jelszó -->
        <h3>Jelszó</h3>
        <input type="password" placeholder="Jelszó" name="jelszo" value="">
        <br>
        <label>A jelszó minimum 4 karakter lehet, nem tartalmazhat speciális karaktert, <br>
                minimum 1 nagy karaktert kell tartalmaznia.</label>
        <br>

        <!-- Jelszó Megerősítése-->
        <h3>Jelszó megerősítése</h3>
        <input type="password" placeholder="Jelszó megerősítése" name="jelszo_megerosit" value="">

        <input type="submit" value="Adatok módosítása" class="button" name="adatokmodositasa">

        <?php
        if ($count > 0) {
            // Van adat az étkezések között, tehát megjelenítjük a gombot
            echo '<input type="submit" value="Régebbi étrendeim megtekintése" class="button" name="regebbietrendek">';
        }
    
        ?>

    </form>
    </div>
</div>

</body>
</html>
