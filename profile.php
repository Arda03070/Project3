<?php
include 'db_config.php';

// Check als ingelogd
if (!isset($_SESSION['lid_id'])) {
    header("Location: login.php");
    exit;
}

$lid_id = $_SESSION['lid_id'];
$error = '';
$success = '';

// Huidige gegevens laden
try {
    $stmt = $conn->prepare("SELECT naam, email, telefoon FROM leden WHERE id = ?");
    $stmt->execute([$lid_id]);
    $lid = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Fout bij laden gegevens: " . $e->getMessage();
}

// Update profiel
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $telefoon = trim($_POST['telefoon']);
    
    if (empty($naam) || empty($email) || empty($telefoon)) {
        $error = "Alle velden zijn verplicht!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "E-mailadres is ongeldig!";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE leden SET naam = ?, email = ?, telefoon = ? WHERE id = ?");
            $stmt->execute([$naam, $email, $telefoon, $lid_id]);
            
            $_SESSION['naam'] = $naam;
            $_SESSION['email'] = $email;
            
            $lid = ['naam' => $naam, 'email' => $email, 'telefoon' => $telefoon];
            $success = "Profielgegevens succesvol bijgewerkt!";
        } catch (PDOException $e) {
            $error = "Fout bij bijwerken: " . $e->getMessage();
        }
    }
}

// Wachtwoord wijzigen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Alle wachtwoordvelden zijn verplicht!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Nieuwe wachtwoorden komen niet overeen!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT wachtwoord FROM leden WHERE id = ?");
            $stmt->execute([$lid_id]);
            $user = $stmt->fetch();
            
            if (password_verify($old_password, $user['wachtwoord'])) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE leden SET wachtwoord = ? WHERE id = ?");
                $stmt->execute([$hashed, $lid_id]);
                $success = "Wachtwoord succesvol gewijzigd!";
            } else {
                $error = "Huidige wachtwoord is onjuist!";
            }
        } catch (PDOException $e) {
            $error = "Fout bij wijzigen wachtwoord: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Profiel - FitForFun</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/profile.css">

</head>
<body>
<header>
    <div class="burger" onclick="toggleMenu()">☰</div>
    <div class="logo">FitForFun</div>
</header>

<nav class="menu" id="menu">
    <a href="website maken/index.html">Home</a>
    <a href="website maken/lessen.html">Lessen</a>
    <a href="profile.php">Mijn Profiel</a>
    <a href="logout.php">Uitloggen</a>
</nav>

<div class="profile-container">
    <div class="profile-header">
        <h1>Welkom, <?php echo htmlspecialchars($_SESSION['naam']); ?>!</h1>
        <a href="logout.php" class="logout-btn">Uitloggen</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Profiel bijwerken -->
    <div class="form-section">
        <h2>Mijn Gegevens</h2>
        <form method="POST">
            <div class="form-group">
                <label for="naam">Naam *</label>
                <input type="text" id="naam" name="naam" required value="<?php echo htmlspecialchars($lid['naam']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">E-mailadres *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($lid['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefoon">Telefoonnummer *</label>
                <input type="tel" id="telefoon" name="telefoon" required value="<?php echo htmlspecialchars($lid['telefoon']); ?>">
            </div>
            
            <button type="submit" name="update_profile" class="btn">Gegevens Bijwerken</button>
        </form>
    </div>

   
    <div class="form-section">
        <h2>Wachtwoord Wijzigen</h2>
        <form method="POST">
            <div class="form-group">
                <label for="old_password">Huidige Wachtwoord *</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">Nieuw Wachtwoord *</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Wachtwoord Herhalen *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" name="change_password" class="btn">Wachtwoord Wijzigen</button>
        </form>
    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
