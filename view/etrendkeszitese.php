<?php
session_start(); 

require("../sql/sql.php");


$hiba_uzenet = '';

if(isset($_POST['submit'])) {
    
    if (isset($_SESSION['felhasznalo_id'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $eletkor = $_POST['eletkor'];
        $testsuly = $_POST['testsuly'];
        $magassag = $_POST['magassag'];
        $nem = $_POST['nem'];
        $aktivitas = $_POST['aktivitas'];
        $cel = $_POST['cel'];

        
        if(empty($eletkor) || empty($testsuly) || empty($magassag) || empty($nem) || empty($aktivitas) || empty($cel)) {
            $hiba_uzenet = 'Kérjük, töltse ki az összes mezőt!';
        } else {
            
            $keres = "UPDATE `felhasznalo` SET `eletkor`='$eletkor', `testsuly`='$testsuly', `magassag`='$magassag', `nem`='$nem', `aktivitas`='$aktivitas', `cel`='$cel' WHERE `felhasznalo_id`='$felhasznalo_id'";                  
            mysqli_query($conn, $keres);

            
            if(mysqli_affected_rows($conn) > 0) {
                
                $_SESSION['etrend_keszites_sikeres'] = true;
                header("Location: etrend.php");
                exit();
            } else {
                $hiba_uzenet = 'Hiba történt az adatok frissítése közben. Kérjük, próbálja újra.';
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
            require("../sql/sql.php");
            $felhasznalo_id = $_SESSION['felhasznalo_id'];

            
            $keres = "SELECT kivalasztott_kepek FROM felhasznalo WHERE felhasznalo_id = $felhasznalo_id";
            $valasz = mysqli_query($conn, $keres);
            $sor = mysqli_fetch_assoc($valasz);
            $kivalasztott_kepek = $sor['kivalasztott_kepek'];

            if (!empty($kivalasztott_kepek)) {
                
                echo '
                <a href="../view/rolunk.php">Rólunk</a> |
                <a href="../view/profil.php">Profil</a> |
                <a href="../view/etrendem.php">Étrendem</a> |
                <a href="../logout.php">Kijelentkezés</a>
                ';
            } else {
                
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



<div class="profil_lap">
    <div class="kartya">
        <header>Étrendkészítése</header>

        <?php
            
            if (!empty($hiba_uzenet)) {
                echo '<div class="hiba-uzenetek">';
                echo '<ul>';
                echo '<li>' . $hiba_uzenet . '</li>';
                echo '</ul>';
                echo '</div>';
            }
        ?>

        <form action="" method="post">

                
                <h3>Életkor:</h3>
                <input type="number" placeholder="Adja meg az életkorát" name="eletkor">
                <br>

                
                <h3>Testsúly:</h3>
                <input type="number" placeholder="Adja meg a testsúlyát" name="testsuly">
                <br>

                
                <h3>Magasság:</h3>
                <input type="number" placeholder="Adja meg a magasságát" name="magassag">
                <br>

                
                <h3>Nem:</h3>
                <select name="nem" id="nem">
                    <option value="1">Válasszon</option>
                    <option value="2">Nő</option>
                    <option value="3">Férfi</option>
                </select>
                <br>

                
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

                
                <h3>Cél:</h3>
                <select name="cel" id="cel">
                    <option value="1">Válasszon</option>
                    <option value="2">Szintentartás</option>
                    <option value="3">Fogyás</option>
                    <option value="4">Tömegelés</option>
                </select>

                
                <input type="submit" class="button" value="Étrendkészítése" name="submit">



      </form>
    </div>
</div>

</body>
</html>
