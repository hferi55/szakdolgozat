<?php
require("../sql/sql.php");
session_start();

$hibaUzenet = ""; 



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"]; 
    $jelszo = $_POST["jelszo"]; 

    
    $sql = "SELECT felhasznalo_id, jelszo FROM felhasznalo WHERE email_cim=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    try {
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($felhasznalo_id, $hashed_jelszo);
                $stmt->fetch();

                
                if (password_verify($jelszo, $hashed_jelszo)) {
                    
                    $_SESSION['felhasznalo_id'] = $felhasznalo_id; 
                    header("Location: bejelentkezve.php"); 
                    exit();
                } else {
                    $hibaUzenet = "Hibás email cím vagy jelszó.";
                }
            } else {
                $hibaUzenet = "Hibás email cím vagy jelszó.";
            }
        } else {
            $hibaUzenet = "Hiba történt a bejelentkezés során.";
        }
    } catch (Exception $e) {
        $hibaUzenet = "Hiba: " . $e->getMessage();
    }

    $stmt->close();
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
        <a href="../index.php">Főoldal</a> 
    </nav>
</header>

    <div class="kontener">
        <div class="bejelentkezes kartya">
            <header>Bejelentkezés</header>
            
            <?php if ($hibaUzenet): ?>
                <p><?php echo $hibaUzenet; ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <input type="text" placeholder="Adja meg az email címét" name="email" required>
                <input type="password" placeholder="Adja meg a jelszavát" name="jelszo" required>
                <input type="submit" class="button" value="Bejelentkezés" name="kuldes">
            </form>
            <div class="regisztralas">
                <span class="regisztralas">Még nincs fiókja?
                    <a href="../view/regisztracio.php">Regisztráció</a>
                </span>
            </div>
        </div>
    </div>
</body>
</html>
<?php

mysqli_close($conn);
?>