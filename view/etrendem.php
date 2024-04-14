<?php
session_start();

require("../sql/sql.php");

if (isset($_SESSION['etrend_keszites_sikeres']) && $_SESSION['etrend_keszites_sikeres'] === true) {
    
    // További műveletek az étrend sikeres elkészítése esetén

    // Ne felejtsük el törölni a munkamenet változót, hogy ne jelenjen meg újra az üzenet frissítéskor
    unset($_SESSION['etrend_keszites_sikeres']);
}

// Ellenőrizzük, hogy van-e POST kérés
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selected_images"]) && is_array($_POST["selected_images"])) {
    // Tároljuk el az étrendet a POST kérésben küldött adatokban
    $_SESSION["selected_images"] = $_POST["selected_images"];


    // Frissítjük a felhasználó kiválasztott képeit az adatbázisban
    if (isset($_SESSION['felhasznalo_id']) && isset($_SESSION['selected_images'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $selected_images = $_SESSION['selected_images'];

        // Átalakítjuk a kiválasztott képek tömböt stringgé, hogy felhasználhassuk az SQL lekérdezésben
        $selected_images_str = implode(',', $selected_images);

        // Frissítjük a felhasználó kiválasztott képeit az adatbázisban
        $updateQuery = "UPDATE felhasznalo SET kivalasztott_kepek = ? WHERE felhasznalo_id = ?";
        $stmtUpdate = $conn->prepare($updateQuery);
        $stmtUpdate->bind_param("si", $selected_images_str, $felhasznalo_id);
        $stmtUpdate->execute();
        $stmtUpdate->close();

    }
}

//if ($_SERVER["REQUEST_METHOD"] != "POST"){

    $felhasznalo_id = $_SESSION['felhasznalo_id'];

    // Kérjük le az adatbázisból az adatokat és mentsük el a SESSION-be
    $sqlQuery = "SELECT kivalasztott_kepek FROM felhasznalo WHERE felhasznalo_id = ?";
    $stmtSelect = $conn->prepare($sqlQuery);
    $stmtSelect->bind_param("i", $felhasznalo_id);
    $stmtSelect->execute();
    $stmtSelect->bind_result($selected_images_str);
    $stmtSelect->fetch();
    $stmtSelect->close();
    
    // Az adatokat elmentjük a SESSION-be
    $_SESSION["selected_images"] = explode(',', $selected_images_str);
    
//}


// Kezdetben üres üzenet
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["etrendmentese"])) {
        if (isset($_SESSION["selected_images"])) {

            // Definiáljuk a reggelihez, ebédhez és uzsonnához tartozó számokat
            $reggeli_szamok = array(1, 2, 3, 4, 8, 9, 29);
            $uzsonna_szamok = array(5, 6, 7, 10, 26, 27, 28, 30);
        
            // Frissítjük a felhasználó kiválasztott képeit az adatbázisban
            if (isset($_SESSION['felhasznalo_id']) && isset($_SESSION['selected_images'])) {
                $felhasznalo_id = $_SESSION['felhasznalo_id'];
                $selected_images = $_SESSION['selected_images'];
        
                // Tömb létrehozása a $selected_images-ből
                $selected_images_array = array_values($selected_images);
        
                // Ellenőrizzük, hogy melyik kép melyik étkezéshez tartozik, és létrehozzuk az adott stringet
                $reggeli_images = array_intersect($selected_images_array, $reggeli_szamok);
                $uzsonna_images = array_intersect($selected_images_array, $uzsonna_szamok);
        
                $reggeli_id_str = implode(',', $reggeli_images);
                $uzsonna_id_str = implode(',', $uzsonna_images);
        
                // Ebéd id-jei
                $ebed_id_str = "";
                if (isset($selected_images_array[2]) && isset($selected_images_array[4])) {
                    $ebed_id_str = $selected_images_array[2] . ',' . $selected_images_array[4];
                } elseif (isset($selected_images_array[2])) {
                    $ebed_id_str = $selected_images_array[2];
                } elseif (isset($selected_images_array[4])) {
                    $ebed_id_str = $selected_images_array[4];
                }
        
                // Vacsora id-jei
                $vacsora_id_str = "";
                if (isset($selected_images_array[3]) && isset($selected_images_array[5])) {
                    $vacsora_id_str = $selected_images_array[3] . ',' . $selected_images_array[5];
                } elseif (isset($selected_images_array[3])) {
                    $vacsora_id_str = $selected_images_array[3];
                } elseif (isset($selected_images_array[5])) {
                    $vacsora_id_str = $selected_images_array[5];
                }
        
                // Az aktuális dátum
                $etkezes_datuma = date("Y-m-d");
        
                // Ellenőrizzük, hogy van-e már bejegyzés az adott felhasználóhoz és dátumhoz az adatbázisban
                $checkQuery = "SELECT COUNT(*) FROM etkezes WHERE felhasznalo_id = ? AND etkezes_datuma = ?";
                $stmtCheck = $conn->prepare($checkQuery);
                $stmtCheck->bind_param("is", $felhasznalo_id, $etkezes_datuma);
                $stmtCheck->execute();
                $stmtCheck->bind_result($count);
                $stmtCheck->fetch();
                $stmtCheck->close();
        
                // Ha van már bejegyzés, akkor frissítjük az adatokat
                if ($count > 0) {
                    $updateQuery = "UPDATE etkezes SET reggeli_id = ?, ebed_id = ?, vacsora_id = ?, uzsonna_id = ? WHERE felhasznalo_id = ? AND etkezes_datuma = ?";
                    $stmtUpdate = $conn->prepare($updateQuery);
                    $stmtUpdate->bind_param("ssssis", $reggeli_id_str, $ebed_id_str, $vacsora_id_str, $uzsonna_id_str, $felhasznalo_id, $etkezes_datuma);
                    if ($stmtUpdate->execute()) {
                        $message = "Sikeresen frissítette az étrendet.";
                    } else {
                        $message = "Nem sikerült frissíteni az étrendet.";
                    }
                    $stmtUpdate->close();
                } else {
                    // Ha nincs még bejegyzés, akkor újat hozunk létre
                    $insertQuery = "INSERT INTO etkezes (felhasznalo_id, reggeli_id, ebed_id, vacsora_id, uzsonna_id, etkezes_datuma) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmtInsert = $conn->prepare($insertQuery);
                    $stmtInsert->bind_param("isssss", $felhasznalo_id, $reggeli_id_str, $ebed_id_str, $vacsora_id_str, $uzsonna_id_str, $etkezes_datuma);
                    if ($stmtInsert->execute()) {
                        $message = "Sikeresen mentette az étrendet.";
                    } else {
                        $message = "Nem sikerült menteni az étrendet.";
                    }
                    $stmtInsert->close();
                }
            }
        } else {
            // Ha nincsenek kiválasztott képek, akkor üzenetet küldünk
            $message = "Nincsenek kiválasztva ételek az étrendhez.";
        }
    } elseif (isset($_POST['adatmodositas'])) {
        header("Location: etrendkeszitese.php");
        exit();
    } elseif (isset($_POST['etrendmodositas'])) {
        header("Location: etrend.php");
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
  <title>Étrend Készítő Weboldal</title>
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

        <article>
            <p>
                A reggeli az egész napi kalória bevitel 25%-a.
            </p>
            <p>
                Az ebéd az egész napi kalória bevitel 35%-a.
            </p>
            <p>
                A vacsora az egész napi kalória bevitel 25%-a.
            </p>
            <p>
                Az uzsonna az egész napi kalória bevitel 15%-a.
            </p>
            <br>
            <p>
                Az adagok úgy vannak kiszámolva hogy az adott étel
            </p>
            <p>
                adag száma meg van szorozva úgy hogy az adott étel
            </p>
            <p>
                kalória száma egyenlő legyen az étkezés beviteli kalóriájával.
            </p>
            <br>
            <p>
                A jobb oldalon található ételekre rá lehet kattintani,
            </p>
            <p>
                és akkor át ugrunk az adott étel adatlapjára.
            </p>
            <p>
                Az étel adatlapjain minden egység 1 adag ételhez van kiszámítva.
            </p>
            <br>
            <p>
                Ha más ételeket szeretnénk az étrendünkbe rakni
            </p>
            <p>
                akkor azt megtehetjük az Étrend módosítása gommbal.
            </p>
            <br>
            <p>
                Ha rá kattintunk az Étrend módosítása gombra
            </p>
            <p>
                akkor vissza tudunk ugrani az ételek kiválasztása oldalra,
            </p>
            <p>
                ahol akár más vagy ugyan azokat ételeket tudjuk kiválasztani.
            </p>
        </article>

        <input type="submit" class="button" value="Étrend módosítása" name="etrendmodositas">

        <input type="submit" class="button" value="Étrend mentése" name="etrendmentese">

        <?php if (isset($_POST["etrendmentese"])) { ?>
            <p><b> <?php echo $message; ?> </b></p> 
        <?php } ?>

        </form>
    </div>
    
    <header>Ételek</header>
        
    <!-- Reggeli -->
    <h3>Reggeli:</h3>
    <?php

        // Számoljuk ki a reggelihez szükséges kalóriát (25%-át a fogyasztandónak)
        $reggeli_kaloria = $fogyasztando * 0.25;

        
                // Összegyűjtjük a kiválasztott ételek azonosítóit
                $selected_images = $_SESSION["selected_images"];
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
                    <!-- Adjunk egy egyedi azonosítót a képnek és a névnek -->
                    <img id="img_<?php echo $selected_image_id; ?>" src="<?php echo htmlspecialchars($kep); ?>" alt="<?php echo htmlspecialchars($nev); ?>" data-etel-id="<?php echo $selected_image_id; ?>"><br>
                    <label id="label_<?php echo $selected_image_id; ?>">
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
            <script>
                // Adjunk hozzá eseményfigyelőt az img és label elemekhez
                document.getElementById('img_<?php echo $selected_image_id; ?>').addEventListener('click', function() {
                    const etelId = this.getAttribute('data-etel-id');
                    window.location.href = `etel.php?etel_id=${etelId}`;
                });
                document.getElementById('label_<?php echo $selected_image_id; ?>').addEventListener('click', function() {
                    const etelId = document.getElementById('img_<?php echo $selected_image_id; ?>').getAttribute('data-etel-id');
                    window.location.href = `etel.php?etel_id=${etelId}`;
                });
            </script>
            <?php
        }
        // Bezárjuk a lekérdezést
        $stmtQuery->close();
    }
    ?>
