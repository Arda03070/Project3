<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

try {
    $stmt = $conn->query("
        SELECT g.Id as id,
               CONCAT(g.Voornaam, IF(g.Tussenvoegsel != '', CONCAT(' ', g.Tussenvoegsel), ''), ' ', g.Achternaam) AS naam,
               g.Gebruikersnaam as email,
               '' as telefoon,
               COALESCE(r.Naam, 'Lid') as rol,
               DATE_FORMAT(g.Datumaangemaakt,'%d-%m-%Y') as aangemeld
        FROM gebruiker g
        LEFT JOIN rol r ON r.GebruikerId = g.Id AND r.IsActief = 1
        WHERE g.IsActief = 1
        ORDER BY g.Datumaangemaakt DESC
    ");
    $gebruikers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $gebruikers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
