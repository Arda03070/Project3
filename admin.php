<?php
include 'db_config.php';

if (!isset($_SESSION['lid_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$lid_id = isset($_GET['id']) ? (int)$_GET['id'] : null;


if ($action === 'delete' && $lid_id) {
    try {
        $stmt = $conn->prepare("DELETE FROM leden WHERE id = ? AND rol != 'admin'");
        $stmt->execute([$lid_id]);
        
        if ($stmt->rowCount() > 0) {
            $success = "Lid succesvol verwijderd!";
        } else {
            $error = "Kan admin account niet verwijderen!";
        }
    } catch (PDOException $e) {
        $error = "Fout bij verwijderen: " . $e->getMessage();
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_lid'])) {
    $id = (int)$_POST['id'];
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $telefoon = trim($_POST['telefoon']);
    $rol = $_POST['rol'];
    
    try {
        $stmt = $conn->prepare("UPDATE leden SET naam = ?, email = ?, telefoon = ?, rol = ? WHERE id = ?");
        $stmt->execute([$naam, $email, $telefoon, $rol, $id]);
        $success = "Lid succesvol bijgewerkt!";
    } catch (PDOException $e) {
        $error = "Fout bij bijwerken: " . $e->getMessage();
    }
}

try {
    $stmt = $conn->query("SELECT * FROM leden ORDER BY created_at DESC");
    $leden = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Fout bij laden leden: " . $e->getMessage();
    $leden = [];
}


$edit_lid = null;
if ($action === 'edit' && $lid_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM leden WHERE id = ?");
        $stmt->execute([$lid_id]);
        $edit_lid = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Fout bij laden lid: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FitForFun</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<header>
    <div class="burger" onclick="toggleMenu()">☰</div>
    <div class="logo">FitForFun</div>
</header>

<nav class="menu" id="menu">
    <a href="website maken/index.html">Home</a>
    <a href="admin.php">Admin Dashboard</a>
    <a href="logout.php">Uitloggen</a>
</nav>

<div class="admin-container">
    <div class="admin-header">
        <h1>👨‍💼 Admin Dashboard</h1>
        <a href="logout.php" class="logout-btn">Uitloggen</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

   
    <?php if ($edit_lid): ?>
    <div class="form-section">
        <h2>Lid Bewerken</h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $edit_lid['id']; ?>">
            
            <div class="form-group">
                <label for="naam">Naam *</label>
                <input type="text" id="naam" name="naam" required value="<?php echo htmlspecialchars($edit_lid['naam']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">E-mailadres *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($edit_lid['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="telefoon">Telefoonnummer *</label>
                <input type="tel" id="telefoon" name="telefoon" required value="<?php echo htmlspecialchars($edit_lid['telefoon']); ?>">
            </div>
            
            <div class="form-group">
                <label for="rol">Rol *</label>
                <select id="rol" name="rol" required>
                    <option value="lid" <?php echo $edit_lid['rol'] === 'lid' ? 'selected' : ''; ?>>Lid</option>
                    <option value="admin" <?php echo $edit_lid['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="update_lid" class="btn">Opslaan</button>
                <a href="admin.php" class="btn" style="background-color: #95a5a6; padding: 10px 20px; text-decoration: none; border-radius: 5px; color: white;">Annuleren</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

  
    <div class="table-container">
        <h2>👥 Alle Leden (<?php echo count($leden); ?>)</h2>
        
        <?php if (count($leden) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>E-mailadres</th>
                    <th>Telefoonnummer</th>
                    <th>Rol</th>
                    <th>Ingeschreven</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leden as $lid): ?>
                <tr>
                    <td><?php echo $lid['id']; ?></td>
                    <td><?php echo htmlspecialchars($lid['naam']); ?></td>
                    <td><?php echo htmlspecialchars($lid['email']); ?></td>
                    <td><?php echo htmlspecialchars($lid['telefoon']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $lid['rol']; ?>">
                            <?php echo ucfirst($lid['rol']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d-m-Y', strtotime($lid['created_at'])); ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="admin.php?action=edit&id=<?php echo $lid['id']; ?>" class="btn-edit">✏️ Bewerk</a>
                            <?php if ($lid['rol'] !== 'admin'): ?>
                            <a href="admin.php?action=delete&id=<?php echo $lid['id']; ?>" class="btn-delete" onclick="return confirm('Weet je zeker dat je dit lid wilt verwijderen?');">🗑️ Verwijder</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Geen leden gevonden.</p>
        <?php endif; ?>
    </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
