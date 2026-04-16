<?php
include 'db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    
    if (empty($gebruikersnaam) || empty($wachtwoord)) {
        $error = "Gebruikersnaam en wachtwoord zijn verplicht!";
    } else {
        try {
            // Zoek gebruiker op Gebruikersnaam in de gebruiker tabel
            $stmt = $conn->prepare("
                SELECT g.Id, g.Voornaam, g.Tussenvoegsel, g.Achternaam, g.Gebruikersnaam, g.Wachtwoord, r.Naam AS Rol
                FROM gebruiker g
                LEFT JOIN rol r ON r.GebruikerId = g.Id AND r.IsActief = 1
                WHERE g.Gebruikersnaam = ? AND g.IsActief = 1
            ");
            $stmt->execute([$gebruikersnaam]);
            $gebruiker = $stmt->fetch();
            
            if ($gebruiker && password_verify($wachtwoord, $gebruiker['Wachtwoord'])) {
                // Volledige naam samenstellen
                $volledige_naam = trim($gebruiker['Voornaam'] . ' ' . $gebruiker['Tussenvoegsel'] . ' ' . $gebruiker['Achternaam']);
                $volledige_naam = preg_replace('/\s+/', ' ', $volledige_naam);
                
                $_SESSION['lid_id'] = $gebruiker['Id'];
                $_SESSION['naam'] = $volledige_naam;
                $_SESSION['gebruikersnaam'] = $gebruiker['Gebruikersnaam'];
                $_SESSION['rol'] = $gebruiker['Rol'];
                
                // Update inlogstatus
                $update = $conn->prepare("UPDATE gebruiker SET IsIngelogd = 1, Ingelogd = CURDATE() WHERE Id = ?");
                $update->execute([$gebruiker['Id']]);
                
                if ($gebruiker['Rol'] == 'Administrator') {
                    header("Location: admin.php");
                } else {
                    header("Location: profile.php");
                }
                exit;
            } else {
                $error = "Gebruikersnaam of wachtwoord is onjuist!";
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
<title>Inloggen — FitForFun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{--red:#e63946;--red2:#ff4d5a;--dark:#0a0a0a;--dark2:#111111;--dark3:#1a1a1a;--gray:#888;--font-display:'Bebas Neue',sans-serif;--font-body:'DM Sans',sans-serif;}
body{background:var(--dark);color:#fff;font-family:var(--font-body);min-height:100vh;display:grid;grid-template-columns:1fr 1fr;overflow:hidden;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.03;pointer-events:none;z-index:9999;}

/* LEFT IMAGE PANEL */
.auth-left{position:relative;overflow:hidden;}
.auth-left-bg{position:absolute;inset:0;background:url('img/gym.jpg') center/cover no-repeat;animation:zoom 10s ease-out both;}
@keyframes zoom{from{transform:scale(1.1);}to{transform:scale(1.04);}}
.auth-left-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,10,10,.8) 0%,rgba(10,10,10,.3) 100%);}
.auth-left-content{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;justify-content:space-between;padding:48px;}
.logo{font-family:var(--font-display);font-size:36px;letter-spacing:3px;color:#fff;text-decoration:none;}
.logo span{color:var(--red);}
.auth-left-bottom h2{font-family:var(--font-display);font-size:clamp(48px,5vw,80px);line-height:.95;letter-spacing:2px;margin-bottom:12px;}
.auth-left-bottom p{font-size:15px;color:rgba(255,255,255,.55);font-weight:300;}

/* RIGHT FORM PANEL */
.auth-right{background:var(--dark2);display:flex;flex-direction:column;justify-content:center;padding:60px 64px;overflow-y:auto;}
.auth-tabs{display:flex;gap:4px;background:rgba(255,255,255,.05);border-radius:10px;padding:4px;margin-bottom:44px;}
.auth-tabs a{flex:1;text-align:center;padding:11px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;color:rgba(255,255,255,.45);transition:.2s;}
.auth-tabs a.active{background:var(--red);color:#fff;}
.auth-tabs a:hover:not(.active){color:#fff;}

.form-title{font-family:var(--font-display);font-size:52px;letter-spacing:2px;margin-bottom:8px;}
.form-subtitle{font-size:14px;color:var(--gray);margin-bottom:36px;}

.alert{padding:14px 18px;border-radius:10px;font-size:14px;font-weight:500;margin-bottom:24px;display:flex;align-items:center;gap:10px;}
.alert-error{background:rgba(230,57,70,.12);border:1px solid rgba(230,57,70,.3);color:#ff8a8a;}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac;}

.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px;}
.form-group input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;font-family:var(--font-body);font-size:15px;padding:13px 16px;border-radius:10px;transition:.2s;outline:none;}
.form-group input:focus{border-color:var(--red);background:rgba(230,57,70,.06);}
.form-group input::placeholder{color:rgba(255,255,255,.2);}

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

.submit-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:10px;background:var(--red);color:#fff;font-family:var(--font-body);font-weight:700;font-size:16px;padding:16px;border:none;border-radius:10px;cursor:pointer;transition:.2s;box-shadow:0 4px 24px rgba(230,57,70,.35);margin-top:8px;}
.submit-btn:hover{background:var(--red2);transform:translateY(-1px);box-shadow:0 8px 32px rgba(230,57,70,.5);}

.form-footer{text-align:center;margin-top:24px;font-size:13px;color:var(--gray);}
.form-footer a{color:var(--red);text-decoration:none;}

@media(max-width:800px){
  body{grid-template-columns:1fr;grid-template-rows:auto 1fr;}
  .auth-left{height:200px;}
  .auth-left-bottom{display:none;}
  .auth-right{padding:40px 28px;}
}
</style>
</head>
<body>

<div class="auth-left">
  <div class="auth-left-bg"></div>
  <div class="auth-left-content">
    <a href="index.html" class="logo">Fit<span>For</span>Fun</a>
    <div class="auth-left-bottom">
      <h2>Welkom<br>Terug.</h2>
      <p>Log in en ga verder met jouw journey.</p>
    </div>
  </div>
</div>

<div class="auth-right">
  <div class="auth-tabs">
    <a href="login.php" class="active">Inloggen</a>
    <a href="register.php">Registreren</a>
  </div>

  <div class="form-title">Inloggen</div>
  <div class="form-subtitle">Voer je gegevens in om verder te gaan.</div>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Gebruikersnaam</label>
      <input type="text" name="gebruikersnaam" placeholder="bijv. janj" required value="<?= isset($_POST['gebruikersnaam']) ? htmlspecialchars($_POST['gebruikersnaam']) : '' ?>">
    </div>
    <div class="form-group">
      <label>Wachtwoord</label>
      <input type="password" name="wachtwoord" placeholder="••••••••" required>
    </div>
    <button type="submit" class="submit-btn">
      Inloggen
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </button>
  </form>

  <div class="form-footer">
    Nog geen account? <a href="register.php">Registreer hier</a>
  </div>
</div>
</body>
</html>
