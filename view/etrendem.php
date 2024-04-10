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
                <a href="../view/etrendem.php">Étrendem</a> |
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

        </form>
    </div>
    
    <header>Étrendem</header>
        
    <!-- Reggeli -->
    <h3>Reggeli:</h3>
    <?php

        // Számoljuk ki a reggelihez szükséges kalóriát (25%-át a fogyasztandónak)
        $reggeli_kaloria = $fogyasztando * 0.25;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Ellenőrizzük, hogy a felhasználó kiválasztott-e ételeket
            if(isset($_POST["selected_images"]) && is_array($_POST["selected_images"])) {
                // Összegyűjtjük a kiválasztott ételek azonosítóit
                $selected_images = $_POST["selected_images"];
                $total_selected_calories = 0;

                // Lekérdezzük az reggeli ételek kalóriáját és összesítjük azokat
                foreach ($selected_images as $selected_image_id) {
                    $sqlQuery = "SELECT kaloria FROM etelek WHERE etel_id = ? AND reggeli = 1";
                    $stmtQuery = $conn->prepare($sqlQuery);
                    $stmtQuery->bind_param("i", $selected_image_id);
                    $stmtQuery->execute();
                    $stmtQuery->store_result();
                    $stmtQuery->bind_result($kaloria);
                
                    if ($stmtQuery->fetch()) {
                        $total_selected_calories += $kaloria;
                    }
                
                    $stmtQuery->close();
                }

                // Csökkentjük az reggeli_kaloria értékét a kiválasztott ételek kalóriáival
                $reggeli_kaloria -= $total_selected_calories;

                // Megkeressük a legnagyobb kalóriájú kiválasztott reggeli ételt az adatbázisból
                $sqlQuery = "SELECT nev, kaloria FROM etelek WHERE reggeli = 1 AND etel_id IN (" . implode(",", $selected_images) . ") ORDER BY kaloria DESC LIMIT 1";
                $stmtQuery = $conn->prepare($sqlQuery);
                $stmtQuery->execute();
                $stmtQuery->store_result();
                $stmtQuery->bind_result($legnagyobb_kaloria_nev, $legnagyobb_kaloria);

                if ($stmtQuery->fetch()) {
                    // Kiszámítjuk a szorzó faktort
                    $multiplication_factor = $reggeli_kaloria / $legnagyobb_kaloria;

                    // Szorozzuk meg a legnagyobb kalóriájú étel kalóriáját a szorzó faktorral
                    // és vonjuk ki az reggeli_kaloria értékéből
                    $reggeli_kaloria -= ($legnagyobb_kaloria * $multiplication_factor);
                }

            
    ?>

    <div class="image-container">
        <?php

                // Lekérdezzük a kiválasztott reggeli ételek képeit és címeiket az adatbázisból
                foreach ($selected_images as $selected_image_id) {
                    $sqlQuery = "SELECT nev, kep FROM etelek WHERE etel_id = ? AND reggeli = 1";
                    $stmtQuery = $conn->prepare($sqlQuery);
                    $stmtQuery->bind_param("i", $selected_image_id);
                    $stmtQuery->execute();
                    $stmtQuery->store_result();
                    $stmtQuery->bind_result($nev, $kep);

                    // Ellenőrizzük, hogy van-e eredmény
                    if ($stmtQuery->fetch()) {
                        // Megjelenítjük az étel nevét és képét
                        ?>
                        <div class="image-item">
                            <div>
                                <img src="<?php echo htmlspecialchars($kep); ?>" alt="<?php echo htmlspecialchars($nev); ?>"><br>
                                <label>
                                    <?php echo htmlspecialchars($nev); ?><br>
                                    <?php if($nev == $legnagyobb_kaloria_nev){?>
                                        <?php echo number_format($multiplication_factor + 1, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                                    <?php } else { ?>
                                        1
                                    <?php } ?>
                                        Adag
                                </label>
                            </div>
                        </div>
                        <?php
                    }
                    // Bezárjuk a lekérdezést
                    $stmtQuery->close();
                }
            }
        }
        ?>
    </div>


    <!-- Ebéd -->
    <h3>Ebéd:</h3>
    <?php

        // Számoljuk ki az ebédhez szükséges kalóriát (35%-át a fogyasztandónak)
        $ebed_kaloria = $fogyasztando * 0.35;

        // Ellenőrizzük, hogy van-e POST kérés
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Ellenőrizzük, hogy a felhasználó kiválasztott-e ételeket
            if(isset($_POST["selected_images"]) && is_array($_POST["selected_images"])) {
                // Összegyűjtjük a kiválasztott ételek azonosítóit
                $selected_images = $_POST["selected_images"];
                $total_selected_calories = 0;

                //Ebéd:

                // Kiválasztott "hús" és "köret" ételek kalóriájának inicializálása
                $hussal_kaloria = 0;
                $korettel_kaloria = 0;

                // Ellenőrizzük, hogy már választott-e húst és köretet
                $hussal_valasztva = false;
                $korettel_valasztva = false;

                // Lekérdezzük a kiválasztott ebed ételek kalóriáját az adatbázisból
                foreach ($selected_images as $selected_image_id) {
                    $sqlQuery = "SELECT nev, kaloria, milyenetel FROM etelek WHERE etel_id = ? AND ebed = 1";
                    $stmtQuery = $conn->prepare($sqlQuery);
                    $stmtQuery->bind_param("i", $selected_image_id);
                    $stmtQuery->execute();
                    $stmtQuery->store_result();
                    $stmtQuery->bind_result($nev, $kaloria, $milyenetel);
                
                    // Ellenőrizzük, hogy van-e eredmény
                    if ($stmtQuery->fetch()) {
                        // Ha még nem választottunk "húst"
                        if (!$hussal_valasztva && $milyenetel === "hús") {
                            $hussal_kaloria += $kaloria;
                            $hussal_nev = $nev;
                            $hussal_valasztva = true;
                        }
                        // Ha még nem választottunk "köretet"
                        elseif (!$korettel_valasztva && $milyenetel === "köret") {
                            $korettel_kaloria += $kaloria;
                            $korettel_nev = $nev;
                            $korettel_valasztva = true;
                        }
                    }
                
                    // Ellenőrizzük, hogy már választottunk-e mindkettőt
                    if ($hussal_valasztva && $korettel_valasztva) {
                        break;
                    }
                
                    // Bezárjuk a lekérdezést
                    $stmtQuery->close();
                }

                // Az étel kalóriáinak összege
                $total_selected_calories = $hussal_kaloria + $korettel_kaloria;

                // Szorozzuk az összegzett kalóriákat, hogy az ebed_kaloria értéke 0 legyen
                if ($ebed_kaloria != 0) {
                    $multiplication_factor = $ebed_kaloria / $total_selected_calories;
                    $hussal_kaloria *= $multiplication_factor;
                    $korettel_kaloria *= $multiplication_factor;
                }

                $total_selected_calories = $hussal_kaloria + $korettel_kaloria;
                $ebed_kaloria -= $total_selected_calories;


    ?>

    <div class="image-container">
        <?php
        
                $selected_hus = [];
                $selected_koret = [];

                // Lekérdezzük a kiválasztott ebéd ételek képeit és címeiket az adatbázisból
                foreach ($selected_images as $selected_image_id) {
                    $sqlQuery = "SELECT nev, kep, milyenetel FROM etelek WHERE etel_id = ? AND ebed = 1";
                    $stmtQuery = $conn->prepare($sqlQuery);
                    $stmtQuery->bind_param("i", $selected_image_id);
                    $stmtQuery->execute();
                    $stmtQuery->store_result();
                    $stmtQuery->bind_result($nev, $kep, $milyenetel);

                    // Ellenőrizzük, hogy van-e eredmény
                    if ($stmtQuery->fetch()) {
                        // Tároljuk az összes "hús" és "köret" ételt
                        if ($milyenetel === "hús") {
                            $selected_hus[] = ["nev" => $nev, "kep" => $kep];
                        } elseif ($milyenetel === "köret") {
                            $selected_koret[] = ["nev" => $nev, "kep" => $kep];
                        }
                    }
                }
                // Bezárjuk a lekérdezést
                $stmtQuery->close();

                // Megjelenítjük az első talált "hús" ételt
                if (!empty($selected_hus)) {
                    $hus = $selected_hus[0];
                    ?>
                    <div class="image-item">
                        <div>
                            <img src="<?php echo htmlspecialchars($hus['kep']); ?>" alt="<?php echo htmlspecialchars($hus['nev']); ?>"><br>
                            <label>
                                <?php echo htmlspecialchars($hus['nev']); ?> <br>
                                <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                                    Adag
                            </label>
                        </div>
                    </div>
                    <?php
                }

                // Megjelenítjük az első talált "köret" ételt
                if (!empty($selected_koret)) {
                    $koret = $selected_koret[0];
                    ?>
                    <div class="image-item">
                        <div>
                            <img src="<?php echo htmlspecialchars($koret['kep']); ?>" alt="<?php echo htmlspecialchars($koret['nev']); ?>"><br>
                            <label>
                                <?php echo htmlspecialchars($koret['nev']); ?> <br>
                                    <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                                    Adag
                            </label>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        ?>

    </div>

<!-- Vacsora -->
<h3>Vacsora:</h3>
<?php

    // Számoljuk ki a vacsorahez szükséges kalóriát (25%-át a fogyasztandónak)
    $vacsora_kaloria = $fogyasztando * 0.25;

    // Ellenőrizzük, hogy van-e POST kérés
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ellenőrizzük, hogy a felhasználó kiválasztott-e ételeket
        if(isset($_POST["selected_images"]) && is_array($_POST["selected_images"])) {
            // Összegyűjtjük a kiválasztott ételek azonosítóit
            $selected_images = $_POST["selected_images"];
            $total_selected_calories = 0;

            //Vacsora:

            // Kiválasztott "hús" és "köret" ételek kalóriájának inicializálása
            $hussal_kaloria = 0;
            $korettel_kaloria = 0;
                    
            // Ellenőrizzük, hogy már választott-e húst és köretet
            $hussal_valasztva = false;
            $korettel_valasztva = false;

            $selected_hus = [];
            $selected_koret = [];

            // Lekérdezzük a kiválasztott vacsora ételek kalóriáját az adatbázisból
            foreach ($selected_images as $selected_image_id) {
                $sqlQuery = "SELECT nev, kaloria, milyenetel FROM etelek WHERE etel_id = ? AND vacsora = 1";
                $stmtQuery = $conn->prepare($sqlQuery);
                $stmtQuery->bind_param("i", $selected_image_id);
                $stmtQuery->execute();
                $stmtQuery->store_result();
                $stmtQuery->bind_result($nev, $kaloria, $milyenetel);
            
                // Ellenőrizzük, hogy van-e eredmény
                if ($stmtQuery->fetch()) {
                    // Tároljuk az összes "hús" és "köret" ételt
                    if ($milyenetel === "hús") {
                        $selected_hus[] = ['nev' => $nev, 'kaloria' => $kaloria];
                    } else if ($milyenetel === "köret") {
                        $selected_koret[] = ['nev' => $nev, 'kaloria' => $kaloria];
                    }
                }
            
                // Bezárjuk a lekérdezést
                $stmtQuery->close();
            }


            // Ha még nem választottunk "húst" és van elég elem a tömbben
            if (!$hussal_valasztva) {
                $hus = $selected_hus[1]; // A második hús ételt választjuk
                $hussal_kaloria += $hus['kaloria'];
                $hussal_nev = $hus['nev'];
                $hussal_valasztva = true;
            }

            // Ha még nem választottunk "köretet" és van elég elem a tömbben
            if (!$korettel_valasztva) {
                $koret = $selected_koret[1]; // A második köretet választjuk
                $korettel_kaloria += ($koret['kaloria']);
                $korettel_nev = ($koret['nev']);
                $korettel_valasztva = true;
            }

            // Az étel kalóriáinak összege
            $total_selected_calories = $hussal_kaloria + $korettel_kaloria;

            // Szorozzuk az összegzett kalóriákat, hogy az ebed_kaloria értéke 0 legyen
            if ($vacsora_kaloria != 0) {
                $multiplication_factor = $vacsora_kaloria / $total_selected_calories;
                $hussal_kaloria *= $multiplication_factor;
                $korettel_kaloria *= $multiplication_factor;
            }


            //$total_selected_calories = $hussal_kaloria + $korettel_kaloria;
            //$vacsora_kaloria -= $total_selected_calories;


?>

<div class="image-container">
    <?php

            $selected_hus = [];
            $selected_koret = [];

            // Lekérdezzük a kiválasztott ebéd ételek képeit és címeiket az adatbázisból
            foreach ($selected_images as $selected_image_id) {
                $sqlQuery = "SELECT nev, kep, milyenetel FROM etelek WHERE etel_id = ? AND ebed = 1";
                $stmtQuery = $conn->prepare($sqlQuery);
                $stmtQuery->bind_param("i", $selected_image_id);
                $stmtQuery->execute();
                $stmtQuery->store_result();
                $stmtQuery->bind_result($nev, $kep, $milyenetel);

                // Ellenőrizzük, hogy van-e eredmény
                if ($stmtQuery->fetch()) {
                    // Tároljuk az összes "hús" és "köret" ételt
                    if ($milyenetel === "hús") {
                        $selected_hus[] = ["nev" => $nev, "kep" => $kep];
                    } elseif ($milyenetel === "köret") {
                        $selected_koret[] = ["nev" => $nev, "kep" => $kep];
                    }
                }
            }
            // Bezárjuk a lekérdezést
            $stmtQuery->close();

            // Megjelenítjük a második talált "hús" ételt
            if (!empty($selected_hus)) {
                $hus = $selected_hus[1];
                ?>
                <div class="image-item">
                    <div>
                        <img src="<?php echo htmlspecialchars($hus['kep']); ?>" alt="<?php echo htmlspecialchars($hus['nev']); ?>"><br>
                        <label>
                            <?php echo htmlspecialchars($hus['nev']); ?> <br>
                            <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                                Adag
                        </label>
                    </div>
                </div>
                <?php
            }

            // Megjelenítjük a második talált "köret" ételt
            if (!empty($selected_koret)) {
                $koret = $selected_koret[1];
                ?>
                <div class="image-item">
                    <div>
                        <img src="<?php echo htmlspecialchars($koret['kep']); ?>" alt="<?php echo htmlspecialchars($koret['nev']); ?>"><br>
                        <label>
                            <?php echo htmlspecialchars($koret['nev']); ?> <br>
                            <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                                Adag
                        </label>
                    </div>
                </div>
                <?php
            }
        }
    }
    ?>
</div>

    <!-- Uzsonna -->
    <h3>Uzsonna:</h3>
    <?php

    // Számoljuk ki az uzsonnához szükséges kalóriát (15%-át a fogyasztandónak)
    $uzsonna_kaloria = $fogyasztando * 0.15;

    // Ellenőrizzük, hogy van-e POST kérés
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ellenőrizzük, hogy a felhasználó kiválasztott-e ételeket
        if(isset($_POST["selected_images"]) && is_array($_POST["selected_images"])) {
            // Összegyűjtjük a kiválasztott ételek azonosítóit
            $selected_images = $_POST["selected_images"];
            $total_selected_calories = 0;

            // Lekérdezzük az uzsonna ételek kalóriáját és összesítjük azokat
            $total_selected_calories = 0;
            foreach ($selected_images as $selected_image_id) {
                $sqlQuery = "SELECT kaloria FROM etelek WHERE etel_id = ? AND uzsonna = 1";
                $stmtQuery = $conn->prepare($sqlQuery);
                $stmtQuery->bind_param("i", $selected_image_id);
                $stmtQuery->execute();
                $stmtQuery->store_result();
                $stmtQuery->bind_result($kaloria);
            
                if ($stmtQuery->fetch()) {
                    $total_selected_calories += $kaloria;
                }
            
                $stmtQuery->close();
            }

            // Csökkentjük az uzsonna_kaloria értékét a kiválasztott ételek kalóriáival
            $uzsonna_kaloria -= $total_selected_calories;

            // Megkeressük a legnagyobb kalóriájú kiválasztott uzsonna ételt az adatbázisból
            $sqlQuery = "SELECT nev, kaloria FROM etelek WHERE uzsonna = 1 AND etel_id IN (" . implode(",", $selected_images) . ") ORDER BY kaloria DESC LIMIT 1";
            $stmtQuery = $conn->prepare($sqlQuery);
            $stmtQuery->execute();
            $stmtQuery->store_result();
            $stmtQuery->bind_result($legnagyobb_kaloria_nev, $legnagyobb_kaloria);
                    
            if ($stmtQuery->fetch()) {
                // Kiszámítjuk a szorzó faktort
                $multiplication_factor = $uzsonna_kaloria / $legnagyobb_kaloria;
                
                // Szorozzuk meg a legnagyobb kalóriájú étel kalóriáját a szorzó faktorral
                // és vonjuk ki az uzsonna_kaloria értékéből
                $uzsonna_kaloria -= ($legnagyobb_kaloria * $multiplication_factor);
            }

            

    ?>
<div class="image-container">
    <?php
            // Lekérdezzük a kiválasztott uzsonna ételek képeit és címeiket az adatbázisból
            foreach ($selected_images as $selected_image_id) {
                $sqlQuery = "SELECT nev, kep FROM etelek WHERE etel_id = ? AND uzsonna = 1";
                $stmtQuery = $conn->prepare($sqlQuery);
                $stmtQuery->bind_param("i", $selected_image_id);
                $stmtQuery->execute();
                $stmtQuery->store_result();
                $stmtQuery->bind_result($nev, $kep);
                
                // Ellenőrizzük, hogy van-e eredmény
                if ($stmtQuery->fetch()) {
                    // Megjelenítjük az étel nevét és képét
                    ?>
                    <div class="image-item">
                        <div>
                            <img src="<?php echo htmlspecialchars($kep); ?>" alt="<?php echo htmlspecialchars($nev); ?>"><br>
                            <label>
                                <?php echo htmlspecialchars($nev); ?><br>
                                <?php if($nev == $legnagyobb_kaloria_nev){?>
                                    <?php echo number_format($multiplication_factor + 1, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                                <?php } else { ?>
                                    1
                                <?php } ?>
                                    Adag
                            </label>

                        </div>
                    </div>
                    <?php
                }
                
            }
            // Bezárjuk a lekérdezést
            $stmtQuery->close();
        }
    }
    ?>
</div>

    <?php

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($_POST["selected_images"]) && is_array($_POST["selected_images"])) {
            echo '<div id="selected-images">';
            echo "<h3>Kiválasztott ételek:</h3>";
            foreach ($_POST["selected_images"] as $selected_image_id) {
                // Lekérdezzük az étel nevét és kalóriáját az adatbázisból
                $sqlQuery = "SELECT nev, kaloria FROM etelek WHERE etel_id = ?";
                $stmtQuery = $conn->prepare($sqlQuery);
                $stmtQuery->bind_param("i", $selected_image_id);
                $stmtQuery->execute();
                $stmtQuery->store_result();
                $stmtQuery->bind_result($nev, $kaloria);

                // Ellenőrizzük, hogy volt-e eredmény
                if ($stmtQuery->fetch()) {
                    // Ha volt eredmény, kiírjuk az étel nevét és kalóriáját
                    echo "<p>Etel neve: " . htmlspecialchars($nev) . ", Kalória: " . htmlspecialchars($kaloria) . "</p>";
                } else {
                    // Ha nem volt eredmény, kiírjuk csak az étel ID-ját
                    echo "<p>Etel ID: " . htmlspecialchars($selected_image_id) . "</p>";
                }

                // Bezárjuk a lekérdezést
                $stmtQuery->close();
            }
            echo '</div>';
        } else {
            // Ha nincs kiválasztott étel, lekérdezzük az összes ételt az adatbázisból
            echo '<div id="selected-images">';
            echo "<h3>Kiválasztott ételek:</h3>";

            // Lekérdezzük az összes étel nevét és kalóriáját az adatbázisból
            $sqlQuery = "SELECT nev, kaloria FROM etelek";
            $result = $conn->query($sqlQuery);

            // Ellenőrizzük, hogy van-e eredmény
            if ($result->num_rows > 0) {
                // Ha van eredmény, kiírjuk az összes ételt és kalóriáját
                while ($row = $result->fetch_assoc()) {
                    echo "<p>Etel neve: " . htmlspecialchars($row["nev"]) . ", Kalória: " . htmlspecialchars($row["kaloria"]) . "</p>";
                }
            } 

            // Bezárjuk az eredmény halmazt
            $result->close();

            echo '</div>';
        }
    }
    ?>



    <script>
        // JavaScript kód a kijelölés megvalósításához
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var parentDiv = checkbox.closest('.image-item');
                if (checkbox.checked) {
                    parentDiv.classList.add('selected');
                } else {
                    parentDiv.classList.remove('selected');
                }
            });
        });
    </script>
            
    
</div>

</body>
</html>