<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Ongeldig ID']);
    exit;
}

if ($id === (int)$_SESSION['lid_id']) {
    echo json_encode(['success' => false, 'error' => 'Je kunt jezelf niet verwijderen']);
    exit;
}

try {
    $conn->beginTransaction();

    // Controleer of de gebruiker bestaat
    $stmt = $conn->prepare("SELECT Gebruikersnaam FROM gebruiker WHERE Id = ? AND IsActief = 1");
    $stmt->execute([$id]);
    $gebruiker = $stmt->fetch();

    if (!$gebruiker) {
        echo json_encode(['success' => false, 'error' => 'Lid niet gevonden of al verwijderd']);
        $conn->rollBack();
        exit;
    }

    // Soft-delete de gebruiker
    $stmt = $conn->prepare("UPDATE gebruiker SET IsActief = 0 WHERE Id = ?");
    $stmt->execute([$id]);

    // Soft-delete de bijbehorende rol
    $stmt = $conn->prepare("UPDATE rol SET IsActief = 0 WHERE GebruikerId = ?");
    $stmt->execute([$id]);

    // Soft-delete ook het lid als we email kunnen matchen
    $stmt = $conn->prepare("UPDATE lid SET IsActief = 0 WHERE Email = ?");
    $stmt->execute([$gebruiker['Gebruikersnaam']]);

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
