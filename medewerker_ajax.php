<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

$is_admin = isset($_SESSION['lid_id']) && $_SESSION['rol'] === 'Administrator';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $conn->query("SELECT * FROM medewerker WHERE IsActief = 1");
        $medewerkers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $medewerkers]);
        exit;
    }
    
    if (!$is_admin) {
        echo json_encode(['success' => false, 'error' => 'Geen toegang tot beheer van medewerkers']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if ($method === 'POST') {
        $voornaam = trim($input['voornaam'] ?? '');
        $tussenvoegsel = trim($input['tussenvoegsel'] ?? '');
        $achternaam = trim($input['achternaam'] ?? '');
        $nummer = (int)($input['nummer'] ?? rand(1000, 9999));
        $soort = trim($input['soort'] ?? 'Beheerder');
        
        if (!$voornaam || !$achternaam) {
            echo json_encode(['success' => false, 'error' => 'Voornaam en achternaam zijn verplicht']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO medewerker (Voornaam, Tussenvoegsel, Achternaam, Nummer, Medewerkersoort, IsActief) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $nummer, $soort]);
        
        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        exit;
    }

    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? 0);
        $voornaam = trim($input['voornaam'] ?? '');
        $tussenvoegsel = trim($input['tussenvoegsel'] ?? '');
        $achternaam = trim($input['achternaam'] ?? '');
        $nummer = (int)($input['nummer'] ?? 0);
        $soort = trim($input['soort'] ?? 'Beheerder');
        
        if (!$id || !$voornaam || !$achternaam) {
            echo json_encode(['success' => false, 'error' => 'ID, voornaam en achternaam zijn verplicht']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE medewerker SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Nummer = ?, Medewerkersoort = ? WHERE Id = ?");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $nummer, $soort, $id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id && isset($input['id'])) $id = (int)$input['id'];

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Ongeldig ID']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE medewerker SET IsActief = 0 WHERE Id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Ongeldige request methode']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
