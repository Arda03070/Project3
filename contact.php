<?php session_start(); ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FitForFun — Contact</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --red: #e63946;
  --red2: #ff4d5a;
  --dark: #0a0a0a;
  --dark2: #111111;
  --dark3: #1a1a1a;
  --gray: #888;
  --font-display: 'Bebas Neue', sans-serif;
  --font-body: 'DM Sans', sans-serif;
}
html { scroll-behavior: smooth; }
body { background: var(--dark); color: #fff; font-family: var(--font-body); overflow-x: hidden; }
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
  opacity: 0.035;
  pointer-events: none;
  z-index: 9999;
}

/* HEADER */
header {
  position: fixed; top: 0; left: 0; right: 0;
  z-index: 500;
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 40px;
  background: rgba(10,10,10,0.85);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(230,57,70,0.15);
  animation: slideDown 0.7s ease both;
}
@keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.logo { font-family: var(--font-display); font-size: 32px; letter-spacing: 3px; color: #fff; line-height: 1; text-decoration: none; }
.logo span { color: var(--red); }
.nav-links { display: flex; gap: 8px; list-style: none; }
.nav-links a { color: rgba(255,255,255,0.7); text-decoration: none; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; padding: 8px 16px; border-radius: 6px; transition: color 0.25s, background 0.25s; }
.nav-links a:hover { color: #fff; background: rgba(255,255,255,0.06); }
.nav-links a.active { color: var(--red); }
.nav-cta { display: flex; gap: 10px; align-items: center; }
.btn-ghost { color: #fff; text-decoration: none; font-size: 14px; font-weight: 500; padding: 9px 20px; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; transition: border-color 0.25s, background 0.25s; }
.btn-ghost:hover { border-color: #fff; background: rgba(255,255,255,0.05); }
.btn-red { color: #fff; text-decoration: none; font-size: 14px; font-weight: 700; padding: 9px 22px; background: var(--red); border-radius: 6px; transition: background 0.2s, transform 0.2s; }
.btn-red:hover { background: var(--red2); transform: translateY(-1px); }
.burger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.burger span { display: block; width: 24px; height: 2px; background: #fff; border-radius: 2px; transition: 0.3s; }

/* MOBILE MENU */
.menu { position: fixed; top: 0; left: -100%; width: min(320px,85vw); height: 100vh; background: var(--dark2); z-index: 1000; padding: 90px 30px 30px; transition: left 0.4s cubic-bezier(0.77,0,0.18,1); border-right: 1px solid rgba(230,57,70,0.15); }
.menu.active { left: 0; }
.menu a { display: block; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 20px; font-weight: 500; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.06); transition: color 0.2s, padding-left 0.2s; }
.menu a:hover { color: var(--red); padding-left: 8px; }
.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 999; opacity: 0; pointer-events: none; transition: opacity 0.3s; backdrop-filter: blur(4px); }
.overlay.active { opacity: 1; pointer-events: all; }

/* PAGE HERO */
.page-hero {
  padding: 160px 40px 80px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.page-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse 60% 60% at 50% 0%, rgba(230,57,70,0.10) 0%, transparent 70%);
  pointer-events: none;
}
.section-label { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: var(--red); margin-bottom: 16px; }
.page-hero h1 { font-family: var(--font-display); font-size: clamp(56px, 8vw, 110px); line-height: 0.93; letter-spacing: 2px; margin-bottom: 22px; }
.page-hero h1 span { color: var(--red); }
.page-hero p { font-size: 18px; font-weight: 300; color: rgba(255,255,255,0.6); max-width: 480px; margin: 0 auto; line-height: 1.7; }

/* CONTACT SECTION */
.contact-section {
  padding: 80px 40px 100px;
}
.contact-inner {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 60px;
  align-items: start;
}

/* INFO SIDE */
.contact-info { }
.contact-info-title {
  font-family: var(--font-display);
  font-size: clamp(36px, 4vw, 56px);
  letter-spacing: 1px;
  line-height: 0.95;
  margin-bottom: 20px;
}
.contact-info-title span { color: var(--red); }
.contact-info-sub { font-size: 15px; color: rgba(255,255,255,0.55); line-height: 1.7; margin-bottom: 48px; max-width: 380px; }

.contact-cards { display: flex; flex-direction: column; gap: 14px; margin-bottom: 40px; }
.contact-card {
  display: flex;
  align-items: center;
  gap: 18px;
  background: var(--dark2);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 20px 22px;
  text-decoration: none;
  color: #fff;
  transition: border-color 0.25s, background 0.25s, transform 0.25s;
}
.contact-card:hover { border-color: rgba(230,57,70,0.4); background: var(--dark3); transform: translateX(4px); }
.contact-card-icon {
  width: 44px; height: 44px;
  background: rgba(230,57,70,0.12);
  border: 1px solid rgba(230,57,70,0.25);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.contact-card-label { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--gray); margin-bottom: 3px; }
.contact-card-value { font-size: 15px; font-weight: 500; }

.hours-title { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--gray); margin-bottom: 14px; }
.hours-grid { display: flex; flex-direction: column; gap: 8px; }
.hours-row { display: flex; justify-content: space-between; font-size: 14px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.hours-row:last-child { border-bottom: none; }
.hours-row .day { color: rgba(255,255,255,0.6); }
.hours-row .time { color: #fff; font-weight: 500; }
.hours-row .closed { color: var(--red); }

/* FORM SIDE */
.contact-form-wrapper {
  background: var(--dark2);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 20px;
  padding: 42px 40px;
  animation: fadeUp 0.7s 0.2s ease both;
}
@keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

.form-title { font-family: var(--font-display); font-size: 36px; letter-spacing: 1px; margin-bottom: 6px; }
.form-sub { font-size: 14px; color: var(--gray); margin-bottom: 30px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--gray); margin-bottom: 8px; }
.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  background: var(--dark3);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  color: #fff;
  font-family: var(--font-body);
  font-size: 15px;
  padding: 14px 16px;
  outline: none;
  transition: border-color 0.25s, box-shadow 0.25s;
  appearance: none;
}
.form-group input::placeholder,
.form-group textarea::placeholder { color: rgba(255,255,255,0.25); }
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--red);
  box-shadow: 0 0 0 3px rgba(230,57,70,0.12);
}
.form-group textarea { resize: vertical; min-height: 120px; }
.form-group select option { background: var(--dark3); color: #fff; }

.form-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 13px;
  color: rgba(255,255,255,0.55);
  line-height: 1.5;
  margin-bottom: 24px;
  cursor: pointer;
}
.form-checkbox input[type="checkbox"] { width: 18px; height: 18px; flex-shrink: 0; accent-color: var(--red); margin-top: 1px; cursor: pointer; }
.form-checkbox a { color: var(--red); text-decoration: none; }
.form-checkbox a:hover { text-decoration: underline; }

