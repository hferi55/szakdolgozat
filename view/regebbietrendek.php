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
            if (isset($_SESSION['kivalasztott_kepek']) && !empty($_SESSION['kivalasztott_kepek'])) {
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
                $keres = "SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id AND (magassag IS NULL OR testsuly IS NULL OR eletkor IS NULL OR cel = '' OR nem = '' OR aktivitas = '' OR cel = 'nincs cel' OR nem = 'valasszon' OR aktivitas = 'valasszon')";
                $valasz = mysqli_query($conn, $keres);
                $sor = mysqli_fetch_row($valasz);
                $etrendVan = $sor[0] == 0;

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

                    $sqlKeres = "SELECT `reggeli_id`, `ebed_id`, `vacsora_id`, `uzsonna_id`, `etkezes_datuma` FROM `etkezes` WHERE felhasznalo_id=?";
                    $stmtKeres = $conn->prepare($sqlKeres);
                    $stmtKeres->bind_param("i", $felhasznalo_id);
                    $stmtKeres->execute();
                    $stmtKeres->store_result();
                    $stmtKeres->bind_result($reggeli_idk, $ebed_idk, $vacsora_idk, $uzsonna_idk, $etkezes_datuma);
                    while ($stmtKeres->fetch()) {
                        // Szétválasztjuk a reggeli_id-ket
                        $reggeli_id_tomb = explode(",", $reggeli_idk);
                        $reggeli_nevek = array();
                        foreach ($reggeli_id_tomb as $reggeli_id) {
                            $sqlKeres = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtKeresBelso = $conn->prepare($sqlKeres);
                            $stmtKeresBelso->bind_param("i", $reggeli_id);
                            $stmtKeresBelso->execute();
                            $stmtKeresBelso->store_result();
                            $stmtKeresBelso->bind_result($reggeli_nev);
                            $stmtKeresBelso->fetch();
                            $reggeli_nevek[] = $reggeli_nev;
                            $stmtKeresBelso->close();
                        }

                        // Szétválasztjuk az ebed_id-ket
                        $ebed_id_tomb = explode(",", $ebed_idk);
                        $ebed_nevek = array();
                        foreach ($ebed_id_tomb as $ebed_id) {
                            $sqlKeres = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtKeresBelso = $conn->prepare($sqlKeres);
                            $stmtKeresBelso->bind_param("i", $ebed_id);
                            $stmtKeresBelso->execute();
                            $stmtKeresBelso->store_result();
                            $stmtKeresBelso->bind_result($ebed_nev);
                            $stmtKeresBelso->fetch();
                            $ebed_nevek[] = $ebed_nev;
                            $stmtKeresBelso->close();
                        }

                        // Szétválasztjuk a vacsora_id-ket
                        $vacsora_id_tomb = explode(",", $vacsora_idk);
                        $vacsora_nevek = array();
                        foreach ($vacsora_id_tomb as $vacsora_id) {
                            $sqlKeres = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtKeresBelso = $conn->prepare($sqlKeres);
                            $stmtKeresBelso->bind_param("i", $vacsora_id);
                            $stmtKeresBelso->execute();
                            $stmtKeresBelso->store_result();
                            $stmtKeresBelso->bind_result($vacsora_nev);
                            $stmtKeresBelso->fetch();
                            $vacsora_nevek[] = $vacsora_nev;
                            $stmtKeresBelso->close();
                        }

                        // Szétválasztjuk az uzsonna_id-ket
                        $uzsonna_id_tomb = explode(",", $uzsonna_idk);
                        $uzsonna_nevek = array();
                        foreach ($uzsonna_id_tomb as $uzsonna_id) {
                            $sqlKeres = "SELECT nev FROM etelek WHERE etel_id = ?";
                            $stmtKeresBelso = $conn->prepare($sqlKeres);
                            $stmtKeresBelso->bind_param("i", $uzsonna_id);
                            $stmtKeresBelso->execute();
                            $stmtKeresBelso->store_result();
                            $stmtKeresBelso->bind_result($uzsonna_nev);
                            $stmtKeresBelso->fetch();
                            $uzsonna_nevek[] = $uzsonna_nev;
                            $stmtKeresBelso->close();
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
                    $stmtKeres->close();
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>

</body>
</html>