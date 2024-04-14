<?php
require("../sql/sql.php");
session_start();

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

<div class="lap">
    <div class="kartya">
        <header>Régebbi étrendek</header>
        <form action="" method="post">
            <table>
                <thead>
                    <tr>
                        <th>Étkezés dátuma</th>
                        <th>Reggeli</th>
                        <th>Ebéd</th>
                        <th>Vacsora</th>
                        <th>Uzsonna</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $felhasznalo_id = $_SESSION['felhasznalo_id'];

                    $sqlQuery = "SELECT `reggeli_id`, `ebed_id`, `vacsora_id`, `uzsonna_id`, `etkezes_datuma` FROM `etkezes` WHERE felhasznalo_id=?";
                    $stmtQuery = $conn->prepare($sqlQuery);
                    $stmtQuery->bind_param("i", $felhasznalo_id);
                    $stmtQuery->execute();
                    $stmtQuery->store_result();
                    $stmtQuery->bind_result($reggeli_ids, $ebed_ids, $vacsora_ids, $uzsonna_ids, $etkezes_datuma);
                    while ($stmtQuery->fetch()) {
                        // Szétválasztjuk a reggeli_id-ket
                        $reggeli_id_arr = explode(",", $reggeli_ids);
                        $reggeli_nevek = array();
                        foreach ($reggeli_id_arr as $reggeli_id) {
                            $sqlQuery = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtQueryInner = $conn->prepare($sqlQuery);
                            $stmtQueryInner->bind_param("i", $reggeli_id);
                            $stmtQueryInner->execute();
                            $stmtQueryInner->store_result();
                            $stmtQueryInner->bind_result($reggeli_nev);
                            $stmtQueryInner->fetch();
                            $reggeli_nevek[] = $reggeli_nev;
                            $stmtQueryInner->close();
                        }

                        // Szétválasztjuk az ebed_id-ket
                        $ebed_id_arr = explode(",", $ebed_ids);
                        $ebed_nevek = array();
                        foreach ($ebed_id_arr as $ebed_id) {
                            $sqlQuery = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtQueryInner = $conn->prepare($sqlQuery);
                            $stmtQueryInner->bind_param("i", $ebed_id);
                            $stmtQueryInner->execute();
                            $stmtQueryInner->store_result();
                            $stmtQueryInner->bind_result($ebed_nev);
                            $stmtQueryInner->fetch();
                            $ebed_nevek[] = $ebed_nev;
                            $stmtQueryInner->close();
                        }

                        // Szétválasztjuk a vacsora_id-ket
                        $vacsora_id_arr = explode(",", $vacsora_ids);
                        $vacsora_nevek = array();
                        foreach ($vacsora_id_arr as $vacsora_id) {
                            $sqlQuery = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtQueryInner = $conn->prepare($sqlQuery);
                            $stmtQueryInner->bind_param("i", $vacsora_id);
                            $stmtQueryInner->execute();
                            $stmtQueryInner->store_result();
                            $stmtQueryInner->bind_result($vacsora_nev);
                            $stmtQueryInner->fetch();
                            $vacsora_nevek[] = $vacsora_nev;
                            $stmtQueryInner->close();
                        }

                        // Szétválasztjuk az uzsonna_id-ket
                        $uzsonna_id_arr = explode(",", $uzsonna_ids);
                        $uzsonna_nevek = array();
                        foreach ($uzsonna_id_arr as $uzsonna_id) {
                            $sqlQuery = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtQueryInner = $conn->prepare($sqlQuery);
                            $stmtQueryInner->bind_param("i", $uzsonna_id);
                            $stmtQueryInner->execute();
                            $stmtQueryInner->store_result();
                            $stmtQueryInner->bind_result($uzsonna_nev);
                            $stmtQueryInner->fetch();
                            $uzsonna_nevek[] = $uzsonna_nev;
                            $stmtQueryInner->close();
                        }

                        // Kiírás a táblázatba
                        echo "<tr>";
                        echo "<td>$etkezes_datuma</td>";
                        echo "<td>" . implode(", ", $reggeli_nevek) . "</td>";
                        echo "<td>" . implode(", ", $ebed_nevek) . "</td>";
                        echo "<td>" . implode(", ", $vacsora_nevek) . "</td>";
                        echo "<td>" . implode(", ", $uzsonna_nevek) . "</td>";
                        echo "</tr>";
                    }
                    $stmtQuery->close();
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

</body>
</html>