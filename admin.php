<?php
//session_start();
include 'db_config.php';

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$stmt = $conn->query("SELECT COUNT(*) as totaal FROM leden");
$totaalLeden = $stmt->fetch()['totaal'];

$stmt = $conn->prepare("SELECT COUNT(*) as nieuw FROM leden WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$stmt->execute();
$nieuweLeden = $stmt->fetch()['nieuw'];

$stmt = $conn->query("SELECT id, naam, email, telefoon, rol, YEAR(created_at) as jaar FROM leden ORDER BY created_at DESC");
$leden = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — FitForFun</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --red:#e63946;--red2:#ff4d5a;
  --dark:#0a0a0a;--dark2:#0f0f0f;--dark3:#161616;--dark4:#1c1c1c;
  --border:rgba(255,255,255,.07);
  --gray:#666;--gray2:#999;
  --font-display:'Bebas Neue',sans-serif;
  --font-body:'DM Sans',sans-serif;
  --sidebar:260px;
}
body{background:var(--dark);color:#fff;font-family:var(--font-body);display:flex;min-height:100vh;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.03;pointer-events:none;z-index:9999;}

/* ─── SIDEBAR ─── */
.sidebar{
  width:var(--sidebar);flex-shrink:0;
  background:var(--dark2);border-right:1px solid var(--border);
  display:flex;flex-direction:column;
  position:fixed;top:0;left:0;bottom:0;z-index:200;
  transition:transform .3s ease;
}
.sidebar-header{padding:28px 24px;border-bottom:1px solid var(--border);}
.sidebar-logo{font-family:var(--font-display);font-size:28px;letter-spacing:3px;color:#fff;text-decoration:none;}
.sidebar-logo span{color:var(--red);}
.sidebar-role{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray);margin-top:4px;}

.sidebar-nav{flex:1;padding:20px 12px;overflow-y:auto;}
.nav-section-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray);padding:0 12px;margin-bottom:8px;margin-top:16px;}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;cursor:pointer;font-size:14px;font-weight:500;color:rgba(255,255,255,.55);transition:.2s;margin-bottom:2px;border:none;background:transparent;width:100%;text-align:left;font-family:var(--font-body);}
.nav-item:hover{color:#fff;background:rgba(255,255,255,.05);}
.nav-item.active{color:#fff;background:rgba(230,57,70,.12);border:1px solid rgba(230,57,70,.2);}
.nav-item.active .nav-icon{color:var(--red);}
.nav-icon{width:18px;text-align:center;flex-shrink:0;}

.sidebar-footer{padding:20px 12px;border-top:1px solid var(--border);}
.sidebar-user{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;background:rgba(255,255,255,.04);margin-bottom:8px;}
.sidebar-avatar{width:36px;height:36px;background:linear-gradient(135deg,var(--red),#c1121f);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:18px;flex-shrink:0;}
.sidebar-user-info .name{font-size:13px;font-weight:600;color:#fff;}
.sidebar-user-info .role-badge{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--red);}
.logout-btn{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;color:rgba(255,255,255,.4);text-decoration:none;transition:.2s;}
.logout-btn:hover{color:var(--red);background:rgba(230,57,70,.08);}

/* ─── MAIN CONTENT ─── */
.main{margin-left:var(--sidebar);flex:1;min-height:100vh;display:flex;flex-direction:column;}

/* TOP BAR */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:20px 36px;border-bottom:1px solid var(--border);background:rgba(10,10,10,.7);backdrop-filter:blur(10px);position:sticky;top:0;z-index:100;}
.topbar-left{display:flex;align-items:center;gap:16px;}
.menu-btn{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:8px;background:transparent;border:none;}
.menu-btn span{display:block;width:22px;height:2px;background:#fff;border-radius:2px;transition:.3s;}
.topbar-title{font-family:var(--font-display);font-size:24px;letter-spacing:1px;color:#fff;}
.topbar-right{display:flex;align-items:center;gap:12px;}
.topbar-search{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:8px;padding:8px 14px;}
.topbar-search input{background:transparent;border:none;color:#fff;font-family:var(--font-body);font-size:13px;outline:none;width:180px;}
.topbar-search input::placeholder{color:var(--gray);}

/* ─── PAGES ─── */
.page{display:none;padding:36px;animation:fadeIn .3s ease;}
.page.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}

/* SECTION HEADER */
.section-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;gap:16px;flex-wrap:wrap;}
.section-header h2{font-family:var(--font-display);font-size:40px;letter-spacing:1.5px;}
.section-header p{font-size:14px;color:var(--gray2);margin-top:2px;}

/* STATS GRID */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
.stat-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:24px;transition:.2s;}
.stat-card:hover{border-color:rgba(230,57,70,.2);}
.stat-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;}
.stat-icon{width:42px;height:42px;background:rgba(230,57,70,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.stat-trend{font-size:12px;font-weight:700;color:#4ade80;background:rgba(74,222,128,.1);padding:4px 8px;border-radius:6px;}
.stat-num{font-family:var(--font-display);font-size:44px;letter-spacing:1px;color:#fff;line-height:1;}
.stat-label{font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray);margin-top:4px;}

/* CHART */
.chart-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:28px;margin-bottom:32px;}
.chart-card h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;margin-bottom:24px;color:#fff;}
.chart-wrap{position:relative;height:220px;}

/* TABLE */
.table-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
.table-top{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border);}
.table-top h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;}
.table-controls{display:flex;gap:10px;}
.tbl-search{background:rgba(255,255,255,.05);border:1px solid var(--border);color:#fff;font-family:var(--font-body);font-size:13px;padding:8px 14px;border-radius:8px;outline:none;transition:.2s;}
.tbl-search:focus{border-color:rgba(230,57,70,.4);}
.tbl-search::placeholder{color:var(--gray);}
.tbl-select{background:rgba(255,255,255,.05);border:1px solid var(--border);color:#fff;font-family:var(--font-body);font-size:13px;padding:8px 14px;border-radius:8px;outline:none;cursor:pointer;}

table{width:100%;border-collapse:collapse;}
thead tr{background:rgba(255,255,255,.03);}
th{padding:14px 20px;text-align:left;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray);border-bottom:1px solid var(--border);}
td{padding:16px 20px;font-size:14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;}
tbody tr:hover{background:rgba(255,255,255,.02);}
tbody tr:last-child td{border-bottom:none;}

.badge-admin{display:inline-block;padding:3px 10px;background:rgba(230,57,70,.15);border:1px solid rgba(230,57,70,.3);color:var(--red);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border-radius:100px;}
.badge-lid{display:inline-block;padding:3px 10px;background:rgba(255,255,255,.07);border:1px solid var(--border);color:rgba(255,255,255,.5);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border-radius:100px;}
.badge-ingepland{display:inline-block;padding:3px 10px;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);color:#93c5fd;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border-radius:100px;}
.badge-geannuleerd{display:inline-block;padding:3px 10px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--gray2);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border-radius:100px;}

.del-btn{background:rgba(230,57,70,.1);border:1px solid rgba(230,57,70,.2);color:var(--red);font-family:var(--font-body);font-size:12px;font-weight:600;padding:6px 14px;border-radius:6px;cursor:pointer;transition:.2s;}
.del-btn:hover{background:var(--red);color:#fff;}

/* FORM CARD */
.form-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:28px;margin-bottom:24px;}
.form-card h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;margin-bottom:22px;}
.form-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;align-items:end;}
.fg{display:flex;flex-direction:column;gap:7px;}
.fg label{font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.4);}
.fg input,.fg select{background:rgba(255,255,255,.05);border:1px solid var(--border);color:#fff;font-family:var(--font-body);font-size:14px;padding:11px 14px;border-radius:9px;outline:none;transition:.2s;}
.fg input:focus,.fg select:focus{border-color:rgba(230,57,70,.4);}
.fg input::placeholder{color:rgba(255,255,255,.2);}
.fg select option{background:#1c1c1c;}
.add-btn{display:inline-flex;align-items:center;gap:8px;background:var(--red);color:#fff;font-family:var(--font-body);font-weight:700;font-size:13px;padding:11px 22px;border:none;border-radius:9px;cursor:pointer;transition:.2s;align-self:flex-end;white-space:nowrap;}
.add-btn:hover{background:var(--red2);}

/* MOBILE SIDEBAR */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:199;}

@media(max-width:1000px){
  .stats-grid{grid-template-columns:1fr 1fr;}
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .sidebar-overlay.open{display:block;}
  .main{margin-left:0;}
  .menu-btn{display:flex;}
  .page{padding:24px 20px;}
  .topbar{padding:16px 20px;}
}
@media(max-width:600px){
  .stats-grid{grid-template-columns:1fr;}
  .section-header{flex-direction:column;align-items:flex-start;}
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="website maken/index.html" class="sidebar-logo">Fit<span>For</span>Fun</a>
    <div class="sidebar-role">Admin Dashboard</div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Overzicht</div>
    <button class="nav-item active" onclick="switchPage('dashboard', this)">
      <span class="nav-icon">📊</span> Dashboard
    </button>

    <div class="nav-section-label">Beheer</div>
    <button class="nav-item" onclick="switchPage('leden', this)">
      <span class="nav-icon">👥</span> Leden
    </button>
    <button class="nav-item" onclick="switchPage('lessen', this)">
      <span class="nav-icon">🗓</span> Lessen
    </button>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['naam'], 0, 1)) ?></div>
      <div class="sidebar-user-info">
        <div class="name"><?= htmlspecialchars($_SESSION['naam']) ?></div>
        <div class="role-badge">Administrator</div>
      </div>
    </div>
    <a href="logout.php" class="logout-btn">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      Uitloggen
    </a>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <button class="menu-btn" onclick="toggleSidebar()"><span></span><span></span><span></span></button>
      <div class="topbar-title" id="topbarTitle">Dashboard</div>
    </div>
    <div class="topbar-right">
      <div class="topbar-search">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--gray)"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" placeholder="Zoeken..." id="globalSearch" oninput="globalFilter()">
      </div>
    </div>
  </div>

  <!-- DASHBOARD PAGE -->
  <div class="page active" id="page-dashboard">
    <div class="section-header">
      <div>
        <h2>Dashboard</h2>
        <p>Overzicht van jouw sportschool</p>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon">👥</div>
          <div class="stat-trend">+<?= $nieuweLeden ?> mnd</div>
        </div>
        <div class="stat-num" id="stat-leden"><?= $totaalLeden ?></div>
        <div class="stat-label">Totaal leden</div>
      </div>
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon">✨</div>
        </div>
        <div class="stat-num" id="stat-nieuw"><?= $nieuweLeden ?></div>
        <div class="stat-label">Nieuwe leden</div>
      </div>
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon">🗓</div>
        </div>
        <div class="stat-num" id="stat-lessen">3</div>
        <div class="stat-label">Actieve lessen</div>
      </div>
      <div class="stat-card">
        <div class="stat-top">
          <div class="stat-icon">💰</div>
        </div>
        <div class="stat-num">€<span id="stat-omzet">0</span></div>
        <div class="stat-label">Omzet (schatting)</div>
      </div>
    </div>

    <div class="chart-card">
      <h3>Ledengroei 2026</h3>
      <div class="chart-wrap"><canvas id="ledenChart"></canvas></div>
    </div>

    <div class="table-card">
      <div class="table-top">
        <h3>Geplande Lessen</h3>
        <div class="table-controls">
          <input class="tbl-search" id="dashLesSearch" placeholder="Zoek les..." oninput="filterDashLessen()">
          <select class="tbl-select" id="dashStatusFilter" onchange="filterDashLessen()">
            <option value="alle">Alle</option>
            <option value="Ingepland">Ingepland</option>
            <option value="Geannuleerd">Geannuleerd</option>
          </select>
        </div>
      </div>
      <table>
        <thead><tr><th>Les</th><th>Datum</th><th>Tijd</th><th>Prijs</th><th>Status</th><th>Actie</th></tr></thead>
        <tbody id="dashLessenTabel"></tbody>
      </table>
    </div>
  </div>

  <!-- LEDEN PAGE -->
  <div class="page" id="page-leden">
    <div class="section-header">
      <div><h2>Leden</h2><p>Beheer alle leden</p></div>
    </div>

    <div class="form-card">
      <h3>Lid Toevoegen</h3>
      <div class="form-grid">
        <div class="fg"><label>Naam</label><input id="lidNaam" placeholder="Jan Jansen"></div>
        <div class="fg"><label>Email</label><input id="lidEmail" type="email" placeholder="jan@email.nl"></div>
        <div class="fg"><label>Telefoon</label><input id="lidTelefoon" placeholder="06 12 34 56 78"></div>
        <div class="fg"><label>Wachtwoord</label><input id="lidWachtwoord" type="password" placeholder="••••••••"></div>
        <div class="fg"><button class="add-btn" onclick="addLid()">+ Toevoegen</button></div>
      </div>
    </div>

    <div class="table-card">
      <div class="table-top">
        <h3>Alle Leden</h3>
        <input class="tbl-search" placeholder="Zoek lid..." oninput="filterLeden(this.value)">
      </div>
      <table>
        <thead><tr><th>Naam</th><th>Email</th><th>Telefoon</th><th>Rol</th><th>Jaar</th><th>Actie</th></tr></thead>
        <tbody id="ledenTabel">
          <?php foreach($leden as $lid): ?>
          <tr data-id="<?= $lid['id'] ?>">
            <td><?= htmlspecialchars($lid['naam']) ?></td>
            <td><?= htmlspecialchars($lid['email']) ?></td>
            <td><?= htmlspecialchars($lid['telefoon'] ?? '—') ?></td>
            <td><span class="badge-<?= $lid['rol'] ?>"><?= ucfirst($lid['rol']) ?></span></td>
            <td><?= $lid['jaar'] ?></td>
            <td><button class="del-btn" onclick="deleteLid(<?= $lid['id'] ?>, this)">Verwijderen</button></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- LESSEN PAGE -->
  <div class="page" id="page-lessen">
    <div class="section-header">
      <div><h2>Lessen</h2><p>Plan en beheer lessen</p></div>
    </div>

    <div class="form-card">
      <h3>Nieuwe Les Toevoegen</h3>
      <div class="form-grid">
        <div class="fg"><label>Naam</label><input id="lesNaam" placeholder="HIIT Training"></div>
        <div class="fg"><label>Datum</label><input type="date" id="lesDatum"></div>
        <div class="fg"><label>Tijd</label><input type="time" id="lesTijd"></div>
        <div class="fg"><label>Prijs (€)</label><input type="number" id="lesPrijs" placeholder="15.00" step="0.01"></div>
        <div class="fg"><label>Status</label>
          <select id="lesStatus">
            <option value="Ingepland">Ingepland</option>
            <option value="Geannuleerd">Geannuleerd</option>
          </select>
        </div>
        <div class="fg"><button class="add-btn" onclick="addLes()">+ Toevoegen</button></div>
      </div>
    </div>

    <div class="table-card">
      <div class="table-top"><h3>Alle Lessen</h3></div>
      <table>
        <thead><tr><th>Les</th><th>Datum</th><th>Tijd</th><th>Prijs</th><th>Status</th><th>Actie</th></tr></thead>
        <tbody id="lessenTabel"></tbody>
      </table>
    </div>
  </div>

