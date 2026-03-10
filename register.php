<?php
include 'db_config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $telefoon = trim($_POST['telefoon']);
    $wachtwoord = $_POST['wachtwoord'];
    $confirm_wachtwoord = $_POST['confirm_wachtwoord'];
    
    if (empty($naam) || empty($email) || empty($telefoon) || empty($wachtwoord)) {
        $error = "Alle velden zijn verplicht!";
    } elseif ($wachtwoord !== $confirm_wachtwoord) {
        $error = "Wachtwoorden komen niet overeen!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "E-mailadres is ongeldig!";
    } else {
        try {
           
            $check = $conn->prepare("SELECT id FROM leden WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                $error = "Dit e-mailadres is al geregistreerd!";
            } else {
               
                $hashed_password = password_hash($wachtwoord, PASSWORD_BCRYPT);
                
             
                $stmt = $conn->prepare("INSERT INTO leden (naam, email, telefoon, wachtwoord) VALUES (?, ?, ?, ?)");
                $stmt->execute([$naam, $email, $telefoon, $hashed_password]);
                
                $success = "Registratie succesvol! Je kunt nu inloggen.";
               
                $_POST = [];
            }
        } catch (PDOException $e) {
            $error = "Registratiefout: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie - FitForFun</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    
</head>
<body>
<header>
    <div class="burger" onclick="toggleMenu()">☰</div>
    <div class="logo">FitForFun</div>
</header>

<nav class="menu" id="menu">
    <a href="website maken/index.html">Home</a>
    <a href="website maken/lessen.html">Lessen</a>
    <a href="login.php">Inloggen</a>
    <a href="register.php">Registreren</a>
</nav>

<section class="auth-main">
    <div class="auth-container">
        <div class="auth-tabs">
            <a href="login.php">Inloggen</a>
            <a href="register.php" class="active">Registreren</a>
        </div>

        <h1>Registratie</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="naam">Naam *</label>
                <input type="text" id="naam" name="naam" required value="<?php echo isset($_POST['naam']) ? htmlspecialchars($_POST['naam']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">E-mailadres *</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="telefoon">Telefoonnummer *</label>
                <input type="tel" id="telefoon" name="telefoon" required value="<?php echo isset($_POST['telefoon']) ? htmlspecialchars($_POST['telefoon']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="wachtwoord">Wachtwoord *</label>
                <input type="password" id="wachtwoord" name="wachtwoord" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_wachtwoord">Wachtwoord herhalen *</label>
                <input type="password" id="confirm_wachtwoord" name="confirm_wachtwoord" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Registreren</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
            Al geregistreerd? <a href="login.php" style="color: #e63946; text-decoration: none;">Inloggen</a>
        </p>
    </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
