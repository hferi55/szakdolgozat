<?php
session_start();

require("../sql/sql.php");

if (isset($_SESSION['etrend_keszites_sikeres']) && $_SESSION['etrend_keszites_sikeres'] === true) {
    
    

    
    unset($_SESSION['etrend_keszites_sikeres']);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["kivalasztott_kepek"]) && is_array($_POST["kivalasztott_kepek"])) {
    
    $_SESSION["kivalasztott_kepek"] = $_POST["kivalasztott_kepek"];


    
    if (isset($_SESSION['felhasznalo_id']) && isset($_SESSION['kivalasztott_kepek'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $kivalasztott_kepek = $_SESSION['kivalasztott_kepek'];

        
        $kivalasztott_kepek_str = implode(',', $kivalasztott_kepek);

        
        $frissitKeres = "UPDATE felhasznalo SET kivalasztott_kepek = ? WHERE felhasznalo_id = ?";
        $stmtFrissit = $conn->prepare($frissitKeres);
        $stmtFrissit->bind_param("si", $kivalasztott_kepek_str, $felhasznalo_id);
        $stmtFrissit->execute();
        $stmtFrissit->close();

    }
}



    $felhasznalo_id = $_SESSION['felhasznalo_id'];

    
    $sqlKeres = "SELECT kivalasztott_kepek FROM felhasznalo WHERE felhasznalo_id = ?";
    $stmtValaszt = $conn->prepare($sqlKeres);
    $stmtValaszt->bind_param("i", $felhasznalo_id);
    $stmtValaszt->execute();
    $stmtValaszt->bind_result($kivalasztott_kepek_str);
    $stmtValaszt->fetch();
    $stmtValaszt->close();
    
    
    $_SESSION["kivalasztott_kepek"] = explode(',', $kivalasztott_kepek_str);
    



$uzenet = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["etrendmentese"])) {
        if (isset($_SESSION["kivalasztott_kepek"])) {

            
            $reggeli_szamok = array(1, 2, 3, 4, 8, 9, 29);
            $uzsonna_szamok = array(5, 6, 7, 10, 26, 27, 28, 30);
        
            
            if (isset($_SESSION['felhasznalo_id']) && isset($_SESSION['kivalasztott_kepek'])) {
                $felhasznalo_id = $_SESSION['felhasznalo_id'];
                $kivalasztott_kepek = $_SESSION['kivalasztott_kepek'];
        
                
                $kivalasztott_kepek_tomb = array_values($kivalasztott_kepek);
        
                
                $reggeli_kepek = array_intersect($kivalasztott_kepek_tomb, $reggeli_szamok);
                $uzsonna_kepek = array_intersect($kivalasztott_kepek_tomb, $uzsonna_szamok);
        
                $reggeli_id_str = implode(',', $reggeli_kepek);
                $uzsonna_id_str = implode(',', $uzsonna_kepek);
        
                
                $ebed_id_str = "";
                if (isset($kivalasztott_kepek_tomb[2]) && isset($kivalasztott_kepek_tomb[4])) {
                    $ebed_id_str = $kivalasztott_kepek_tomb[2] . ',' . $kivalasztott_kepek_tomb[4];
                } elseif (isset($kivalasztott_kepek_tomb[2])) {
                    $ebed_id_str = $kivalasztott_kepek_tomb[2];
                } elseif (isset($kivalasztott_kepek_tomb[4])) {
                    $ebed_id_str = $kivalasztott_kepek_tomb[4];
                }
        
                
                $vacsora_id_str = "";
                if (isset($kivalasztott_kepek_tomb[3]) && isset($kivalasztott_kepek_tomb[5])) {
                    $vacsora_id_str = $kivalasztott_kepek_tomb[3] . ',' . $kivalasztott_kepek_tomb[5];
                } elseif (isset($kivalasztott_kepek_tomb[3])) {
                    $vacsora_id_str = $kivalasztott_kepek_tomb[3];
                } elseif (isset($kivalasztott_kepek_tomb[5])) {
                    $vacsora_id_str = $kivalasztott_kepek_tomb[5];
                }
        
                
                $etkezes_datuma = date("Y-m-d");
        
                
                $ellenorzoKeres = "SELECT COUNT(*) FROM etkezes WHERE felhasznalo_id = ? AND etkezes_datuma = ?";
                $stmtEllenorzes = $conn->prepare($ellenorzoKeres);
                $stmtEllenorzes->bind_param("is", $felhasznalo_id, $etkezes_datuma);
                $stmtEllenorzes->execute();
                $stmtEllenorzes->bind_result($szamolas);
                $stmtEllenorzes->fetch();
                $stmtEllenorzes->close();
        
                
                if ($szamolas > 0) {
                    $frissitKeres = "UPDATE etkezes SET reggeli_id = ?, ebed_id = ?, vacsora_id = ?, uzsonna_id = ? WHERE felhasznalo_id = ? AND etkezes_datuma = ?";
                    $stmtFrissit = $conn->prepare($frissitKeres);
                    $stmtFrissit->bind_param("ssssis", $reggeli_id_str, $ebed_id_str, $vacsora_id_str, $uzsonna_id_str, $felhasznalo_id, $etkezes_datuma);
                    if ($stmtFrissit->execute()) {
                        $uzenet = "Sikeresen frissítette az étrendet.";
                    } else {
                        $uzenet = "Nem sikerült frissíteni az étrendet.";
                    }
                    $stmtFrissit->close();
                } else {
                    
                    $beillesztKeres = "INSERT INTO etkezes (felhasznalo_id, reggeli_id, ebed_id, vacsora_id, uzsonna_id, etkezes_datuma) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmtBeilleszt = $conn->prepare($beillesztKeres);
                    $stmtBeilleszt->bind_param("isssss", $felhasznalo_id, $reggeli_id_str, $ebed_id_str, $vacsora_id_str, $uzsonna_id_str, $etkezes_datuma);
                    if ($stmtBeilleszt->execute()) {
                        $uzenet = "Sikeresen mentette az étrendet.";
                    } else {
                        $uzenet = "Nem sikerült menteni az étrendet.";
                    }
                    $stmtBeilleszt->close();
                }
            }
        } else {
            
            $uzenet = "Nincsenek kiválasztva ételek az étrendhez.";
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




<div class="etrend_lap">
    <header>Étrendem</header>
    
    <div class="adatok">
      <form action="" method="post">
      <?php
        
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $sqlKeres = "SELECT `testsuly`, `magassag`, `cel`, `nem`, `eletkor`, `aktivitas` FROM felhasznalo WHERE felhasznalo_id=?";
        $stmtKeres = $conn->prepare($sqlKeres);
        $stmtKeres->bind_param("i", $felhasznalo_id);
        $stmtKeres->execute();
        $stmtKeres->store_result();
        $stmtKeres->bind_result($testsuly, $magassag, $cel, $nem, $eletkor, $aktivitas);

        if (!$stmtKeres->fetch()) {
            $hibak[] = "Hiba történt az adatok lekérdezése során.";
        }

        $stmtKeres->close();

        if($nem == 'Férfi'){
        $bmr = 10 * $testsuly + 6.25 * $magassag - 5 * $eletkor + 5; 
        } elseif($nem == 'Nő'){
        $bmr = 10 * $testsuly + 6.25 * $magassag - 5 * $eletkor - 161; 
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
            <p><b> <?php echo $uzenet; ?> </b></p> 
        <?php } ?>

        </form>
    </div>
    
    <header>Ételek</header>
        
    
    <h3>Reggeli:</h3>
    <?php

        
        $reggeli_kaloria = $fogyasztando * 0.25;

        
                
                $kivalasztott_kepek = $_SESSION["kivalasztott_kepek"];
                $osszes_kivalasztott_kaloria = 0;

                
                foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
                    $sqlKeres = "SELECT kaloria FROM etelek WHERE etel_id = ? AND reggeli = 1";
                    $stmtKeres = $conn->prepare($sqlKeres);
                    $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
                    $stmtKeres->execute();
                    $stmtKeres->store_result();
                    $stmtKeres->bind_result($kaloria);
                
                    if ($stmtKeres->fetch()) {
                        $osszes_kivalasztott_kaloria += $kaloria;
                    }
                
                    $stmtKeres->close();
                }

                
                $reggeli_kaloria -= $osszes_kivalasztott_kaloria;

                
                $sqlKeres = "SELECT nev, kaloria FROM etelek WHERE reggeli = 1 AND etel_id IN (" . implode(",", $kivalasztott_kepek) . ") ORDER BY kaloria DESC LIMIT 1";
                $stmtKeres = $conn->prepare($sqlKeres);
                $stmtKeres->execute();
                $stmtKeres->store_result();
                $stmtKeres->bind_result($legnagyobb_kaloria_nev, $legnagyobb_kaloria);

                if ($stmtKeres->fetch()) {
                    
                    $szorzo_faktor = $reggeli_kaloria / $legnagyobb_kaloria;

                    
                    $reggeli_kaloria -= ($legnagyobb_kaloria * $szorzo_faktor);
                }
            
    ?>

<div class="kep-kontener">
    <?php
    
    foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
        $sqlKeres = "SELECT nev, kep FROM etelek WHERE etel_id = ? AND reggeli = 1";
        $stmtKeres = $conn->prepare($sqlKeres);
        $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
        $stmtKeres->execute();
        $stmtKeres->store_result();
        $stmtKeres->bind_result($nev, $kep);

        
        if ($stmtKeres->fetch()) {
            
            ?>
            <div class="kep-targy">
                <div>
                    
                    <img id="img_<?php echo $kivalasztott_kepek_id; ?>" src="<?php echo htmlspecialchars($kep); ?>" alt="<?php echo htmlspecialchars($nev); ?>" data-etel-id="<?php echo $kivalasztott_kepek_id; ?>"><br>
                    <label id="label_<?php echo $kivalasztott_kepek_id; ?>">
                        <?php echo htmlspecialchars($nev); ?><br>
                        <?php if($nev == $legnagyobb_kaloria_nev){?>
                            <?php echo number_format($szorzo_faktor + 1, 2); ?> 
                        <?php } else { ?>
                            1
                        <?php } ?>
                        Adag
                    </label>
                </div>
            </div>
            <script>
                
                document.getElementById('img_<?php echo $kivalasztott_kepek_id; ?>').addEventListener('click', function() {
                    const etelId = this.getAttribute('data-etel-id');
                    window.location.href = `etel.php?etel_id=${etelId}`;
                });
                document.getElementById('label_<?php echo $kivalasztott_kepek_id; ?>').addEventListener('click', function() {
                    const etelId = document.getElementById('img_<?php echo $kivalasztott_kepek_id; ?>').getAttribute('data-etel-id');
                    window.location.href = `etel.php?etel_id=${etelId}`;
                });
            </script>
            <?php
        }
        
        $stmtKeres->close();
    }
    ?>
