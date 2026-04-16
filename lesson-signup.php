<?php 
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['lid_id'])) {
        echo json_encode(['success' => false, 'msg' => 'Je moet ingelogd zijn om te reserveren.']);
        exit;
    }
    
    $lessonName = $_POST['lessonName'] ?? 'Onbekende Les';
    $uid = $_SESSION['lid_id'];
    
    try {
        $stmt = $conn->prepare("SELECT Voornaam, Tussenvoegsel, Achternaam FROM gebruiker WHERE Id = ?");
        $stmt->execute([$uid]);
        $u = $stmt->fetch();
        
        $datum = date('Y-m-d', strtotime('+1 week'));
        $tijd = '18:00:00';
        
        $ins = $conn->prepare("INSERT INTO reservering (Voornaam, Tussenvoegsel, Achternaam, Nummer, Datum, Tijd, Reserveringstatus, Opmerking) VALUES (?, ?, ?, ?, ?, ?, 'Gereserveerd', ?)");
        $ins->execute([$u['Voornaam'], $u['Tussenvoegsel'], $u['Achternaam'], $uid, $datum, $tijd, "Les: " . $lessonName]);
        
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inschrijven — FitForFun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --red:#e63946;--red2:#ff4d5a;
  --dark:#0a0a0a;--dark2:#111111;--dark3:#1a1a1a;
  --gray:#888;
  --font-display:'Bebas Neue',sans-serif;
  --font-body:'DM Sans',sans-serif;
}
html{scroll-behavior:smooth;}
body{background:var(--dark);color:#fff;font-family:var(--font-body);overflow-x:hidden;min-height:100vh;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.035;pointer-events:none;z-index:9999;}

/* HEADER */
header{position:fixed;top:0;left:0;right:0;z-index:500;display:flex;align-items:center;justify-content:space-between;padding:18px 40px;background:rgba(10,10,10,0.88);backdrop-filter:blur(14px);border-bottom:1px solid rgba(230,57,70,0.15);}
.logo{font-family:var(--font-display);font-size:32px;letter-spacing:3px;color:#fff;line-height:1;text-decoration:none;}
.logo span{color:var(--red);}
.nav-links{display:flex;gap:8px;list-style:none;}
.nav-links a{color:rgba(255,255,255,.7);text-decoration:none;font-size:14px;font-weight:500;padding:8px 16px;border-radius:6px;transition:.25s;}
.nav-links a:hover{color:#fff;background:rgba(255,255,255,.06);}
.nav-cta{display:flex;gap:10px;}
.btn-ghost{color:#fff;text-decoration:none;font-size:14px;font-weight:500;padding:9px 20px;border:1px solid rgba(255,255,255,.2);border-radius:6px;transition:.25s;}
.btn-ghost:hover{border-color:#fff;}
.btn-red{color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:9px 22px;background:var(--red);border-radius:6px;transition:.2s;}
.btn-red:hover{background:var(--red2);}
.burger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:8px;}
.burger span{display:block;width:24px;height:2px;background:#fff;border-radius:2px;}
.menu{position:fixed;top:0;left:-100%;width:min(320px,85vw);height:100vh;background:var(--dark2);z-index:1000;padding:90px 30px 30px;transition:left .4s cubic-bezier(.77,0,.18,1);border-right:1px solid rgba(230,57,70,.15);}
.menu.active{left:0;}
.menu a{display:block;color:rgba(255,255,255,.8);text-decoration:none;font-size:20px;font-weight:500;padding:14px 0;border-bottom:1px solid rgba(255,255,255,.06);transition:.2s;}
.menu a:hover{color:var(--red);padding-left:8px;}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;opacity:0;pointer-events:none;transition:.3s;}
.overlay.active{opacity:1;pointer-events:all;}

/* ─── MAIN LAYOUT ─── */
.signup-wrap{
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 1fr;
  padding-top:72px;
}

/* LEFT PANEL */
.signup-left{
  position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:flex-end;padding:60px;
  background:var(--dark3);
}
.signup-left-bg{
  position:absolute;inset:0;
  background:url('../img/gym.jpg') center/cover no-repeat;
  transition:transform 8s ease-out;
  animation:heroZoom 8s ease-out both;
}
@keyframes heroZoom{from{transform:scale(1.1);}to{transform:scale(1.04);}}
.signup-left-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(10,10,10,.95) 0%,rgba(10,10,10,.4) 100%);}
.signup-left-content{position:relative;z-index:2;}
.back-link{display:inline-flex;align-items:center;gap:8px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;margin-bottom:40px;transition:.2s;}
.back-link:hover{color:#fff;}
.lesson-info-card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);backdrop-filter:blur(12px);border-radius:16px;padding:28px;}
.lesson-info-tag{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:12px;}
.lesson-info-name{font-family:var(--font-display);font-size:52px;letter-spacing:2px;line-height:.95;margin-bottom:16px;}
.lesson-info-desc{font-size:14px;font-weight:300;color:rgba(255,255,255,.6);line-height:1.65;margin-bottom:22px;}
.lesson-info-row{display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.5);margin-bottom:8px;}
.lesson-info-row svg{color:var(--red);}
.lesson-price-big{margin-top:22px;padding-top:20px;border-top:1px solid rgba(255,255,255,.1);}
.lesson-price-big .num{font-family:var(--font-display);font-size:56px;color:#fff;line-height:1;}
.lesson-price-big .per{font-size:13px;color:var(--gray);}

/* RIGHT PANEL — FORM */
.signup-right{
  background:var(--dark2);
  padding:60px 56px;
  overflow-y:auto;
  display:flex;flex-direction:column;justify-content:center;
}
.form-header{margin-bottom:40px;}
.form-header h1{font-family:var(--font-display);font-size:48px;letter-spacing:2px;margin-bottom:8px;}
.form-header p{font-size:14px;color:var(--gray);}

/* FORM ELEMENTS */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:8px;}
.form-group input,
.form-group textarea,
.form-group select{
  width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
  color:#fff;font-family:var(--font-body);font-size:15px;font-weight:400;
  padding:13px 16px;border-radius:10px;transition:.2s;outline:none;
}
.form-group input:focus,
.form-group textarea:focus{border-color:var(--red);background:rgba(230,57,70,.06);}
.form-group input::placeholder,
.form-group textarea::placeholder{color:rgba(255,255,255,.2);}
.form-group input[type="date"]::-webkit-calendar-picker-indicator{filter:invert(1);opacity:.4;}
.form-group textarea{resize:vertical;min-height:80px;}

