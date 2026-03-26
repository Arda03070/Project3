<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id  = isset($data['id'])  ? (int)$data['id']  : 0;
$rol = isset($data['rol']) ? trim($data['rol']) : '';

if (!$id || !in_array($rol, ['lid', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Ongeldige gegevens']);
    exit;
}

// Voorkom dat admin zichzelf degradeert
if ($id === (int)$_SESSION['lid_id'] && $rol !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Je kunt je eigen rol niet wijzigen']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE leden SET rol = ? WHERE id = ?");
    $stmt->execute([$rol, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
