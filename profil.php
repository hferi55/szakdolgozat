<?php
  require("sql.php");
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


<div class="profil_lap">
    <div class="kartya">
    <header>Profil</header>
      <form action="" method="post">

      <h3>Profilkép</h3>
      <img src="kepek/uresprofilkep.png" alt="Profilkép" class="profilkep">

      <h3>Név</h3>
      <input type="text" placeholder="Név" name="nev">

      <h3>Email-cím</h3>
      <input type="text" placeholder="Email cím" name="email">

      <h3>Jelszó</h3>
      <input type="password" placeholder="Jelszó" name="jelszo">

      <input type="submit" value="Adatok módosítása" class="button">

      </form>
    </div>
</div>

</body>
</html>