.btn-submit {
  width: 100%;
  padding: 16px;
  background: var(--red);
  color: #fff;
  font-family: var(--font-body);
  font-size: 16px;
  font-weight: 700;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 20px rgba(230,57,70,0.35);
  letter-spacing: 0.5px;
}
.btn-submit:hover { background: var(--red2); transform: translateY(-1px); box-shadow: 0 8px 30px rgba(230,57,70,0.5); }

/* Success message */
.form-success {
  display: none;
  text-align: center;
  padding: 40px 20px;
}
.form-success .check-circle {
  width: 64px; height: 64px;
  background: rgba(230,57,70,0.1);
  border: 2px solid var(--red);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 28px;
  margin: 0 auto 20px;
  animation: popIn 0.4s ease both;
}
@keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.form-success h3 { font-family: var(--font-display); font-size: 36px; letter-spacing: 1px; margin-bottom: 10px; }
.form-success p { color: rgba(255,255,255,0.55); font-size: 15px; line-height: 1.6; }

/* MAP PLACEHOLDER */
.map-section {
  padding: 0 40px 100px;
}
.map-inner {
  max-width: 1100px;
  margin: 0 auto;
}
.map-title { font-family: var(--font-display); font-size: clamp(36px,4vw,56px); letter-spacing: 1px; margin-bottom: 30px; text-align: center; }
.map-container {
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,0.07);
  height: 400px;
  background: var(--dark2);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.map-placeholder {
  text-align: center;
}
.map-placeholder .map-icon { font-size: 52px; margin-bottom: 14px; }
.map-placeholder h3 { font-family: var(--font-display); font-size: 28px; letter-spacing: 1px; margin-bottom: 8px; }
.map-placeholder p { font-size: 14px; color: var(--gray); margin-bottom: 20px; }
.btn-maps {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--red);
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.2s, transform 0.2s;
}
.btn-maps:hover { background: var(--red2); transform: translateY(-1px); }
.map-grid-line {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
}
.map-dot {
  position: absolute;
  width: 18px; height: 18px;
  background: var(--red);
  border-radius: 50%;
  top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  box-shadow: 0 0 0 6px rgba(230,57,70,0.2);
  animation: mapPulse 2s infinite;
}
@keyframes mapPulse {
  0%, 100% { box-shadow: 0 0 0 6px rgba(230,57,70,0.2); }
  50% { box-shadow: 0 0 0 14px rgba(230,57,70,0.05); }
}

