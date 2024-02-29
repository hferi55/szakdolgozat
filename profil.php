<?php
require("sql.php");
session_start();

// Ellenőrzés, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalo_id'])) {
    header("Location: bejelentkezes.php"); // Változtasd meg a céloldalt a bejelentkezési oldalra
    exit();
}

// Változók inicializálása
$nev = $email = $jelszo = $jelszoMegerosit = ""; // Alapértelmezett értékek
$profilkep_id = isset($_POST['profilkep_id']) ? $_POST['profilkep_id'] : $_SESSION['profilkep_id']; // Profilkép azonosító

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
        $_SESSION['profilkep_id'] = $profilkep_id;

        // Az adatok frissítése az adatbázisban
        $felhasznalo_id = $_SESSION['felhasznalo_id']; // Hozzuk létre a felhasználó azonosítóját  
        $sqlUpdate = "UPDATE felhasznalo SET nev=?, email_cim=?, jelszo=? WHERE felhasznalo_id=?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("sssi", $nev, $email, $hashelt_jelszo, $felhasznalo_id);

        if ($stmtUpdate->execute()) {

        } else {
            $errors[] = "Hiba történt az adatok frissítése során.";
        }
    }
}

// A kiválasztott profilkép elérési útvonala
switch ($profilkep_id) {
    case 1:
        $profilkep = "profilkepek/uresprofilkep.png";
        break;
    case 2:
        $profilkep = "profilkepek/no.jpg";
        break;
    case 3:
        $profilkep = "profilkepek/ferfi.jpg";
        break;
    default:
        $profilkep = "profilkepek/uresprofilkep.png";
        break;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Étrendkészítő Weboldal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <a href="rolunk2.php">Rólunk</a> |
        <a href="profil.php">Profil</a> |
        <a href="etrendkeszitese.php">Étrendkészítése</a> |
        <a href="logout.php">Kijelentkezés</a>
    </nav>
</header>

<div class="profil_lap">
    <div class="kartya">
    <header>Profil</header>

    <?php
    // Hibaüzenetek megjelenítése
    if (!empty($errors)) {
        echo '<div class="hiba-uzenetek">';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    // Megjelenítés csak akkor, ha nincsenek hibaüzenetek és POST kérésből érkezett adatok
    if(empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST"){
        // Sikeres adatmódosítás esetén üzenet
        echo '<div class="sikeres-uzenet">';
            echo "Adataid sikeresen frissítve.";
        echo '</div>';
    }
    ?>

    <form action="" method="post">
        <!-- Profilkép megjelenítése -->
        <img src="<?php echo htmlspecialchars($profilkep); ?>" alt="Profilkép" class="profilkep">
        <br>
        <!-- Profilkép kiválasztása -->
        <label for="profilkep_id">Profilkép kiválasztása:</label>
        <br>
        <select name="profilkep_id" id="profilkep_id">
            <option value="1" <?php if ($profilkep_id == 1) echo "selected"; ?>>Üres profilkép</option>
            <option value="2" <?php if ($profilkep_id == 2) echo "selected"; ?>>Nő</option>
            <option value="3" <?php if ($profilkep_id == 3) echo "selected"; ?>>Férfi</option>
        </select>

        <!-- Név -->
        <h3>Név</h3>
        <input type="text" placeholder="<?php echo htmlspecialchars($nev !== "" ? $nev : 'Név'); ?>" name="nev" value="<?php echo htmlspecialchars($nev); ?>">
        <br>
        <label>A név minimum 3, maximum 8 karakter lehet, <br>
                nem tartalmazhat speciális karaktert.</label>
        <br>

        <!-- Email-cím -->
        <h3>Email-cím</h3>
        <input type="text" placeholder="<?php echo htmlspecialchars($email !== "" ? $email : 'Email cím'); ?>" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <!-- Jelszó -->
        <h3>Jelszó</h3>
        <input type="password" placeholder="jelszó" name="jelszo" value="<?php echo htmlspecialchars($jelszo); ?>">
        <br>
        <label>A jelszó minimum 4 karakter lehet, nem tartalmazhat speciális karaktert, <br>
                minimum 1 nagy karaktert kell tartalmaznia.</label>
        <br>

        <!-- Jelszó Megerősítése-->
        <h3>Jelszó megerősítése</h3>
        <input type="password" placeholder="jelszó megerősítése" name="jelszo_megerosit" value="<?php echo htmlspecialchars($jelszo); ?>">

        <input type="submit" value="Adatok módosítása" class="button">
    </form>
    </div>
</div>

</body>
</html>
