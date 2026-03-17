<?php
session_start();
include 'db_config.php';

// ADMIN CHECK
if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Haal leden op voor de stats
$stmt = $conn->query("SELECT COUNT(*) as totaal FROM leden");
$totaalLeden = $stmt->fetch()['totaal'];

// Haal nieuwe leden van deze maand op
$stmt = $conn->prepare("SELECT COUNT(*) as nieuw FROM leden WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$stmt->execute();
$nieuweLeden = $stmt->fetch()['nieuw'];

// Haal echte leden op voor de ledentabel
$stmt = $conn->query("SELECT id, naam, email, telefoon, YEAR(created_at) as jaar FROM leden ORDER BY created_at DESC");
$leden = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Beheer Dashboard</title>
    <link rel="stylesheet" href="walid.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0b1220;
            color: white;
            font-family: Arial, sans-serif;
        }
        
        .topbar {
            position: fixed;
            top: 0;
            width: 100%;
            height: 70px;
            background: #111827;
            display: flex;
            align-items: center;
            padding: 0 30px;
            z-index: 1000;
            border-bottom: 1px solid #1f2937;
        }
        
        .menu-btn {
            font-size: 24px;
            cursor: pointer;
            margin-right: 20px;
            color: white;
        }
        
        .sidebar {
            position: fixed;
            top: 70px;
            left: -240px;
            width: 240px;
            height: 100%;
            background: #1f2937;
            padding: 30px 20px;
            transition: 0.3s;
            z-index: 999;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar ul li {
            padding: 15px;
            margin-bottom: 8px;
            cursor: pointer;
            border-radius: 6px;
            color: white;
        }
        
        .sidebar ul li:hover {
            background: #374151;
        }
        
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        
        .main {
            margin-top: 90px;
            padding: 50px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            transition: 0.3s;
        }
        
        .main.shift {
            margin-left: 260px;
        }
        
        .lesson-table {
            width: 100%;
            border-collapse: collapse;
            background: #111827;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 50px;
        }
        
        .lesson-table th {
            padding: 18px;
            color: #8fa1c7;
            text-align: left;
            font-weight: normal;
        }
        
        .lesson-table td {
            padding: 18px;
            border-top: 1px solid #1f2937;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            display: inline-block;
        }
        
        .blue {
            background: #2563eb;
        }
        
        .gray {
            background: #374151;
        }
        
        .delete-btn {
            background: #b91c1c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .delete-btn:hover {
            background: #991b1b;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .card {
            padding: 25px;
            background: #111827;
            border-radius: 12px;
        }
        
        .card p {
            font-size: 28px;
            margin-top: 10px;
            color: #3b82f6;
        }
        
        .chart-box {
            margin-top: 50px;
            background: #111827;
            padding: 35px;
            border-radius: 12px;
        }
        
        .form-box {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            background: #111827;
            padding: 20px;
            border-radius: 12px;
        }
        
        input, select {
            padding: 10px;
            border: 1px solid #374151;
            border-radius: 6px;
            background: #1f2937;
            color: white;
            flex: 1;
            min-width: 150px;
        }
        
        input::placeholder {
            color: #9ca3af;
        }
        
        button {
            padding: 10px 15px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        button:hover {
            background: #1d4ed8;
        }
        
        .top-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .content-page {
            display: none;
        }
        
        #page-dashboard {
            display: block;
        }
        
        h1, h2 {
            margin-bottom: 20px;
            color: white;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="menu-btn" id="menuBtn">☰</div>
    <h2>Gym Beheer Dashboard</h2>
</div>

<div class="sidebar" id="sidebar">
    <ul>
        <li onclick="switchPage('dashboard')">Dashboard</li>
        <li onclick="switchPage('leden')">Leden</li>
        <li onclick="switchPage('lessen')">Lessen</li>
        <li><a href="logout.php">Uitloggen</a></li>
    </ul>
</div>

<div class="main" id="main">

    <!-- DASHBOARD PAGINA -->
    <div id="page-dashboard" class="content-page">
        <h1>Geplande Lessen</h1>
        <div class="top-controls">
            <input type="text" id="searchInput" placeholder="Zoek op lesnaam..." onkeyup="filterLessen()">
            <select id="statusFilter" onchange="filterLessen()">
                <option value="alle">Alle statussen</option>
                <option value="Ingepland">Ingepland</option>
                <option value="Geannuleerd">Geannuleerd</option>
            </select>
        </div>

        <table class="lesson-table">
            <thead>
                <tr>
                    <th>Les</th>
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Prijs</th>
                    <th>Status</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody id="lesTabelDashboard">
            </tbody>
        </table>

        <h2>Aantal Leden</h2>
        <div class="stats">
            <div class="card">
                <h3>TOTAAL LEDEN</h3>
                <p id="stat-leden"><?= $totaalLeden ?></p>
            </div>
            <div class="card">
                <h3>NIEUWE LEDEN</h3>
                <p id="stat-nieuw"><?= $nieuweLeden ?></p>
            </div>
            <div class="card">
                <h3>ACTIEVE LEDEN</h3>
                <p id="stat-actief"><?= $totaalLeden ?></p>
            </div>
            <div class="card">
                <h3>LESSEN</h3>
                <p id="stat-lessen">3</p>
            </div>
        </div>

        <div class="chart-box">
            <canvas id="ledenChart"></canvas>
        </div>
    </div>

    <!-- LEDEN PAGINA -->
    <div id="page-leden" class="content-page">
        <h2>Lid toevoegen</h2>
        <div class="form-box">
            <input id="lidNaam" placeholder="Naam">
            <input id="lidEmail" type="email" placeholder="Email">
            <input id="lidTelefoon" placeholder="Telefoon">
            <input id="lidWachtwoord" type="password" placeholder="Wachtwoord">
            <button onclick="addLid()">Toevoegen</button>
        </div>
        
        <table class="lesson-table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Telefoon</th>
                    <th>Jaar</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody id="ledenTabelBody">
                <?php foreach($leden as $lid): ?>
                <tr data-id="<?= $lid['id'] ?>">
                    <td><?= htmlspecialchars($lid['naam']) ?></td>
                    <td><?= htmlspecialchars($lid['email']) ?></td>
                    <td><?= htmlspecialchars($lid['telefoon'] ?? '') ?></td>
                    <td><?= $lid['jaar'] ?></td>
                    <td>
                        <button class="delete-btn" onclick="deleteLid(<?= $lid['id'] ?>, this)">X</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- LESSEN PAGINA -->
    <div id="page-lessen" class="content-page">
        <h2>Nieuwe les toevoegen</h2>
        <div class="form-box">
            <input id="lesNaam" placeholder="Les naam">
            <input type="date" id="lesDatum">
            <input type="time" id="lesTijd">
            <input type="number" id="lesPrijs" placeholder="Prijs" step="0.01">
            <select id="lesStatus">
                <option value="Ingepland">Ingepland</option>
                <option value="Geannuleerd">Geannuleerd</option>
            </select>
            <button onclick="addLes()">Les toevoegen</button>
        </div>

        <h2>Overzicht Lessen</h2>
        <table class="lesson-table">
            <thead>
                <tr>
                    <th>Les</th>
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Prijs</th>
                    <th>Status</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody id="lesTabelPagina">
            </tbody>
        </table>
    </div>

</div>

<script>
// Data opslag voor lessen (in localStorage)
let lessen = JSON.parse(localStorage.getItem('lessen')) || [
    { naam: 'Yoga', datum: '2026-03-01', tijd: '09:00', prijs: 12.50, status: 'Ingepland' },
    { naam: 'Krachttraining', datum: '2026-03-02', tijd: '18:00', prijs: 15.00, status: 'Ingepland' },
    { naam: 'Spinning', datum: '2026-03-03', tijd: '19:30', prijs: 10.00, status: 'Geannuleerd' }
];

// SIDEBAR
const sidebar = document.getElementById("sidebar");
const main = document.getElementById("main");
const menuBtn = document.getElementById("menuBtn");
 
menuBtn.onclick = () => {
    sidebar.classList.toggle("active");
    main.classList.toggle("shift");
};
 
// PAGINA SWITCH
function switchPage(page){
    document.querySelectorAll(".content-page").forEach(p=>{
        p.style.display = "none";
    });
    document.getElementById("page-" + page).style.display = "block";
    
    if(page === 'dashboard') {
        toonLessen();
    } else if(page === 'lessen') {
        toonLessenPagina();
    }
}
 
// LEDEN TOEVOEGEN
function addLid(){
    let naam = document.getElementById("lidNaam").value;
    let email = document.getElementById("lidEmail").value;
    let telefoon = document.getElementById("lidTelefoon").value;
    let wachtwoord = document.getElementById("lidWachtwoord").value;
 
    if(!naam || !email || !wachtwoord) {
        alert('Naam, email en wachtwoord zijn verplicht!');
        return;
    }
    
    fetch('add_lid_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            naam: naam,
            email: email,
            telefoon: telefoon,
            wachtwoord: wachtwoord
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            let table = document.getElementById("ledenTabelBody");
            let row = table.insertRow();
            row.setAttribute('data-id', data.id);
            row.innerHTML = `
                <td>${naam}</td>
                <td>${email}</td>
                <td>${telefoon || ''}</td>
                <td>${new Date().getFullYear()}</td>
                <td><button class="delete-btn" onclick="deleteLid(${data.id}, this)">X</button></td>
            `;
            
            document.getElementById("lidNaam").value = '';
            document.getElementById("lidEmail").value = '';
            document.getElementById("lidTelefoon").value = '';
            document.getElementById("lidWachtwoord").value = '';
            
            updateStats();
        } else {
            alert('Fout: ' + data.error);
        }
    });
}
 
