<?php
require("sql.php");
session_start();

// Változók inicializálása
$nev = $email = $jelszo = ""; // Alapértelmezett értékek
$profilkep_id = 1; // Alapértelmezett profilkép azonosító

// Ellenőrzés, hogy a form elküldésre került-e
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Az űrlap adatainak feldolgozása

    // Név
    $nev = $_POST["nev"];

    // Email-cím
    $email = $_POST["email"];

    // Jelszó
    $jelszo = $_POST["jelszo"];

    // Jelszó hashelése
    $hashelt_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

    // Profilkép azonosító
    $profilkep_id = $_POST["profilkep_id"];

    // Az adatok frissítése a session-ben
    $_SESSION['nev'] = $nev;
    $_SESSION['email'] = $email;
    $_SESSION['jelszo'] = $jelszo;
    $_SESSION['profilkep_id'] = $profilkep_id;

    // Az adatok frissítése az adatbázisban
    $sqlUpdate = "UPDATE felhasznalo SET nev=?, email_cim=?, jelszo=? WHERE felhasznalo_id=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sssi", $nev, $email, $hashelt_jelszo, $_SESSION['felhasznalo_id']);

    if ($stmtUpdate->execute()) {
        echo "Adataid sikeresen frissítve.";
    } else {
        echo "Hiba történt az adatok frissítése során.";
    }
}

// A kiválasztott profilkép elérési útvonala
switch ($profilkep_id) {
    case 1:
        $profilkep = "profilkepek/ferfi.jpg";
        break;
    case 2:
        $profilkep = "profilkepek/no.jpg";
        break;
    case 3:
        $profilkep = "profilkepek/uresprofilkep.png";
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
      <form action="" method="post">

      <!-- Profilkép megjelenítése -->
      <img src="<?php echo htmlspecialchars($profilkep); ?>" alt="Profilkép" class="profilkep">
      <br>
      <!-- Profilkép kiválasztása -->
      <label for="profilkep_id">Profilkép kiválasztása:</label>
      <br>
      <select name="profilkep_id" id="profilkep_id">
          <option value="1" <?php if ($profilkep_id == 1) echo "selected"; ?>>Ferfi</option>
          <option value="2" <?php if ($profilkep_id == 2) echo "selected"; ?>>No</option>
          <option value="3" <?php if ($profilkep_id == 3) echo "selected"; ?>>Üres profilkép</option>
      </select>

        <!-- Név -->
        <h3>Név</h3>
        <input type="text" placeholder="<?php echo htmlspecialchars($nev !== "" ? $nev : 'Név'); ?>" name="nev" value="<?php echo htmlspecialchars($nev); ?>">

        <!-- Email-cím -->
        <h3>Email-cím</h3>
        <input type="text" placeholder="<?php echo htmlspecialchars($email !== "" ? $email : 'Email cím'); ?>" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <!-- Jelszó -->
        <h3>Jelszó</h3>
        <input type="text" placeholder="<?php echo htmlspecialchars($jelszo !== "" ? $jelszo : 'Jelszó'); ?>" name="jelszo" value="<?php echo htmlspecialchars($jelszo); ?>">

      <input type="submit" value="Adatok módosítása" class="button">
      </form>
    </div>
</div>

</body>
</html>
