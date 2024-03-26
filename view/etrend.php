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


  <style>
  /* Stílusok a navigációs sávhoz */
  .preferencia nav {
    color: #fff;
    background: #009579;
    overflow: hidden;
  }

  .preferencia nav a {
    float: left;
    display: block;
    color: white;
    text-align: center;
    padding: 14px 20px;
    text-decoration: none;
  }

  .preferencia nav a:hover {
    background-color: #006653;
  }

  /* Stílusok a képgalériákhoz */
  .gallery {
    display: none;
    flex-wrap: wrap;
  }

  .gallery.active {
    display: flex;
  }

  .gallery img {
    width: 200px;
    margin: 10px;
  }

  .image-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px; /* 20px távolság a sorok között */
}

.image-container figure {
    width: 40%; /* Egy sorba két kép */
    margin: 0;
}

.image-container figure img {
    width: 100%;
    height: auto;
}


  
</style>


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
                <a href="../view/etrend.php">Étrendem</a> |
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
    
    <header>Preferencia</header>
    <div class="preferencia">
        <nav>
            <a onclick="showGallery('reggeli')">Reggeli</a>
            <a onclick="showGallery('ebed')">Ebéd</a>
            <a onclick="showGallery('vacsora')">Vacsora</a>
            <a onclick="showGallery('uzsonna')">Uzsonna</a>
        </nav>
        
        <?php
            // Adatok lekérdezése
            $sql = "SELECT `kep` FROM etelek WHERE etel_id=1";
            $result = $conn->query($sql);

            // Kép elérési útvonalának lekérése
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $kep = $row['kep'];
            } else {
                $kep = ''; // Alapértelmezett érték, ha nincs kép
            }
        
            // Adatbázis kapcsolat bezárása
            $conn->close();
        ?>

        <div id="reggeli" class="gallery">
            <!-- Reggeli --> 
            <h3>Reggeli:</h3>

            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="<?php echo htmlspecialchars($kep); ?>" class="kepek" alt="Zabkása gyümölccsel és mandulával">
                        <figcaption>Zabkása gyümölccsel és mandulával</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="<?php echo htmlspecialchars($kep); ?>" class="kepek" alt="Görög joghurt gyümölcsökkel és mézzel">
                        <figcaption>Görög joghurt gyümölcsökkel és mézzel</figcaption>
                    </figure>
                </label>
            </div>

            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli3.jpg" class="kepek" alt="Tojásrántotta zöldségekkel">
                        <figcaption>Zabkása gyümölccsel és mandulával</figcaption>
                    </figure>
                </label>
            
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli4.jpg" class="kepek" alt="Avokádós teljes kiőrlésű pirítós">
                        <figcaption>Avokádós teljes kiőrlésű pirítós</figcaption>
                    </figure>
                </label>
            </div>

            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli5.jpg" class="kepek" alt="Banános zabkeksz">
                        <figcaption>Banános zabkeksz</figcaption>
                    </figure>
                </label>
            
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli6.jpg" class="kepek" alt="Chia-puding gyümölcsökkel">
                        <figcaption>Chia-puding gyümölcsökkel</figcaption>
                    </figure>
                </label>
            </div>

            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli7.jpg" class="kepek" alt="Túrós zabkeksz">
                        <figcaption>Túrós zabkeksz</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli8.jpg" class="kepek" alt="Birsalma és fahéjas zabkása">
                        <figcaption>Birsalma és fahéjas zabkása</figcaption>
                    </figure>
                </label>
            </div>

            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli9.jpg" class="kepek" alt="Avokádó és paradicsom omlett">
                        <figcaption>Avokádó és paradicsom omlett</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/reggeli10.jpg" class="kepek" alt="Gyors smoothie tál">
                        <figcaption>Gyors smoothie tál</figcaption>
                    </figure>
                </label>
            </div>

        </div>
        
    <div id="ebed" class="gallery">
        <!-- Ebéd -->
        
                <h3>Ebéd:</h3>
                
                
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed1.jpg" class="kepek" alt="Sült csirke salátával">
                        <figcaption>Sült csirke salátával</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed2.jpg" class="kepek" alt="Quinoa zöldségekkel">
                        <figcaption>Quinoa zöldségekkel</figcaption>
                    </figure>
                </label>
            </div>
            
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed3.jpg" class="kepek" alt="Lencseleves spenóttal">
                        <figcaption>Lencseleves spenóttal</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed4.jpg" class="kepek" alt="Tonhalas teljes kiőrlésű wrap">
                        <figcaption>Tonhalas teljes kiőrlésű wrap</figcaption>
                    </figure>
                </label>
            </div>
                
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed5.jpg" class="kepek" alt="Sült lazac édesburgonyával">
                        <figcaption>Sült lazac édesburgonyával</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed6.jpg" class="kepek" alt="Színes borsókrémleves">
                        <figcaption>Színes borsókrémleves</figcaption>
                    </figure>
                </label>
            </div>
                
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed7.jpg" class="kepek" alt="Szezámmagos csirke saláta">
                        <figcaption>Szezámmagos csirke saláta</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed8.jpg" class="kepek" alt="Quinoa saláta fetával és görög olívával">
                        <figcaption>Quinoa saláta fetával és görög olívával</figcaption>
                    </figure>
                </label>
            </div>
                
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed9.jpg" class="kepek" alt="Zöldséges tojás wrap">
                        <figcaption>Zöldséges tojás wrap</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/ebed10.jpg" class="kepek" alt="Sütőben sült lazac spárgával">
                        <figcaption>Sütőben sült lazac spárgával</figcaption>
                    </figure>
                </label>
            </div>    
                
                
        
    </div>
        
    <div id="vacsora" class="gallery">
        <!-- Vacsora -->
        
            <h3>Vacsora:</h3>
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora1.jpg" class="kepek" alt="Vegetáriánus csicseriborsó curry">
                        <figcaption>Vegetáriánus csicseriborsó curry</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora2.jpg" class="kepek" alt="Grillezett zöldségek tofuval">
                        <figcaption>Grillezett zöldségek tofuval</figcaption>
                    </figure>
                </label>
            </div>    
            
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora3.jpg" class="kepek" alt="Sült csirkecomb sült zöldségekkel">
                        <figcaption>Sült csirkecomb sült zöldségekkel</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora4.jpg" class="kepek" alt="Brokkoli spagetti fokhagymás olívaolajjal">
                        <figcaption>Brokkoli spagetti fokhagymás olívaolajjal</figcaption>
                    </figure>
                </label>
            </div>
            
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora5.jpg" class="kepek" alt="Sushi tál lazaccal és zöldségekkel">
                        <figcaption>Sushi tál lazaccal és zöldségekkel</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora6.jpg" class="kepek" alt="Vegetáriánus lencsédal">
                        <figcaption>Vegetáriánus lencsédal</figcaption>
                    </figure>
                </label>
            </div>
            
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora7.jpg" class="kepek" alt="Fűszeres csirke curry">
                        <figcaption>Fűszeres csirke curry</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora8.jpg" class="kepek" alt="Brokkoli-karfiol pite">
                        <figcaption>Brokkoli-karfiol pite</figcaption>
                    </figure>
                </label>
            </div>
            
            <div class="image-container">
                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora9.jpg" class="kepek" alt="Mexikói csirke quinoa-val">
                        <figcaption>Mexikói csirke quinoa-val</figcaption>
                    </figure>
                </label>

                <label>
                    <figure>
                        <input type="checkbox">
                        <img src="../kepek/vacsora10.jpg" class="kepek" alt="Grillezett hal filé édesburgonya pürével">
                        <figcaption>Grillezett hal filé édesburgonya pürével</figcaption>
                    </figure>
                </label>
            </div>
            
        
    </div>
        
    <div id="uzsonna" class="gallery">
       <!-- Uzsonna -->
        
            <h3>Uzsonna:</h3>
            <br style="margin-top: 20px; margin-bottom: 10px;">
            <img src="../kepek/uzsonna1.jpg" class="kepek" alt="Mandula és mazsola mix">
            <img src="../kepek/uzsonna2.jpg" class="kepek" alt="Görög joghurt gyümölcssaláttal">
            <img src="../kepek/uzsonna3.jpg" class="kepek" alt="Almás mogyoróvaj szendvics">
            <img src="../kepek/uzsonna4.jpg" class="kepek" alt="Zöldségek hummusszal">
            <img src="../kepek/uzsonna5.jpg" class="kepek" alt="Banános és epres smoothie">
            <img src="../kepek/uzsonna6.jpg" class="kepek" alt="Szezámmagos alma szeletek">
            <img src="../kepek/uzsonna7.jpg" class="kepek" alt="Gyümölcsös joghurt pohárban">
            <img src="../kepek/uzsonna8.jpg" class="kepek" alt="Teljes kiőrlésű kenyér paradicsomsalátával">
            <img src="../kepek/uzsonna9.jpg" class="kepek" alt="Avokádó toast tojással">
            <img src="../kepek/uzsonna10.jpg" class="kepek" alt="Céklás és répás smoothie">      
        
    </div>
        
    <script>
    function showGallery(option) {
        var galleries = document.querySelectorAll('.gallery');
        galleries.forEach(function(gallery) {
            gallery.classList.remove('active');
        });

        var selectedGallery = document.getElementById(option);
        selectedGallery.classList.add('active');

        // Az etrend_lap osztályhoz tartozó elemnek átírjuk a margin-top tulajdonságát 750px-re
        var etrendLapElement = document.querySelector('.etrend_lap');
        etrendLapElement.style.marginTop = '750px';
    }
    </script>
    
    </div>

</body>
</html>