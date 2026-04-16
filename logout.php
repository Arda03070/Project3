<?php
include 'db_config.php';

// Update uitlogstatus in de database
if (isset($_SESSION['lid_id'])) {
    try {
        $stmt = $conn->prepare("UPDATE gebruiker SET IsIngelogd = 0, Uitgelogd = CURDATE() WHERE Id = ?");
        $stmt->execute([$_SESSION['lid_id']]);
    } catch (PDOException $e) {
        // Stille fout bij uitloggen
    }
}

session_destroy();

header("Location: index.html");
exit;
?>
