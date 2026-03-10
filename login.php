<?php
include 'db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];
    
    if (empty($email) || empty($wachtwoord)) {
        $error = "E-mail en wachtwoord zijn verplicht!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, naam, email, rol, wachtwoord FROM leden WHERE email = ?");
            $stmt->execute([$email]);
            $lid = $stmt->fetch();
            
            if ($lid && password_verify($wachtwoord, $lid['wachtwoord'])) {
                // Login succesvol
                $_SESSION['lid_id'] = $lid['id'];
                $_SESSION['naam'] = $lid['naam'];
                $_SESSION['email'] = $lid['email'];
                $_SESSION['rol'] = $lid['rol'];
                
                // Redirect
                if ($lid['rol'] == 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: profile.php");
                }
                exit;
            } else {
                $error = "E-mail of wachtwoord is onjuist!";
            }
        } catch (PDOException $e) {
            $error = "Loginfout: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen - FitForFun</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/login.css">
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
            <a href="login.php" class="active">Inloggen</a>
            <a href="register.php">Registreren</a>
        </div>

        <h1>Inloggen</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">E-mailadres *</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="wachtwoord">Wachtwoord *</label>
                <input type="password" id="wachtwoord" name="wachtwoord" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Inloggen</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #7f8c8d;">
            Nog geen account? <a href="register.php" style="color: #e63946; text-decoration: none;">Registreren</a>
        </p>
    </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
