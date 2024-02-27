<?php
  require("sql.php");
  session_start();
  
  // Ellenőrzés, hogy a felhasználó be van-e jelentkezve
  if (!isset($_SESSION['felhasznalo_id'])) {
      header("Location: bejelentkezes.php"); // Változtasd meg a céloldalt a bejelentkezési oldalra
      exit();
  }
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE-edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Étrendkészítő Weboldal</title>
  <!-- CSS -->
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

<div class="lap">
    <div class="kartya">
      <header>Főoldal</header>
      <form action="" method="post">
          <label>Főoldal</label>
         
      </form>
    </div>
</div>

</body>
</html>