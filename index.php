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

<div class="container">
    <div class="regisztracio kartya">
      <header>Regisztráció</header>
      <form action="regisztracio.php" method="post">
          <label>Még nincs fiókja?</label>
          <input type="submit" class="button" value="Regisztráció" name="submit">
      </form>

      <form action="bejelentkezes.php" method="post">
          <label>Már van fiókja?</label>
          <input type="submit" class="button" value="Bejelentkezés" name="submit">
      </form>
    </div>
</div>

</body>
</html>