</div><!-- end .main -->

<script>
// ── DATA ──
let lessen = JSON.parse(localStorage.getItem('fff_lessen')) || [
  {naam:'Yoga',datum:'2026-03-01',tijd:'09:00',prijs:12.50,status:'Ingepland'},
  {naam:'Krachttraining',datum:'2026-03-02',tijd:'18:00',prijs:15.00,status:'Ingepland'},
  {naam:'Spinning',datum:'2026-03-03',tijd:'19:30',prijs:10.00,status:'Geannuleerd'}
];

function saveLessen(){localStorage.setItem('fff_lessen',JSON.stringify(lessen));}

// ── SIDEBAR ──
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

// ── PAGES ──
function switchPage(page, btn){
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(b=>b.classList.remove('active'));
  document.getElementById('page-'+page).classList.add('active');
  if(btn) btn.classList.add('active');
  const titles={dashboard:'Dashboard',leden:'Leden',lessen:'Lessen'};
  document.getElementById('topbarTitle').textContent=titles[page]||page;
  if(page==='dashboard'||page==='lessen') renderLessen();
  closeSidebar();
}

// ── RENDER ──
function badgeLes(s){return s==='Ingepland'?'badge-ingepland':'badge-geannuleerd';}

function renderLessen(){
  const rows=lessen.map((l,i)=>`
    <tr>
      <td>${l.naam}</td><td>${l.datum}</td><td>${l.tijd}</td>
      <td>€${l.prijs.toFixed(2)}</td>
      <td><span class="${badgeLes(l.status)}">${l.status}</span></td>
      <td><button class="del-btn" onclick="deleteLes(${i})">Verwijderen</button></td>
    </tr>`).join('');
  const dash=document.getElementById('dashLessenTabel');
  const lesPage=document.getElementById('lessenTabel');
  if(dash) dash.innerHTML=rows;
  if(lesPage) lesPage.innerHTML=rows;
  document.getElementById('stat-lessen').textContent=lessen.length;
  const omzet=lessen.filter(l=>l.status==='Ingepland').reduce((a,l)=>a+l.prijs,0);
  document.getElementById('stat-omzet').textContent=omzet.toFixed(0);
}

