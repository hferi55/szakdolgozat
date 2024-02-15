<?php
  require("sql.php");

  $bejelentkezve = isset($_POST['email']) && !empty($_POST['email']);

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
                <a href="#">Rólunk</a> |
                <a href="profil.php">Profil</a> |
                <a href="etrendkeszitese.php">Étrendkészítése</a> 
            </nav>
    </header>

<div class="container">
    <div class="kartya">
      <header>Rólunk</header>
      <form action="" method="post">
          <label>Információk</label>
      </form>
    </div>
</div>

</body>
</html>
