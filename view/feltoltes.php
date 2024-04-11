<?php
// Adatbázis kapcsolat beállítása
require("../sql/sql.php");

// Ellenőrizzük, hogy a kiválasztott képek érkeztek-e a kérésben
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_images'])) {
    // Kiválasztott képek id-jainak kinyerése
    $selectedImages = json_decode($_POST['selected_images']);

    // Ellenőrizzük, hogy a felhasználó be van-e jelentkezve, ha szükséges
    session_start();
    if (!isset($_SESSION['felhasznalo_id'])) {
        // Felhasználó nincs bejelentkezve, itt kezelhetjük ezt az esetet
        http_response_code(401); // Unauthorized
        exit("Nincs bejelentkezve felhasználó.");
    }

    // Felhasználó azonosítója
    $felhasznalo_id = $_SESSION['felhasznalo_id'];

    try {
        // Töröljük az előzőleg tárolt képeket
        $deleteQuery = "UPDATE felhasznalo SET kivalasztott_kepek = NULL WHERE felhasznalo_id = ?";
        $stmtDelete = $conn->prepare($deleteQuery);
        $stmtDelete->execute([$felhasznalo_id]);

        // Feltöltjük az új kiválasztott képekkel
        $insertQuery = "UPDATE felhasznalo SET kivalasztott_kepek = ? WHERE felhasznalo_id = ?";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->execute([json_encode($selectedImages), $felhasznalo_id]);

        // Sikeres művelet
        http_response_code(200); // OK
        echo "A kiválasztott képek sikeresen feltöltve.";
    } catch (PDOException $e) {
        // Hiba esetén
        http_response_code(500); // Internal Server Error
        echo "Hiba történt a kiválasztott képek feltöltése közben: " . $e->getMessage();
    }
} else {
    // Hibás vagy hiányzó adatok esetén
    http_response_code(400); // Bad Request
    echo "Hibás vagy hiányzó adatok.";
}
