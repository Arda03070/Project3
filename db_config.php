<?php

$host = 'localhost'; 
$dbname = 'fitforfun'; 
$user = 'root'; 
$password = ''; 

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
} catch (PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

session_start();
?>
