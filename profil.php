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
      session_start();
      
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
      <input type="text" placeholder="Név" name="nev">

      <h3>Email-cím</h3>
      <input type="text" placeholder="Email cím" name="email">

      <h3>Testsúly</h3>
      <input type="text" placeholder="Testsúly" name="testsuly">

      <h3>Magasság</h3>
      <input type="text" placeholder="Magasság" name="magassag">

      <h3>Cél</h3>
      <select name="cel" id="cel">
        <option value="1">Nincs cél</option>
        <option value="2">Szintentartás</option>
        <option value="3">Fogyás</option>
        <option value="4">Tömegelés</option>
      </select>

      <input type="submit" value="Adatok módosítása" class="button">

      </form>
    </div>
</div>

</body>
</html>
