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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
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

<div class="lap">
    <div class="kartya">
    <header><b><?php echo htmlspecialchars($selected_food_nev); ?> </b></header>
      <form action="" method="post">
        <div class="image-item">
            <img src="<?php echo isset($selected_food_kep) ? htmlspecialchars($selected_food_kep) : 'Nincs kép'; ?>" alt="<?php echo isset($selected_food_nev) ? htmlspecialchars($selected_food_nev) : 'Nincs adat'; ?>"><br>
        </div>
      </form>
    </div>
</div>

</body>
</html>
