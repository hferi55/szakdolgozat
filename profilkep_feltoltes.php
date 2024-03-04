<?php
session_start();

// Ellenőrizzük, hogy a fájl valóban feltöltődött-e
if(isset($_FILES['profilkep']) && $_FILES['profilkep']['error'] === UPLOAD_ERR_OK) {
    // Ellenőrizzük a fájl típusát
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    if(in_array($_FILES['profilkep']['type'], $allowed_types)) {
        $target_dir = "profilkepek/";
        // A fájlnév biztonságossá tétele
        $newFileName = time() . "-" . basename($_FILES['profilkep']['name']);
        $target_file = $target_dir . $newFileName;

        // Fájl áthelyezése
        if(move_uploaded_file($_FILES['profilkep']['tmp_name'], $target_file)) {
            echo "A fájl sikeresen feltöltve.";

            // Session-ben tároljuk el a profilkép elérési útját
            $_SESSION['profilkep'] = $target_file;

            // Átirányítás a profil oldalra
            header("Location: ../view/profil.php?upload=success");
        } else {
            echo "Hiba történt a fájl feltöltése közben.";
        }
    } else {
        echo "Csak JPG, JPEG, PNG vagy GIF formátumú fájlok engedélyezettek.";
    }
} else {
    echo "Hiba történt a fájl feltöltése közben.";
}
?>