// LID VERWIJDEREN
function deleteLid(id, btn) {
    if(!confirm('Weet je zeker dat je dit lid wilt verwijderen?')) return;
    
    fetch('delete_lid_ajax.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            btn.closest('tr').remove();
            updateStats();
        } else {
            alert('Fout bij verwijderen: ' + data.error);
        }
    });
}
 
// LES TOEVOEGEN
function addLes(){
    let naam = document.getElementById("lesNaam").value;
    let datum = document.getElementById("lesDatum").value;
    let tijd = document.getElementById("lesTijd").value;
    let prijs = document.getElementById("lesPrijs").value;
    let status = document.getElementById("lesStatus").value;
 
    if (!naam || !datum || !tijd || !prijs) {
        alert('Vul alle velden in!');
        return;
    }
    
    let nieuweLes = {
        naam: naam,
        datum: datum,
        tijd: tijd,
        prijs: parseFloat(prijs),
        status: status
    };
    
    lessen.push(nieuweLes);
    localStorage.setItem('lessen', JSON.stringify(lessen));
    
    document.getElementById("lesNaam").value = '';
    document.getElementById("lesDatum").value = '';
    document.getElementById("lesTijd").value = '';
    document.getElementById("lesPrijs").value = '';
    
    document.getElementById("stat-lessen").innerText = lessen.length;
    
    toonLessen();
    toonLessenPagina();
    
    switchPage('dashboard');
}
 
