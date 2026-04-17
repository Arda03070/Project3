<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

$is_admin = isset($_SESSION['lid_id']) && $_SESSION['rol'] === 'Administrator';
$lid_id = $_SESSION['lid_id'] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Als beheerder halen we alles op, anders alleen van de ingelogde gebruiker
        if ($is_admin) {
            $stmt = $conn->query("SELECT * FROM reservering WHERE IsActief = 1 ORDER BY Datum DESC, Tijd DESC");
            $reserveringen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else if ($lid_id) {
            // Hiervoor hebben we het relatienummer nodig of we matchen op naam
            // Voor nu matchen we op email of ID via een simpele koppeling als voorbeeld
            // Als er een specifieke kolom was voor GebruikerId zou dat makkelijker zijn,
            // we sturen voor nu gewoon een error of implementeren o.b.v. sessie naam
            $stmt = $conn->prepare("SELECT * FROM reservering WHERE IsActief = 1 AND Voornaam = ?"); // Versimpeld
            $stmt->execute([$_SESSION['naam'] ?? '']);
            $reserveringen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo json_encode(['success' => false, 'error' => 'Niet ingelogd']);
            exit;
        }
        
        echo json_encode(['success' => true, 'data' => $reserveringen]);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);

    if ($method === 'POST') {
        // Zowel leden als admins kunnen reserveren (meestal)
        if (!$lid_id) {
            echo json_encode(['success' => false, 'error' => 'Je moet ingelogd zijn om te reserveren']);
            exit;
        }

        $voornaam = trim($input['voornaam'] ?? $_SESSION['naam'] ?? '');
        $tussenvoegsel = trim($input['tussenvoegsel'] ?? '');
        $achternaam = trim($input['achternaam'] ?? 'Onbekend');
        $nummer = (int)($input['nummer'] ?? rand(1000, 9999));
        $datum = $input['datum'] ?? '';
        $tijd = $input['tijd'] ?? '';
        $status = 'Gereserveerd';
        
        if (!$datum || !$tijd) {
            echo json_encode(['success' => false, 'error' => 'Datum en tijd zijn verplicht']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO reservering (Voornaam, Tussenvoegsel, Achternaam, Nummer, Datum, Tijd, Reserveringstatus, IsActief) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $nummer, $datum, $tijd, $status]);
        
        echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        exit;
    }

    // Voor PUT en DELETE, vaak alleen admin of eigenaar
    if (!$is_admin) {
        echo json_encode(['success' => false, 'error' => 'Geen toegang tot bewerken van reserveringen via deze API']);
        exit;
    }

    if ($method === 'PUT') {
        $id = (int)($input['id'] ?? 0);
        $status = trim($input['status'] ?? 'Gereserveerd');
        
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID is verplicht']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE reservering SET Reserveringstatus = ? WHERE Id = ?");
        $stmt->execute([$status, $id]);
        
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

        $stmt = $conn->prepare("UPDATE reservering SET IsActief = 0 WHERE Id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Ongeldige request methode']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
