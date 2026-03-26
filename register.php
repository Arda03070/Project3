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
                $success = "Registratie gelukt! Je kunt nu inloggen.";
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
<title>Registreren — FitForFun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{--red:#e63946;--red2:#ff4d5a;--dark:#0a0a0a;--dark2:#111111;--dark3:#1a1a1a;--gray:#888;--font-display:'Bebas Neue',sans-serif;--font-body:'DM Sans',sans-serif;}
body{background:var(--dark);color:#fff;font-family:var(--font-body);min-height:100vh;display:grid;grid-template-columns:1fr 1fr;overflow:hidden;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.03;pointer-events:none;z-index:9999;}

.auth-left{position:relative;overflow:hidden;}
.auth-left-bg{position:absolute;inset:0;background:url('img/gym2.jpg') center/cover no-repeat;animation:zoom 10s ease-out both;}
@keyframes zoom{from{transform:scale(1.1);}to{transform:scale(1.04);}}
.auth-left-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,10,10,.8) 0%,rgba(10,10,10,.3) 100%);}
.auth-left-content{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;justify-content:space-between;padding:48px;}
.logo{font-family:var(--font-display);font-size:36px;letter-spacing:3px;color:#fff;text-decoration:none;}
.logo span{color:var(--red);}
.auth-left-bottom h2{font-family:var(--font-display);font-size:clamp(48px,5vw,80px);line-height:.95;letter-spacing:2px;margin-bottom:12px;}
.auth-left-bottom p{font-size:15px;color:rgba(255,255,255,.55);font-weight:300;}

.auth-right{background:var(--dark2);display:flex;flex-direction:column;justify-content:center;padding:60px 64px;overflow-y:auto;}
.auth-tabs{display:flex;gap:4px;background:rgba(255,255,255,.05);border-radius:10px;padding:4px;margin-bottom:36px;}
.auth-tabs a{flex:1;text-align:center;padding:11px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;color:rgba(255,255,255,.45);transition:.2s;}
.auth-tabs a.active{background:var(--red);color:#fff;}
.auth-tabs a:hover:not(.active){color:#fff;}

.form-title{font-family:var(--font-display);font-size:52px;letter-spacing:2px;margin-bottom:6px;}
.form-subtitle{font-size:14px;color:var(--gray);margin-bottom:30px;}

.alert{padding:14px 18px;border-radius:10px;font-size:14px;font-weight:500;margin-bottom:22px;display:flex;align-items:center;gap:10px;}
.alert-error{background:rgba(230,57,70,.12);border:1px solid rgba(230,57,70,.3);color:#ff8a8a;}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac;}

.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:7px;}
.form-group input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;font-family:var(--font-body);font-size:15px;padding:12px 16px;border-radius:10px;transition:.2s;outline:none;}
.form-group input:focus{border-color:var(--red);background:rgba(230,57,70,.06);}
.form-group input::placeholder{color:rgba(255,255,255,.2);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

.submit-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:10px;background:var(--red);color:#fff;font-family:var(--font-body);font-weight:700;font-size:16px;padding:15px;border:none;border-radius:10px;cursor:pointer;transition:.2s;box-shadow:0 4px 24px rgba(230,57,70,.35);margin-top:8px;}
.submit-btn:hover{background:var(--red2);transform:translateY(-1px);}

.form-footer{text-align:center;margin-top:20px;font-size:13px;color:var(--gray);}
.form-footer a{color:var(--red);text-decoration:none;}

@media(max-width:800px){
  body{grid-template-columns:1fr;grid-template-rows:160px 1fr;}
  .auth-left-bottom{display:none;}
  .auth-right{padding:32px 24px;}
  .form-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>
<div class="auth-left">
  <div class="auth-left-bg"></div>
  <div class="auth-left-content">
    <a href="website maken/index.html" class="logo">Fit<span>For</span>Fun</a>
    <div class="auth-left-bottom">
      <h2>Begin<br>Vandaag.</h2>
      <p>Eerste week gratis. Geen verplichtingen.</p>
    </div>
  </div>
</div>

<div class="auth-right">
  <div class="auth-tabs">
    <a href="login.php">Inloggen</a>
    <a href="register.php" class="active">Registreren</a>
  </div>

  <div class="form-title">Registreren</div>
  <div class="form-subtitle">Maak gratis een account aan.</div>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-row">
      <div class="form-group">
        <label>Naam *</label>
        <input type="text" name="naam" placeholder="Jan Jansen" required value="<?= isset($_POST['naam']) ? htmlspecialchars($_POST['naam']) : '' ?>">
      </div>
      <div class="form-group">
        <label>Telefoonnummer *</label>
        <input type="tel" name="telefoon" placeholder="06 12 34 56 78" required value="<?= isset($_POST['telefoon']) ? htmlspecialchars($_POST['telefoon']) : '' ?>">
      </div>
    </div>
    <div class="form-group">
      <label>E-mailadres *</label>
      <input type="email" name="email" placeholder="jij@email.nl" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Wachtwoord *</label>
        <input type="password" name="wachtwoord" placeholder="••••••••" required>
      </div>
      <div class="form-group">
        <label>Herhaal wachtwoord *</label>
        <input type="password" name="confirm_wachtwoord" placeholder="••••••••" required>
      </div>
    </div>
    <button type="submit" class="submit-btn">
      Account aanmaken
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </button>
  </form>

  <div class="form-footer">
    Al een account? <a href="login.php">Log hier in</a>
  </div>
</div>
</body>
</html>
