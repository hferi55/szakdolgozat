<?php
require("../sql/sql.php");
session_start();

$errorMessage = ""; // Itt inicializáljuk a változót

// Felhasználó bejelentkezése

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"]; 
    $password = $_POST["jelszo"]; 

    // Ellenőrzés a felhasználónév és jelszó alapján az adatbázisban
    $sql = "SELECT felhasznalo_id, jelszo FROM felhasznalo WHERE email_cim=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    try {
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($felhasznalo_id, $hashed_password);
                $stmt->fetch();

                // Ellenőrzés a hashelt jelszó alapján
                if (password_verify($password, $hashed_password)) {
                    // Sikeres bejelentkezés
                    $_SESSION['felhasznalo_id'] = $felhasznalo_id; // Felhasználó azonosítója a session-be
                    header("Location: loggedin.php"); 
                    exit();
                } else {
                    $errorMessage = "Hibás email cím vagy jelszó.";
                }
            } else {
                $errorMessage = "Hibás email cím vagy jelszó.";
            }
        } else {
            $errorMessage = "Hiba történt a bejelentkezés során.";
        }
    } catch (Exception $e) {
        $errorMessage = "Hiba: " . $e->getMessage();
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
    <title>Étrendkészítő Weboldal Bejelentkezés</title>
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="fooldalheader">
    <h1 class="cim">ÉKW</h1>
    <nav class="navbar">
        <a href="../index.php">Főoldal</a> 
    </nav>
</header>

    <div class="container">
        <div class="bejelentkezes kartya">
            <header>Bejelentkezés</header>
            
            <?php if ($errorMessage): ?>
                <p><?php echo $errorMessage; ?></p>
            <?php endif; ?>

            <form action="" method="post">
                <input type="text" placeholder="Adja meg az email címét" name="email" required>
                <input type="password" placeholder="Adja meg a jelszavát" name="jelszo" required>
                <input type="submit" class="button" value="Bejelentkezés" name="submit">
            </form>
            <div class="signup">
                <span class="signup">Még nincs fiókja?
                    <a href="../view/regisztracio.php">Regisztráció</a>
                </span>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Adatbázis kapcsolat lezárása
mysqli_close($conn);
?>