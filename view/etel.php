<?php
session_start();
require("../sql/sql.php");

if(isset($_GET['etel_id'])) {
    $kivalasztott_kepek_id = $_GET['etel_id'];

    $sqlKeres = "SELECT nev, kep FROM etelek WHERE etel_id = ?";
    $stmtKeres = $conn->prepare($sqlKeres);
    $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
    $stmtKeres->execute();
    $stmtKeres->store_result();
    $stmtKeres->bind_result($kivalasztott_etel_nev, $kivalasztott_etel_kep);

    if ($stmtKeres->fetch()) {
        $_SESSION['kivalasztott_etel_nev'] = $kivalasztott_etel_nev;
        $_SESSION['kivalasztott_etel_kep'] = $kivalasztott_etel_kep;
    } else {
        echo 'Nincs eredmény a lekérdezésben.';
        exit;
    }

    $stmtKeres->close();
} else {
    echo 'Nincs megfelelő paraméter az URL-ben.';
    exit;
}

if(isset($_SESSION['kivalasztott_etel_nev']) && isset($_SESSION['kivalasztott_etel_kep'])) {
    $kivalasztott_etel_nev = $_SESSION['kivalasztott_etel_nev'];
    $kivalasztott_etel_kep = $_SESSION['kivalasztott_etel_kep'];
    unset($_SESSION['kivalasztott_etel_nev']);
    unset($_SESSION['kivalasztott_etel_kep']);
}

$sqlKeres = "SELECT osszetevok, kaloria, recept FROM etelek WHERE etel_id = ?";
$stmtKeres = $conn->prepare($sqlKeres);
$stmtKeres->bind_param("i", $kivalasztott_kepek_id);
$stmtKeres->execute();
$stmtKeres->store_result();
$stmtKeres->bind_result($kivalasztott_etel_osszetevok, $kivalasztott_etel_kaloria, $kivalasztott_etel_recept);
$stmtKeres->fetch(); 
$stmtKeres->close();

$sqlKeres = "SELECT allergenek_id FROM `etelek allergenei` WHERE etel_id = ?";
$stmtKeres = $conn->prepare($sqlKeres);
$stmtKeres->bind_param("i", $kivalasztott_kepek_id);
$stmtKeres->execute();
$stmtKeres->store_result();
$stmtKeres->bind_result($kivalasztott_etel_allergenei);

$allergen_idk = array();
while ($stmtKeres->fetch()) {
    $allergen_idk[] = $kivalasztott_etel_allergenei;
}
$stmtKeres->close();

$allergen_nevek = array();
foreach ($allergen_idk as $allergen_id) {
    $sqlKeres = "SELECT nev FROM allergenek WHERE allergenek_id = ?";
    $stmtKeres = $conn->prepare($sqlKeres);
    $stmtKeres->bind_param("i", $allergen_id);
    $stmtKeres->execute();
    $stmtKeres->store_result();
    $stmtKeres->bind_result($allergen_nev);
    $stmtKeres->fetch();
    $allergen_nevek[] = $allergen_nev;
    $stmtKeres->close();
}

$preferencia_erteke = ""; 

if (isset($_SESSION['felhasznalo_id']) && isset($_GET['etel_id'])) {
    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    $etel_id = $_GET['etel_id'];

    $sqlKeres = "SELECT kedveli FROM `preferencia` WHERE felhasznalo_id = $felhasznalo_id AND etel_id = $etel_id";
    $stmtKeres = $conn->prepare($sqlKeres);
    $stmtKeres->execute();
    $stmtKeres->store_result();
    $stmtKeres->bind_result($preferencia_erteke);
    $stmtKeres->fetch(); 
    $stmtKeres->close();

}

if(isset($_POST['valaszkuldese'])) {
    
    if (isset($_SESSION['felhasznalo_id'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $etel_id = $_GET['etel_id'];
        $kedveli = $_POST['kedveli'];

        
        if(!empty($kedveli)) {
            
            $keres = "SELECT * FROM `preferencia` WHERE felhasznalo_id = $felhasznalo_id AND etel_id = $etel_id";
            $valasz = mysqli_query($conn, $keres);
            if(mysqli_num_rows($valasz) > 0) {
                
                $keres = "UPDATE `preferencia` SET kedveli = $kedveli WHERE felhasznalo_id = $felhasznalo_id AND etel_id = $etel_id";
            } else {
                
                $keres = "INSERT INTO `preferencia`(`felhasznalo_id`, `etel_id`, `kedveli`) VALUES ($felhasznalo_id,$etel_id,$kedveli)";
            }

            if(mysqli_query($conn, $keres)) {
                
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
  
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <?php
        
        if (!isset($_SESSION['felhasznalo_id'])) {
            echo '
            <a href="../view/rolunk.php">Rólunk</a> |
            <a href="../view/bejelentkezes.php">Bejelentkezés</a> |
            <a href="../view/regisztracio.php">Regisztráció</a>
            ';
        } else { 
            
            if (isset($_SESSION['kivalasztott_kepek']) && !empty($_SESSION['kivalasztott_kepek'])) {
                echo '
                <a href="../view/rolunk.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrendem.php">Étrendem</a> |
                <a href="../kijelentkezes.php">Kijelentkezés</a>
                ';
            } else {
                require("../sql/sql.php");
                $felhasznalo_id = $_SESSION['felhasznalo_id'];

                
                $keres = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '' OR cel = 'nincs cel' OR nem = 'valasszon' OR aktivitas = 'valasszon')";
                $valasz = mysqli_query($conn, $keres);
                $sor = mysqli_fetch_row($valasz);
                $etrendVan = $sor[0] == 0;

                if ($etrendVan) {
                    echo '
                    <a href="../view/rolunk.php">Rólunk</a> |
                    <a href="../view/profil.php">Profil</a> |
                    <a href="../view/etrend.php">Étrend</a> |
                    <a href="../kijelentkezes.php">Kijelentkezés</a>
                    ';
                } else {
                    echo '
                    <a href="../view/rolunk.php">Rólunk</a> |
                    <a href="../view/profil.php">Profil</a> |
                    <a href="../view/etrendkeszitese.php">Étrendkészítés</a> |
                    <a href="../kijelentkezes.php">Kijelentkezés</a>
                    ';
                }
            }
        }
        ?>
    </nav>
</header>

<div class="etel_lap">
    <div class="kartya">
    <header><b><?php echo htmlspecialchars($kivalasztott_etel_nev); ?> </b></header>
      <form action="" method="post">
        <div class="kep">
            <img src="<?php echo isset($kivalasztott_etel_kep) ? htmlspecialchars($kivalasztott_etel_kep) : 'Nincs kép'; ?>" alt="<?php echo isset($kivalasztott_etel_nev) ? htmlspecialchars($kivalasztott_etel_nev) : 'Nincs adat'; ?>"><br>
        </div>
        
        <article>
            <h2><?php echo htmlspecialchars($kivalasztott_etel_nev); ?> allergénei: </h2>
            <?php
            if (!empty($allergen_nevek)) { ?>
                <p><?php echo htmlspecialchars(implode(', ', $allergen_nevek)); ?></p>
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
            <input type="submit" class="button" value="Válasz küldése" name="valaszkuldese">

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
