<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id  = isset($data['id'])  ? (int)$data['id']  : 0;
$rol = isset($data['rol']) ? trim($data['rol']) : '';

if (!$id || !in_array($rol, ['Lid', 'Administrator', 'Medewerker', 'Gastgebruiker'])) {
    echo json_encode(['success' => false, 'error' => 'Ongeldige gegevens']);
    exit;
}

// Voorkom dat admin zichzelf degradeert
if ($id === (int)$_SESSION['lid_id'] && $rol !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Je kunt je eigen rol niet wijzigen']);
    exit;
}

try {
    // Update de rol in de rol tabel
    $stmt = $conn->prepare("UPDATE rol SET Naam = ? WHERE GebruikerId = ? AND IsActief = 1");
    $stmt->execute([$rol, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