/* CTA BANNER */
.cta-banner { background: linear-gradient(135deg, var(--red) 0%, #c1121f 100%); padding: 80px 40px; text-align: center; position: relative; overflow: hidden; }
.cta-banner::before { content: 'FFF'; font-family: var(--font-display); font-size: 280px; position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%); color: rgba(255,255,255,0.06); letter-spacing: 20px; white-space: nowrap; pointer-events: none; }
.cta-banner h2 { font-family: var(--font-display); font-size: clamp(48px, 7vw, 96px); letter-spacing: 2px; margin-bottom: 16px; position: relative; }
.cta-banner p { font-size: 18px; font-weight: 300; opacity: 0.85; margin-bottom: 36px; position: relative; }
.btn-white { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: var(--red); font-weight: 700; font-size: 15px; padding: 16px 36px; border-radius: 8px; text-decoration: none; position: relative; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
.btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }

/* FOOTER */
footer { background: var(--dark2); border-top: 1px solid rgba(255,255,255,0.07); padding: 50px 40px 30px; }
.footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: flex-start; gap: 40px; flex-wrap: wrap; padding-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.07); margin-bottom: 24px; }
.footer-logo { font-family: var(--font-display); font-size: 36px; letter-spacing: 3px; }
.footer-logo span { color: var(--red); }
.footer-tagline { color: var(--gray); font-size: 13px; margin-top: 6px; }
.footer-links { display: flex; gap: 40px; flex-wrap: wrap; }
.footer-col h4 { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--gray); margin-bottom: 14px; }
.footer-col a { display: block; color: rgba(255,255,255,0.65); text-decoration: none; font-size: 14px; margin-bottom: 8px; transition: color 0.2s; }
.footer-col a:hover { color: #fff; }
.footer-bottom { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--gray); flex-wrap: wrap; gap: 10px; }

/* RESPONSIVE */
@media (max-width: 900px) {
  .nav-links, .nav-cta { display: none; }
  .burger { display: flex; }
  header { padding: 16px 24px; }
  .contact-section, .map-section { padding-left: 24px; padding-right: 24px; }
  .contact-inner { grid-template-columns: 1fr; gap: 40px; }
  .form-row { grid-template-columns: 1fr; }
  .contact-form-wrapper { padding: 28px 22px; }
  .page-hero { padding-left: 24px; padding-right: 24px; }
  .footer-inner { flex-direction: column; }
}
</style>
</head>
<body>

