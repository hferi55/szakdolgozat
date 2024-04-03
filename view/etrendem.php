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

        <p><b>
            A jobb oldalon található képekből kérem jelöljön 
        </b></p>
        <p><b>
            meg legalább kettő olyan ételt amit kedvel
        </b></p>
        </form>
    </div>
    
    <header>Étrendem</header>

    <?php

    // Számoljuk ki a reggelihez szükséges kalóriát (25%-át a fogyasztandónak)
    $reggeli_kaloria = $fogyasztando * 0.25;
    
    // Számoljuk ki az ebédhez szükséges kalóriát (35%-át a fogyasztandónak)
    $ebed_kaloria = $fogyasztando * 0.35;
   
    // Számoljuk ki a vacsorahez szükséges kalóriát (25%-át a fogyasztandónak)
    $vacsora_kaloria = $fogyasztando * 0.25;
    
    // Számoljuk ki az uzsonnához szükséges kalóriát (15%-át a fogyasztandónak)
    $uzsonna_kaloria = $fogyasztando * 0.15;

    ?>
        
    <!-- Reggeli -->
    <h3>Reggeli:</h3>
    <p><b>Reggelihez szükséges kalória:  </b> <?php echo htmlspecialchars($reggeli_kaloria); ?> kcal</p>

    <!-- Ebéd -->
    <h3>Ebéd:</h3>
    <p><b>Ebédhez szükséges kalória: </b> <?php echo htmlspecialchars($ebed_kaloria); ?> kcal</p>

    <!-- Vacsora -->
    <h3>Vacsora:</h3>
    <p><b>Vacsorahez szükséges kalória: </b> <?php echo htmlspecialchars($vacsora_kaloria); ?> kcal</p>

    <!-- Uzsonna -->
    <h3>Uzsonna:</h3>
    <p><b>Uzsonnához szükséges kalória: </b> <?php echo htmlspecialchars($uzsonna_kaloria); ?> kcal</p>

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