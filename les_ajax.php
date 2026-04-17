<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

// Authenticatie check (bijvoorbeeld voor POST/PUT/DELETE)
$is_admin = isset($_SESSION['lid_id']) && $_SESSION['rol'] === 'Administrator';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $conn->query("SELECT * FROM les WHERE IsActief = 1 ORDER BY Datum DESC, Tijd DESC");
        $lessen_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $lessen = [];
        foreach ($lessen_db as $l) {
            // Map DB velden naar Front-end velden
            $status = $l['Beschikbaarheid'];
            if ($status === 'Gestart') $status = 'Afgerond'; // Map terug naar UI term indien nodig
            
            $lessen[] = [
                'id' => (int)$l['Id'],
                'naam' => $l['Naam'],
                'datum' => $l['Datum'],
                'tijd' => substr($l['Tijd'], 0, 5), // 'HH:MM'
                'prijs' => (float)$l['Prijs'],
                'max' => $l['MaxAantalPersonen'],
                'instructeur' => $l['Opmerking'], // We gebruiken Opmerking voor instructeur
                'status' => $status
            ];
        }
        echo json_encode(['success' => true, 'data' => $lessen]);
        exit;
    }
    
    if (!$is_admin) {
        echo json_encode(['success' => false, 'error' => 'Geen toegang tot beheer van lessen']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if ($method === 'POST') {
        // Toevoegen
        $naam = trim($input['naam'] ?? '');
        $datum = $input['datum'] ?? '';
        $tijd = $input['tijd'] ?? '';
        $prijs = isset($input['prijs']) ? (float)$input['prijs'] : 0.0;
        $max = isset($input['max']) && $input['max'] !== '' ? (int)$input['max'] : 9;
        $instructeur = trim($input['instructeur'] ?? '');
        $status = trim($input['status'] ?? 'Ingepland');
        
        if ($status === 'Afgerond') $status = 'Gestart'; // Map UI term naar DB term

        if (!$naam || !$datum || !$tijd || $prijs < 0) {
            echo json_encode(['success' => false, 'error' => 'Naam, datum, tijd en prijs zijn verplicht en moeten geldig zijn']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO les (Naam, Prijs, Datum, Tijd, MaxAantalPersonen, Beschikbaarheid, IsActief, Opmerking) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute([$naam, $prijs, $datum, $tijd, $max, $status, $instructeur]);
        
        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        exit;
    }

    if ($method === 'PUT') {
        // Bewerken
        $id = (int)($input['id'] ?? 0);
        $naam = trim($input['naam'] ?? '');
        $datum = $input['datum'] ?? '';
        $tijd = $input['tijd'] ?? '';
        $prijs = isset($input['prijs']) ? (float)$input['prijs'] : 0.0;
        $max = isset($input['max']) && $input['max'] !== '' ? (int)$input['max'] : 9;
        $instructeur = trim($input['instructeur'] ?? '');
        $status = trim($input['status'] ?? 'Ingepland');
        
        if ($status === 'Afgerond') $status = 'Gestart';

        if (!$id || !$naam || !$datum || !$tijd || $prijs < 0) {
            echo json_encode(['success' => false, 'error' => 'ID, naam, datum, tijd en prijs zijn verplicht']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE les SET Naam = ?, Prijs = ?, Datum = ?, Tijd = ?, MaxAantalPersonen = ?, Beschikbaarheid = ?, Opmerking = ? WHERE Id = ?");
        $stmt->execute([$naam, $prijs, $datum, $tijd, $max, $status, $instructeur, $id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'DELETE') {
        // Verwijderen
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id && isset($input['id'])) $id = (int)$input['id'];

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Ongeldig ID']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE les SET IsActief = 0 WHERE Id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Ongeldige request methode']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