// ── LEDEN ──
function addLid(){
  const naam=document.getElementById('lidNaam').value;
  const email=document.getElementById('lidEmail').value;
  const telefoon=document.getElementById('lidTelefoon').value;
  const ww=document.getElementById('lidWachtwoord').value;
  if(!naam||!email||!ww){alert('Naam, email en wachtwoord zijn verplicht!');return;}
  fetch('add_lid_ajax.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({naam,email,telefoon,wachtwoord:ww})})
    .then(r=>r.json()).then(data=>{
      if(data.success){
        const tbl=document.getElementById('ledenTabel');
        const yr=new Date().getFullYear();
        tbl.insertAdjacentHTML('afterbegin',`<tr data-id="${data.id}">
          <td>${naam}</td><td>${email}</td><td>${telefoon||'—'}</td>
          <td><span class="badge-lid">Lid</span></td><td>${yr}</td>
          <td><button class="del-btn" onclick="deleteLid(${data.id},this)">Verwijderen</button></td></tr>`);
        ['lidNaam','lidEmail','lidTelefoon','lidWachtwoord'].forEach(id=>document.getElementById(id).value='');
        updateLedenStats();
      } else alert('Fout: '+data.error);
    });
}
function deleteLid(id,btn){
  if(!confirm('Lid verwijderen?'))return;
  fetch('delete_lid_ajax.php?id='+id).then(r=>r.json()).then(d=>{
    if(d.success){btn.closest('tr').remove();updateLedenStats();}
    else alert('Fout: '+d.error);
  });
}
function updateLedenStats(){
  fetch('get_leden_count.php').then(r=>r.json()).then(d=>{
    document.getElementById('stat-leden').textContent=d.totaal;
    document.getElementById('stat-nieuw').textContent=d.nieuw;
  });
}

// ── LESSEN CRUD ──
function addLes(){
  const naam=document.getElementById('lesNaam').value;
  const datum=document.getElementById('lesDatum').value;
  const tijd=document.getElementById('lesTijd').value;
  const prijs=document.getElementById('lesPrijs').value;
  const status=document.getElementById('lesStatus').value;
  if(!naam||!datum||!tijd||!prijs){alert('Vul alle velden in!');return;}
  lessen.push({naam,datum,tijd,prijs:parseFloat(prijs),status});
  saveLessen();
  ['lesNaam','lesDatum','lesTijd','lesPrijs'].forEach(id=>document.getElementById(id).value='');
  renderLessen();
}
function deleteLes(i){
  if(!confirm('Les verwijderen?'))return;
  lessen.splice(i,1);saveLessen();renderLessen();
}

// ── FILTERS ──
function filterDashLessen(){
  const q=document.getElementById('dashLesSearch').value.toLowerCase();
  const s=document.getElementById('dashStatusFilter').value;
  document.querySelectorAll('#dashLessenTabel tr').forEach(row=>{
    const name=row.cells[0].textContent.toLowerCase();
    const stat=row.cells[4].textContent.trim();
    row.style.display=(name.includes(q)&&(s==='alle'||stat===s))?'':'none';
  });
}
function filterLeden(q){
  document.querySelectorAll('#ledenTabel tr').forEach(r=>{
    r.style.display=r.textContent.toLowerCase().includes(q.toLowerCase())?'':'none';
  });
}
function globalFilter(){
  const q=document.getElementById('globalSearch').value.toLowerCase();
  document.querySelectorAll('tbody tr').forEach(r=>{
    r.style.display=q===''||r.textContent.toLowerCase().includes(q)?'':'none';
  });
}

// ── CHART ──
const ctx=document.getElementById('ledenChart').getContext('2d');
new Chart(ctx,{
  type:'line',
  data:{
    labels:['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
    datasets:[{
      label:'Leden',
      data:[3,5,8,12,15,<?= $totaalLeden ?>,0,0,0,0,0,0],
      borderColor:'#e63946',
      backgroundColor:'rgba(230,57,70,0.08)',
      fill:true,tension:.4,
      pointBackgroundColor:'#e63946',
      pointRadius:4,
      pointHoverRadius:6
    }]
  },
  options:{
    responsive:true,maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{
      y:{beginAtZero:true,grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#666'}},
      x:{grid:{display:false},ticks:{color:'#666'}}
    }
  }
});

// INIT
renderLessen();
</script>
</body>
</html>