</div>




    
    <h3>Ebéd:</h3>
    <?php

        
        $ebed_kaloria = $fogyasztando * 0.35;

        
                
                $kivalasztott_kepek = $_SESSION["kivalasztott_kepek"];
                $osszes_kivalasztott_kaloria = 0;

                

                
                $hussal_kaloria = 0;
                $korettel_kaloria = 0;

                
                $hussal_valasztva = false;
                $korettel_valasztva = false;

                
                foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
                    $sqlKeres = "SELECT nev, kaloria, milyenetel FROM etelek WHERE etel_id = ? AND ebed = 1";
                    $stmtKeres = $conn->prepare($sqlKeres);
                    $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
                    $stmtKeres->execute();
                    $stmtKeres->store_result();
                    $stmtKeres->bind_result($nev, $kaloria, $milyenetel);
                
                    
                    if ($stmtKeres->fetch()) {
                        
                        if (!$hussal_valasztva && $milyenetel === "hús") {
                            $hussal_kaloria += $kaloria;
                            $hussal_nev = $nev;
                            $hussal_valasztva = true;
                        }
                        
                        elseif (!$korettel_valasztva && $milyenetel === "köret") {
                            $korettel_kaloria += $kaloria;
                            $korettel_nev = $nev;
                            $korettel_valasztva = true;
                        }
                    }
                
                    
                    if ($hussal_valasztva && $korettel_valasztva) {
                        break;
                    }
                
                    
                    $stmtKeres->close();
                }

                
                $osszes_kivalasztott_kaloria = $hussal_kaloria + $korettel_kaloria;

                
                if ($ebed_kaloria != 0) {
                    $szorzo_faktor = $ebed_kaloria / $osszes_kivalasztott_kaloria;
                    $hussal_kaloria *= $szorzo_faktor;
                    $korettel_kaloria *= $szorzo_faktor;
                }

                $osszes_kivalasztott_kaloria = $hussal_kaloria + $korettel_kaloria;
                $ebed_kaloria -= $osszes_kivalasztott_kaloria;


    ?>

