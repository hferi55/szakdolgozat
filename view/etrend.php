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
            meg kettő ételt soronként
        </b></p>
        </form>
    </div>
    
    <header>Preferencia</header>

    <?php
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Hiba: " . $e->getMessage();
        }
    ?>
        
        <form action="etrendem.php" method="post" id="image-selection-form"> 
            <!-- Reggeli -->
            <h3>Reggeli:</h3>

            <div class="image-container">
                <?php
                // Reggeli képek és címek lekérdezése
                $stmt1 = $conn->prepare("SELECT nev, kep, etel_id FROM etelek WHERE reggeli = 1");
                $stmt1->execute();
                $images1 = $stmt1->fetchAll();
                ?>
                <?php foreach ($images1 as $image): ?>
                    <div class="image-item <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'selected'; ?>">
                        <div>
                            <img src="<?php echo $image['kep']; ?>" alt="<?php echo $image['nev']; ?>"><br>
                            <label>
                                <input type="checkbox" name="selected_images[]" value="<?php echo $image['etel_id']; ?>" <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'checked="checked"'; ?>>
                                <?php echo $image['nev']; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Hús -->
            <h3>Hús:</h3>
            
            <div class="image-container">
                <?php
                // Hús képek és címek lekérdezése
                $stmt2 = $conn->prepare("SELECT nev, kep, etel_id FROM etelek WHERE milyenetel = 'hús'");
                $stmt2->execute();
                $images2 = $stmt2->fetchAll();
                ?>
                <?php foreach ($images2 as $image): ?>
                    <div class="image-item <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'selected'; ?>">
                        <div>
                            <img src="<?php echo $image['kep']; ?>" alt="<?php echo $image['nev']; ?>"><br>
                            <label>
                                <input type="checkbox" name="selected_images[]" value="<?php echo $image['etel_id']; ?>" <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'checked="checked"'; ?>>
                                <?php echo $image['nev']; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>


            <!-- Köret -->
            <h3>Köret:</h3>

            <div class="image-container">
                <?php
                // Köret képek és címek lekérdezése
                $stmt3 = $conn->prepare("SELECT nev, kep, etel_id FROM etelek WHERE milyenetel = 'köret'");
                $stmt3->execute();
                $images3 = $stmt3->fetchAll();
                ?>
                <?php foreach ($images3 as $image): ?>
                    <div class="image-item <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'selected'; ?>">
                        <div>
                            <img src="<?php echo $image['kep']; ?>" alt="<?php echo $image['nev']; ?>"><br>
                            <label>
                                <input type="checkbox" name="selected_images[]" value="<?php echo $image['etel_id']; ?>" <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'checked="checked"'; ?>>
                                <?php echo $image['nev']; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Uzsonna -->
            <h3>Uzsonna:</h3>
            
            <div class="image-container">
                <?php
                // Uzsonna képek és címek lekérdezése
                $stmt4 = $conn->prepare("SELECT nev, kep, etel_id FROM etelek WHERE uzsonna = 1");
                $stmt4->execute();
                $images4 = $stmt4->fetchAll();
                ?>
                <?php foreach ($images4 as $image): ?>
                    <div class="image-item <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'selected'; ?>">
                        <div>
                            <img src="<?php echo $image['kep']; ?>" alt="<?php echo $image['nev']; ?>"><br>
                            <label>
                                <input type="checkbox" name="selected_images[]" value="<?php echo $image['etel_id']; ?>" <?php if(isset($_POST['selected_images']) && in_array($image['etel_id'], $_POST['selected_images'])) echo 'checked="checked"'; ?>>
                                <?php echo $image['nev']; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="kivalaszt_gomb">
                <input type="submit" class="button" value="Kiválasztottam az ételeket" id="submitButton">
            </div>

            <script>
                document.getElementById('submitButton').disabled = true; // Gomb alapértelmezett letiltása

                // Ellenőrzi, hogy minden sorban pontosan két kép van-e kiválasztva
                function checkSelection() {
                    var rows = document.querySelectorAll('.image-container');
                    var allRowsHaveTwoSelected = true;
                    rows.forEach(function(row) {
                        var selectedImages = row.querySelectorAll('input[type="checkbox"]:checked');
                        if (selectedImages.length !== 2) {
                            allRowsHaveTwoSelected = false;
                        }
                    });
                    // Ha minden sorban pontosan két kép van kiválasztva, engedélyezi a gombot, különben letiltja
                    if (allRowsHaveTwoSelected) {
                        document.getElementById('submitButton').disabled = false;
                    } else {
                        document.getElementById('submitButton').disabled = true;
                    }
                }
            
                // Ellenőrzés minden egyes jelölés módosításakor
                var checkboxes = document.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        checkSelection();
                    });
                });
            </script>


            </form>

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