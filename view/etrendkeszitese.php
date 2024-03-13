<?php
session_start(); // Munkamenet inicializálása

require("../sql/sql.php");


if(isset($_POST['submit'])) {
    // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
    if (isset($_SESSION['felhasznalo_id'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $eletkor = $_POST['eletkor'];
        $testsuly = $_POST['testsuly'];
        $magassag = $_POST['magassag'];
        $nem = $_POST['nem'];
        $aktivitas = $_POST['aktivitas'];
        $cel = $_POST['cel'];

        // Ellenőrizze, hogy minden mező kitöltve van-e
        if(empty($eletkor) || empty($testsuly) || empty($magassag) || empty($nem) || empty($aktivitas) || empty($cel)) {
            echo "<script>alert('Kérjük, töltse ki az összes mezőt!');</script>";
        } else {
            // Az SQL lekérdezés összeállítása és végrehajtása
            $query = "UPDATE `felhasznalo` SET `eletkor`='$eletkor', `testsuly`='$testsuly', `magassag`='$magassag', `nem`='$nem', `aktivitas`='$aktivitas', `cel`='$cel' WHERE `felhasznalo_id`='$felhasznalo_id'";                  
            mysqli_query($conn, $query);

            // Ellenőrizzük, hogy sikeresen frissítettük-e az adatokat
            if(mysqli_affected_rows($conn) > 0) {
                //Ha sikeresen frissítetük az adatokat át dob az etrend.php oldalra
                $_SESSION['etrend_keszites_sikeres'] = true;
                header("Location: etrend.php");
                exit();
            } else {
                echo "<script>alert('Hiba történt az adatok frissítése közben. Kérjük, próbálja újra.');</script>";
            }
        }
    } else {
        header("Location: bejelentkezes.php");
        exit();
    }
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

    <style>
        .lap {
            overflow: auto;
        }

        .adatok {
            float: left;
            width: 40%;
        }

        .etelek {
            overflow-x: auto;
            white-space: nowrap;
            margin-top: 10px;
        }

        .kepek {
            width: 200px; /* Kép szélessége */
            height: auto;
            margin: 5px; /* Képek közötti margó */
            display: inline-block;
            vertical-align: top;
            border-radius: 50px; /* Itt állítsd be a kívánt sugárt */
        }

    </style>
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
            $query = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '')";
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


<div class="lap">
    <div class="kartya">
        <header>Étrendkészítése</header>
        <form action="" method="post">
            <div class="adatok">

                <!-- Életkor -->
                <h3>Életkor:</h3>
                <input type="number" placeholder="Adja meg az életkorát" name="eletkor">
                <br>

                <!-- Testsúly -->
                <h3>Testsúly:</h3>
                <input type="number" placeholder="Adja meg a testsúlyát" name="testsuly">
                <br>

                <!-- Magasság -->
                <h3>Magasság:</h3>
                <input type="number" placeholder="Adja meg a magasságát" name="magassag">
                <br>

                <!-- Nem -->
                <h3>Nem:</h3>
                <select name="nem" id="nem">
                    <option value="1">Válasszon</option>
                    <option value="2">Nő</option>
                    <option value="3">Férfi</option>
                </select>
                <br>

                <!-- Aktivitási szint -->
                <h3>Aktivitási szint:</h3>
                <select name="aktivitas" id="aktivitas">
                    <option value="1">Válasszon</option>
                    <option value="2">Inaktív</option>
                    <option value="3">Kevésbé aktív</option>
                    <option value="4">Mérsékelten aktív</option>
                    <option value="5">Aktív</option>
                    <option value="6">Nagyon aktív</option>
                </select>
                <br>

                <!-- Cél -->
                <h3>Cél:</h3>
                <select name="cel" id="cel">
                    <option value="1">Válasszon</option>
                    <option value="2">Szintentartás</option>
                    <option value="3">Fogyás</option>
                    <option value="4">Tömegelés</option>
                </select>

                <!-- Gomb -->
                <input type="submit" class="button" value="Étrendkészítése" name="submit">

            </div>

    <!--
                <div class="etelek">
                 Reggeli 
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

                 Ebéd 
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

               Vacsora 
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


               Uzsonna 
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
    -->
      </form>
    </div>
</div>

</body>
</html>
