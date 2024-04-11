<?php
require("../sql/sql.php");
session_start(); // Session inicializálása

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
        <?php
        // Ha a felhasználó nincs bejelentkezve
        if (!isset($_SESSION['felhasznalo_id'])) {
            echo '
            <a href="../view/rolunk.php">Rólunk</a> |
            <a href="../view/bejelentkezes.php">Bejelentkezés</a> |
            <a href="../view/regisztracio.php">Regisztráció</a>
            ';
        } else { // Ha a felhasználó be van jelentkezve
            require("../sql/sql.php");
            $felhasznalo_id = $_SESSION['felhasznalo_id'];

            // Ellenőrizzük az adatbázisban, hogy van-e kiválasztott kép
            $query = "SELECT kivalasztott_kepek FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $kivalasztott_kepek = $row['kivalasztott_kepek'];

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
                $query = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '' OR cel = 'nincs cel' OR nem = 'valasszon' OR aktivitas = 'valasszon')";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_row($result);
                $etrendVan = $row[0] == 0;

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



<div class="lap">
    <div class="kartya">
    <header>Rólunk</header>
      <form action="" method="post">
            <article>
                <h2>Üdvözöllek az ÉtrendKészítő Weboldalon!</h2>
                <p>Az ÉtrendKészítő Weboldal egy innovatív és egészséges életmódra összpontosító platform, amely segít felhasználóinak tudatos és kiegyensúlyozott táplálkozás elérésében. 
                    Ezen az oldalon célunk, hogy könnyen elérhetővé tegyük a személyre szabott étrendtervezést, így mindenki megtalálhatja az igényeinek és céljainak megfelelő étrendet.</p>
            </article>
            <article>
                <h2>Főbb szolgáltatások:</h2>
                <p>1. Személyre szabott étrendtervezés: A felhasználók egyéni igényeinek és céljainak megfelelően kialakított étrendeket készíthetnek. 
                    Legyen szó fogyásról, izomépítésről vagy egyszerűen az egészséges életmód megtartásáról, nálunk megtalálhatod a megfelelő táplálkozási tervet.</p>
                <p>2. Receptek és ételajánlatok: Gazdag receptgyűjteményünkben változatos és egészséges ételajánlatokat találhatsz. 
                    Könnyen követhető receptekkel és táplálkozási információkkal segítünk abban, hogy finom és tápláló ételeket készíts otthon.</p>
                <p>3. Étkezési napló vezetése: Az étkezési napló vezetése segít nyomon követni a bevitt kalóriákat, tápanyagokat és a folyadékfogyasztást. 
                    Ezáltal könnyebben megtervezheted és ellenőrizheted az étkezéseidet.</p>
                <p>4. Egészséges életmód támogatás: Információkkal és tippekkel látunk el a helyes táplálkozásról, a testmozgás fontosságáról és az általános egészséges életmódról. 
                    Cikkeink és tanácsaink segítenek abban, hogy megtaláld a számodra legmegfelelőbb életmódot.</p>
                <p>5. Közösségi tér: Csatlakozz a közösséghez, ahol megoszthatod tapasztalataidat, receptjeidet, és inspirálódhatsz másoktól. 
                    A közösség segítséget nyújt és motivációt ad az egészséges életmódhoz vezető úton.</p>
            </article>
            <article>
                <h2>Az ÉtrendKészítő Weboldal még...</h2>
                <p>Az ÉtrendKészítő Weboldal elkötelezett abban, hogy támogassa a felhasználókat az egészséges életmód elérésében, és egy egyszerű, interaktív felülettel segítse őket az étrendjük optimalizálásában. 
                    Legyen szó fittség eléréséről, súlyvesztésről vagy csak az egészséges életmód fenntartásáról, nálunk mindenki megtalálhatja a számára ideális megoldást. 
                    Csatlakozz hozzánk és indulj el az egészséges és boldog élet felé!</p>
            </article>

      </form>
    </div>
</div>

</body>
</html>
