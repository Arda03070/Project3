<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

try {
    // Totaal aantal actieve leden (gebruikers)
    $stmt = $conn->query("SELECT COUNT(*) as totaal FROM gebruiker WHERE IsActief = 1");
    $totaalLeden = (int)$stmt->fetch()['totaal'];

    // Aantal nieuwe leden van deze maand
    $stmt = $conn->query("SELECT COUNT(*) as nieuw FROM gebruiker WHERE MONTH(Datumaangemaakt) = MONTH(CURRENT_DATE()) AND YEAR(Datumaangemaakt) = YEAR(CURRENT_DATE()) AND IsActief = 1");
    $nieuweLeden = (int)$stmt->fetch()['nieuw'];

    echo json_encode([
        'success' => true,
        'totaal' => $totaalLeden,
        'nieuw' => $nieuweLeden
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
