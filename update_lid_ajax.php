<?php
session_start();
include 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    echo json_encode(['success' => false, 'error' => 'Geen toegang']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id         = isset($data['id']) ? (int)$data['id'] : 0;
$naam       = isset($data['naam']) ? trim($data['naam']) : '';
$email      = isset($data['email']) ? trim($data['email']) : '';
$telefoon   = isset($data['telefoon']) ? trim($data['telefoon']) : '';
$rol        = isset($data['rol']) ? trim($data['rol']) : '';
$wachtwoord = isset($data['wachtwoord']) ? $data['wachtwoord'] : '';

if (!$id || !$naam || !$email || !$rol) {
    echo json_encode(['success' => false, 'error' => 'Naam, e-mail en rol zijn verplicht']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Ongeldig e-mailadres']);
    exit;
}

$parts = explode(' ', $naam);
$voornaam = array_shift($parts);
$achternaam = count($parts) > 0 ? array_pop($parts) : $voornaam;
if ($achternaam === $voornaam) {
    $achternaam = 'Onbekend';
}
$tussenvoegsel = count($parts) > 0 ? implode(' ', $parts) : '';

try {
    $conn->beginTransaction();

    // Check of email (gebruikersnaam) al bestaat bij een andere gebruiker
    $stmt = $conn->prepare("SELECT Id FROM gebruiker WHERE Gebruikersnaam = ? AND Id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Dit e-mailadres is al in gebruik door een ander lid']);
        $conn->rollBack();
        exit;
    }

    // Update gebruiker
    if (!empty($wachtwoord)) {
        $hashed_wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE gebruiker SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Gebruikersnaam = ?, Wachtwoord = ? WHERE Id = ?");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $email, $hashed_wachtwoord, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE gebruiker SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Gebruikersnaam = ? WHERE Id = ?");
        $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $email, $id]);
    }

    // Update rol
    $stmt = $conn->prepare("UPDATE rol SET Naam = ? WHERE GebruikerId = ? AND IsActief = 1");
    $stmt->execute([$rol, $id]);

    // Optioneel: lid tabel en medewerker tabel updaten (voor nu slaan we dit over of doen we een best effort match op basis van email)
    // Gezien admin de lid pagina via de 'gebruiker' tabel ophaalt, is update op gebruiker en rol voldoende voor UI.
    $stmt = $conn->prepare("UPDATE lid SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Mobiel = ?, Email = ? WHERE Email = ?");
    $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $telefoon, $email, $email]);

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database fout: ' . $e->getMessage()]);
}
