<?php
session_start(); // Munkamenet inicializálása

require("../sql/sql.php");

// Hibaüzenet
$error_message = '';

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
            $error_message = 'Kérjük, töltse ki az összes mezőt!';
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
                $error_message = 'Hiba történt az adatok frissítése közben. Kérjük, próbálja újra.';
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
            // Ellenőrizzük, hogy vannak-e kiválasztott képek a SESSION-ben
            if (isset($_SESSION['selected_images']) && !empty($_SESSION['selected_images'])) {
                echo '
                <a href="../view/rolunk.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrendem.php">Étrendem</a> |
                <a href="../logout.php">Kijelentkezés</a>
                ';
            } else {
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



<div class="profil_lap">
    <div class="kartya">
        <header>Étrendkészítése</header>

        <?php
            // Hibaüzenet megjelenítése
            if (!empty($error_message)) {
                echo '<div class="hiba-uzenetek">';
                echo '<ul>';
                echo '<li>' . $error_message . '</li>';
                echo '</ul>';
                echo '</div>';
            }
        ?>

        <form action="" method="post">

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

    <!--
            Reggeli és Uzsonna:
                Zabkása gyümölccsel és mandulával recept: https://diet.hu/dietas-zabkasa-recept/
                Görög joghurt gyümölcsökkel és mézzel recept: https://cookpad.com/hu/receptek/16746502-afonyamalnagorogjoghurt?ref=search&search_term=g%C3%B6r%C3%B6g%20joghurt%20reggeli
                Tojásrántotta zöldségekkel recept: https://tojas.info.hu/zoldseges-rantotta/
                Avokádós teljes kiőrlésű pirítós recept: https://cookpad.com/hu/receptek/6942934-villamgyors-avokados-piritos
                Banános zabkeksz recept: https://babamamaegyutteszik.hu/recept/bananos-zabkeksz/
                Chia-puding gyümölcsökkel recept: https://femina.hu/recept/chia-puding-bogyos-gyumolcsokkel/
                Túrós zabkeksz recept: https://www.nosalty.hu/recept/zabpelyhes-turos-keksz
                Birsalma és fahéjas zabkása recept: https://streetkitchen.hu/instant/almas-fahejas-zabkasa/
                Avokádó és paradicsom omlett recept: https://zest.hu/omlett-avokadoval-es-paradicsommal/
                Gyors smoothie tál recept: https://sobors.hu/receptek/smoothie-tal-recept/
                
                Zöldségek hummusszal recept: https://adriskitchen.hu/receptek/olvasas/szines-zoldseges-humusz-variaciok
                Almás mogyoróvaj szendvics recept: https://www.origo.hu/tafelspicc/2012/09/almasmogyorovajas-szendvics
                Banános és epres smoothie recept: https://www.mindmegette.hu/epres-bananos-turmix.recept/
                Gyümölcsös joghurt pohárban recept: https://cookpad.com/hu/receptek/16211932-afonyas-joghurtos-poharkrem?ref=search&search_term=joghurtos%20poh%C3%A1rkr%C3%A9m
                Céklás és répás smoothie recept: https://cookpad.com/hu/receptek/16768339-ceklas-smoothie-turmix?ref=search&search_term=c%C3%A9kla%20smoothie
                
            Ebéd és Vacsora:
                Rántott csirkemell recept: https://www.mindmegette.hu/rantott-csirkemell-vajas-burgonyapurevel.recept/
                Natúr csirkemell recept: https://www.mindmegette.hu/gyors-fuszeres-csirkemell.recept/
                Rántott sertésszelet recept: https://www.nosalty.hu/recept/tokeletes-rantott-hus
                Rántott marhaszelet recept: https://cookpad.com/hu/receptek/15415552-forditott-marhaszelet
                Steak recept: https://femina.hu/recept/igy_susd_meg_a_tokeletes_steaket/
                Rizs recept: https://www.nosalty.hu/recept/elronthatatlan-rizs
                Barna rizs recept: https://www.nosalty.hu/recept/lime-os-barna-rizs
                Saláta recept: https://www.mindmegette.hu/ecetes-fejes-salata.recept/
                Sütőben sült lazac spárgával recept: https://magazin.klarstein.hu/receptek/lazac-zoeld-spargaval/
                Sült édesburgonya recept: https://www.mindmegette.hu/sult-edesburgonya.recept/
                Sült hasábburgonya recept: https://www.mindmegette.hu/hasabburgonya-sutoben-sutve.recept/
                Krumplipüré recept: https://www.mindmegette.hu/krumplipure-alaprecept.recept/
                Rántott tofu recept: https://streetkitchen.hu/green-kitchen/vegan/vegan-csirkemellcsikok/
                Bolognai spagetti recept: https://www.mindmegette.hu/az-en-bolognai-spagettim.recept/
                Karfiolfasírt recept: https://streetkitchen.hu/green-kitchen/szezon/karfiol-receptek/karfiol-fasirt/
    -->

      </form>
    </div>
</div>

</body>
</html>
