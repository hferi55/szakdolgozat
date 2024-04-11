<?php
// Kapott adatok feldolgozása
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_images'])) {
    // Adatbázis kapcsolat beállítása
    require("../sql/sql.php");

    // Kiválasztott képek id-jainak kinyerése
    $selectedImages = json_decode($_POST['selected_images']);

    // Ellenőrizd, hogy a felhasználó be van-e jelentkezve, ha szükséges
    session_start();
    if (!isset($_SESSION['felhasznalo_id'])) {
        // Felhasználó nincs bejelentkezve, itt kezelheted ezt az esetet
        exit("Nincs bejelentkezve felhasználó.");
    }

    // Felhasználó azonosítója
    $felhasznalo_id = $_SESSION['felhasznalo_id'];

    try {
        // Törlés az előzőleg tárolt képekből
        $deleteQuery = "UPDATE felhasznalo SET kivalasztott_kepek = NULL WHERE felhasznalo_id = ?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->execute([$felhasznalo_id]);

        // Feltöltés az új kiválasztott képekkel
        $insertQuery = "UPDATE felhasznalo SET kivalasztott_kepek = ? WHERE felhasznalo_id = ?";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->execute([json_encode($selectedImages), $felhasznalo_id]);

        // Sikeres művelet
        http_response_code(200);
        echo "A kiválasztott képek sikeresen feltöltve.";
    } catch (PDOException $e) {
        // Hiba esetén
        http_response_code(500);
        echo "Hiba történt a kiválasztott képek feltöltése közben: " . $e->getMessage();
    }
} else {
    // Hibás vagy hiányzó adatok esetén
    http_response_code(400);
    echo "Hibás vagy hiányzó adatok.";
}
