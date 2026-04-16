<?php session_start(); ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lessen — FitForFun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700;1,300&display=swap" rel="stylesheet">
<style>
/* ─── SHARED BASE (same as index) ─── */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --red:#e63946;--red2:#ff4d5a;
  --dark:#0a0a0a;--dark2:#111111;--dark3:#1a1a1a;
  --gray:#888;
  --font-display:'Bebas Neue',sans-serif;
  --font-body:'DM Sans',sans-serif;
}
html{scroll-behavior:smooth;}
body{background:var(--dark);color:#fff;font-family:var(--font-body);overflow-x:hidden;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.035;pointer-events:none;z-index:9999;}

/* HEADER */
header{position:fixed;top:0;left:0;right:0;z-index:500;display:flex;align-items:center;justify-content:space-between;padding:18px 40px;background:rgba(10,10,10,0.88);backdrop-filter:blur(14px);border-bottom:1px solid rgba(230,57,70,0.15);animation:slideDown .7s ease both;}
@keyframes slideDown{from{transform:translateY(-100%);opacity:0;}to{transform:translateY(0);opacity:1;}}
.logo{font-family:var(--font-display);font-size:32px;letter-spacing:3px;color:#fff;line-height:1;text-decoration:none;}
.logo span{color:var(--red);}
.nav-links{display:flex;gap:8px;list-style:none;}
.nav-links a{color:rgba(255,255,255,.7);text-decoration:none;font-size:14px;font-weight:500;padding:8px 16px;border-radius:6px;transition:.25s;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,.06);}
.nav-links a.active{color:var(--red);}
.nav-cta{display:flex;gap:10px;align-items:center;}
.btn-ghost{color:#fff;text-decoration:none;font-size:14px;font-weight:500;padding:9px 20px;border:1px solid rgba(255,255,255,.2);border-radius:6px;transition:.25s;}
.btn-ghost:hover{border-color:#fff;background:rgba(255,255,255,.05);}
.btn-red{color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:9px 22px;background:var(--red);border-radius:6px;transition:.2s;}
.btn-red:hover{background:var(--red2);}
.burger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:8px;}
.burger span{display:block;width:24px;height:2px;background:#fff;border-radius:2px;transition:.3s;}

/* MOBILE MENU */
.menu{position:fixed;top:0;left:-100%;width:min(320px,85vw);height:100vh;background:var(--dark2);z-index:1000;padding:90px 30px 30px;transition:left .4s cubic-bezier(.77,0,.18,1);border-right:1px solid rgba(230,57,70,.15);}
.menu.active{left:0;}
.menu a{display:block;color:rgba(255,255,255,.8);text-decoration:none;font-size:20px;font-weight:500;padding:14px 0;border-bottom:1px solid rgba(255,255,255,.06);transition:.2s;}
.menu a:hover{color:var(--red);padding-left:8px;}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;opacity:0;pointer-events:none;transition:.3s;backdrop-filter:blur(4px);}
.overlay.active{opacity:1;pointer-events:all;}

/* ─── HERO ─── */
.hero-lessen{
  position:relative;min-height:52vh;display:flex;align-items:flex-end;overflow:hidden;margin-top:0;padding-top:80px;
}
.hero-bg{position:absolute;inset:0;background:url('../img/gym7.jpg') center/cover no-repeat;animation:heroZoom 8s ease-out both;}
@keyframes heroZoom{from{transform:scale(1.1);}to{transform:scale(1.04);}}
.hero-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(10,10,10,1) 0%,rgba(10,10,10,.55) 55%,rgba(10,10,10,.3) 100%);}
.hero-content{position:relative;z-index:2;padding:0 40px 60px;animation:fadeUp .9s .2s ease both;}
@keyframes fadeUp{from{opacity:0;transform:translateY(28px);}to{opacity:1;transform:translateY(0);}}
.hero-tag{display:inline-flex;align-items:center;gap:8px;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.35);color:var(--red);font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:20px;}
.hero-tag::before{content:'';width:7px;height:7px;background:var(--red);border-radius:50%;animation:pulse 1.5s infinite;}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(.7);}}
.hero-content h1{font-family:var(--font-display);font-size:clamp(56px,8vw,110px);line-height:.9;letter-spacing:2px;margin-bottom:16px;}
.hero-content h1 span{color:var(--red);}
.hero-content p{font-size:17px;font-weight:300;color:rgba(255,255,255,.6);max-width:460px;}

