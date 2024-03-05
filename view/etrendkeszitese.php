<?php
  require("../sql/sql.php");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE-edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Étrendkészítő Weboldal</title>
  <!-- CSS -->
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <header class="fooldalheader">
        <h1 class="cim">ÉKW</h1>
            <nav class="navbar">
                <a href="../view/rolunk2.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrendkeszitese.php">Étrendkészítése</a> |
                <a href="../logout.php">Kijelentkezés</a>
            </nav>
    </header>



<div class="lap">
    <div class="kartya">
      <header>Étrendkészítése</header>
      <form action="" method="post">
        <!-- Testsúly -->
        <h3>Testsúly:</h3>
        <input type="number" placeholder="Adja meg a testsúlyát" name="testsuly">
        <br>

        <!-- Életkor -->
        <h3>Életkor:</h3>
        <input type="number" placeholder="Adja meg az életkorát" name="eletkor">
        <br>
         
      </form>
    </div>
</div>

</body>
</html>