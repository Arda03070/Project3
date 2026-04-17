<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$naam       = isset($data['naam']) ? trim($data['naam']) : '';
$email      = isset($data['email']) ? trim($data['email']) : '';
$telefoon   = isset($data['telefoon']) ? trim($data['telefoon']) : '';
$wachtwoord = isset($data['wachtwoord']) ? $data['wachtwoord'] : '';
$rol        = isset($data['rol']) ? trim($data['rol']) : 'Lid';

if (!$naam || !$email || !$wachtwoord) {
    echo json_encode(['success' => false, 'error' => 'Naam, e-mail en wachtwoord zijn verplicht']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Ongeldig e-mailadres']);
    exit;
}

// Splits naam in voornaam, tussenvoegsel, achternaam
$parts = explode(' ', $naam);
$voornaam = array_shift($parts);
$achternaam = count($parts) > 0 ? array_pop($parts) : $voornaam;
if ($achternaam === $voornaam) {
    $achternaam = 'Onbekend'; // Achternaam is required in db
}
$tussenvoegsel = count($parts) > 0 ? implode(' ', $parts) : '';

$hashed_wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);

try {
    $conn->beginTransaction();

    // Check of email (gebruikersnaam) al bestaat
    $stmt = $conn->prepare("SELECT Id FROM gebruiker WHERE Gebruikersnaam = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Dit e-mailadres is al in gebruik']);
        $conn->rollBack();
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO gebruiker (Voornaam, Tussenvoegsel, Achternaam, Gebruikersnaam, Wachtwoord, IsActief) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $email, $hashed_wachtwoord]);
    $gebruikerId = $conn->lastInsertId();

    $stmt = $conn->prepare("INSERT INTO rol (GebruikerId, Naam, IsActief) VALUES (?, ?, 1)");
    $stmt->execute([$gebruikerId, $rol]);

    // Optioneel: voeg toe aan lid tabel als het een lid is, en aan medewerker als het medewerker is.
    // Omdat UI verwacht dat het in `gebruiker` en `rol` zit, is dat in ieder geval nodig.
    if ($rol === 'Lid') {
        $relatienummer = rand(10000, 99999);
        $stmt = $conn->prepare("INSERT INTO lid (Voornaam, Tussenvoegsel, Achternaam, Relatienummer, Mobiel, Email, IsActief) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $relatienummer, $telefoon, $email]);
    } else if ($rol === 'Medewerker' || $rol === 'Administrator') {
        $nummer = rand(1000, 9999);
        $stmt = $conn->prepare("INSERT INTO medewerker (Voornaam, Tussenvoegsel, Achternaam, Nummer, Medewerkersoort, IsActief) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $nummer, $rol]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'id' => $gebruikerId]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
