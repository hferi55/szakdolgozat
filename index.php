<?php
  require("sql.php");
  
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible"content="IE-edge">;
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Étrendkészítő Weboldal</title>
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <input type="checkbox" id="check"> 
    <div class="bejelentkezes kartya">
      <header>Bejelentkezés</header>
      <form action="bejelentkezes.php" method="post">
        <input type="text" placeholder="Adja meg az email címét" name="email">
        <input type="password" placeholder="Adja meg a jelszavát" name="jelszo">
        <input type="submit" class="button" value="Bejelentkezés" name="submit">
      </form>
      <div class="signup">
        <span class="signup">Még nincs fiókja?
         <label for="check">Regisztráció</label>
        </span>
      </div>
    </div>

    <div class="regisztracio kartya">
      <header>Regisztráció</header>
      <form action="regisztracio.php" method="post">
          <input type="text" placeholder="Adja meg a nevét" name="nev">
          <input type="text" placeholder="Adja meg az email címét" name="email">
          <input type="password" placeholder="Adja meg a jelszavát" name="jelszo">
          <input type="password" placeholder="Erősítse meg a jelszavát" name="jelszo_megerosit">
          <input type="button" class="button" value="Regisztráció" name="submit">
      </form>
      <div class="signup">
        <span class="signup">Már van fiókja?
         <label for="check">Bejelentkezés</label>
        </span>
      </div>
    </div>
</div>

</body>
</html>