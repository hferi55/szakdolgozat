<?php
require("../sql/sql.php");
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
        .lap {
            overflow: auto;
        }

        .adatok {
            float: left;
            width: 40%;
        }

        .etelek {
            overflow-x: auto;
            white-space: nowrap;
            margin-top: 10px;
        }

        .kepek {
            width: 200px; /* Kép szélessége */
            height: auto;
            margin: 5px; /* Képek közötti margó */
            display: inline-block;
            vertical-align: top;
            border-radius: 50px; /* Itt állítsd be a kívánt sugárt */
        }

    </style>
</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <a href="../view/rolunk2.php">Rólunk</a> |
        <a href="../view/profil.php">Profil</a> |
        <a href="../view/etrendkeszitese.php">Étrendkészítése</a> |
        <a href="../logout.php">Kijelentkezés</a>
    </nav>
</header>

<div class="lap">
    <div class="kartya">
        <header>Étrendkészítése</header>
        <form action="" method="post">
            <div class="adatok">

                <!-- Életkor -->
                <h3>Életkor:</h3>
                <input type="number" placeholder="Adja meg az életkorát" name="eletkor">
                <br>

                <!-- Testsúly -->
                <h3>Testsúly:</h3>
                <input type="number" placeholder="Adja meg a testsúlyát" name="testsuly">
                <br>

                <!-- Magasság -->
                <h3>Magasság:</h3>
                <input type="number" placeholder="Adja meg a magasságát" name="magassag">
                <br>

                <!-- Nem -->
                <h3>Nem:</h3>
                <select name="nem" id="nem">
                    <option value="1">Válasszon</option>
                    <option value="2">Nő</option>
                    <option value="3">Férfi</option>
                </select>
                <br>

                <!-- Aktivitási szint -->
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

                <!-- Cél -->
                <h3>Cél:</h3>
                <select name="cel" id="cel">
                    <option value="1">Válasszon</option>
                    <option value="2">Szintentartás</option>
                    <option value="3">Fogyás</option>
                    <option value="4">Tömegelés</option>
                </select>

            </div>

            <div class="etelek">
                <!-- Reggeli -->
                <h3>Reggeli:</h3>
                <img src="../kepek/reggeli1.jpg" class="kepek" alt="Zabkása gyümölccsel és mandulával">
                <img src="../kepek/reggeli2.jpg" class="kepek" alt="Görög joghurt gyümölcsökkel és mézzel">
                <img src="../kepek/reggeli3.jpg" class="kepek" alt="Tojásrántotta zöldségekkel">
                <img src="../kepek/reggeli4.jpg" class="kepek" alt="Avokádós teljes kiőrlésű pirítós">
                <img src="../kepek/reggeli5.jpg" class="kepek" alt="Banános zabkeksz">
                <img src="../kepek/reggeli6.jpg" class="kepek" alt="Chia-puding gyümölcsökkel">
                <img src="../kepek/reggeli7.jpg" class="kepek" alt="Túrós zabkeksz">
                <img src="../kepek/reggeli8.jpg" class="kepek" alt="Birsalma és fahéjas zabkása">
                <img src="../kepek/reggeli9.jpg" class="kepek" alt="Avokádó és paradicsom omlett">
                <img src="../kepek/reggeli10.jpg" class="kepek" alt="Gyors smoothie tál">

              <!-- Ebéd -->
              <h3>Ebéd:</h3>
                <img src="../kepek/ebed1.jpg" class="kepek" alt="Sült csirke salátával">
                <img src="../kepek/ebed2.jpg" class="kepek" alt="Quinoa zöldségekkel">
                <img src="../kepek/ebed3.jpg" class="kepek" alt="Lencseleves spenóttal">
                <img src="../kepek/ebed4.jpg" class="kepek" alt="Tonhalas teljes kiőrlésű wrap">
                <img src="../kepek/ebed5.jpg" class="kepek" alt="Sült lazac édesburgonyával">
                <img src="../kepek/ebed6.jpg" class="kepek" alt="Színes borsókrémleves">
                <img src="../kepek/ebed7.jpg" class="kepek" alt="Szezámmagos csirke saláta">
                <img src="../kepek/ebed8.jpg" class="kepek" alt="Quinoa saláta fetával és görög olívával">
                <img src="../kepek/ebed9.jpg" class="kepek" alt="Zöldséges tojás wrap">
                <img src="../kepek/ebed10.jpg" class="kepek" alt="Sütőben sült lazac spárgával">

              <!-- Vacsora -->
              <h3>Vacsora:</h3>
                <img src="../kepek/vacsora1.jpg" class="kepek" alt="Vegetáriánus csicseriborsó curry">
                <img src="../kepek/vacsora2.jpg" class="kepek" alt="Grillezett zöldségek tofuval">
                <img src="../kepek/vacsora3.jpg" class="kepek" alt="Sült csirkecomb sült zöldségekkel">
                <img src="../kepek/vacsora4.jpg" class="kepek" alt="Brokkoli spagetti fokhagymás olívaolajjal">
                <img src="../kepek/vacsora5.jpg" class="kepek" alt="Sushi tál lazaccal és zöldségekkel">
                <img src="../kepek/vacsora6.jpg" class="kepek" alt="Vegetáriánus lencsédal">
                <img src="../kepek/vacsora7.jpg" class="kepek" alt="Fűszeres csirke curry">
                <img src="../kepek/vacsora8.jpg" class="kepek" alt="Brokkoli-karfiol pite">
                <img src="../kepek/vacsora9.jpg" class="kepek" alt="Mexikói csirke quinoa-val">
                <img src="../kepek/vacsora10.jpg" class="kepek" alt="Grillezett hal filé édesburgonya pürével">


              <!-- Uzsonna -->
              <h3>Uzsonna:</h3>
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

      </form>
    </div>
</div>

</body>
</html>