/* ─── FILTER BAR ─── */
.filter-bar{background:var(--dark2);border-bottom:1px solid rgba(255,255,255,.06);padding:20px 40px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;position:sticky;top:72px;z-index:100;backdrop-filter:blur(10px);}
.filter-label{font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray);margin-right:6px;}
.filter-btn{background:transparent;border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.6);font-family:var(--font-body);font-size:13px;font-weight:500;padding:8px 18px;border-radius:100px;cursor:pointer;transition:.2s;}
.filter-btn:hover{border-color:rgba(255,255,255,.4);color:#fff;}
.filter-btn.active{background:var(--red);border-color:var(--red);color:#fff;}

/* ─── LESSONS GRID ─── */
.lessons-section{padding:60px 40px 100px;max-width:1300px;margin:0 auto;}
.lessons-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:24px;}

.lesson-card{
  background:var(--dark2);border:1px solid rgba(255,255,255,.07);border-radius:18px;overflow:hidden;
  text-decoration:none;color:#fff;display:flex;flex-direction:column;
  transition:transform .35s ease,box-shadow .35s ease,border-color .35s;
  animation:fadeUp .6s ease both;
}
.lesson-card:hover{transform:translateY(-8px);box-shadow:0 24px 60px rgba(0,0,0,.5);border-color:rgba(230,57,70,.3);}

.lesson-img{height:220px;position:relative;overflow:hidden;background:var(--dark3);}
.lesson-img-bg{width:100%;height:100%;object-fit:cover;transition:transform .5s ease;}
.lesson-card:hover .lesson-img-bg{transform:scale(1.06);}
.lesson-img-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:64px;background:var(--dark3);}
.lesson-level-badge{position:absolute;top:14px;right:14px;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.8);font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:5px 12px;border-radius:100px;}

