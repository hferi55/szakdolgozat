<?php
session_start();
require("../sql/sql.php");

if(isset($_GET['etel_id'])) {
    $selected_image_id = $_GET['etel_id'];

    $sqlQuery = "SELECT nev, kep FROM etelek WHERE etel_id = ?";
    $stmtQuery = $conn->prepare($sqlQuery);
    $stmtQuery->bind_param("i", $selected_image_id);
    $stmtQuery->execute();
    $stmtQuery->store_result();
    $stmtQuery->bind_result($selected_food_nev, $selected_food_kep);

    if ($stmtQuery->fetch()) {
        $_SESSION['selected_food_nev'] = $selected_food_nev;
        $_SESSION['selected_food_kep'] = $selected_food_kep;
    } else {
        echo 'Nincs eredmény a lekérdezésben.';
        exit;
    }

    $stmtQuery->close();
} else {
    echo 'Nincs megfelelő paraméter az URL-ben.';
    exit;
}

if(isset($_SESSION['selected_food_nev']) && isset($_SESSION['selected_food_kep'])) {
    $selected_food_nev = $_SESSION['selected_food_nev'];
    $selected_food_kep = $_SESSION['selected_food_kep'];
    unset($_SESSION['selected_food_nev']);
    unset($_SESSION['selected_food_kep']);
}

$sqlQuery = "SELECT osszetevok, kaloria, recept FROM etelek WHERE etel_id = ?";
$stmtQuery = $conn->prepare($sqlQuery);
$stmtQuery->bind_param("i", $selected_image_id);
$stmtQuery->execute();
$stmtQuery->store_result();
$stmtQuery->bind_result($kivalasztott_etel_osszetevok, $kivalasztott_etel_kaloria, $kivalasztott_etel_recept);
$stmtQuery->fetch(); // Fetch to get the results
$stmtQuery->close();

$sqlQuery = "SELECT allergenek_id FROM `etelek allergenei` WHERE etel_id = ?";
$stmtQuery = $conn->prepare($sqlQuery);
$stmtQuery->bind_param("i", $selected_image_id);
$stmtQuery->execute();
$stmtQuery->store_result();
$stmtQuery->bind_result($kivalasztott_etel_allergenei);

$allergen_ids = array();
while ($stmtQuery->fetch()) {
    $allergen_ids[] = $kivalasztott_etel_allergenei;
}
$stmtQuery->close();

$allergen_names = array();
foreach ($allergen_ids as $allergen_id) {
    $sqlQuery = "SELECT nev FROM allergenek WHERE allergenek_id = ?";
    $stmtQuery = $conn->prepare($sqlQuery);
    $stmtQuery->bind_param("i", $allergen_id);
    $stmtQuery->execute();
    $stmtQuery->store_result();
    $stmtQuery->bind_result($allergen_name);
    $stmtQuery->fetch();
    $allergen_names[] = $allergen_name;
    $stmtQuery->close();
}

$preferencia_erteke = ""; // Alapértelmezett preferencia-érték üres string
// Ellenőrizzük, hogy van-e preferencia az adott felhasználóhoz és ételhez
if (isset($_SESSION['felhasznalo_id']) && isset($_GET['etel_id'])) {
    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    $etel_id = $_GET['etel_id'];

    $sqlQuery = "SELECT kedveli FROM `preferencia` WHERE felhasznalo_id = $felhasznalo_id AND etel_id = $etel_id";
    $stmtQuery = $conn->prepare($sqlQuery);
    $stmtQuery->execute();
    $stmtQuery->store_result();
    $stmtQuery->bind_result($preferencia_erteke);
    $stmtQuery->fetch(); // Fetch to get the results
    $stmtQuery->close();

}

if(isset($_POST['submit'])) {
    // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
    if (isset($_SESSION['felhasznalo_id'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $etel_id = $_GET['etel_id'];
        $kedveli = $_POST['kedveli'];

        // Ellenőrizze, hogy minden mező kitöltve van-e
        if(!empty($kedveli)) {
            // Ellenőrizzük, hogy már van-e preferencia az adott felhasználóhoz és ételhez
            $query = "SELECT * FROM `preferencia` WHERE felhasznalo_id = $felhasznalo_id AND etel_id = $etel_id";
            $result = mysqli_query($conn, $query);
            if(mysqli_num_rows($result) > 0) {
                // Ha már létezik preferencia, frissítjük az adatokat
                $query = "UPDATE `preferencia` SET kedveli = $kedveli WHERE felhasznalo_id = $felhasznalo_id AND etel_id = $etel_id";
            } else {
                // Ha még nem létezik preferencia, új rekordot szúrunk be
                $query = "INSERT INTO `preferencia`(`felhasznalo_id`, `etel_id`, `kedveli`) VALUES ($felhasznalo_id,$etel_id,$kedveli)";
            }

            if(mysqli_query($conn, $query)) {
                // Sikeres beszúrás vagy frissítés esetén átirányítás
                header("Location: ".$_SERVER['PHP_SELF']."?etel_id=".$etel_id);
                exit();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
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

<div class="etel_lap">
    <div class="kartya">
    <header><b><?php echo htmlspecialchars($selected_food_nev); ?> </b></header>
      <form action="" method="post">
        <div class="kep">
            <img src="<?php echo isset($selected_food_kep) ? htmlspecialchars($selected_food_kep) : 'Nincs kép'; ?>" alt="<?php echo isset($selected_food_nev) ? htmlspecialchars($selected_food_nev) : 'Nincs adat'; ?>"><br>
        </div>
        
        <article>
            <h2><?php echo htmlspecialchars($selected_food_nev); ?> allergénei: </h2>
            <?php
            if (!empty($allergen_names)) { ?>
                <p><?php echo htmlspecialchars(implode(', ', $allergen_names)); ?></p>
            <?php } else {?>
            <p>Az ételnek nincsenek allergénei</p>
            <?php } ?>

            <h2>1 adag étel elkészítéséhez való összetevők: </h2>
            <p><?php echo htmlspecialchars($kivalasztott_etel_osszetevok); ?></p>

            <h2>1 adag kalória tartalma: </h2>
            <p><?php echo htmlspecialchars($kivalasztott_etel_kaloria); ?> kalória</p>

            <h2>Recept: </h2>
            <p><?php echo htmlspecialchars($kivalasztott_etel_recept); ?></p>
        
            <h2>Kedveli ezt az ételt?</h2>
            <select name="kedveli" id="kedveli">
                <option value="1">kedveli</option>
                <option value="2">nem kedveli</option>
            </select>
            <input type="submit" class="button" value="Válasz küldése" name="submit">

            <?php
            if ($preferencia_erteke !== "") {
                echo "<h3>Ezt az ételt ";
                echo ($preferencia_erteke);
                echo "</h3>";
            }
            ?>

        </article>

      </form>
    </div>
</div>

</body>
</html>
