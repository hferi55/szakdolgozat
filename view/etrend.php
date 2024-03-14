<?php
session_start();

require("../sql/sql.php");

if (isset($_SESSION['etrend_keszites_sikeres']) && $_SESSION['etrend_keszites_sikeres'] === true) {
    
    // További műveletek az étrend sikeres elkészítése esetén

    // Ne felejtsük el törölni a munkamenet változót, hogy ne jelenjen meg újra az üzenet frissítéskor
    unset($_SESSION['etrend_keszites_sikeres']);
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
            // Ellenőrizzük, hogy van-e már étrendje
            $query = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '' OR cel = 'nincs cel' OR nem = 'valasszon' OR aktivitas = 'valasszon')";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);
            $etrendVan = $row[0] == 0;

            if ($etrendVan) {
                echo '
                <a href="../view/rolunk.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrend.php">Étrendem</a> |
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
        ?>
    </nav>
</header>



<div class="etrend_lap">
    <header>Étrendem</header>
    

    
    <div class="adatok">
      <form action="" method="post">
      <?php
        // Lekérdezés az adatok megjelenítéséhez
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $sqlQuery = "SELECT `testsuly`, `magassag`, `cel`, `nem`, `eletkor`, `aktivitas` FROM felhasznalo WHERE felhasznalo_id=?";
        $stmtQuery = $conn->prepare($sqlQuery);
        $stmtQuery->bind_param("i", $felhasznalo_id);
        $stmtQuery->execute();
        $stmtQuery->store_result();
        $stmtQuery->bind_result($testsuly, $magassag, $cel, $nem, $eletkor, $aktivitas);

        if (!$stmtQuery->fetch()) {
            $errors[] = "Hiba történt az adatok lekérdezése során.";
        }

        $stmtQuery->close();

        if($nem == 'Férfi'){
        $bmr = 10 * $testsuly + 6.25 * $magassag - 5 * $eletkor + 5; //Férfi BMR kiszámítás Mifflin-St. Jeor képlettel
        } elseif($nem == 'Nő'){
        $bmr = 10 * $testsuly + 6.25 * $magassag - 5 * $eletkor - 161; //Női BMR kiszámítás Mifflin-St. Jeor képlettel
        }

        if($aktivitas == 'Inaktív'){
            $fogyasztando = $bmr * 1.2;
        } elseif ($aktivitas == 'Kevésbé aktív'){
            $fogyasztando = $bmr * 1.375;
        } elseif ($aktivitas == 'Mérsékelten aktív'){
            $fogyasztando = $bmr * 1.55;
        } elseif ($aktivitas == 'Aktív'){
            $fogyasztando = $bmr * 1.725;
        } elseif ($aktivitas == 'Nagyon aktív'){
            $fogyasztando = $bmr * 1.9;
        }

        if($cel == 'Szintentartás'){
            $fogyasztando = $fogyasztando * 1;
        } elseif ($cel == 'Fogyás'){
            $fogyasztando = $fogyasztando-500;
        } elseif ($cel == 'Tömegelés'){
            $fogyasztando = $fogyasztando+500;
        }


        if(isset($_POST['adatmodositas'])) {
            header("Location: etrendkeszitese.php");
            exit();
        }

    ?>
        <header>Adatok</header>

        <p><b>Életkor: </b><?php echo htmlspecialchars($eletkor); ?> éves</p>
        <p><b>Testsúly: </b><?php echo htmlspecialchars($testsuly); ?> kg</p>
        <p><b>Magasság: </b><?php echo htmlspecialchars($magassag); ?> cm</p>
        <p><b>Nem: </b><?php echo htmlspecialchars($nem); ?> </p>
        <p><b>Aktivitási szint: </b><?php echo htmlspecialchars($aktivitas); ?> </p>
        <p><b>Cél: </b><?php echo htmlspecialchars($cel); ?> </p>
        <p><b>BMR (alapmetabolikus ráta): </b> <?php echo htmlspecialchars($bmr); ?> kcal</p>
        <p><b>Fogyasztandó kalória száma: </b> <?php echo htmlspecialchars($fogyasztando); ?> kcal</p>

        <input type="submit" class="button" value="Adatok módosítása" name="adatmodositas">

        <p><b>
            A jobb oldalon található képekből kérem jelöljön 
        </b></p>
        <p><b>
            meg legalább kettő olyan ételt amit kedvel
        </b></p>
        </form>
    </div>
    
    <header>Preferencia</header>

    <!-- Reggeli -->
    <div class="reggeli"> 
        <h3>Reggeli:</h3>
        <img src="../kepek/reggeli1.jpg" class="kepek" alt="Zabkása gyümölccsel és mandulával">
        <img src="../kepek/reggeli2.jpg" class="kepek" alt="Görög joghurt gyümölcsökkel és mézzel">
        <img src="../kepek/reggeli3.jpg" class="kepek" alt="Tojásrántotta zöldségekkel">
        <img src="../kepek/reggeli4.jpg" class="kepek" alt="Avokádós teljes kiőrlésű pirítós">
        <img src="../kepek/reggeli5.jpg" class="kepek" alt="Banános zabkeksz">
        <img src="../kepek/reggeli6.jpg" class="kepek" alt="Chia-puding gyümölcsökkel">
        <img src="../kepek/reggeli7.jpg" class="kepek" alt="Túrós zabkeksz">
        <img src="../kepek/reggeli8.jpg" class="kepek" alt="Birsalma és fahéjas zabkása">
        <img src="../kepek/reggeli9.jpg" class="kepek" alt="Avokádó és paradicsom omlett">
        <img src="../kepek/reggeli10.jpg" class="kepek" alt="Gyors smoothie tál">

    </div>

    <!-- Ebéd -->
    <div class="ebed">
        <h3>Ebéd:</h3>
        <img src="../kepek/ebed1.jpg" class="kepek" alt="Sült csirke salátával">
        <img src="../kepek/ebed2.jpg" class="kepek" alt="Quinoa zöldségekkel">
        <img src="../kepek/ebed3.jpg" class="kepek" alt="Lencseleves spenóttal">
        <img src="../kepek/ebed4.jpg" class="kepek" alt="Tonhalas teljes kiőrlésű wrap">
        <img src="../kepek/ebed5.jpg" class="kepek" alt="Sült lazac édesburgonyával">
        <img src="../kepek/ebed6.jpg" class="kepek" alt="Színes borsókrémleves">
        <img src="../kepek/ebed7.jpg" class="kepek" alt="Szezámmagos csirke saláta">
        <img src="../kepek/ebed8.jpg" class="kepek" alt="Quinoa saláta fetával és görög olívával">
        <img src="../kepek/ebed9.jpg" class="kepek" alt="Zöldséges tojás wrap">
        <img src="../kepek/ebed10.jpg" class="kepek" alt="Sütőben sült lazac spárgával">

    </div>

    <!-- Vacsora -->
    <div class="vacsora">
        <h3>Vacsora:</h3>
        <img src="../kepek/vacsora1.jpg" class="kepek" alt="Vegetáriánus csicseriborsó curry">
        <img src="../kepek/vacsora2.jpg" class="kepek" alt="Grillezett zöldségek tofuval">
        <img src="../kepek/vacsora3.jpg" class="kepek" alt="Sült csirkecomb sült zöldségekkel">
        <img src="../kepek/vacsora4.jpg" class="kepek" alt="Brokkoli spagetti fokhagymás olívaolajjal">
        <img src="../kepek/vacsora5.jpg" class="kepek" alt="Sushi tál lazaccal és zöldségekkel">
        <img src="../kepek/vacsora6.jpg" class="kepek" alt="Vegetáriánus lencsédal">
        <img src="../kepek/vacsora7.jpg" class="kepek" alt="Fűszeres csirke curry">
        <img src="../kepek/vacsora8.jpg" class="kepek" alt="Brokkoli-karfiol pite">
        <img src="../kepek/vacsora9.jpg" class="kepek" alt="Mexikói csirke quinoa-val">
        <img src="../kepek/vacsora10.jpg" class="kepek" alt="Grillezett hal filé édesburgonya pürével">

    </div>

    <!-- Uzsonna -->
    <div class="uzsonna">
        <h3>Uzsonna:</h3>
        <img src="../kepek/uzsonna1.jpg" class="kepek" alt="Mandula és mazsola mix">
        <img src="../kepek/uzsonna2.jpg" class="kepek" alt="Görög joghurt gyümölcssaláttal">
        <img src="../kepek/uzsonna3.jpg" class="kepek" alt="Almás mogyoróvaj szendvics">
        <img src="../kepek/uzsonna4.jpg" class="kepek" alt="Zöldségek hummusszal">
        <img src="../kepek/uzsonna5.jpg" class="kepek" alt="Banános és epres smoothie">
        <img src="../kepek/uzsonna6.jpg" class="kepek" alt="Szezámmagos alma szeletek">
        <img src="../kepek/uzsonna7.jpg" class="kepek" alt="Gyümölcsös joghurt pohárban">
        <img src="../kepek/uzsonna8.jpg" class="kepek" alt="Teljes kiőrlésű kenyér paradicsomsalátával">
        <img src="../kepek/uzsonna9.jpg" class="kepek" alt="Avokádó toast tojással">
        <img src="../kepek/uzsonna10.jpg" class="kepek" alt="Céklás és répás smoothie">      

    </div>
</div>

</body>
</html>