</div>




    <!-- Ebéd -->
    <h3>Ebéd:</h3>
    <?php

        // Számoljuk ki az ebédhez szükséges kalóriát (35%-át a fogyasztandónak)
        $ebed_kaloria = $fogyasztando * 0.35;

        
                // Összegyűjtjük a kiválasztott ételek azonosítóit
                $selected_images = $_SESSION["selected_images"];
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
                $selected_hus[] = ["nev" => $nev, "kep" => $kep, "id" => $selected_image_id];
            } elseif ($milyenetel === "köret") {
                $selected_koret[] = ["nev" => $nev, "kep" => $kep, "id" => $selected_image_id];
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
                <!-- Adjunk egy egyedi azonosítót a képnek és a névnek -->
                <img id="img_hus" src="<?php echo htmlspecialchars($hus['kep']); ?>" alt="<?php echo htmlspecialchars($hus['nev']); ?>" data-etel-id="<?php echo $hus['id']; ?>"><br>
                <label id="label_hus">
                    <?php echo htmlspecialchars($hus['nev']); ?> <br>
                    <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                        Adag
                </label>
            </div>
        </div>
        <script>
            // Adjunk hozzá eseményfigyelőt az img és label elemekhez
            document.getElementById('img_hus').addEventListener('click', function() {
                const etelId = this.getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
            document.getElementById('label_hus').addEventListener('click', function() {
                const etelId = document.getElementById('img_hus').getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
        </script>
        <?php
    }

    // Megjelenítjük az első talált "köret" ételt
    if (!empty($selected_koret)) {
        $koret = $selected_koret[0];
        ?>
        <div class="image-item">
            <div>
                <!-- Adjunk egy egyedi azonosítót a képnek és a névnek -->
                <img id="img_koret" src="<?php echo htmlspecialchars($koret['kep']); ?>" alt="<?php echo htmlspecialchars($koret['nev']); ?>" data-etel-id="<?php echo $koret['id']; ?>"><br>
                <label id="label_koret">
                    <?php echo htmlspecialchars($koret['nev']); ?> <br>
                    <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                        Adag
                </label>
            </div>
        </div>
        <script>
            // Adjunk hozzá eseményfigyelőt az img és label elemekhez
            document.getElementById('img_koret').addEventListener('click', function() {
                const etelId = this.getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
            document.getElementById('label_koret').addEventListener('click', function() {
                const etelId = document.getElementById('img_koret').getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
        </script>
        <?php
    }
    
    ?>

</div>



<!-- Vacsora -->
<h3>Vacsora:</h3>
<?php

    // Számoljuk ki a vacsorahez szükséges kalóriát (25%-át a fogyasztandónak)
    $vacsora_kaloria = $fogyasztando * 0.25;

            // Összegyűjtjük a kiválasztott ételek azonosítóit
            $selected_images = $_SESSION["selected_images"];
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
        $sqlQuery = "SELECT nev, kep, milyenetel FROM etelek WHERE etel_id = ? AND vacsora = 1";
        $stmtQuery = $conn->prepare($sqlQuery);
        $stmtQuery->bind_param("i", $selected_image_id);
        $stmtQuery->execute();
        $stmtQuery->store_result();
        $stmtQuery->bind_result($nev, $kep, $milyenetel);

        // Ellenőrizzük, hogy van-e eredmény
        if ($stmtQuery->fetch()) {
            // Tároljuk az összes "hús" és "köret" ételt
            if ($milyenetel === "hús") {
                $selected_hus[] = ["nev" => $nev, "kep" => $kep, "id" => $selected_image_id];
            } elseif ($milyenetel === "köret") {
                $selected_koret[] = ["nev" => $nev, "kep" => $kep, "id" => $selected_image_id];
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
                <!-- Adjunk egy egyedi azonosítót a képnek és a névnek -->
                <img id="img_hus2" src="<?php echo htmlspecialchars($hus['kep']); ?>" alt="<?php echo htmlspecialchars($hus['nev']); ?>" data-etel-id="<?php echo $hus['id']; ?>"><br>
                <label id="label_hus2">
                    <?php echo htmlspecialchars($hus['nev']); ?> <br>
                    <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                        Adag
                </label>
            </div>
        </div>
        <script>
            // Adjunk hozzá eseményfigyelőt az img és label elemekhez
            document.getElementById('img_hus2').addEventListener('click', function() {
                const etelId = this.getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
            document.getElementById('label_hus2').addEventListener('click', function() {
                const etelId = document.getElementById('img_hus2').getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
        </script>
        <?php
    }

    // Megjelenítjük a második talált "köret" ételt
    if (!empty($selected_koret)) {
        $koret = $selected_koret[1];
        ?>
        <div class="image-item">
            <div>
                <!-- Adjunk egy egyedi azonosítót a képnek és a névnek -->
                <img id="img_koret2" src="<?php echo htmlspecialchars($koret['kep']); ?>" alt="<?php echo htmlspecialchars($koret['nev']); ?>" data-etel-id="<?php echo $koret['id']; ?>"><br>
                <label id="label_koret2">
                    <?php echo htmlspecialchars($koret['nev']); ?> <br>
                    <?php echo number_format($multiplication_factor, 2); ?> <!-- Hozzáadva egy szóköz a HTML formázáshoz -->
                        Adag
                </label>
            </div>
        </div>
        <script>
            // Adjunk hozzá eseményfigyelőt az img és label elemekhez
            document.getElementById('img_koret2').addEventListener('click', function() {
                const etelId = this.getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
            document.getElementById('label_koret2').addEventListener('click', function() {
                const etelId = document.getElementById('img_koret2').getAttribute('data-etel-id');
                window.location.href = `etel.php?etel_id=${etelId}`;
            });
        </script>
        <?php
    }
    
    ?>
</div>

    <!-- Uzsonna -->
    <h3>Uzsonna:</h3>
    <?php

    // Számoljuk ki az uzsonnához szükséges kalóriát (15%-át a fogyasztandónak)
    $uzsonna_kaloria = $fogyasztando * 0.15;

            // Összegyűjtjük a kiválasztott ételek azonosítóit
            $selected_images = $_SESSION["selected_images"];
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
    foreach ($selected_images as $selected_image_id) {
        // Létrehozzuk az üres tömböket minden iteráció előtt
        $_SESSION['selected_food_nev'] = '';
        $_SESSION['selected_food_kep'] = '';
        
        $sqlQuery = "SELECT nev, kep FROM etelek WHERE etel_id = ? AND uzsonna = 1";
        $stmtQuery = $conn->prepare($sqlQuery);
        $stmtQuery->bind_param("i", $selected_image_id);
        $stmtQuery->execute();
        $stmtQuery->store_result();
        $stmtQuery->bind_result($nev, $kep);
        
        // Ellenőrizzük, hogy van-e eredmény
        if ($stmtQuery->fetch()) {
            // Elmentjük az étel nevét és képét a SESSION változókba
            $_SESSION['selected_food_nev'] = $nev;
            $_SESSION['selected_food_kep'] = $kep;

            // Megjelenítjük az étel nevét és képét
            ?>
            <div class="image-item">
                <div>
                    <!-- Adjunk egy egyedi azonosítót a képnek és a névnek -->
                    <img id="img_<?php echo $selected_image_id; ?>" src="<?php echo htmlspecialchars($kep); ?>" alt="<?php echo htmlspecialchars($nev); ?>"><br>
                    <label id="label_<?php echo $selected_image_id; ?>">
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
            <script>
                // Adjunk hozzá eseményfigyelőt a képekre és a névre, hogy átirányítsuk az etel.php oldalra
                document.getElementById('img_<?php echo $selected_image_id; ?>').addEventListener('click', function() {
                    window.location.href = `etel.php?etel_id=<?php echo $selected_image_id; ?>`; // Az étel azonosítójának átadása az URL-ben
                });
                document.getElementById('label_<?php echo $selected_image_id; ?>').addEventListener('click', function() {
                    window.location.href = `etel.php?etel_id=<?php echo $selected_image_id; ?>`; // Az étel azonosítójának átadása az URL-ben
                });
            </script>
            <?php
        }
        
    }
    // Bezárjuk a lekérdezést
    $stmtQuery->close();
    ?>
</div>

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