<?php
include 'db_config.php';

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'Administrator') {
    header("Location: login.php");
    exit;
}

$stmt = $conn->query("SELECT COUNT(*) as totaal FROM gebruiker WHERE IsActief = 1");
$totaalLeden = $stmt->fetch()['totaal'];

$stmt = $conn->prepare("SELECT COUNT(*) as nieuw FROM gebruiker WHERE MONTH(Datumaangemaakt) = MONTH(CURRENT_DATE()) AND YEAR(Datumaangemaakt) = YEAR(CURRENT_DATE())");
$stmt->execute();
$nieuweLeden = $stmt->fetch()['nieuw'];

$stmt = $conn->query("SELECT COUNT(*) as admins FROM rol WHERE Naam = 'Administrator' AND IsActief = 1");
$totaalAdmins = $stmt->fetch()['admins'];

$stmt = $conn->query("
    SELECT g.Id as id,
           CONCAT(g.Voornaam, IF(g.Tussenvoegsel != '', CONCAT(' ', g.Tussenvoegsel), ''), ' ', g.Achternaam) AS naam,
           g.Gebruikersnaam as email,
           '' as telefoon,
           COALESCE(r.Naam, 'Lid') as rol,
           DATE_FORMAT(g.Datumaangemaakt,'%d-%m-%Y') as aangemeld
    FROM gebruiker g
    LEFT JOIN rol r ON r.GebruikerId = g.Id AND r.IsActief = 1
    WHERE g.IsActief = 1
    ORDER BY g.Datumaangemaakt DESC
");
// Leden worden nu via AJAX geladen
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
  --dark:#0a0a0a;--dark2:#0f0f0f;--dark3:#161616;
  --border:rgba(255,255,255,.07);
  --gray:#666;--gray2:#999;
  --font-display:'Bebas Neue',sans-serif;
  --font-body:'DM Sans',sans-serif;
  --sidebar:260px;
}
*{scrollbar-width:thin;scrollbar-color:rgba(230,57,70,.3) transparent;}
body{background:var(--dark);color:#fff;font-family:var(--font-body);display:flex;min-height:100vh;}
body::before{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");opacity:.03;pointer-events:none;z-index:9999;}
.sidebar{width:var(--sidebar);flex-shrink:0;background:var(--dark2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;transition:transform .3s ease;}
.sidebar-header{padding:28px 24px;border-bottom:1px solid var(--border);}
.sidebar-logo{font-family:var(--font-display);font-size:28px;letter-spacing:3px;color:#fff;text-decoration:none;}
.sidebar-logo span{color:var(--red);}
.sidebar-role{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray);margin-top:4px;}
.sidebar-nav{flex:1;padding:20px 12px;overflow-y:auto;}
.nav-section-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gray);padding:0 12px;margin-bottom:8px;margin-top:16px;}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:10px;cursor:pointer;font-size:14px;font-weight:500;color:rgba(255,255,255,.55);transition:.2s;margin-bottom:2px;border:none;background:transparent;width:100%;text-align:left;font-family:var(--font-body);}
.nav-item:hover{color:#fff;background:rgba(255,255,255,.05);}
.nav-item.active{color:#fff;background:rgba(230,57,70,.12);border:1px solid rgba(230,57,70,.2);}
.nav-icon{width:18px;text-align:center;flex-shrink:0;}
.sidebar-footer{padding:20px 12px;border-top:1px solid var(--border);}
.sidebar-user{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;background:rgba(255,255,255,.04);margin-bottom:8px;}
.sidebar-avatar{width:36px;height:36px;background:linear-gradient(135deg,var(--red),#c1121f);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:18px;flex-shrink:0;}
.sidebar-user-info .name{font-size:13px;font-weight:600;}
.sidebar-user-info .role-badge{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--red);}
.logout-btn{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;color:rgba(255,255,255,.4);text-decoration:none;transition:.2s;}
.logout-btn:hover{color:var(--red);background:rgba(230,57,70,.08);}
.main{margin-left:var(--sidebar);flex:1;min-height:100vh;display:flex;flex-direction:column;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:20px 36px;border-bottom:1px solid var(--border);background:rgba(10,10,10,.7);backdrop-filter:blur(10px);position:sticky;top:0;z-index:100;}
.topbar-left{display:flex;align-items:center;gap:16px;}
.menu-btn{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:8px;background:transparent;border:none;}
.menu-btn span{display:block;width:22px;height:2px;background:#fff;border-radius:2px;}
.topbar-title{font-family:var(--font-display);font-size:24px;letter-spacing:1px;}
.topbar-search{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:8px;padding:8px 14px;}
.topbar-search input{background:transparent;border:none;color:#fff;font-family:var(--font-body);font-size:13px;outline:none;width:180px;}
.topbar-search input::placeholder{color:var(--gray);}
.page{display:none;padding:36px;animation:fadeIn .3s ease;}
.page.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.section-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;gap:16px;flex-wrap:wrap;}
.section-header h2{font-family:var(--font-display);font-size:40px;letter-spacing:1.5px;}
.section-header p{font-size:14px;color:var(--gray2);margin-top:2px;}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
.stat-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:24px;transition:.2s;}
.stat-card:hover{border-color:rgba(230,57,70,.2);}
.stat-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;}
.stat-icon{width:42px;height:42px;background:rgba(230,57,70,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.stat-trend{font-size:12px;font-weight:700;color:#4ade80;background:rgba(74,222,128,.1);padding:4px 8px;border-radius:6px;}
.stat-num{font-family:var(--font-display);font-size:44px;letter-spacing:1px;line-height:1;}
.stat-label{font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray);margin-top:4px;}
.chart-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:28px;margin-bottom:32px;}
.chart-card h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;margin-bottom:24px;}
.chart-wrap{position:relative;height:220px;}
.table-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px;}
.table-top{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:12px;}
.table-top h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;}
.table-controls{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.tbl-search{background:rgba(255,255,255,.05);border:1px solid var(--border);color:#fff;font-family:var(--font-body);font-size:13px;padding:8px 14px;border-radius:8px;outline:none;transition:.2s;}
.tbl-search:focus{border-color:rgba(230,57,70,.4);}
.tbl-search::placeholder{color:var(--gray);}
.tbl-select{background:rgba(255,255,255,.05);border:1px solid var(--border);color:#fff;font-family:var(--font-body);font-size:13px;padding:8px 14px;border-radius:8px;outline:none;cursor:pointer;}
.tbl-count{font-size:13px;color:var(--gray2);}
table{width:100%;border-collapse:collapse;min-width:600px;}
thead tr{background:rgba(255,255,255,.03);}
th{padding:14px 20px;text-align:left;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray);border-bottom:1px solid var(--border);white-space:nowrap;}
td{padding:14px 20px;font-size:14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;white-space:nowrap;}
tbody tr:hover{background:rgba(255,255,255,.02);}
tbody tr:last-child td{border-bottom:none;}
.table-responsive{overflow-x:auto;}
.no-results{text-align:center;padding:48px 20px;color:var(--gray);font-size:14px;}
.badge{display:inline-block;padding:3px 10px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;border-radius:100px;border:1px solid;}
.badge-admin{background:rgba(230,57,70,.15);border-color:rgba(230,57,70,.3);color:var(--red);}
.badge-lid{background:rgba(255,255,255,.07);border-color:var(--border);color:rgba(255,255,255,.5);}
.badge-ingepland{background:rgba(59,130,246,.15);border-color:rgba(59,130,246,.3);color:#93c5fd;}
.badge-geannuleerd{background:rgba(255,255,255,.06);border-color:var(--border);color:var(--gray2);}
.badge-afgerond{background:rgba(74,222,128,.1);border-color:rgba(74,222,128,.25);color:#86efac;}
.action-group{display:flex;gap:6px;align-items:center;}
.del-btn{background:rgba(230,57,70,.1);border:1px solid rgba(230,57,70,.2);color:var(--red);font-family:var(--font-body);font-size:12px;font-weight:600;padding:6px 12px;border-radius:6px;cursor:pointer;transition:.2s;white-space:nowrap;}
.del-btn:hover{background:var(--red);color:#fff;}
.edit-btn{background:rgba(255,255,255,.07);border:1px solid var(--border);color:rgba(255,255,255,.7);font-family:var(--font-body);font-size:12px;font-weight:600;padding:6px 12px;border-radius:6px;cursor:pointer;transition:.2s;white-space:nowrap;}
.edit-btn:hover{background:rgba(255,255,255,.12);color:#fff;}
.rol-select{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#fff;font-family:var(--font-body);font-size:12px;font-weight:700;padding:5px 10px;border-radius:6px;cursor:pointer;outline:none;transition:.2s;}
.rol-select:focus{border-color:rgba(230,57,70,.4);}
.rol-select option{background:#1c1c1c;}
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
.member-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--red),#c1121f);display:inline-flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:15px;color:#fff;flex-shrink:0;}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:1000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:.2s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:var(--dark3);border:1px solid rgba(255,255,255,.1);border-radius:18px;padding:36px;width:100%;max-width:520px;transform:translateY(20px) scale(.97);transition:.25s;position:relative;max-height:90vh;overflow-y:auto;}
.modal-overlay.open .modal{transform:translateY(0) scale(1);}
.modal h2{font-family:var(--font-display);font-size:34px;letter-spacing:1.5px;margin-bottom:24px;}
.modal-close{position:absolute;top:20px;right:20px;background:rgba(255,255,255,.07);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:.2s;}
.modal-close:hover{background:var(--red);}
.modal .fg{margin-bottom:14px;}
.modal-footer{display:flex;gap:12px;margin-top:24px;justify-content:flex-end;}
.cancel-btn{background:rgba(255,255,255,.07);border:1px solid var(--border);color:#fff;font-family:var(--font-body);font-weight:600;font-size:14px;padding:11px 22px;border-radius:9px;cursor:pointer;transition:.2s;}
.cancel-btn:hover{background:rgba(255,255,255,.12);}
.save-btn{background:var(--red);color:#fff;font-family:var(--font-body);font-weight:700;font-size:14px;padding:11px 28px;border:none;border-radius:9px;cursor:pointer;transition:.2s;}
.save-btn:hover{background:var(--red2);}
.modal-note{font-size:11px;color:var(--gray);margin-top:4px;}
.modal-2col{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.toast-container{position:fixed;bottom:28px;right:28px;z-index:5000;display:flex;flex-direction:column;gap:10px;pointer-events:none;}
.toast{background:var(--dark3);border:1px solid var(--border);border-radius:12px;padding:14px 20px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px;min-width:240px;animation:toastIn .3s ease;box-shadow:0 8px 32px rgba(0,0,0,.5);}
.toast.success{border-color:rgba(74,222,128,.25);color:#86efac;}
.toast.error{border-color:rgba(230,57,70,.3);color:#ff8a8a;}
.toast.info{border-color:rgba(96,165,250,.25);color:#93c5fd;}
@keyframes toastIn{from{opacity:0;transform:translateX(20px);}to{opacity:1;transform:translateX(0);}}
.settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;}
.settings-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:28px;}
.settings-card h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;margin-bottom:20px;}
.setting-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid rgba(255,255,255,.05);}
.setting-row:last-child{border-bottom:none;padding-bottom:0;}
.setting-label{font-size:14px;font-weight:500;}
.setting-desc{font-size:12px;color:var(--gray);margin-top:2px;}
.toggle{position:relative;width:44px;height:24px;flex-shrink:0;cursor:pointer;}
.toggle input{opacity:0;width:0;height:0;position:absolute;}
.toggle-slider{position:absolute;inset:0;background:rgba(255,255,255,.1);border-radius:100px;transition:.2s;}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;left:3px;top:3px;transition:.2s;}
.toggle input:checked+.toggle-slider{background:var(--red);}
.toggle input:checked+.toggle-slider::before{transform:translateX(20px);}
.danger-zone{background:var(--dark3);border:1px solid rgba(230,57,70,.2);border-radius:14px;padding:28px;grid-column:1/-1;}
.danger-zone h3{font-family:var(--font-display);font-size:22px;letter-spacing:1px;color:var(--red);margin-bottom:16px;}
.danger-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid rgba(255,255,255,.05);}
.danger-row:last-child{border-bottom:none;}
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
  .settings-grid{grid-template-columns:1fr;}
}
@media(max-width:600px){
  .stats-grid{grid-template-columns:1fr;}
  .action-group{flex-wrap:wrap;}
  .modal-2col{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<!-- MODAL: LID BEWERKEN -->
<div class="modal-overlay" id="editModal" onclick="if(event.target===this)closeEditModal()">
  <div class="modal">
    <button class="modal-close" onclick="closeEditModal()">✕</button>
    <h2>Gebruiker Bewerken</h2>
    <input type="hidden" id="editLidId">
    <div class="fg"><label>Naam *</label><input type="text" id="editNaam" placeholder="Jan Jansen"></div>
    <div class="fg"><label>E-mailadres *</label><input type="email" id="editEmail" placeholder="jan@email.nl"></div>
    <div class="fg"><label>Telefoonnummer</label><input type="tel" id="editTelefoon" placeholder="06 12 34 56 78"></div>
    <div class="fg"><label>Rol</label>
      <select id="editRol"><option value="Lid">Lid</option><option value="Administrator">Administrator</option><option value="Medewerker">Medewerker</option><option value="Gastgebruiker">Gastgebruiker</option></select>
    </div>
    <div class="fg">
      <label>Nieuw wachtwoord</label>
      <input type="password" id="editWachtwoord" placeholder="••••••••">
      <span class="modal-note">Leeglaten = wachtwoord niet wijzigen</span>
    </div>
    <div class="modal-footer">
      <button class="cancel-btn" onclick="closeEditModal()">Annuleren</button>
      <button class="save-btn" onclick="saveEdit()">Opslaan</button>
    </div>
  </div>
</div>

<!-- MODAL: LES BEWERKEN -->
<div class="modal-overlay" id="editLesModal" onclick="if(event.target===this)closeEditLesModal()">
  <div class="modal">
    <button class="modal-close" onclick="closeEditLesModal()">✕</button>
    <h2>Les Bewerken</h2>
    <input type="hidden" id="editLesIndex">
    <div class="fg"><label>Naam *</label><input type="text" id="editLesNaam" placeholder="HIIT Training"></div>
    <div class="modal-2col">
      <div class="fg"><label>Datum *</label><input type="date" id="editLesDatum"></div>
      <div class="fg"><label>Tijd *</label><input type="time" id="editLesTijd"></div>
      <div class="fg"><label>Prijs (€) *</label><input type="number" id="editLesPrijs" step="0.01" min="0"></div>
      <div class="fg"><label>Max. deelnemers</label><input type="number" id="editLesMax" placeholder="20" min="1"></div>
    </div>
    <div class="fg"><label>Instructeur</label><input type="text" id="editLesInstructeur" placeholder="Naam instructeur"></div>
    <div class="fg"><label>Status</label>
      <select id="editLesStatus">
        <option value="Ingepland">Ingepland</option>
        <option value="Geannuleerd">Geannuleerd</option>
        <option value="Afgerond">Afgerond</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="cancel-btn" onclick="closeEditLesModal()">Annuleren</button>
      <button class="save-btn" onclick="saveEditLes()">Opslaan</button>
    </div>
  </div>
</div>

<!-- MODAL: RESERVERING BEWERKEN -->
<div class="modal-overlay" id="editResModal" onclick="if(event.target===this)closeEditResModal()">
  <div class="modal">
    <button class="modal-close" onclick="closeEditResModal()">✕</button>
    <h2>Reservering Bewerken</h2>
    <input type="hidden" id="editResId">
    <div class="fg"><label>Status</label>
      <select id="editResStatus">
        <option value="Gereserveerd">Gereserveerd</option>
        <option value="Geannuleerd">Geannuleerd</option>
        <option value="Afgerond">Afgerond</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="cancel-btn" onclick="closeEditResModal()">Annuleren</button>
      <button class="save-btn" onclick="saveEditRes()">Opslaan</button>
    </div>
  </div>
</div>

<!-- MODAL: BEVESTIG VERWIJDEREN -->
<div class="modal-overlay" id="deleteModal" onclick="if(event.target===this)closeDeleteModal()">
  <div class="modal" style="max-width:420px;">
    <h2>Bevestigen</h2>
    <p id="deleteMsg" style="color:var(--gray2);font-size:15px;line-height:1.6;margin-bottom:4px;"></p>
    <div class="modal-footer">
      <button class="cancel-btn" onclick="closeDeleteModal()">Annuleren</button>
      <button class="del-btn" style="padding:11px 22px;font-size:14px;" onclick="confirmDelete()">Verwijderen</button>
    </div>
  </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="index.php" class="sidebar-logo">Fit<span>For</span>Fun</a>
    <div class="sidebar-role">Admin Dashboard</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overzicht</div>
    <button class="nav-item active" id="nav-dashboard" onclick="switchPage('dashboard',this)"><span class="nav-icon">📊</span> Dashboard</button>
    <div class="nav-section-label">Beheer</div>
    <button class="nav-item" id="nav-leden"   onclick="switchPage('leden',this)"><span class="nav-icon">👥</span> Leden</button>
    <button class="nav-item" id="nav-lessen"  onclick="switchPage('lessen',this)"><span class="nav-icon">🗓</span> Lessen</button>
    <button class="nav-item" id="nav-medewerkers" onclick="switchPage('medewerkers',this)"><span class="nav-icon">👨‍💼</span> Medewerkers</button>
    <button class="nav-item" id="nav-reserveringen" onclick="switchPage('reserveringen',this)"><span class="nav-icon">🔖</span> Reserveringen</button>
    <div class="nav-section-label">Systeem</div>
    <button class="nav-item" id="nav-instellingen" onclick="switchPage('instellingen',this)"><span class="nav-icon">⚙️</span> Instellingen</button>
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
    <div class="topbar-search">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--gray)"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="text" placeholder="Zoeken..." id="globalSearch" oninput="globalFilter()">
    </div>
  </div>

  <!-- DASHBOARD -->
  <div class="page active" id="page-dashboard">
    <div class="section-header"><div><h2>Dashboard</h2><p>Overzicht van jouw sportschool</p></div></div>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-top"><div class="stat-icon">👥</div><div class="stat-trend">+<?= $nieuweLeden ?> mnd</div></div>
        <div class="stat-num" id="stat-leden"><?= $totaalLeden ?></div>
        <div class="stat-label">Totaal leden</div>
      </div>
      <div class="stat-card">
        <div class="stat-top"><div class="stat-icon">✨</div></div>
        <div class="stat-num" id="stat-nieuw"><?= $nieuweLeden ?></div>
        <div class="stat-label">Nieuwe leden</div>
      </div>
      <div class="stat-card">
        <div class="stat-top"><div class="stat-icon">🗓</div></div>
        <div class="stat-num" id="stat-lessen">0</div>
        <div class="stat-label">Actieve lessen</div>
      </div>
      <div class="stat-card">
        <div class="stat-top"><div class="stat-icon">💰</div></div>
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
          <input class="tbl-search" id="dashLesSearch" placeholder="Zoek les..." oninput="renderDashLessen()">
          <select class="tbl-select" id="dashStatusFilter" onchange="renderDashLessen()">
            <option value="alle">Alle</option>
            <option value="Ingepland">Ingepland</option>
            <option value="Geannuleerd">Geannuleerd</option>
            <option value="Afgerond">Afgerond</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table><thead><tr><th>Les</th><th>Datum</th><th>Tijd</th><th>Prijs</th><th>Status</th><th>Acties</th></tr></thead>
        <tbody id="dashLessenTabel"></tbody></table>
      </div>
    </div>
  </div>

  <!-- LEDEN -->
  <div class="page" id="page-leden">
    <div class="section-header"><div><h2>Leden</h2><p>Beheer alle leden van FitForFun</p></div></div>
    <div class="form-card">
      <h3>Nieuw Lid Toevoegen</h3>
      <div class="form-grid">
        <div class="fg"><label>Naam *</label><input id="lidNaam" placeholder="Jan Jansen"></div>
        <div class="fg"><label>Email *</label><input id="lidEmail" type="email" placeholder="jan@email.nl"></div>
        <div class="fg"><label>Telefoon</label><input id="lidTelefoon" placeholder="06 12 34 56 78"></div>
        <div class="fg"><label>Wachtwoord *</label><input id="lidWachtwoord" type="password" placeholder="••••••••"></div>
        <div class="fg"><label>Rol</label>
          <select id="lidRol"><option value="Lid">Lid</option><option value="Administrator">Administrator</option><option value="Medewerker">Medewerker</option><option value="Gastgebruiker">Gastgebruiker</option></select>
        </div>
        <div class="fg"><button class="add-btn" onclick="addLid()">+ Toevoegen</button></div>
      </div>
    </div>
    <div class="table-card">
      <div class="table-top">
        <h3>Alle Leden</h3>
        <div class="table-controls">
          <input class="tbl-search" id="ledenSearch" placeholder="Zoek naam of email..." oninput="filterLeden()">
          <select class="tbl-select" id="rolFilter" onchange="filterLeden()">
            <option value="alle">Alle rollen</option>
            <option value="Lid">Leden</option>
            <option value="Administrator">Admins</option>
            <option value="Medewerker">Medewerkers</option>
            <option value="Gastgebruiker">Gastgebruikers</option>
          </select>
          <span class="tbl-count" id="ledenCount"></span>
        </div>
      </div>
      <div class="table-responsive">
        <table>
          <thead><tr><th></th><th>Naam</th><th>Email</th><th>Telefoon</th><th>Rol</th><th>Aangemeld</th><th>Acties</th></tr></thead>
          <tbody id="ledenTabel"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- LESSEN -->
  <div class="page" id="page-lessen">
    <div class="section-header"><div><h2>Lessen</h2><p>Plan en beheer lessen</p></div></div>
    <div class="form-card">
      <h3>Nieuwe Les Toevoegen</h3>
      <div class="form-grid">
        <div class="fg"><label>Naam *</label><input id="lesNaam" placeholder="HIIT Training"></div>
        <div class="fg"><label>Datum *</label><input type="date" id="lesDatum"></div>
        <div class="fg"><label>Tijd *</label><input type="time" id="lesTijd"></div>
        <div class="fg"><label>Prijs (€) *</label><input type="number" id="lesPrijs" placeholder="15.00" step="0.01" min="0"></div>
        <div class="fg"><label>Max. deelnemers</label><input type="number" id="lesMax" placeholder="20" min="1"></div>
        <div class="fg"><label>Instructeur</label><input id="lesInstructeur" placeholder="Naam instructeur"></div>
        <div class="fg"><label>Status</label>
          <select id="lesStatus">
            <option value="Ingepland">Ingepland</option>
            <option value="Geannuleerd">Geannuleerd</option>
            <option value="Afgerond">Afgerond</option>
          </select>
        </div>
        <div class="fg"><button class="add-btn" onclick="addLes()">+ Toevoegen</button></div>
      </div>
    </div>
    <div class="table-card">
      <div class="table-top">
        <h3>Alle Lessen</h3>
        <div class="table-controls">
          <input class="tbl-search" id="lessenSearch" placeholder="Zoek les..." oninput="renderLessenPage()">
          <select class="tbl-select" id="lesStatusFilter" onchange="renderLessenPage()">
            <option value="alle">Alle</option>
            <option value="Ingepland">Ingepland</option>
            <option value="Geannuleerd">Geannuleerd</option>
            <option value="Afgerond">Afgerond</option>
          </select>
          <span class="tbl-count" id="lessenCount"></span>
        </div>
      </div>
      <div class="table-responsive">
        <table>
          <thead><tr><th>Les</th><th>Datum</th><th>Tijd</th><th>Instructeur</th><th>Prijs</th><th>Max</th><th>Status</th><th>Acties</th></tr></thead>
          <tbody id="lessenTabel"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- MEDEWERKERS -->
  <div class="page" id="page-medewerkers">
    <div class="section-header"><div><h2>Medewerkers</h2><p>Beheer het personeel</p></div></div>
    <div class="form-card">
      <h3>Nieuwe Medewerker</h3>
      <div class="form-grid">
        <div class="fg"><label>Voornaam *</label><input id="mwVoornaam" placeholder="Jan"></div>
        <div class="fg"><label>Tussenvoegsel</label><input id="mwTussenvoegsel" placeholder="de"></div>
        <div class="fg"><label>Achternaam *</label><input id="mwAchternaam" placeholder="Vries"></div>
        <div class="fg"><label>E-mail *</label><input id="mwEmail" type="email" placeholder="jan@fitforfun.nl"></div>
        <div class="fg"><label>Soort</label>
          <select id="mwSoort">
            <option value="Beheerder">Beheerder</option>
            <option value="Manager">Manager</option>
            <option value="Instructeur">Instructeur</option>
            <option value="Diskmedewerker">Diskmedewerker</option>
          </select>
        </div>
        <div class="fg"><button class="add-btn" onclick="addMedewerker()">+ Toevoegen</button></div>
      </div>
    </div>
    <div class="table-card">
      <div class="table-top">
        <h3>Alle Medewerkers</h3>
      </div>
      <div class="table-responsive">
        <table>
          <thead><tr><th></th><th>Naam</th><th>Email</th><th>Telefoon</th><th>Rol</th><th>Aangemeld</th><th>Acties</th></tr></thead>
          <tbody id="medewerkersTabel"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- RESERVERINGEN -->
  <div class="page" id="page-reserveringen">
    <div class="section-header"><div><h2>Reserveringen</h2><p>Beheer de reserveringen</p></div></div>
    <div class="form-card">
      <h3>Nieuwe Reservering</h3>
      <div class="form-grid">
        <div class="fg"><label>Voornaam *</label><input id="resVoornaam" placeholder="Jan"></div>
        <div class="fg"><label>Tussenvoegsel</label><input id="resTussenvoegsel" placeholder="de"></div>
        <div class="fg"><label>Achternaam *</label><input id="resAchternaam" placeholder="Vries"></div>
        <div class="fg"><label>Datum *</label><input id="resDatum" type="date"></div>
        <div class="fg"><label>Tijd *</label><input id="resTijd" type="time"></div>
        <div class="fg"><button class="add-btn" onclick="addReservering()">+ Toevoegen</button></div>
      </div>
    </div>
    <div class="table-card">
      <div class="table-top">
        <h3>Alle Reserveringen</h3>
      </div>
      <div class="table-responsive">
        <table>
          <thead><tr><th>Naam</th><th>Nummer</th><th>Datum</th><th>Tijd</th><th>Status</th><th>Acties</th></tr></thead>
          <tbody id="reserveringenTabel"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- INSTELLINGEN -->
  <div class="page" id="page-instellingen">
    <div class="section-header"><div><h2>Instellingen</h2><p>Systeeminstellingen en beheeropties</p></div></div>
    <div class="settings-grid">
      <div class="settings-card">
        <h3>Systeem Info</h3>
        <div class="setting-row">
          <div><div class="setting-label">Totaal leden</div><div class="setting-desc">Geregistreerde accounts</div></div>
          <strong style="color:var(--red);font-family:var(--font-display);font-size:22px;"><?= (int)$totaalLeden ?></strong>
        </div>
        <div class="setting-row">
          <div><div class="setting-label">Admins</div><div class="setting-desc">Gebruikers met beheerdersrechten</div></div>
          <strong style="color:var(--red);font-family:var(--font-display);font-size:22px;"><?= (int)$totaalAdmins ?></strong>
        </div>
        <div class="setting-row">
          <div><div class="setting-label">Database</div><div class="setting-desc">Verbindingsstatus</div></div>
          <span class="badge badge-afgerond">Verbonden</span>
        </div>
        <div class="setting-row">
          <div><div class="setting-label">PHP versie</div></div>
          <span style="color:var(--gray2);font-size:13px;"><?= phpversion() ?></span>
        </div>
      </div>
      <div class="settings-card">
        <h3>Mijn Account</h3>
        <form method="POST" action="profile.php">
          <div class="fg" style="margin-bottom:14px;"><label>Naam</label>
            <input type="text" name="naam" value="<?= htmlspecialchars($_SESSION['naam']) ?>" required>
          </div>
          <div class="fg" style="margin-bottom:14px;"><label>E-mailadres</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['email']) ?>" required>
          </div>
          <button type="submit" name="update_profile" class="add-btn" style="width:100%;justify-content:center;">Opslaan</button>
        </form>
      </div>
      <div class="settings-card">
        <h3>Voorkeuren</h3>
        <div class="setting-row">
          <div><div class="setting-label">Bevestigingen</div><div class="setting-desc">Vraag altijd bij verwijderen</div></div>
          <label class="toggle"><input type="checkbox" id="toggleConfirm" checked><span class="toggle-slider"></span></label>
        </div>
        <div class="setting-row">
          <div><div class="setting-label">E-mail notificaties</div><div class="setting-desc">Alerts bij nieuwe leden</div></div>
          <label class="toggle"><input type="checkbox" checked><span class="toggle-slider"></span></label>
        </div>
        <div class="setting-row">
          <div><div class="setting-label">Donkere modus</div><div class="setting-desc">Interface thema</div></div>
          <label class="toggle"><input type="checkbox" checked><span class="toggle-slider"></span></label>
        </div>
      </div>
      <div class="settings-card">
        <h3>Exporteren</h3>
        <p style="font-size:14px;color:var(--gray2);margin-bottom:20px;">Download gegevens als CSV-bestand.</p>
        <button class="add-btn" style="width:100%;justify-content:center;margin-bottom:12px;" onclick="exportCSV()">📥 Leden exporteren (CSV)</button>
        <button class="add-btn" style="width:100%;justify-content:center;background:rgba(96,165,250,.2);color:#93c5fd;box-shadow:none;" onclick="exportLessenCSV()">📥 Lessen exporteren (CSV)</button>
      </div>
      <div class="danger-zone">
        <h3>⚠ Gevaarlijke Acties</h3>
        <div class="danger-row">
          <div><div class="setting-label">Alle lessen wissen</div><div class="setting-desc">Verwijdert alle lokaal opgeslagen lessen permanent</div></div>
          <button class="del-btn" onclick="clearAllLessen()">Wis alle lessen</button>
        </div>
        <div class="danger-row">
          <div><div class="setting-label">Cache leegmaken</div><div class="setting-desc">Verwijdert alle lokale opslag data</div></div>
          <button class="del-btn" onclick="clearStorage()">Cache wissen</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ═══════════════════════════════════════════════
//  DATA — lessen via database
// ═══════════════════════════════════════════════
let lessen = [];

function fetchLessen() {
  fetch('les_ajax.php')
    .then(r => r.json())
    .then(d => {
      if(d.success) {
        lessen = d.data;
        renderLessenPage();
      } else {
        toast('Fout bij ophalen lessen: ' + d.error, 'error');
      }
    }).catch(e => toast('Verbindingsfout bij ophalen lessen', 'error'));
}
// Initiele ophaalactie
fetchLessen();

// ═══════════════════════════════════════════════
//  SIDEBAR
// ═══════════════════════════════════════════════
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar(){
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

// ═══════════════════════════════════════════════
//  PAGES
// ═══════════════════════════════════════════════
function switchPage(page, btn){
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  if(btn) btn.classList.add('active');
  const titles = {dashboard:'Dashboard', leden:'Leden', lessen:'Lessen', medewerkers:'Medewerkers', reserveringen:'Reserveringen', instellingen:'Instellingen'};
  document.getElementById('topbarTitle').textContent = titles[page] || page;
  if(page === 'dashboard')    renderDashLessen();
  if(page === 'lessen')       renderLessenPage();
  if(page === 'leden' || page === 'medewerkers') fetchAlleGebruikers();
  if(page === 'reserveringen') fetchReserveringen();
  closeSidebar();
}

// ═══════════════════════════════════════════════
//  TOAST NOTIFICATIES
// ═══════════════════════════════════════════════
function toast(msg, type='success'){
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.innerHTML = '<span>' + {success:'✓',error:'⚠',info:'ℹ'}[type] + '</span> ' + msg;
  c.appendChild(t);
  setTimeout(() => {
    t.style.transition = '.3s'; t.style.opacity = '0'; t.style.transform = 'translateX(20px)';
    setTimeout(() => t.remove(), 300);
  }, 3000);
}

// ═══════════════════════════════════════════════
//  VEILIGE HTML HELPER
// ═══════════════════════════════════════════════
function esc(s){
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ═══════════════════════════════════════════════
//  BADGE HELPERS
// ═══════════════════════════════════════════════
function badgeLes(s){
  const map = {Ingepland:'badge badge-ingepland', Afgerond:'badge badge-afgerond', Geannuleerd:'badge badge-geannuleerd'};
  return map[s] || 'badge badge-geannuleerd';
}

// ═══════════════════════════════════════════════
//  RENDER LESSEN — DASHBOARD
// ═══════════════════════════════════════════════
function renderDashLessen(){
  const q  = document.getElementById('dashLesSearch').value.toLowerCase();
  const sf = document.getElementById('dashStatusFilter').value;
  const filtered = lessen.filter(l => l.naam.toLowerCase().includes(q) && (sf==='alle'||l.status===sf));
  const dT = document.getElementById('dashLessenTabel');
  if(!dT) return;
  dT.innerHTML = filtered.length
    ? filtered.map(l => {
        const i = lessen.indexOf(l);
        return `<tr><td><strong>${esc(l.naam)}</strong></td><td>${l.datum}</td><td>${l.tijd}</td>
          <td>€${parseFloat(l.prijs).toFixed(2)}</td>
          <td><span class="${badgeLes(l.status)}">${l.status}</span></td>
          <td><div class="action-group">
            <button class="edit-btn" onclick="goToLesEdit(${i})">✏</button>
            <button class="del-btn"  onclick="askDeleteLes(${i})">✕</button>
          </div></td></tr>`;
      }).join('')
    : '<tr><td colspan="6" class="no-results">Geen lessen gevonden</td></tr>';
  // update stats
  document.getElementById('stat-lessen').textContent = lessen.filter(l=>l.status==='Ingepland').length;
  document.getElementById('stat-omzet').textContent  = lessen.filter(l=>l.status==='Ingepland').reduce((a,l)=>a+parseFloat(l.prijs),0).toFixed(0);
}

// Navigeer naar lessen-pagina en open edit modal
function goToLesEdit(i){
  switchPage('lessen', document.getElementById('nav-lessen'));
  setTimeout(() => openEditLesModal(i), 50);
}

// ═══════════════════════════════════════════════
//  RENDER LESSEN — LESSEN PAGINA
// ═══════════════════════════════════════════════
function renderLessenPage(){
  const q  = (document.getElementById('lessenSearch')    || {value:''}).value;
  const sf = (document.getElementById('lesStatusFilter') || {value:'alle'}).value;
  const filtered = lessen.filter(l =>
    l.naam.toLowerCase().includes(q.toLowerCase()) && (sf==='alle'||l.status===sf)
  );
  const lT = document.getElementById('lessenTabel');
  if(!lT) return;
  lT.innerHTML = filtered.length
    ? filtered.map(l => {
        const i = lessen.indexOf(l);
        return `<tr>
          <td><strong>${esc(l.naam)}</strong></td><td>${l.datum}</td><td>${l.tijd}</td>
          <td>${l.instructeur ? esc(l.instructeur) : '<span style="color:var(--gray)">—</span>'}</td>
          <td>€${parseFloat(l.prijs).toFixed(2)}</td>
          <td>${l.max || '<span style="color:var(--gray)">—</span>'}</td>
          <td><span class="${badgeLes(l.status)}">${l.status}</span></td>
          <td><div class="action-group">
            <button class="edit-btn" onclick="openEditLesModal(${i})">✏ Bewerken</button>
            <button class="del-btn"  onclick="askDeleteLes(${i})">✕</button>
          </div></td>
        </tr>`;
      }).join('')
    : '<tr><td colspan="8" class="no-results">Geen lessen gevonden</td></tr>';
  const cnt = document.getElementById('lessenCount');
  if(cnt) cnt.textContent = filtered.length + ' les' + (filtered.length!==1?'sen':'');
  renderDashLessen(); // sync stats + dashboard tabel
}

// ═══════════════════════════════════════════════
//  LID TOEVOEGEN
// ═══════════════════════════════════════════════
function addLid(){
  const naam     = document.getElementById('lidNaam').value.trim();
  const email    = document.getElementById('lidEmail').value.trim();
  const telefoon = document.getElementById('lidTelefoon').value.trim();
  const ww       = document.getElementById('lidWachtwoord').value;
  const rol      = document.getElementById('lidRol').value;
  if(!naam||!email||!ww){ toast('Naam, email en wachtwoord zijn verplicht!','error'); return; }

  fetch('add_lid_ajax.php',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({naam, email, telefoon, wachtwoord:ww, rol})
  })
  .then(r=>r.json()).then(d=>{
    if(d.success){
      const dt = new Date();
      const datumStr = String(dt.getDate()).padStart(2,'0')+'-'+String(dt.getMonth()+1).padStart(2,'0')+'-'+dt.getFullYear();
      document.getElementById('ledenTabel').insertAdjacentHTML('afterbegin',
        buildRij({id:d.id, naam, email, telefoon, rol}, datumStr)
      );
      ['lidNaam','lidEmail','lidTelefoon','lidWachtwoord'].forEach(id=>document.getElementById(id).value='');
      updateLedenStats(); updateLedenCount();
      toast(naam + ' toegevoegd als ' + rol + '!');
    } else toast('Fout: '+(d.error||'Onbekend'),'error');
  }).catch(()=>toast('Verbindingsfout','error'));
}

// ═══════════════════════════════════════════════
//  GEBRUIKERS (LEDEN & MEDEWERKERS)
// ═══════════════════════════════════════════════
let alleGebruikers = [];

function fetchAlleGebruikers() {
  fetch('get_alle_gebruikers_ajax.php')
    .then(r=>r.json())
    .then(d=>{
      if(d.success) {
        alleGebruikers = d.data;
        renderGebruikers();
      } else {
        toast('Fout bij ophalen gebruikers', 'error');
      }
    }).catch(()=>toast('Verbindingsfout','error'));
}

function renderGebruikers() {
  const ledenFilter = document.getElementById('rolFilter') ? document.getElementById('rolFilter').value : 'alle';
  const ledenZoek = document.getElementById('ledenSearch') ? document.getElementById('ledenSearch').value.toLowerCase() : '';

  const ledenTabel = document.getElementById('ledenTabel');
  const mwTabel = document.getElementById('medewerkersTabel');
  
  let ledenHTML = '';
  let mwHTML = '';
  let ledenAantal = 0;

  alleGebruikers.forEach(g => {
    // Check if it's a Medewerker or Administrator
    const isMedewerker = g.rol === 'Administrator' || g.rol === 'Medewerker';
    
    // HTML for the row (shared for both)
    const json = esc(JSON.stringify({id:parseInt(g.id), naam:g.naam, email:g.email, telefoon:g.telefoon||'', rol:g.rol}));
    const rowHTML = `
      <tr data-id="${g.id}" data-rol="${g.rol}">
        <td><div class="member-avatar">${g.naam.charAt(0).toUpperCase()}</div></td>
        <td>${esc(g.naam)}</td>
        <td>${esc(g.email)}</td>
        <td>${esc(g.telefoon || '—')}</td>
        <td>
          <select class="rol-select" data-id="${g.id}" data-prev="${g.rol}" onchange="updateRol(${g.id},this)">
            <option value="Lid" ${g.rol==='Lid'?'selected':''}>Lid</option>
            <option value="Gastgebruiker" ${g.rol==='Gastgebruiker'?'selected':''}>Gastgebruiker</option>
            <option value="Medewerker" ${g.rol==='Medewerker'?'selected':''}>Medewerker</option>
            <option value="Administrator" ${g.rol==='Administrator'?'selected':''}>Administrator</option>
          </select>
        </td>
        <td>${g.aangemeld}</td>
        <td>
          <div class="action-group">
            <button class="edit-btn" data-lid="${json}" onclick="openEditModal(this)">✏ Bewerken</button>
            <button class="del-btn" onclick="askDeleteLid(${g.id})">✕ Verwijderen</button>
          </div>
        </td>
      </tr>
    `;

    if (isMedewerker) {
      mwHTML += rowHTML;
    } else {
      // Apply filters only to Leden tab for now
      if ((ledenFilter === 'alle' || g.rol === ledenFilter) && (g.naam.toLowerCase().includes(ledenZoek) || g.email.toLowerCase().includes(ledenZoek))) {
        ledenHTML += rowHTML;
        ledenAantal++;
      }
    }
  });

  if(ledenTabel) ledenTabel.innerHTML = ledenHTML || '<tr><td colspan="7" class="no-results">Geen leden gevonden</td></tr>';
  if(mwTabel) mwTabel.innerHTML = mwHTML || '<tr><td colspan="7" class="no-results">Geen medewerkers gevonden</td></tr>';
  
  const cnt = document.getElementById('ledenCount');
  if(cnt) cnt.textContent = ledenAantal + (ledenAantal===1?' lid':' leden');
}

function filterLeden() {
  renderGebruikers();
}

function updateRol(id, select){
  const rol = select.value;
  fetch('update_rol_ajax.php',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id, rol})
  })
  .then(r=>r.json()).then(d=>{
    if(!d.success){ toast('Rol bijwerken mislukt: ' + (d.error||''),'error'); select.value = select.dataset.prev||'Lid'; }
    else { 
      toast('Rol bijgewerkt naar '+rol); 
      fetchAlleGebruikers(); // Ververs data om lid te verplaatsen
    }
  }).catch(()=>toast('Verbindingsfout','error'));
}

// ═══════════════════════════════════════════════
//  EDIT MODAL — LID
//  Data wordt gelezen uit data-lid attribuut (JSON)
//  → geen problemen met apostroffen of aanhalingstekens in namen
// ═══════════════════════════════════════════════
function openEditModal(btn){
  const lid = JSON.parse(btn.getAttribute('data-lid'));
  document.getElementById('editLidId').value     = lid.id;
  document.getElementById('editNaam').value      = lid.naam;
  document.getElementById('editEmail').value     = lid.email;
  document.getElementById('editTelefoon').value  = lid.telefoon || '';
  document.getElementById('editRol').value       = lid.rol;
  document.getElementById('editWachtwoord').value= '';
  document.getElementById('editModal').classList.add('open');
}
function closeEditModal(){ document.getElementById('editModal').classList.remove('open'); }

function saveEdit(){
  const id         = document.getElementById('editLidId').value;
  const naam       = document.getElementById('editNaam').value.trim();
  const email      = document.getElementById('editEmail').value.trim();
  const telefoon   = document.getElementById('editTelefoon').value.trim();
  const rol        = document.getElementById('editRol').value;
  const wachtwoord = document.getElementById('editWachtwoord').value;
  if(!naam||!email){ toast('Naam en email zijn verplicht!','error'); return; }

  fetch('update_lid_ajax.php',{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({id, naam, email, telefoon, rol, wachtwoord})
  })
  .then(r=>r.json()).then(d=>{
    if(d.success){
      closeEditModal();
      toast(naam + ' bijgewerkt!');
      fetchAlleGebruikers();
    } else toast('Fout: '+(d.error||'Onbekend'),'error');
  }).catch(()=>toast('Verbindingsfout','error'));
}

// ═══════════════════════════════════════════════
//  EDIT MODAL — LES
// ═══════════════════════════════════════════════
function openEditLesModal(i){
  const l = lessen[i];
  if(!l){ toast('Les niet gevonden','error'); return; }
  document.getElementById('editLesIndex').value       = i;
  document.getElementById('editLesNaam').value        = l.naam;
  document.getElementById('editLesDatum').value       = l.datum;
  document.getElementById('editLesTijd').value        = l.tijd;
  document.getElementById('editLesPrijs').value       = l.prijs;
  document.getElementById('editLesMax').value         = l.max || '';
  document.getElementById('editLesInstructeur').value = l.instructeur || '';
  document.getElementById('editLesStatus').value      = l.status;
  document.getElementById('editLesModal').classList.add('open');
}
function closeEditLesModal(){ document.getElementById('editLesModal').classList.remove('open'); }

function saveEditLes(){
  const i    = parseInt(document.getElementById('editLesIndex').value);
  const les  = lessen[i];
  if (!les) return;
  const id   = les.id;
  const naam = document.getElementById('editLesNaam').value.trim();
  const datum= document.getElementById('editLesDatum').value;
  const tijd = document.getElementById('editLesTijd').value;
  const prijs= document.getElementById('editLesPrijs').value;
  if(!naam||!datum||!tijd||!prijs){ toast('Naam, datum, tijd en prijs zijn verplicht!','error'); return; }
  
  const max = document.getElementById('editLesMax').value ? parseInt(document.getElementById('editLesMax').value) : '';
  const instructeur = document.getElementById('editLesInstructeur').value.trim();
  const status = document.getElementById('editLesStatus').value;

  fetch('les_ajax.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({id, naam, datum, tijd, prijs, max, instructeur, status})
  }).then(r => r.json()).then(d => {
    if(d.success) {
      toast('Les "' + naam + '" bijgewerkt!');
      closeEditLesModal();
      fetchLessen();
    } else {
      toast('Fout: ' + (d.error || 'Onbekend'), 'error');
    }
  }).catch(() => toast('Verbindingsfout', 'error'));
}

// ═══════════════════════════════════════════════
//  LES TOEVOEGEN
// ═══════════════════════════════════════════════
function addLes(){
  const naam  = document.getElementById('lesNaam').value.trim();
  const datum = document.getElementById('lesDatum').value;
  const tijd  = document.getElementById('lesTijd').value;
  const prijs = document.getElementById('lesPrijs').value;
  if(!naam||!datum||!tijd||!prijs){ toast('Naam, datum, tijd en prijs zijn verplicht!','error'); return; }

  const max   = document.getElementById('lesMax').value ? parseInt(document.getElementById('lesMax').value) : '';
  const instructeur = document.getElementById('lesInstructeur').value.trim();
  const status = document.getElementById('lesStatus').value;

  fetch('les_ajax.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({naam, datum, tijd, prijs, max, instructeur, status})
  }).then(r => r.json()).then(d => {
    if(d.success) {
      toast('Les "' + naam + '" toegevoegd!');
      ['lesNaam','lesDatum','lesTijd','lesPrijs','lesMax','lesInstructeur'].forEach(id=>document.getElementById(id).value='');
      fetchLessen();
    } else {
      toast('Fout: ' + (d.error || 'Onbekend'), 'error');
    }
  }).catch(() => toast('Verbindingsfout', 'error'));
}

// ═══════════════════════════════════════════════
//  VERWIJDEREN MET BEVESTIGING
// ═══════════════════════════════════════════════
let _deleteCb = null;

function askDeleteLid(id){
  const chk = document.getElementById('toggleConfirm');
  if(chk && !chk.checked){ deleteLid(id); return; }
  document.getElementById('deleteMsg').textContent = 'Weet je zeker dat je dit lid wilt verwijderen? Dit kan niet ongedaan worden gemaakt.';
  _deleteCb = () => deleteLid(id);
  document.getElementById('deleteModal').classList.add('open');
}
function askDeleteLes(i){
  const chk = document.getElementById('toggleConfirm');
  if(chk && !chk.checked){ deleteLes(i); return; }
  document.getElementById('deleteMsg').textContent = 'Weet je zeker dat je les "' + (lessen[i]?lessen[i].naam:'') + '" wilt verwijderen?';
  _deleteCb = () => deleteLes(i);
  document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal(){ document.getElementById('deleteModal').classList.remove('open'); _deleteCb = null; }
function confirmDelete(){ if(_deleteCb) _deleteCb(); closeDeleteModal(); }

function deleteLid(id){
  fetch('delete_lid_ajax.php?id=' + id)
  .then(r=>r.json()).then(d=>{
    if(d.success){
      updateLedenStats(); 
      fetchAlleGebruikers(); // Ververs data
      toast('Gebruiker verwijderd');
    } else toast('Fout: '+(d.error||'Onbekend'),'error');
  }).catch(()=>toast('Verbindingsfout','error'));
}
function deleteLes(i){
  if(i<0||i>=lessen.length) return;
  const les = lessen[i];
  
  fetch('les_ajax.php?id=' + les.id, { method: 'DELETE' })
  .then(r => r.json()).then(d => {
    if(d.success) {
      toast('Les "' + les.naam + '" verwijderd');
      fetchLessen();
    } else {
      toast('Fout: ' + (d.error || 'Onbekend'), 'error');
    }
  }).catch(() => toast('Verbindingsfout', 'error'));
}

// ═══════════════════════════════════════════════
//  STATS UPDATE
// ═══════════════════════════════════════════════
function updateLedenStats(){
  fetch('get_leden_count.php').then(r=>r.json()).then(d=>{
    document.getElementById('stat-leden').textContent = d.totaal;
    document.getElementById('stat-nieuw').textContent = d.nieuw;
  }).catch(()=>{});
}
function updateLedenCount(){
  let n = 0;
  document.querySelectorAll('#ledenTabel tr[data-id]').forEach(r=>{ if(r.style.display!=='none') n++; });
  const c = document.getElementById('ledenCount');
  if(c) c.textContent = n + (n===1?' lid':' leden');
}

// ═══════════════════════════════════════════════
//  FILTERS
// ═══════════════════════════════════════════════
// Leden worden nu gefilterd in renderGebruikers()
function globalFilter(){
  const q = document.getElementById('globalSearch').value.toLowerCase();
  document.querySelectorAll('tbody tr').forEach(r=>{
    r.style.display = (!q || r.textContent.toLowerCase().includes(q)) ? '' : 'none';
  });
}

// ═══════════════════════════════════════════════
//  CSV EXPORT
// ═══════════════════════════════════════════════
function exportCSV(){
  let csv = 'Naam,Email,Telefoon,Rol,Aangemeld\n';
  document.querySelectorAll('#ledenTabel tr[data-id]').forEach(r=>{
    csv += '"'+r.cells[1].textContent.trim()+'","'+r.cells[2].textContent.trim()+'","'
          +r.cells[3].textContent.trim()+'","'+(r.cells[4].querySelector('select')?.value||'')+'","'
          +r.cells[5].textContent.trim()+'"\n';
  });
  dlCSV(csv,'leden_export.csv'); toast('Ledenlijst geëxporteerd!');
}
function exportLessenCSV(){
  let csv = 'Naam,Datum,Tijd,Instructeur,Prijs,Max,Status\n';
  lessen.forEach(l=>{ csv+='"'+l.naam+'","'+l.datum+'","'+l.tijd+'","'+(l.instructeur||'')+'","'+l.prijs+'","'+(l.max||'')+'","'+l.status+'"\n'; });
  dlCSV(csv,'lessen_export.csv'); toast('Lessenlijst geëxporteerd!');
}
function dlCSV(c,f){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(c); a.download=f; document.body.appendChild(a); a.click(); document.body.removeChild(a); }

// ═══════════════════════════════════════════════
//  INSTELLINGEN ACTIES
// ═══════════════════════════════════════════════
function clearAllLessen(){ toast('Alle lessen wissen is via de interface uitgeschakeld om dataverlies te voorkomen.', 'info'); }
function clearStorage(){ if(!confirm('Lokale cache wissen?')) return; localStorage.clear(); toast('Cache gewist. Herladen...','info'); setTimeout(()=>location.reload(),1500); }

// ═══════════════════════════════════════════════
//  CHART
// ═══════════════════════════════════════════════
new Chart(document.getElementById('ledenChart').getContext('2d'),{
  type:'line',
  data:{
    labels:['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'],
    datasets:[{
      label:'Leden', data:[3,5,8,12,15,<?= (int)$totaalLeden ?>,0,0,0,0,0,0],
      borderColor:'#e63946', backgroundColor:'rgba(230,57,70,0.08)',
      fill:true, tension:.4, pointBackgroundColor:'#e63946', pointRadius:4, pointHoverRadius:6
    }]
  },
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
    scales:{y:{beginAtZero:true,grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#666'}},x:{grid:{display:false},ticks:{color:'#666'}}}}
});

// ═══════════════════════════════════════════════
//  MEDEWERKERS API
// ═══════════════════════════════════════════════
let medewerkers = [];
function fetchMedewerkers() {
  fetch('medewerker_ajax.php').then(r=>r.json()).then(d=>{
    if(d.success) { medewerkers = d.data; renderMedewerkers(); }
    else toast('Fout bij ophalen medewerkers', 'error');
  }).catch(()=>toast('Verbindingsfout','error'));
}
function renderMedewerkers() {
  const tbody = document.getElementById('medewerkersTabel');
  if(!tbody) return;
  tbody.innerHTML = medewerkers.length ? medewerkers.map(m => `
    <tr>
      <td><strong>${esc(m.Voornaam)} ${esc(m.Tussenvoegsel)} ${esc(m.Achternaam)}</strong></td>
      <td>${m.Nummer}</td>
      <td><span class="badge badge-lid">${esc(m.Medewerkersoort)}</span></td>
      <td>
        <button class="del-btn" onclick="deleteMedewerker(${m.Id})">✕ Verwijderen</button>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="4" class="no-results">Geen medewerkers gevonden</td></tr>';
}
function addMedewerker() {
  const naam = document.getElementById('mwVoornaam').value.trim() + ' ' + document.getElementById('mwTussenvoegsel').value.trim() + ' ' + document.getElementById('mwAchternaam').value.trim();
  const email = document.getElementById('mwEmail').value.trim();
  const wachtwoord = 'Start123!';
  const rol = document.getElementById('mwSoort').value;
  if(!naam || !email) return toast('Naam en email verplicht!', 'error');
  fetch('add_lid_ajax.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({naam, email, telefoon:'', wachtwoord, rol}) })
  .then(r=>r.json()).then(d=>{
    if(d.success){ 
      toast('Medewerker toegevoegd met wachtwoord Start123!'); 
      ['mwVoornaam','mwTussenvoegsel','mwAchternaam','mwEmail'].forEach(id=>document.getElementById(id).value='');
      fetchAlleGebruikers(); 
    }
    else toast(d.error, 'error');
  });
}
function deleteMedewerker(id) { askDeleteLid(id); }

// ═══════════════════════════════════════════════
//  RESERVERINGEN API
// ═══════════════════════════════════════════════
let reserveringen = [];
function fetchReserveringen() {
  fetch('reservering_ajax.php').then(r=>r.json()).then(d=>{
    if(d.success) { reserveringen = d.data; renderReserveringen(); }
    else toast('Fout bij ophalen reserveringen', 'error');
  }).catch(()=>toast('Verbindingsfout','error'));
}
function renderReserveringen() {
  const tbody = document.getElementById('reserveringenTabel');
  if(!tbody) return;
  tbody.innerHTML = reserveringen.length ? reserveringen.map(r => `
    <tr>
      <td><strong>${esc(r.Voornaam)} ${esc(r.Tussenvoegsel)} ${esc(r.Achternaam)}</strong></td>
      <td>${r.Nummer}</td>
      <td>${r.Datum}</td>
      <td>${r.Tijd}</td>
      <td><span class="${r.Reserveringstatus === 'Gereserveerd' ? 'badge badge-ingepland' : 'badge badge-geannuleerd'}">${esc(r.Reserveringstatus)}</span></td>
      <td>
        <div class="action-group">
          <button class="edit-btn" onclick="openEditResModal(${r.Id}, '${r.Reserveringstatus}')">✏ Bewerken</button>
          <button class="del-btn" onclick="deleteReservering(${r.Id})">✕ Annuleren</button>
        </div>
      </td>
    </tr>
  `).join('') : '<tr><td colspan="6" class="no-results">Geen reserveringen gevonden</td></tr>';
}
function openEditResModal(id, status) {
  document.getElementById('editResId').value = id;
  document.getElementById('editResStatus').value = status;
  document.getElementById('editResModal').classList.add('open');
}
function closeEditResModal() {
  document.getElementById('editResModal').classList.remove('open');
}
function saveEditRes() {
  const id = document.getElementById('editResId').value;
  const status = document.getElementById('editResStatus').value;
  fetch('reservering_ajax.php', { method: 'PUT', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id, status}) })
  .then(r=>r.json()).then(d=>{
    if(d.success){ toast('Reservering bijgewerkt!'); fetchReserveringen(); closeEditResModal(); }
    else toast(d.error, 'error');
  });
}
function addReservering() {
  const voornaam = document.getElementById('resVoornaam').value.trim();
  const tussenvoegsel = document.getElementById('resTussenvoegsel').value.trim();
  const achternaam = document.getElementById('resAchternaam').value.trim();
  const datum = document.getElementById('resDatum').value;
  const tijd = document.getElementById('resTijd').value;
  if(!voornaam || !achternaam || !datum || !tijd) return toast('Vul alle verplichte velden in!', 'error');
  fetch('reservering_ajax.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({voornaam, tussenvoegsel, achternaam, datum, tijd}) })
  .then(r=>r.json()).then(d=>{
    if(d.success){ toast('Reservering geplaatst!'); fetchReserveringen(); }
    else toast(d.error, 'error');
  });
}
function deleteReservering(id) {
  if(!confirm('Reservering annuleren?')) return;
  fetch('reservering_ajax.php?id='+id, { method: 'DELETE' }).then(r=>r.json()).then(d=>{
    if(d.success){ toast('Geannuleerd!'); fetchReserveringen(); } else toast(d.error, 'error');
  });
}

// ═══════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════
fetchAlleGebruikers();
updateLedenStats();
</script>
</body>
</html>
