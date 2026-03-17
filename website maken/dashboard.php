<?php
include '../db_config.php';

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $lid_id = (int)$_POST['lid_id'];
    $rol = in_array($_POST['rol'], ['lid', 'admin']) ? $_POST['rol'] : 'lid';

    if ($lid_id === $_SESSION['lid_id'] && $rol !== 'admin') {
        $error = 'Je kunt jouw eigen rol niet naar lid wijzigen.';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE leden SET rol = ? WHERE id = ?");
            $stmt->execute([$rol, $lid_id]);
            $success = 'Rol succesvol bijgewerkt.';
        } catch (PDOException $e) {
            $error = 'Fout bij rol wijzigen: ' . $e->getMessage();
        }
    }
}

try {
    $stmt = $conn->query("SELECT * FROM leden ORDER BY created_at DESC");
    $leden = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Fout bij laden leden: ' . $e->getMessage();
    $leden = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Beheer Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/walid.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <div class="burger" onclick="toggleMenu()">☰</div>
    <div class="logo">FitForFun</div>
</header>

<nav class="menu" id="menu">
    <a href="index.html">Home</a>
    <a href="lessen.html">Lessen</a>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="#">Abonnement</a>
    <a href="#">Contact</a>
    <a href="../logout.php">Uitloggen</a>
</nav>

<div class="topbar">
    <div class="menu-btn" id="menuBtn">☰</div>
    <h2>Gym Beheer Dashboard</h2>
</div>

<div class="sidebar" id="sidebar">
    <ul>
        <li onclick="switchPage('dashboard')">Dashboard</li>
        <li onclick="switchPage('leden')">Leden</li>
        <li onclick="switchPage('lessen')">Lessen</li>
    </ul>
</div>

<div class="main" id="main">

    <div id="page-dashboard" class="content-page">
        <h1>Geplande Lessen</h1>
        <div class="top-controls">
            <input type="text" id="searchInput" placeholder="Zoek op lesnaam..." onkeyup="filterLessen()">
            <select id="statusFilter" onchange="filterLessen()">
                <option value="all">Alle statussen</option>
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
                <tr>
                    <td>Yoga</td>
                    <td>2026-03-01</td>
                    <td>09:00</td>
                    <td>€12.50</td>
                    <td><span class="badge blue">Ingepland</span></td>
                    <td><button class="delete-btn" onclick="deleteRow(this)">X</button></td>
                </tr>
            </tbody>
        </table>

        <h2>Aantal Leden per Periode</h2>
        <div class="stats">
            <div class="card">
                <h3>TOTAAL LEDEN</h3>
                <p id="stat-leden">0</p>
            </div>
            <div class="card">
                <h3>NIEUWE LEDEN</h3>
                <p id="stat-nieuw">0</p>
            </div>
            <div class="card">
                <h3>ACTIEVE LEDEN</h3>
                <p id="stat-actief">0</p>
            </div>
            <div class="card">
                <h3>INACTIEVE LEDEN</h3>
                <p id="stat-inactief">0</p>
            </div>
        </div>

        <div class="chart-box">
            <canvas id="ledenChart"></canvas>
        </div>
    </div>

    <div id="page-leden" class="content-page" style="display:none;">
        <h2>Ledenbeheer</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <table class="lesson-table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Ingeschreven</th>
                    <th>Status</th>
                    <th>Rol</th>
                    <th>Bewerken</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leden as $lid): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lid['naam']); ?></td>
                        <td><?php echo htmlspecialchars($lid['email']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($lid['created_at'])); ?></td>
                        <td><?php echo ($lid['id'] == $_SESSION['lid_id']) ? 'Ingelogd' : 'Niet ingelogd'; ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($lid['rol'])); ?></td>
                        <td>
                            <form method="POST" style="display:flex; gap:0.4rem; align-items:center;">
                                <select name="rol">
                                    <option value="lid" <?php echo $lid['rol'] === 'lid' ? 'selected' : ''; ?>>Lid</option>
                                    <option value="admin" <?php echo $lid['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="lid_id" value="<?php echo $lid['id']; ?>">
                                <button type="submit" name="change_role" class="btn">Opslaan</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="page-lessen" class="content-page" style="display:none;">
        <h2>Nieuwe les toevoegen</h2>
        <div class="form-box">
            <input id="lesNaam" placeholder="Les naam">
            <input type="date" id="lesDatum">
            <input type="time" id="lesTijd">
            <input type="number" id="lesPrijs" placeholder="Prijs">
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
            <tbody id="lesTabelPagina"></tbody>
        </table>
    </div>

</div>

<script src="../js/script.js"></script>
<script src="../js/walid.js"></script>
</body>
</html>