<div class="kep-kontener">
    <?php
    
    $kivalasztott_hus = [];
    $kivalasztott_koret = [];

    
    foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
        $sqlKeres = "SELECT nev, kep, milyenetel FROM etelek WHERE etel_id = ? AND ebed = 1";
        $stmtKeres = $conn->prepare($sqlKeres);
        $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
        $stmtKeres->execute();
        $stmtKeres->store_result();
        $stmtKeres->bind_result($nev, $kep, $milyenetel);

        
        if ($stmtKeres->fetch()) {
            
            if ($milyenetel === "hús") {
                $kivalasztott_hus[] = ["nev" => $nev, "kep" => $kep, "id" => $kivalasztott_kepek_id];
            } elseif ($milyenetel === "köret") {
                $kivalasztott_koret[] = ["nev" => $nev, "kep" => $kep, "id" => $kivalasztott_kepek_id];
            }
        }
    }
    
    $stmtKeres->close();

    
    if (!empty($kivalasztott_hus)) {
        $hus = $kivalasztott_hus[0];
        ?>
        <div class="kep-targy">
            <div>
                
                <img id="img_hus" src="<?php echo htmlspecialchars($hus['kep']); ?>" alt="<?php echo htmlspecialchars($hus['nev']); ?>" data-etel-id="<?php echo $hus['id']; ?>"><br>
                <label id="label_hus">
                    <?php echo htmlspecialchars($hus['nev']); ?> <br>
                    <?php echo number_format($szorzo_faktor, 2); ?> 
                        Adag
                </label>
            </div>
        </div>
        <script>
            
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

    
    if (!empty($kivalasztott_koret)) {
        $koret = $kivalasztott_koret[0];
        ?>
        <div class="kep-targy">
            <div>
                
                <img id="img_koret" src="<?php echo htmlspecialchars($koret['kep']); ?>" alt="<?php echo htmlspecialchars($koret['nev']); ?>" data-etel-id="<?php echo $koret['id']; ?>"><br>
                <label id="label_koret">
                    <?php echo htmlspecialchars($koret['nev']); ?> <br>
                    <?php echo number_format($szorzo_faktor, 2); ?> 
                        Adag
                </label>
            </div>
        </div>
        <script>
            
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




<h3>Vacsora:</h3>
<?php

    
    $vacsora_kaloria = $fogyasztando * 0.25;

            
            $kivalasztott_kepek = $_SESSION["kivalasztott_kepek"];
            $osszes_kivalasztott_kaloria = 0;

            

            
            $hussal_kaloria = 0;
            $korettel_kaloria = 0;
                    
            
            $hussal_valasztva = false;
            $korettel_valasztva = false;

            $kivalasztott_hus = [];
            $kivalasztott_koret = [];

            
            foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
                $sqlKeres = "SELECT nev, kaloria, milyenetel FROM etelek WHERE etel_id = ? AND vacsora = 1";
                $stmtKeres = $conn->prepare($sqlKeres);
                $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
                $stmtKeres->execute();
                $stmtKeres->store_result();
                $stmtKeres->bind_result($nev, $kaloria, $milyenetel);
            
                
                if ($stmtKeres->fetch()) {
                    
                    if ($milyenetel === "hús") {
                        $kivalasztott_hus[] = ['nev' => $nev, 'kaloria' => $kaloria];
                    } else if ($milyenetel === "köret") {
                        $kivalasztott_koret[] = ['nev' => $nev, 'kaloria' => $kaloria];
                    }
                }
            
                
                $stmtKeres->close();
            }


            
            if (!$hussal_valasztva) {
                $hus = $kivalasztott_hus[1]; 
                $hussal_kaloria += $hus['kaloria'];
                $hussal_nev = $hus['nev'];
                $hussal_valasztva = true;
            }

            
            if (!$korettel_valasztva) {
                $koret = $kivalasztott_koret[1]; 
                $korettel_kaloria += ($koret['kaloria']);
                $korettel_nev = ($koret['nev']);
                $korettel_valasztva = true;
            }

            
            $osszes_kivalasztott_kaloria = $hussal_kaloria + $korettel_kaloria;

            
            if ($vacsora_kaloria != 0) {
                $szorzo_faktor = $vacsora_kaloria / $osszes_kivalasztott_kaloria;
                $hussal_kaloria *= $szorzo_faktor;
                $korettel_kaloria *= $szorzo_faktor;
            }


            


?>

<div class="kep-kontener">
    <?php

    $kivalasztott_hus = [];
    $kivalasztott_koret = [];

    
    foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
        $sqlKeres = "SELECT nev, kep, milyenetel FROM etelek WHERE etel_id = ? AND vacsora = 1";
        $stmtKeres = $conn->prepare($sqlKeres);
        $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
        $stmtKeres->execute();
        $stmtKeres->store_result();
        $stmtKeres->bind_result($nev, $kep, $milyenetel);

        
        if ($stmtKeres->fetch()) {
            
            if ($milyenetel === "hús") {
                $kivalasztott_hus[] = ["nev" => $nev, "kep" => $kep, "id" => $kivalasztott_kepek_id];
            } elseif ($milyenetel === "köret") {
                $kivalasztott_koret[] = ["nev" => $nev, "kep" => $kep, "id" => $kivalasztott_kepek_id];
            }
        }
    }
    
    $stmtKeres->close();

    
    if (!empty($kivalasztott_hus)) {
        $hus = $kivalasztott_hus[1];
        ?>
        <div class="kep-targy">
            <div>
                
                <img id="img_hus2" src="<?php echo htmlspecialchars($hus['kep']); ?>" alt="<?php echo htmlspecialchars($hus['nev']); ?>" data-etel-id="<?php echo $hus['id']; ?>"><br>
                <label id="label_hus2">
                    <?php echo htmlspecialchars($hus['nev']); ?> <br>
                    <?php echo number_format($szorzo_faktor, 2); ?> 
                        Adag
                </label>
            </div>
        </div>
        <script>
            
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

    
    if (!empty($kivalasztott_koret)) {
        $koret = $kivalasztott_koret[1];
        ?>
        <div class="kep-targy">
            <div>
                
                <img id="img_koret2" src="<?php echo htmlspecialchars($koret['kep']); ?>" alt="<?php echo htmlspecialchars($koret['nev']); ?>" data-etel-id="<?php echo $koret['id']; ?>"><br>
                <label id="label_koret2">
                    <?php echo htmlspecialchars($koret['nev']); ?> <br>
                    <?php echo number_format($szorzo_faktor, 2); ?> 
                        Adag
                </label>
            </div>
        </div>
        <script>
            
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

    
    <h3>Uzsonna:</h3>
    <?php

    
    $uzsonna_kaloria = $fogyasztando * 0.15;

            
            $kivalasztott_kepek = $_SESSION["kivalasztott_kepek"];
            $osszes_kivalasztott_kaloria = 0;

            
            $osszes_kivalasztott_kaloria = 0;
            foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
                $sqlKeres = "SELECT kaloria FROM etelek WHERE etel_id = ? AND uzsonna = 1";
                $stmtKeres = $conn->prepare($sqlKeres);
                $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
                $stmtKeres->execute();
                $stmtKeres->store_result();
                $stmtKeres->bind_result($kaloria);
            
                if ($stmtKeres->fetch()) {
                    $osszes_kivalasztott_kaloria += $kaloria;
                }
            
                $stmtKeres->close();
            }

            
            $uzsonna_kaloria -= $osszes_kivalasztott_kaloria;

            
            $sqlKeres = "SELECT nev, kaloria FROM etelek WHERE uzsonna = 1 AND etel_id IN (" . implode(",", $kivalasztott_kepek) . ") ORDER BY kaloria DESC LIMIT 1";
            $stmtKeres = $conn->prepare($sqlKeres);
            $stmtKeres->execute();
            $stmtKeres->store_result();
            $stmtKeres->bind_result($legnagyobb_kaloria_nev, $legnagyobb_kaloria);
                    
            if ($stmtKeres->fetch()) {
                
                $szorzo_faktor = $uzsonna_kaloria / $legnagyobb_kaloria;
                
                
                $uzsonna_kaloria -= ($legnagyobb_kaloria * $szorzo_faktor);
            }

            

    ?>
<div class="kep-kontener">
    <?php
    foreach ($kivalasztott_kepek as $kivalasztott_kepek_id) {
        
        $_SESSION['kivalasztott_etel_nev'] = '';
        $_SESSION['kivalasztott_etel_kep'] = '';
        
        $sqlKeres = "SELECT nev, kep FROM etelek WHERE etel_id = ? AND uzsonna = 1";
        $stmtKeres = $conn->prepare($sqlKeres);
        $stmtKeres->bind_param("i", $kivalasztott_kepek_id);
        $stmtKeres->execute();
        $stmtKeres->store_result();
        $stmtKeres->bind_result($nev, $kep);
        
        
        if ($stmtKeres->fetch()) {
            
            $_SESSION['kivalasztott_etel_nev'] = $nev;
            $_SESSION['kivalasztott_etel_kep'] = $kep;

            
            ?>
            <div class="kep-targy">
                <div>
                    
                    <img id="img_<?php echo $kivalasztott_kepek_id; ?>" src="<?php echo htmlspecialchars($kep); ?>" alt="<?php echo htmlspecialchars($nev); ?>"><br>
                    <label id="label_<?php echo $kivalasztott_kepek_id; ?>">
                        <?php echo htmlspecialchars($nev); ?><br>
                        <?php if($nev == $legnagyobb_kaloria_nev){?>
                            <?php echo number_format($szorzo_faktor + 1, 2); ?> 
                        <?php } else { ?>
                            1
                        <?php } ?>
                            Adag
                    </label>
                </div>
            </div>
            <script>
                
                document.getElementById('img_<?php echo $kivalasztott_kepek_id; ?>').addEventListener('click', function() {
                    window.location.href = `etel.php?etel_id=<?php echo $kivalasztott_kepek_id; ?>`; 
                });
                document.getElementById('label_<?php echo $kivalasztott_kepek_id; ?>').addEventListener('click', function() {
                    window.location.href = `etel.php?etel_id=<?php echo $kivalasztott_kepek_id; ?>`; 
                });
            </script>
            <?php
        }
        
    }
    
    $stmtKeres->close();
    ?>
</div>

    <script>
        
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var parentDiv = checkbox.closest('.kep-targy');
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