/* months + total */
.months-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:end;margin-bottom:18px;}
.total-display{background:rgba(230,57,70,.08);border:1px solid rgba(230,57,70,.25);border-radius:10px;padding:13px 16px;display:flex;justify-content:space-between;align-items:center;}
.total-label{font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gray);}
.total-amount{font-family:var(--font-display);font-size:28px;color:var(--red);}

/* checkbox */
.checkbox-row{display:flex;align-items:flex-start;gap:12px;margin-bottom:28px;}
.checkbox-row input[type=checkbox]{width:18px;height:18px;flex-shrink:0;margin-top:2px;accent-color:var(--red);cursor:pointer;}
.checkbox-row label{font-size:13px;color:rgba(255,255,255,.5);line-height:1.5;cursor:pointer;}
.checkbox-row a{color:var(--red);text-decoration:none;}

/* submit */
.submit-btn{
  width:100%;display:flex;align-items:center;justify-content:center;gap:10px;
  background:var(--red);color:#fff;font-family:var(--font-body);font-weight:700;font-size:16px;
  padding:17px 32px;border:none;border-radius:10px;cursor:pointer;
  transition:.2s;box-shadow:0 4px 24px rgba(230,57,70,.4);
}
.submit-btn:hover{background:var(--red2);transform:translateY(-1px);box-shadow:0 8px 32px rgba(230,57,70,.55);}
.submit-btn svg{transition:transform .2s;}
.submit-btn:hover svg{transform:translateX(4px);}

/* SUCCESS STATE */
.success-overlay{
  display:none;position:fixed;inset:0;z-index:2000;
  background:rgba(10,10,10,.97);
  flex-direction:column;align-items:center;justify-content:center;text-align:center;
  padding:40px;animation:fadeUp .5s ease;
}
.success-overlay.show{display:flex;}
.success-icon{width:80px;height:80px;background:rgba(230,57,70,.15);border:2px solid var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;margin-bottom:28px;}
.success-overlay h2{font-family:var(--font-display);font-size:64px;letter-spacing:2px;margin-bottom:12px;}
.success-overlay p{font-size:16px;color:rgba(255,255,255,.6);max-width:400px;margin-bottom:36px;line-height:1.7;}
.success-overlay a{display:inline-flex;align-items:center;gap:8px;background:var(--red);color:#fff;font-weight:700;font-size:15px;padding:14px 28px;border-radius:8px;text-decoration:none;}

/* FOOTER */
footer{background:var(--dark2);border-top:1px solid rgba(255,255,255,.07);padding:30px 40px;}
.footer-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;}
.footer-logo{font-family:var(--font-display);font-size:24px;letter-spacing:3px;}
.footer-logo span{color:var(--red);}
.footer-copy{font-size:13px;color:var(--gray);}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

