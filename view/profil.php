<?php
require("../sql/sql.php");
session_start();


if (!isset($_SESSION['felhasznalo_id'])) {
    header("Location: ../view/bejelentkezes.php");
    exit();
}


$nev = $email = $jelszo = $jelszoMegerosit = ""; 


$hibak = array();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    
    $nev = $_POST["nev"];

    
    if (empty($nev) || strlen($nev) < 3 || strlen($nev) > 8 || preg_match("/[^a-zA-Z0-9]/", $nev)) {
        $hibak[] = "A névnek legalább 3 karakter hosszúnak, maximum 8 karakter lehet, és nem tartalmazhat speciális karaktert.";
    }

    
    $email = $_POST["email"];

    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hibak[] = "Érvénytelen email cím formátum.";
    }

    
    $jelszo = isset($_POST["jelszo"]) ? $_POST["jelszo"] : "";

    
    $jelszoMegerosit = isset($_POST['jelszo_megerosit']) ? $_POST['jelszo_megerosit'] : '';

    
    if ($jelszo !== $jelszoMegerosit) {
        $hibak[] = "A jelszó és a jelszó megerősítése nem egyezik meg.";
    }

    
    if (empty($jelszo) || strlen($jelszo) < 4 || !preg_match("/[A-Z]/", $jelszo) || preg_match("/[^a-zA-Z0-9]/", $jelszo)) {
        $hibak[] = "A jelszónak legalább 4 karakter hosszúnak, tartalmaznia kell legalább 1 nagybetűt, és nem tartalmazhat speciális karaktert.";
    }

    
    if (empty($hibak)) {
        
        $hashelt_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

        
        $_SESSION['nev'] = $nev;
        $_SESSION['email'] = $email;

        
        $felhasznalo_id = $_SESSION['felhasznalo_id']; 
        $sqlFrissit = "UPDATE felhasznalo SET nev=?, email_cim=?, jelszo=? WHERE felhasznalo_id=?";
        $stmtFrissit = $conn->prepare($sqlFrissit);
        $stmtFrissit->bind_param("sssi", $nev, $email, $hashelt_jelszo, $felhasznalo_id);

        if ($stmtFrissit->execute()) {
            
        } else {
            $hibak[] = "Hiba történt az adatok frissítése során.";
        }
    }
} else {
    
    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    $sqlKeres = "SELECT nev, email_cim FROM felhasznalo WHERE felhasznalo_id=?";
    $stmtKeres = $conn->prepare($sqlKeres);
    $stmtKeres->bind_param("i", $felhasznalo_id);
    $stmtKeres->execute();
    $stmtKeres->store_result();
    $stmtKeres->bind_result($nev, $email);

    if (!$stmtKeres->fetch()) {
        $hibak[] = "Hiba történt az adatok lekérdezése során.";
    }

    $stmtKeres->close();
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
                <a href="../kijelentkezes.php">Kijelentkezés</a>
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



<div class="profil_lap">
    <div class="kartya">
    <header>Profil</header>

    <?php
    
    if (!empty($hibak) && isset($_POST["adatokmodositasa"])) {
        echo '<div class="hiba-uzenetek">';
        echo '<ul>';
        foreach ($hibak as $hiba) {
            echo '<li>' . $hiba . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    
    if(empty($hibak) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["adatokmodositasa"])) {
        
        echo '<div class="sikeres-uzenet">';
            echo "Adataid sikeresen frissítve.";
        echo '</div>';
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regebbietrendek'])) {
        header("Location: regebbietrendek.php");
        exit();
    }

    try {
        $conn = new PDO("mysql:host=$szervernev;dbname=$dbnev", $felhasznalonev, $jelszo);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Hiba: " . $e->getMessage();
    }

    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    
    $sql = "SELECT * FROM etkezes WHERE felhasznalo_id = :felhasznalo_id LIMIT 1"; 
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':felhasznalo_id', $felhasznalo_id);
    $stmt->execute();
    $sor = $stmt->fetch(PDO::FETCH_ASSOC);
    $szamol = $stmt->rowCount(); 

    ?>

    <form action="" method="post">

        
        <h3>Név:</h3>
        <label>Jelenlegi név: <?php echo htmlspecialchars($nev); ?></label>
        <br>
        <input type="text" placeholder="Név" name="nev" value="">
        <br>
        <label>A név minimum 3, maximum 8 karakter lehet, <br>
                nem tartalmazhat speciális karaktert.</label>
        <br>

        
        <h3>Email-cím</h3>
        <label>Jelenlegi email-cím: <?php echo htmlspecialchars($email); ?></label>
        <br>
        <input type="text" placeholder="Email cím" name="email" value="">

        
        <h3>Jelszó</h3>
        <input type="password" placeholder="Jelszó" name="jelszo" value="">
        <br>
        <label>A jelszó minimum 4 karakter lehet, nem tartalmazhat speciális karaktert, <br>
                minimum 1 nagy karaktert kell tartalmaznia.</label>
        <br>

        
        <h3>Jelszó megerősítése</h3>
        <input type="password" placeholder="Jelszó megerősítése" name="jelszo_megerosit" value="">

        <input type="submit" value="Adatok módosítása" class="button" name="adatokmodositasa">

        <?php
        if ($szamol > 0) {
            
            echo '<input type="submit" value="Régebbi étrendeim megtekintése" class="button" name="regebbietrendek">';
        }
    
        ?>

    </form>
    </div>
</div>

</body>
</html>