<header>
  <a href="index.php" class="logo">FIT<span>FOR</span>FUN</a>
  <nav>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="lessen.php">Lessen</a></li>
      <li><a href="abonnement.php">Abonnement</a></li>
      <li><a href="contact.php" class="active">Contact</a></li>
    </ul>
  </nav>
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
  <button class="burger" id="burgerBtn" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</header>

<div class="menu" id="mobileMenu">
  <a href="index.php">Home</a>
  <a href="lessen.php">Lessen</a>
  <a href="abonnement.php">Abonnement</a>
  <a href="contact.php">Contact</a>
  <a href="login.php">Inloggen</a>
  <a href="register.php" style="color:var(--red)">Gratis starten →</a>
</div>
<div class="overlay" id="overlay"></div>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="section-label">Neem contact op</div>
  <h1>LAAT VAN JE<br><span>HOREN.</span></h1>
  <p>Heb je een vraag, wil je een rondleiding of ben je gewoon nieuwsgierig? We helpen je graag verder.</p>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section">
  <div class="contact-inner">

    <!-- LEFT: Info -->
    <div class="contact-info">
      <h2 class="contact-info-title">WIJ ZIJN ER<br>VOOR <span>JOU.</span></h2>
      <p class="contact-info-sub">Of je nu een vraag hebt over onze lessen, je abonnement wilt wijzigen of gewoon meer wilt weten — ons team staat voor je klaar.</p>

      <div class="contact-cards">
        <a href="mailto:info@fitforfun.nl" class="contact-card">
          <div class="contact-card-icon">📧</div>
          <div>
            <div class="contact-card-label">E-mail</div>
            <div class="contact-card-value">info@fitforfun.nl</div>
          </div>
        </a>
        <a href="tel:+31201234567" class="contact-card">
          <div class="contact-card-icon">📞</div>
          <div>
            <div class="contact-card-label">Telefoon</div>
            <div class="contact-card-value">+31 (0)20 123 45 67</div>
          </div>
        </a>
        <a href="#map" class="contact-card">
          <div class="contact-card-icon">📍</div>
          <div>
            <div class="contact-card-label">Adres</div>
            <div class="contact-card-value">Sportlaan 42, Amsterdam</div>
          </div>
        </a>
      </div>

      <div class="hours-title">Openingstijden</div>
      <div class="hours-grid">
        <div class="hours-row"><span class="day">Maandag – Vrijdag</span><span class="time">06:00 – 23:00</span></div>
        <div class="hours-row"><span class="day">Zaterdag</span><span class="time">07:00 – 21:00</span></div>
        <div class="hours-row"><span class="day">Zondag</span><span class="time">08:00 – 20:00</span></div>
        <div class="hours-row"><span class="day">Feestdagen</span><span class="time closed">Op aanvraag</span></div>
      </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="contact-form-wrapper">
      <div id="contactForm">
        <div class="form-title">STUUR EEN BERICHT</div>
        <p class="form-sub">We reageren binnen 1 werkdag.</p>

        <div class="form-row">
          <div class="form-group">
            <label>Voornaam</label>
            <input type="text" placeholder="Jan" id="fname">
          </div>
          <div class="form-group">
            <label>Achternaam</label>
            <input type="text" placeholder="Janssen" id="lname">
          </div>
        </div>

        <div class="form-group">
          <label>E-mailadres</label>
          <input type="email" placeholder="jan@email.nl" id="email">
        </div>

        <div class="form-group">
          <label>Telefoon (optioneel)</label>
          <input type="tel" placeholder="+31 6 12 34 56 78" id="phone">
        </div>

        <div class="form-group">
          <label>Onderwerp</label>
          <select id="subject">
            <option value="" disabled selected>Kies een onderwerp...</option>
            <option>Abonnement informatie</option>
            <option>Rondleiding aanvragen</option>
            <option>Lessen & rooster</option>
            <option>Personal training</option>
            <option>Opzeggen / pauzeren</option>
            <option>Overig</option>
          </select>
        </div>

        <div class="form-group">
          <label>Bericht</label>
          <textarea placeholder="Schrijf hier je bericht..." id="message"></textarea>
        </div>

        <label class="form-checkbox">
          <input type="checkbox" id="privacy">
          Ik ga akkoord met het <a href="#">privacybeleid</a> en geef toestemming om contact met mij op te nemen.
        </label>

        <button class="btn-submit" onclick="submitForm()">Verstuur Bericht →</button>
      </div>

      <!-- Success state -->
      <div class="form-success" id="formSuccess">
        <div class="check-circle">✓</div>
        <h3>BERICHT VERSTUURD!</h3>
        <p>Bedankt voor je bericht. We nemen binnen 1 werkdag contact met je op.</p>
      </div>
    </div>

  </div>
