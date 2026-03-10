<?php
// Database setup script
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host", $user, $password);
    
    // Database aanmaken
    $conn->exec("CREATE DATABASE IF NOT EXISTS fitforfun CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    
    // Verbinding met database
    $conn = new PDO("mysql:host=$host;dbname=fitforfun;charset=utf8mb4", $user, $password);
    
    // Leden tabel aanmaken
    $sql = "CREATE TABLE IF NOT EXISTS leden (
        id INT AUTO_INCREMENT PRIMARY KEY,
        naam VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        telefoon VARCHAR(20),
        wachtwoord VARCHAR(255) NOT NULL,
        rol ENUM('lid', 'admin') DEFAULT 'lid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    
    echo "✓ Database 'fitforfun' en tabel 'leden' succesvol aangemaakt!<br>";
    echo "Je kunt nu het lidmaatschapssysteem gebruiken.";
    
} catch (PDOException $e) {
    echo "Fout: " . $e->getMessage();
}
?>
