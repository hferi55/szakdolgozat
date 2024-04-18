<?php
  require("sql/sql.php");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étrend Készítő Weboldal</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <?php
        // Ha a felhasználó nincs bejelentkezve
        if (!isset($_SESSION['felhasznalo_id'])) {
            echo '
            <a href="view/rolunk.php">Rólunk</a> |
            <a href="view/bejelentkezes.php">Bejelentkezés</a> |
            <a href="view/regisztracio.php">Regisztráció</a>
            ';
        } else { // Ha a felhasználó be van jelentkezve
            require("../sql/sql.php");
            $felhasznalo_id = $_SESSION['felhasznalo_id'];

            // Ellenőrizzük az adatbázisban, hogy van-e kiválasztott kép
            $keres = "SELECT kivalasztott_kepek FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id";
            $valasz = mysqli_query($conn, $keres);
            $sor = mysqli_fetch_assoc($valasz);
            $kivalasztott_kepek = $sor['kivalasztott_kepek'];

            if (!empty($kivalasztott_kepek)) {
                // Ha van kiválasztott kép, megjelenítjük az "Étrendem" linket
                echo '
                <a href="../view/rolunk.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrendem.php">Étrendem</a> |
                <a href="../logout.php">Kijelentkezés</a>
                ';
            } else {
                // Ha nincs kiválasztott kép, a korábbi logikához hasonlóan jelenítjük meg a linkeket
                // Ellenőrizzük, hogy van-e már étrendje
                $keres = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '' OR cel = 'nincs cel' OR nem = 'valasszon' OR aktivitas = 'valasszon')";
                $valasz = mysqli_query($conn, $keres);
                $sor = mysqli_fetch_row($valasz);
                $etrendVan = $sor[0] == 0;

                if ($etrendVan) {
                    echo '
                    <a href="../view/rolunk.php">Rólunk</a> |
                    <a href="../view/profil.php">Profil</a> |
                    <a href="../view/etrend.php">Étrend</a> |
                    <a href="../logout.php">Kijelentkezés</a>
                    ';
                } else {
                    echo '
                    <a href="../view/rolunk.php">Rólunk</a> |
                    <a href="../view/profil.php">Profil</a> |
                    <a href="../view/etrendkeszitese.php">Étrendkészítés</a> |
                    <a href="../logout.php">Kijelentkezés</a>
                    ';
                }
            }
        }
        ?>
    </nav>
</header>

<div class="fooldal_lap">
    <div class="kartya">
    <div class="diavetites-kontener">
        <div class="oldal">
            <img src="kepek/fooldalkep1.jpg" alt="Főoldalkép 1">
        </div>
        <div class="oldal">
            <img src="kepek/fooldalkep2.jpg" alt="Főoldalkép 2">
        </div>
        <div class="oldal">
            <img src="kepek/fooldalkep3.jpg" alt="Főoldalkép 3">
        </div>
        <div class="oldal">
            <img src="kepek/fooldalkep4.jpg" alt="Főoldalkép 4">
        </div>
        <div class="oldal">
            <img src="kepek/fooldalkep5.jpg" alt="Főoldalkép 5">
        </div>
    </div>
        
        <div class="szoveg">
            <header>Üdvözöljük az Étrend Készítő Weboldalon!</header>
            <form action="" method="post">
                <label>
                    Itt mindenki megtalálhatja a számára legmegfelelőbb étrendet a tudatos és egészséges táplálkozás érdekében. 
                    Személyre szabott étrendtervezésünkkel könnyen elérheti céljait, legyen szó fittség eléréséről, súlyvesztésről vagy egyszerűen az egészséges életmód fenntartásáról.
                    Fedezze fel változatos receptjeinket és kezdje el most az egészséges és boldog élet útját velünk!
                    A bal oldalon az oldal funkcióiról láthat képeket.
                </label>
            </form>
        </div>
    </div>
</div>

<script>
  var oldalIndex = 0;
  oldalMutatas();

  function oldalMutatas() {
    var i;
    var oldalak = document.getElementsByClassName("oldal");
    if (oldalIndex >= oldalak.length) {oldalIndex = 0} // Az ellenőrzés ide került
    for (i = 0; i < oldalak.length; i++) {
        oldalak[i].style.display = "none";  
    }
    oldalak[oldalIndex].style.display = "block";  
    oldalIndex++;
    setTimeout(oldalMutatas, 5000); // Váltás 5 másodpercenként
  }
</script>

</body>
</html>