</section>

<!-- MAP -->
<section class="map-section" id="map">
  <div class="map-inner">
    <h2 class="map-title">VIND ONS</h2>
    <div class="map-container">
      <div class="map-grid-line"></div>
      <div class="map-dot"></div>
      <div class="map-placeholder">
        <div class="map-icon">🗺️</div>
        <h3>SPORTLAAN 42</h3>
        <p>1011 AB Amsterdam, Nederland</p>
        <a href="https://maps.google.com/?q=Amsterdam" target="_blank" class="btn-maps">Open in Google Maps →</a>
      </div>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<div class="cta-banner">
  <h2>KLAAR OM TE STARTEN?</h2>
  <p>Kom langs voor een gratis rondleiding en proeftraining.</p>
  <a href="register.php" class="btn-white">Begin Vandaag →</a>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div>
      <div class="footer-logo">FIT<span>FOR</span>FUN</div>
      <div class="footer-tagline">Geen excuses — alleen resultaten.</div>
    </div>
    <div class="footer-links">
      <div class="footer-col">
        <h4>Navigatie</h4>
        <a href="index.php">Home</a>
        <a href="lessen.php">Lessen</a>
        <a href="abonnement.php">Abonnement</a>
        <a href="contact.php">Contact</a>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
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
        <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrator'): ?>
      <a href="admin.php">Dashboard</a>
    <?php else: ?>
      <a href="profile.php">Mijn Profiel</a>
    <?php endif; ?>
      </div>
      <div class="footer-col">
        <h4>Info</h4>
        <a href="#">Privacybeleid</a>
        <a href="#">Algemene Voorwaarden</a>
        <a href="#">Sitemap</a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2025 FitForFun. Alle rechten voorbehouden.</span>
    <span>Gemaakt met 💪 in Nederland</span>
  </div>
</footer>

<script>
// Burger menu
const burger = document.getElementById('burgerBtn');
const menu = document.getElementById('mobileMenu');
const overlay = document.getElementById('overlay');
burger.addEventListener('click', () => { menu.classList.toggle('active'); overlay.classList.toggle('active'); });
overlay.addEventListener('click', () => { menu.classList.remove('active'); overlay.classList.remove('active'); });

// Form submit
function submitForm() {
  const fname = document.getElementById('fname').value.trim();
  const email = document.getElementById('email').value.trim();
  const message = document.getElementById('message').value.trim();
  const privacy = document.getElementById('privacy').checked;

  if (!fname || !email || !message) {
    alert('Vul alle verplichte velden in.');
    return;
  }
  if (!privacy) {
    alert('Ga akkoord met het privacybeleid om door te gaan.');
    return;
  }

  document.getElementById('contactForm').style.display = 'none';
  document.getElementById('formSuccess').style.display = 'block';
}
</script>
</body>
</html>


