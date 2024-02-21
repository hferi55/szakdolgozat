<?php
require("sql.php");
session_start();

// Változók inicializálása
$nev = $email = $testsuly = $magassag = $cel = "";

// Ellenőrzés, hogy a form elküldésre került-e
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Az űrlap adatainak feldolgozása

    // Név
    $nev = $_POST["nev"];

    // Email-cím
    $email = $_POST["email"];

    // Testsúly
    $testsuly = $_POST["testsuly"];

    // Magasság
    $magassag = $_POST["magassag"];

    // Cél
    $cel = $_POST["cel"];

    // Az adatok frissítése az adatbázisban
    $sqlUpdate = "UPDATE users SET nev=?, email=?, testsuly=?, magassag=?, cel=? WHERE felhasznalo_id=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sssssi", $nev, $email, $testsuly, $magassag, $cel, $_SESSION['felhasznalo_id']);

    if ($stmtUpdate->execute()) {
        echo "Adataid sikeresen frissítve.";
    } else {
        echo "Hiba történt az adatok frissítése során.";
    }
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
      <form action="profilkep_feltoltes.php" method="post" enctype="multipart/form-data">

      <h3>Profilkép</h3>
      <?php
      
      // Profilkép elérési útjának lekérése a session-ből
      $profilkep = isset($_SESSION['profilkep']) ? $_SESSION['profilkep'] : "profilkepek/uresprofilkep.png";
      ?>
      
      <!-- A profilkep változó használata a profilkép megjelenítéséhez -->
      <img src="<?php echo htmlspecialchars($profilkep); ?>" alt="Profilkép" class="profilkep">

      <br>
      <!-- Fájlfeltöltő input elem hozzáadása -->
      <label for="profilkep_feltoltes">Profilkép kicserélése:</label>
      <input type="file" id="profilkep_feltoltes" name="profilkep" accept="image/*">
      <br>

            <h3>Név</h3>
            <input type="text" placeholder="Név" name="nev" value="<?php echo htmlspecialchars($nev); ?>">

            <h3>Email-cím</h3>
            <input type="text" placeholder="Email cím" name="email" value="<?php echo htmlspecialchars($email); ?>">

            <h3>Testsúly</h3>
            <input type="text" placeholder="Testsúly" name="testsuly" value="<?php echo htmlspecialchars($testsuly); ?>">

            <h3>Magasság</h3>
            <input type="text" placeholder="Magasság" name="magassag" value="<?php echo htmlspecialchars($magassag); ?>">

            <h3>Cél</h3>
            <select name="cel" id="cel">
                <option value="1" <?php if ($cel == 1) echo "selected"; ?>>Nincs cél</option>
                <option value="2" <?php if ($cel == 2) echo "selected"; ?>>Szintentartás</option>
                <option value="3" <?php if ($cel == 3) echo "selected"; ?>>Fogyás</option>
                <option value="4" <?php if ($cel == 4) echo "selected"; ?>>Tömegelés</option>
            </select>

            <input type="submit" value="Adatok módosítása" class="button">

        </form>
    </div>
</div>

</body>
</html>