// LESSEN TONEN OP DASHBOARD
function toonLessen() {
    let tabel = document.getElementById("lesTabelDashboard");
    tabel.innerHTML = '';
    
    lessen.forEach(les => {
        let row = tabel.insertRow();
        row.innerHTML = `
            <td>${les.naam}</td>
            <td>${les.datum}</td>
            <td>${les.tijd}</td>
            <td>€${les.prijs.toFixed(2)}</td>
            <td><span class="badge ${les.status === 'Ingepland' ? 'blue' : 'gray'}">${les.status}</span></td>
            <td><button class="delete-btn" onclick="deleteLes(this, '${les.naam}', '${les.datum}')">X</button></td>
        `;
    });
}
 
// LESSEN TONEN OP LESSEN PAGINA
function toonLessenPagina() {
    let tabel = document.getElementById("lesTabelPagina");
    tabel.innerHTML = '';
    
    lessen.forEach(les => {
        let row = tabel.insertRow();
        row.innerHTML = `
            <td>${les.naam}</td>
            <td>${les.datum}</td>
            <td>${les.tijd}</td>
            <td>€${les.prijs.toFixed(2)}</td>
            <td><span class="badge ${les.status === 'Ingepland' ? 'blue' : 'gray'}">${les.status}</span></td>
            <td><button class="delete-btn" onclick="deleteLes(this, '${les.naam}', '${les.datum}')">X</button></td>
        `;
    });
}
 
// LES VERWIJDEREN
function deleteLes(btn, naam, datum) {
    if(!confirm('Weet je zeker dat je deze les wilt verwijderen?')) return;
    
    lessen = lessen.filter(les => !(les.naam === naam && les.datum === datum));
    localStorage.setItem('lessen', JSON.stringify(lessen));
    
    document.getElementById("stat-lessen").innerText = lessen.length;
    
    toonLessen();
    toonLessenPagina();
}
 
// FILTER
function filterLessen() {
    let filter = document.getElementById("searchInput").value.toLowerCase();
    let statusFilter = document.getElementById("statusFilter").value;
    let rows = document.querySelectorAll("#lesTabelDashboard tr");
 
    rows.forEach(row => {
        let text = row.cells[0].textContent.toLowerCase();
        let status = row.cells[4].textContent.trim();
        
        let matchText = text.includes(filter);
        let matchStatus = statusFilter === 'alle' || status === statusFilter;
        
        row.style.display = matchText && matchStatus ? "" : "none";
    });
}
 
// STATS UPDATE
function updateStats() {
    fetch('get_leden_count.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById("stat-leden").innerText = data.totaal;
        document.getElementById("stat-nieuw").innerText = data.nieuw;
        document.getElementById("stat-actief").innerText = data.totaal;
    });
}
 
// GRAFIEK
const ctx = document.getElementById("ledenChart");
new Chart(ctx, {
    type: "line",
    data: {
        labels: ["Jan", "Feb", "Mrt", "Apr", "Mei", "Jun"],
        datasets: [{
            label: 'Aantal leden',
            data: [5, 8, 12, 15, 18, <?= $totaalLeden ?>],
            borderColor: "#3b82f6",
            backgroundColor: "rgba(59,130,246,0.2)",
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        plugins: { 
            legend: { 
                labels: { color: 'white' }
            } 
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#374151' },
                ticks: { color: 'white' }
            },
            x: {
                grid: { color: '#374151' },
                ticks: { color: 'white' }
            }
        }
    }
});

// Initialiseer
toonLessen();
toonLessenPagina();
</script>
</body>
</html>