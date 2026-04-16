<?php
// Database setup script voor FitForFunDB
$host = 'localhost';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host", $user, $password);
    
    // Lees het SQL-bestand in
    $sql = file_get_contents(__DIR__ . '/database_setup.sql');
    
    if ($sql === false) {
        die("Fout: kon database_setup.sql niet lezen!");
    }
    
    // Voer de SQL statements uit
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec($sql);
    
    echo "✓ Database 'FitForFunDB' en alle tabellen succesvol aangemaakt!<br>";
    echo "✓ Tabellen: gebruiker, rol, medewerker, lid, les, reservering<br><br>";
    echo "<strong>Je kunt nu inloggen met:</strong><br>";
    echo "Gebruikersnaam: <code>janj</code> — Wachtwoord: <code>password1</code> (Lid)<br>";
    echo "Gebruikersnaam: <code>saradev</code> — Wachtwoord: <code>password2</code> (Lid)<br>";
    echo "Gebruikersnaam: <code>keesb</code> — Wachtwoord: <code>password3</code> (Administrator)<br>";
    echo "Gebruikersnaam: <code>emmaj</code> — Wachtwoord: <code>password4</code> (Medewerker)<br>";
    echo "Gebruikersnaam: <code>alik</code> — Wachtwoord: <code>password5</code> (Gastgebruiker)<br>";
    
} catch (PDOException $e) {
    echo "Fout: " . $e->getMessage();
}
?>