.lesson-body{padding:24px 26px 26px;display:flex;flex-direction:column;flex:1;}
.lesson-cat{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--red);margin-bottom:8px;}
.lesson-title{font-family:var(--font-display);font-size:32px;letter-spacing:1px;line-height:1;margin-bottom:10px;}
.lesson-desc{font-size:14px;font-weight:300;color:rgba(255,255,255,.55);line-height:1.65;margin-bottom:18px;flex:1;}
.lesson-meta{display:flex;align-items:center;gap:6px;font-size:13px;color:rgba(255,255,255,.45);margin-bottom:20px;}
.lesson-meta svg{color:var(--red);flex-shrink:0;}
.lesson-footer{display:flex;align-items:center;justify-content:space-between;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);}
.lesson-price{font-family:var(--font-display);font-size:28px;color:#fff;letter-spacing:1px;}
.lesson-price span{font-family:var(--font-body);font-size:13px;color:var(--gray);font-weight:400;letter-spacing:0;}
.book-btn{display:inline-flex;align-items:center;gap:8px;background:var(--red);color:#fff;font-weight:700;font-size:14px;padding:11px 22px;border-radius:8px;border:none;cursor:pointer;text-decoration:none;transition:.2s;box-shadow:0 4px 16px rgba(230,57,70,.35);}
.book-btn:hover{background:var(--red2);transform:translateY(-1px);box-shadow:0 8px 24px rgba(230,57,70,.5);}
.book-btn svg{transition:transform .2s;}
.book-btn:hover svg{transform:translateX(3px);}

/* FOOTER */
footer{background:var(--dark2);border-top:1px solid rgba(255,255,255,.07);padding:40px;}
.footer-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;}
.footer-logo{font-family:var(--font-display);font-size:28px;letter-spacing:3px;}
.footer-logo span{color:var(--red);}
.footer-copy{font-size:13px;color:var(--gray);}

/* RESPONSIVE */
@media(max-width:900px){
  .nav-links,.nav-cta{display:none;}
  .burger{display:flex;}
  header{padding:16px 24px;}
  .hero-content{padding:0 24px 50px;}
  .filter-bar{padding:16px 24px;}
  .lessons-section{padding:40px 24px 80px;}
  .lessons-grid{grid-template-columns:1fr;}
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
    <li><a href="lessen.php" class="active">Lessen</a></li>
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

<!-- HERO -->
<section class="hero-lessen">
  <div class="hero-bg"></div>
  <div class="hero-content">
    <div class="hero-tag">35+ lessen per week</div>
    <h1>Onze <span>Lessen</span></h1>
    <p>Kies de les die past bij jouw doelen, niveau en schema. Van rustig tot extreem.</p>
  </div>
</section>

<!-- FILTER -->
<div class="filter-bar">
  <span class="filter-label">Filter:</span>
  <button class="filter-btn active" data-filter="alle">Alle lessen</button>
  <button class="filter-btn" data-filter="rustig">Rustig</button>
  <button class="filter-btn" data-filter="gemiddeld">Gemiddeld</button>
  <button class="filter-btn" data-filter="intensief">Intensief</button>
</div>

<!-- LESSONS -->
<section class="lessons-section">
  <div class="lessons-grid" id="lessonsGrid">

    <a href="lesson-signup.php?lesson=Yoga&price=15" class="lesson-card" data-level="rustig" style="animation-delay:.05s">
      <div class="lesson-img">
        <img class="lesson-img-bg" src="../img/yoga.jpg" alt="Yoga" onerror="this.outerHTML='<div class=\'lesson-img-placeholder\'>🧘</div>'">
        <div class="lesson-level-badge">Alle niveaus</div>
      </div>
      <div class="lesson-body">
        <div class="lesson-cat">Herstel &amp; Balans</div>
        <div class="lesson-title">Yoga</div>
        <p class="lesson-desc">Verbeter je flexibiliteit, kracht en mentale rust met onze geleide yogasessies. Perfect voor elk niveau.</p>
        <div class="lesson-meta">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Ma, Wo, Vr — 18:00 tot 19:00
        </div>
        <div class="lesson-footer">
          <div class="lesson-price">€15 <span>/ maand</span></div>
          <span class="book-btn">Inschrijven <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>

    <a href="lesson-signup.php?lesson=HIIT&price=20" class="lesson-card" data-level="intensief" style="animation-delay:.1s">
      <div class="lesson-img">
        <img class="lesson-img-bg" src="../img/hit.jpg" alt="HIIT" onerror="this.outerHTML='<div class=\'lesson-img-placeholder\'>🔥</div>'">
        <div class="lesson-level-badge">Gevorderd</div>
      </div>
      <div class="lesson-body">
        <div class="lesson-cat">Cardio &amp; Kracht</div>
        <div class="lesson-title">HIIT Training</div>
        <p class="lesson-desc">Maximale calorieverbranding in minimale tijd. Hoge intensiteit, maximaal resultaat. Niets voor watjes.</p>
        <div class="lesson-meta">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Di, Do — 19:00 tot 19:45
        </div>
        <div class="lesson-footer">
          <div class="lesson-price">€20 <span>/ maand</span></div>
          <span class="book-btn">Inschrijven <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>

    <a href="lesson-signup.php?lesson=Spinning&price=18" class="lesson-card" data-level="gemiddeld" style="animation-delay:.15s">
      <div class="lesson-img">
        <img class="lesson-img-bg" src="../img/spin.jpg" alt="Spinning" onerror="this.outerHTML='<div class=\'lesson-img-placeholder\'>🚴</div>'">
        <div class="lesson-level-badge">Gemiddeld</div>
      </div>
      <div class="lesson-body">
        <div class="lesson-cat">Cardio</div>
        <div class="lesson-title">Spinning</div>
        <p class="lesson-desc">Rijd jezelf naar je doelen op het ritme van de muziek. Motiverende instructeurs, intense sessies.</p>
        <div class="lesson-meta">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Ma, Wo, Za — 09:00 tot 09:45
        </div>
        <div class="lesson-footer">
          <div class="lesson-price">€18 <span>/ maand</span></div>
          <span class="book-btn">Inschrijven <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>

    <a href="lesson-signup.php?lesson=Pilates&price=15" class="lesson-card" data-level="rustig" style="animation-delay:.2s">
      <div class="lesson-img">
        <img class="lesson-img-bg" src="../img/pil.jpg" alt="Pilates" onerror="this.outerHTML='<div class=\'lesson-img-placeholder\'>🤸</div>'">
        <div class="lesson-level-badge">Alle niveaus</div>
      </div>
      <div class="lesson-body">
        <div class="lesson-cat">Core &amp; Houding</div>
        <div class="lesson-title">Pilates</div>
        <p class="lesson-desc">Versterk je kernspieren van binnenuit. Gericht, gecontroleerd en effectief voor je houding en kracht.</p>
        <div class="lesson-meta">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Di, Do — 10:00 tot 11:00
        </div>
        <div class="lesson-footer">
          <div class="lesson-price">€15 <span>/ maand</span></div>
          <span class="book-btn">Inschrijven <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>

    <a href="lesson-signup.php?lesson=Zumba&price=15" class="lesson-card" data-level="gemiddeld" style="animation-delay:.25s">
      <div class="lesson-img">
        <img class="lesson-img-bg" src="../img/zumba.jpg" alt="Zumba" onerror="this.outerHTML='<div class=\'lesson-img-placeholder\'>💃</div>'">
        <div class="lesson-level-badge">Alle niveaus</div>
      </div>
      <div class="lesson-body">
        <div class="lesson-cat">Dans &amp; Fun</div>
        <div class="lesson-title">Zumba</div>
        <p class="lesson-desc">Fitnessen voelt nooit als sporten. Energieke Latin-beats, grijnzende gezichten en zweet op de vloer.</p>
        <div class="lesson-meta">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Za, Zo — 16:00 tot 17:00
        </div>
        <div class="lesson-footer">
          <div class="lesson-price">€15 <span>/ maand</span></div>
          <span class="book-btn">Inschrijven <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>

    <a href="lesson-signup.php?lesson=Krachttraining&price=20" class="lesson-card" data-level="intensief" style="animation-delay:.3s">
      <div class="lesson-img">
        <img class="lesson-img-bg" src="../img/kracht.jpg" alt="Krachttraining" onerror="this.outerHTML='<div class=\'lesson-img-placeholder\'>💪</div>'">
        <div class="lesson-level-badge">Alle niveaus</div>
      </div>
      <div class="lesson-body">
        <div class="lesson-cat">Kracht &amp; Spier</div>
        <div class="lesson-title">Krachttraining</div>
        <p class="lesson-desc">Bouw echte spiermassa en kracht op. Begeleide sessies met professionele coaches voor elk level.</p>
        <div class="lesson-meta">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Ma, Wo, Vr — 19:00 tot 20:00
        </div>
        <div class="lesson-footer">
          <div class="lesson-price">€20 <span>/ maand</span></div>
          <span class="book-btn">Inschrijven <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
        </div>
      </div>
    </a>

  </div>
</section>

<footer>
  <div class="footer-inner">
    <div class="footer-logo">Fit<span>For</span>Fun</div>
    <div class="footer-copy">© 2026 FitForFun — Alle rechten voorbehouden</div>
  </div>
</footer>

<script>
function toggleMenu(){document.getElementById('menu').classList.toggle('active');document.getElementById('overlay').classList.toggle('active');}
function closeMenu(){document.getElementById('menu').classList.remove('active');document.getElementById('overlay').classList.remove('active');}

// Filter
document.querySelectorAll('.filter-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const f=btn.dataset.filter;
    document.querySelectorAll('.lesson-card').forEach(card=>{
      const show=f==='alle'||card.dataset.level===f;
      card.style.display=show?'flex':'none';
    });
  });
});

// Scroll reveal
const obs=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)';}}),{threshold:.08});
document.querySelectorAll('.lesson-card').forEach(el=>{el.style.cssText+='opacity:0;transform:translateY(30px);transition:opacity .6s ease,transform .6s ease;';obs.observe(el);});
</script>
</body>
</html>