@media(max-width:900px){
  .nav-links,.nav-cta{display:none;}
  .burger{display:flex;}
  header{padding:16px 24px;}
  .signup-wrap{grid-template-columns:1fr;}
  .signup-left{display:none;}
  .signup-right{padding:40px 24px;}
  .form-row{grid-template-columns:1fr;}
  .months-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div class="overlay" id="overlay" onclick="closeMenu()"></div>
<nav class="menu" id="menu">
  <a href="index.php">Home</a>
  <a href="lessen.php">Lessen</a>
  <a href="abonnement.php">Abonnement</a>
  <a href="contact.php">Contact</a>
  <?php if(isset($_SESSION['lid_id'])): ?>
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrator'): ?>
      <a href="admin.php">Dashboard</a>
    <?php else: ?>
      <a href="profile.php">Mijn Profiel</a>
    <?php endif; ?>
    <a href="logout.php">Uitloggen</a>
  <?php else: ?>
    <a href="login.php">Inloggen</a>
    <a href="register.php">Registreren</a>
  <?php endif; ?>
</nav>

<header>
  <a href="index.php" class="logo">Fit<span>For</span>Fun</a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="lessen.php">Lessen</a></li>
    <li><a href="abonnement.php">Abonnement</a></li>
    <li><a href="contact.php">Contact</a></li>
  </ul>
  <div class="nav-cta">
    <?php if(isset($_SESSION['lid_id'])): ?>
      <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrator'): ?>
        <a href="admin.php" class="btn-ghost">Dashboard</a>
      <?php else: ?>
        <a href="profile.php" class="btn-ghost">Mijn Profiel</a>
      <?php endif; ?>
      <a href="logout.php" class="btn-red">Uitloggen</a>
    <?php else: ?>
      <a href="login.php" class="btn-ghost">Inloggen</a>
      <a href="register.php" class="btn-red">Gratis starten</a>
    <?php endif; ?>
  </div>
  <div class="burger" id="burgerBtn" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>
</header>

<div class="signup-wrap">

  <!-- LEFT: les info -->
  <div class="signup-left">
    <div class="signup-left-bg"></div>
    <div class="signup-left-content">
      <a href="lessen.php" class="back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Terug naar lessen
      </a>
      <div class="lesson-info-card">
        <div class="lesson-info-tag">Je hebt gekozen voor</div>
        <div class="lesson-info-name" id="lessonName">—</div>
        <p class="lesson-info-desc" id="lessonDesc">Laad beschrijving...</p>
        <div class="lesson-info-row">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          <span id="lessonTime">—</span>
        </div>
        <div class="lesson-price-big">
          <div class="num" id="priceNum">€—</div>
          <div class="per">per maand</div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: form -->
  <div class="signup-right">
    <div class="form-header">
      <h1>Inschrijven</h1>
      <p>Vul je gegevens in en begin jouw journey.</p>
    </div>

    <form id="signupForm">
      <div class="form-row">
        <div class="form-group">
          <label>Voornaam *</label>
          <input type="text" id="voornaam" placeholder="Jan" required value="<?= isset($_SESSION['naam']) ? htmlspecialchars(explode(' ', $_SESSION['naam'])[0]) : '' ?>">
        </div>
        <div class="form-group">
          <label>Achternaam *</label>
          <input type="text" id="achternaam" placeholder="Jansen" required value="<?= isset($_SESSION['naam']) ? htmlspecialchars(explode(' ', array_slice(explode(' ', $_SESSION['naam']), -1)[0])[0]) : '' ?>">
        </div>
      </div>

      <div class="form-group">
        <label>E-mailadres *</label>
        <input type="email" id="email" placeholder="jan@email.nl" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Telefoonnummer *</label>
          <input type="tel" id="telefoon" placeholder="06 12 34 56 78" required>
        </div>
        <div class="form-group">
          <label>Geboortedatum *</label>
          <input type="date" id="geboortedatum" required>
        </div>
      </div>

      <div class="form-group">
        <label>Adres *</label>
        <input type="text" id="adres" placeholder="Straatnaam 1" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Stad *</label>
          <input type="text" id="stad" placeholder="Amsterdam" required>
        </div>
        <div class="form-group">
          <label>Postcode *</label>
          <input type="text" id="postcode" placeholder="1234 AB" required>
        </div>
      </div>

      <div class="months-row">
        <div class="form-group" style="margin-bottom:0">
          <label>Aantal maanden *</label>
          <input type="number" id="maanden" min="1" max="24" value="1" required>
        </div>
        <div class="total-display">
          <span class="total-label">Totaal</span>
          <span class="total-amount" id="totalPrice">€0</span>
        </div>
      </div>

      <div class="form-group">
        <label>Opmerkingen (optioneel)</label>
        <textarea id="opmerkingen" placeholder="Blessures, allergieën, of andere opmerkingen..."></textarea>
      </div>

      <div class="checkbox-row">
        <input type="checkbox" id="voorwaarden" required>
        <label for="voorwaarden">Ik ga akkoord met de <a href="#">algemene voorwaarden</a> en het <a href="#">privacybeleid</a> van FitForFun.</label>
      </div>

      <button type="submit" class="submit-btn">
        Bevestig inschrijving
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
    </form>
  </div>
</div>

<!-- SUCCESS -->
<div class="success-overlay" id="successOverlay">
  <div class="success-icon">✓</div>
  <h2>Gelukt!</h2>
  <p id="successMsg">Je bent ingeschreven. Je ontvangt een bevestiging per mail. Welkom bij FitForFun!</p>
  <a href="lessen.php">← Terug naar lessen</a>
</div>

<script>
function toggleMenu(){document.getElementById('menu').classList.toggle('active');document.getElementById('overlay').classList.toggle('active');}
function closeMenu(){document.getElementById('menu').classList.remove('active');document.getElementById('overlay').classList.remove('active');}

const descriptions={
  'Yoga':'Verbeter je flexibiliteit, kracht en mentale rust met onze geleide yogasessies. Perfect voor elk niveau.',
  'HIIT':'Maximale calorieverbranding in minimale tijd. Hoge intensiteit, maximaal resultaat.',
  'Spinning':'Rijd jezelf naar je doelen op het ritme van de muziek. Motiverende instructeurs, intense sessies.',
  'Pilates':'Versterk je kernspieren van binnenuit. Gericht, gecontroleerd en effectief.',
  'Zumba':'Fitnessen voelt nooit als sporten. Energieke Latin-beats en plezier.',
  'Krachttraining':'Bouw echte spiermassa en kracht op met begeleide sessies van professionele coaches.'
};
const times={
  'Yoga':'Ma, Wo, Vr — 18:00 tot 19:00',
  'HIIT':'Di, Do — 19:00 tot 19:45',
  'Spinning':'Ma, Wo, Za — 09:00 tot 09:45',
  'Pilates':'Di, Do — 10:00 tot 11:00',
  'Zumba':'Za, Zo — 16:00 tot 17:00',
  'Krachttraining':'Ma, Wo, Vr — 19:00 tot 20:00'
};

let lessonName,lessonPrice;

document.addEventListener('DOMContentLoaded',()=>{
  const p=new URLSearchParams(window.location.search);
  lessonName=p.get('lesson');
  lessonPrice=parseFloat(p.get('price'));
  if(!lessonName||!lessonPrice){window.location.href='lessen.php';return;}

  document.getElementById('lessonName').textContent=lessonName;
  document.getElementById('lessonDesc').textContent=descriptions[lessonName]||'Professionele fitnesles.';
  document.getElementById('lessonTime').textContent=times[lessonName]||'—';
  document.getElementById('priceNum').textContent='€'+lessonPrice;
  updateTotal();

  document.getElementById('maanden').addEventListener('input',updateTotal);
});

function updateTotal(){
  const m=parseInt(document.getElementById('maanden').value)||1;
  document.getElementById('totalPrice').textContent='€'+(lessonPrice*m).toFixed(2);
}

document.getElementById('signupForm').addEventListener('submit',e=>{
  e.preventDefault();
  const naam=document.getElementById('voornaam').value;
  const m=parseInt(document.getElementById('maanden').value);
  
  const formData = new FormData();
  formData.append('action', 'book');
  formData.append('lessonName', lessonName);
  
  fetch('lesson-signup.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      if(data.success) {
        document.getElementById('successMsg').textContent=
          `${naam}, je bent ingeschreven voor ${lessonName} (${m} maand${m>1?'en':''}). Je vindt de reservering in je profiel. Welkom bij FitForFun!`;
        document.getElementById('successOverlay').classList.add('show');
      } else {
        alert("Kon niet inschrijven: " + data.msg);
      }
    }).catch(err => {
      alert("Er ging iets mis met de verbinding.");
    });
});
</script>
</body>
</html>


