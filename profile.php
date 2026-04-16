<?php
include 'db_config.php';

if (!isset($_SESSION['lid_id'])) {
    header("Location: login.php");
    exit;
}

$lid_id = $_SESSION['lid_id'];
$error = '';
$success = '';

try {
    $stmt = $conn->prepare("
        SELECT g.Voornaam, g.Tussenvoegsel, g.Achternaam, g.Gebruikersnaam, r.Naam AS Rol
        FROM gebruiker g
        LEFT JOIN rol r ON r.GebruikerId = g.Id AND r.IsActief = 1
        WHERE g.Id = ?
    ");
    $stmt->execute([$lid_id]);
    $gebruiker = $stmt->fetch();
    
    // Fetch reserveringen summary
    $stmt2 = $conn->prepare("SELECT Opmerking, Datum, Tijd, Reserveringstatus FROM reservering WHERE Nummer = ? ORDER BY Datum DESC, Tijd DESC");
    $stmt2->execute([$lid_id]);
    $reserveringen = $stmt2->fetchAll();
} catch (PDOException $e) {
    $error = "Fout bij laden gegevens: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $voornaam = trim($_POST['voornaam']);
    $tussenvoegsel = trim($_POST['tussenvoegsel']);
    $achternaam = trim($_POST['achternaam']);
    if (empty($voornaam) || empty($achternaam)) {
        $error = "Voornaam en achternaam zijn verplicht!";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE gebruiker SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ? WHERE Id = ?");
            $stmt->execute([$voornaam, $tussenvoegsel, $achternaam, $lid_id]);
            $volledige_naam = trim($voornaam . ' ' . $tussenvoegsel . ' ' . $achternaam);
            $volledige_naam = preg_replace('/\s+/', ' ', $volledige_naam);
            $_SESSION['naam'] = $volledige_naam;
            $gebruiker = ['Voornaam' => $voornaam, 'Tussenvoegsel' => $tussenvoegsel, 'Achternaam' => $achternaam, 'Gebruikersnaam' => $gebruiker['Gebruikersnaam'], 'Rol' => $gebruiker['Rol']];
            $success = "Profielgegevens bijgewerkt!";
        } catch (PDOException $e) {
            $error = "Fout bij bijwerken: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if (empty($old) || empty($new) || empty($confirm)) {
        $error = "Alle wachtwoordvelden zijn verplicht!";
    } elseif ($new !== $confirm) {
        $error = "Nieuwe wachtwoorden komen niet overeen!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT Wachtwoord FROM gebruiker WHERE Id = ?");
            $stmt->execute([$lid_id]);
            $user = $stmt->fetch();
            if (password_verify($old, $user['Wachtwoord'])) {
                $hashed = password_hash($new, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE gebruiker SET Wachtwoord = ? WHERE Id = ?");
                $stmt->execute([$hashed, $lid_id]);
                $success = "Wachtwoord succesvol gewijzigd!";
            } else {
                $error = "Huidig wachtwoord is onjuist!";
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
<title>Mijn Profiel — FitForFun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{--red:#e63946;--red2:#ff4d5a;--dark:#0a0a0a;--dark2:#111111;--dark3:#1a1a1a;--gray:#888;--font-display:'Bebas Neue',sans-serif;--font-body:'DM Sans',sans-serif;}
body{background:var(--dark);color:#fff;font-family:var(--font-body);min-height:100vh;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.03;pointer-events:none;z-index:9999;}

header{position:fixed;top:0;left:0;right:0;z-index:500;display:flex;align-items:center;justify-content:space-between;padding:18px 40px;background:rgba(10,10,10,.88);backdrop-filter:blur(14px);border-bottom:1px solid rgba(230,57,70,.15);}
.logo{font-family:var(--font-display);font-size:32px;letter-spacing:3px;color:#fff;text-decoration:none;}
.logo span{color:var(--red);}
.nav-links{display:flex;gap:8px;list-style:none;}
.nav-links a{color:rgba(255,255,255,.7);text-decoration:none;font-size:14px;font-weight:500;padding:8px 16px;border-radius:6px;transition:.25s;}
.nav-links a:hover{color:#fff;background:rgba(255,255,255,.06);}
.logout-link{color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:9px 22px;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.3);border-radius:6px;transition:.2s;}
.logout-link:hover{background:var(--red);}

/* PAGE */
.page{max-width:900px;margin:0 auto;padding:120px 40px 80px;}

/* PROFILE HEADER */
.profile-hero{display:flex;align-items:center;gap:28px;margin-bottom:60px;padding-bottom:40px;border-bottom:1px solid rgba(255,255,255,.07);}
.profile-avatar{width:80px;height:80px;background:linear-gradient(135deg,var(--red),#c1121f);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:36px;color:#fff;flex-shrink:0;}
.profile-hero-text h1{font-family:var(--font-display);font-size:48px;letter-spacing:2px;line-height:1;}
.profile-hero-text p{font-size:14px;color:var(--gray);margin-top:4px;}

/* SECTIONS */
.section-card{background:var(--dark2);border:1px solid rgba(255,255,255,.07);border-radius:18px;padding:36px;margin-bottom:24px;}
.section-card h2{font-family:var(--font-display);font-size:30px;letter-spacing:1.5px;margin-bottom:28px;display:flex;align-items:center;gap:12px;}
.section-card h2::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.07);}

/* ALERTS */
.alert{padding:14px 18px;border-radius:10px;font-size:14px;font-weight:500;margin-bottom:24px;display:flex;align-items:center;gap:10px;}
.alert-error{background:rgba(230,57,70,.12);border:1px solid rgba(230,57,70,.3);color:#ff8a8a;}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac;}

.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px;}
.form-group input{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;font-family:var(--font-body);font-size:15px;padding:13px 16px;border-radius:10px;transition:.2s;outline:none;}
.form-group input:focus{border-color:var(--red);background:rgba(230,57,70,.06);}
.form-group input::placeholder{color:rgba(255,255,255,.2);}
.form-group input[readonly]{opacity:.5;cursor:not-allowed;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.form-row-3{display:grid;grid-template-columns:1fr auto 1fr;gap:16px;}

.save-btn{display:inline-flex;align-items:center;gap:8px;background:var(--red);color:#fff;font-family:var(--font-body);font-weight:700;font-size:14px;padding:12px 26px;border:none;border-radius:8px;cursor:pointer;transition:.2s;box-shadow:0 4px 16px rgba(230,57,70,.3);}
.save-btn:hover{background:var(--red2);transform:translateY(-1px);}

@media(max-width:700px){
  header{padding:14px 20px;}
  .nav-links{display:none;}
  .page{padding:100px 20px 60px;}
  .form-row,.form-row-3{grid-template-columns:1fr;}
  .section-card{padding:24px 20px;}
  .quick-stats{grid-template-columns:1fr;}
}
.quick-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:40px;}
.stat-box{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);padding:24px;border-radius:14px;text-align:center;}
.stat-box h3{font-size:12px;color:var(--gray);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;}
.stat-box p{font-family:var(--font-display);font-size:32px;color:var(--red);line-height:1;}

</style>
</head>
<body>

<header>
  <a href="index.php" class="logo">Fit<span>For</span>Fun</a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="lessen.php">Lessen</a></li>
    <li><a href="abonnement.php">Abonnement</a></li>
    <li><a href="contact.php">Contact</a></li>
    <li><a href="profile.php" style="color:var(--red);">Mijn Profiel</a></li>
  </ul>
  <a href="logout.php" class="logout-link">Uitloggen</a>
</header>

<div class="page">

  <div class="profile-hero">
    <div class="profile-avatar"><?= strtoupper(substr($_SESSION['naam'], 0, 1)) ?></div>
    <div class="profile-hero-text">
      <h1>Hoi, <?= htmlspecialchars(explode(' ', $_SESSION['naam'])[0]) ?>.</h1>
      <p><?= htmlspecialchars($_SESSION['gebruikersnaam'] ?? '') ?> · Welkom in je dashboard</p>
    </div>
  </div>

  <div class="quick-stats">
    <div class="stat-box">
      <h3>Jouw Rol</h3>
      <p><?= htmlspecialchars($_SESSION['rol'] ?? 'Lid') ?></p>
    </div>
    <div class="stat-box">
      <h3>Status</h3>
      <p>Actief</p>
    </div>
    <div class="stat-box">
      <h3>Mijn Lessen</h3>
      <p><?= count($reserveringen) ?></p>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- MIJN GEGEVENS -->
  <div class="section-card">
    <h2>Mijn Gegevens</h2>
    <form method="POST">
      <div class="form-row-3">
        <div class="form-group">
          <label>Voornaam</label>
          <input type="text" name="voornaam" required value="<?= htmlspecialchars($gebruiker['Voornaam']) ?>">
        </div>
        <div class="form-group">
          <label>Tussenvoegsel</label>
          <input type="text" name="tussenvoegsel" value="<?= htmlspecialchars($gebruiker['Tussenvoegsel'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Achternaam</label>
          <input type="text" name="achternaam" required value="<?= htmlspecialchars($gebruiker['Achternaam']) ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Gebruikersnaam</label>
        <input type="text" value="<?= htmlspecialchars($gebruiker['Gebruikersnaam']) ?>" readonly>
      </div>
      <button type="submit" name="update_profile" class="save-btn">
        Opslaan
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
    </form>
  </div>

  <!-- WACHTWOORD -->
  <div class="section-card">
    <h2>Wachtwoord</h2>
    <form method="POST">
      <div class="form-group">
        <label>Huidig wachtwoord</label>
        <input type="password" name="old_password" placeholder="••••••••" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Nieuw wachtwoord</label>
          <input type="password" name="new_password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Herhaal nieuw wachtwoord</label>
          <input type="password" name="confirm_password" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" name="change_password" class="save-btn">
        Wachtwoord wijzigen
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
    </form>
  </div>

  <!-- MIJN RESERVERINGEN -->
  <div class="section-card">
    <h2>Mijn Reserveringen</h2>
    <?php if (empty($reserveringen)): ?>
        <p style="color:var(--gray);font-size:14px;">Je hebt momenteel geen lessen gereserveerd. <a href="lessen.php" style="color:var(--red);text-decoration:none;">Bekijk het rooster →</a></p>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($reserveringen as $r): 
                $lesNaam = str_replace("Les: ", "", $r['Opmerking']);
            ?>
            <div style="background:rgba(255,255,255,.05);padding:16px 20px;border-radius:10px;border:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-family:var(--font-display);font-size:24px;color:#fff;margin-bottom:4px;"><?= htmlspecialchars($lesNaam) ?></div>
                    <div style="font-size:13px;color:rgba(255,255,255,.6);">
                        📅 <?= htmlspecialchars($r['Datum']) ?> om <?= substr($r['Tijd'], 0, 5) ?>
                    </div>
                </div>
                <div style="background:<?= $r['Reserveringstatus']=='Gereserveerd' ? 'rgba(34,197,94,.1)' : 'rgba(255,255,255,.1)' ?>;color:<?= $r['Reserveringstatus']=='Gereserveerd' ? '#86efac' : '#fff' ?>;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:6px 12px;border-radius:100px;">
                    <?= htmlspecialchars($r['Reserveringstatus']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>

