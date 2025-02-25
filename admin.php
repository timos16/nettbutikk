<?php
session_start();
require_once 'config.php';

// Sjekk om bruker er definert som admin i database
if (!isset($_SESSION['er_admin']) || !$_SESSION['er_admin']) {
    header("Location: index.php");
    exit();
}

// Handter produktsletting
if (isset($_POST['slett_produkt'])) {
    $produkt_id = mysqli_real_escape_string($conn, $_POST['produkt_id']);
    
    // Slett produktbilete først
    $sql = "SELECT bilde_url FROM produkt WHERE produkt_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $produkt_id);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    
    if ($produkt = mysqli_fetch_assoc($resultat)) {
        if ($produkt['bilde_url']) {
            unlink("produkt_bilder/" . $produkt['bilde_url']);
        }
    }
    
    // Slett produktet
    $sql = "DELETE FROM produkt WHERE produkt_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $produkt_id);
    mysqli_stmt_execute($stmt);
}

// Handter brukarsletting
if (isset($_POST['slett_brukar'])) {
    $brukar_id = mysqli_real_escape_string($conn, $_POST['brukar_id']);
    
    // Hindre admin i å slette seg sjølv
    if ($brukar_id != $_SESSION['brukar_id']) {
        // Slett berre vanlege brukarar, ikkje admin
        $sql = "DELETE FROM brukarar WHERE brukar_id = ? AND er_admin = 0";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $brukar_id);
        mysqli_stmt_execute($stmt);
    }
}

// Handter produktopplasting
if (isset($_POST['legg_til_produkt'])) {
    $namn = mysqli_real_escape_string($conn, $_POST['namn']);
    $pris = floatval($_POST['pris']);
    $lager_antal = intval($_POST['lager_antal']);
    $beskriving = mysqli_real_escape_string($conn, $_POST['beskriving']);
    $bilde_url = '';
    
    // Handter bileteopplasting
    if (isset($_FILES['bilde']) && $_FILES['bilde']['error'] == 0) {
        $tillatne_typar = ['image/jpeg', 'image/png', 'image/gif'];
        $maks_storleik = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['bilde']['type'], $tillatne_typar) && 
            $_FILES['bilde']['size'] <= $maks_storleik) {
            
            $filending = pathinfo($_FILES['bilde']['name'], PATHINFO_EXTENSION);
            $nytt_namn = uniqid() . '.' . $filending;
            
            if (move_uploaded_file($_FILES['bilde']['tmp_name'], 'produkt_bilder/' . $nytt_namn)) {
                $bilde_url = $nytt_namn;
            }
        }
    }
    
    // Legg til produkt i databasen
    $sql = "INSERT INTO produkt (namn, pris, lager_antal, beskriving, bilde_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sdiss", $namn, $pris, $lager_antal, $beskriving, $bilde_url);
    mysqli_stmt_execute($stmt);
}

// Hent alle produkt
$sql = "SELECT * FROM produkt ORDER BY namn";
$produkt_resultat = mysqli_query($conn, $sql);

// Hent alle brukarar
$sql = "SELECT * FROM brukarar ORDER BY brukarnamn";
$brukar_resultat = mysqli_query($conn, $sql);

// Hent alle ordrar
$sql = "SELECT o.*, b.brukarnamn 
        FROM ordrar o 
        JOIN brukarar b ON o.brukar_id = b.brukar_id 
        ORDER BY o.ordre_dato DESC";
$ordre_resultat = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Nettbutikk</title>
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="navbar-brand">Nettbutikk</a>
            <button class="navbar-toggler" onclick="document.querySelector('.navbar-nav').classList.toggle('show')">
                Meny
            </button>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Tilbake til butikk</a>
                </li>
                <li class="nav-item">
                    <a href="logg_ut.php" class="nav-link">Logg ut</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="admin-header">
            <h2>Administrasjonspanel</h2>
        </div>

        <div class="admin-layout">

            <!-- Venstre kolonne -->
            <div class="admin-column">
                <!-- Legg til nytt produkt -->
                <div class="admin-section">
                    <div class="admin-section-header">
                        <h3>Legg til nytt produkt</h3>
                    </div>
                    <div class="admin-section-body">
                        <form method="post" enctype="multipart/form-data" class="admin-form">
                            <div class="form-group">
                                <label for="namn" class="form-label">Produktnamn</label>
                                <input type="text" class="form-control" id="namn" name="namn" required>
                            </div>
                            <div class="form-group">
                                <label for="pris" class="form-label">Pris (NOK)</label>
                                <input type="number" step="0.01" class="form-control" id="pris" name="pris" required>
                            </div>
                            <div class="form-group">
                                <label for="lager_antal" class="form-label">Antal på lager</label>
                                <input type="number" class="form-control" id="lager_antal" name="lager_antal" required>
                            </div>
                            <div class="form-group">
                                <label for="beskriving" class="form-label">Produktbeskriving</label>
                                <textarea class="form-control" id="beskriving" name="beskriving" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="bilde" class="form-label">Produktbilete</label>
                                <input type="file" class="form-control" id="bilde" name="bilde" accept="image/*">
                            </div>
                            <button type="submit" name="legg_til_produkt" class="auth-btn">Legg til produkt</button>
                        </form>
                    </div>
                </div>

                <!-- Produktadministrasjon -->
                <div class="admin-section">
                    <div class="admin-section-header">
                        <h3>Produkt</h3>
                    </div>
                    <div class="admin-section-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Bilete</th>
                                        <th>Namn</th>
                                        <th>Pris</th>
                                        <th>Lager</th>
                                        <th>Handling</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($produkt = mysqli_fetch_assoc($produkt_resultat)): ?>
                                        <tr>
                                            <td>
                                                <?php if ($produkt['bilde_url']): ?>
                                                    <img src="produkt_bilder/<?php echo htmlspecialchars($produkt['bilde_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($produkt['namn']); ?>"
                                                         class="admin-product-image">
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($produkt['namn']); ?></td>
                                            <td><?php echo number_format($produkt['pris'], 2); ?> kr</td>
                                            <td>
                                                <span class="admin-status <?php echo $produkt['lager_antal'] > 0 ? 'admin-status-active' : 'admin-status-inactive'; ?>">
                                                    <?php echo $produkt['lager_antal']; ?> stk
                                                </span>
                                            </td>
                                            <td class="admin-actions">
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="produkt_id" value="<?php echo $produkt['produkt_id']; ?>">
                                                    <button type="submit" name="slett_produkt" class="admin-btn admin-btn-danger"
                                                            onclick="return confirm('Er du sikker på at du vil slette dette produktet?')">
                                                        Slett
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Høyre kolonne -->
            <div class="admin-column">
                <!-- Brukaradministrasjon -->
                <div class="admin-section">
                    <div class="admin-section-header">
                        <h3>Brukarar</h3>
                    </div>
                    <div class="admin-section-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Brukarnamn</th>
                                        <th>Type</th>
                                        <th>Handling</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($brukar = mysqli_fetch_assoc($brukar_resultat)): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($brukar['brukarnamn']); ?>
                                                <div style="font-size: 0.8em; color: #666;">
                                                    <?php echo htmlspecialchars($brukar['epost']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="admin-status <?php echo $brukar['er_admin'] ? 'admin-status-active' : ''; ?>">
                                                    <?php echo $brukar['er_admin'] ? 'Admin' : 'Brukar'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!$brukar['er_admin']): ?>
                                                    <form method="post">
                                                        <input type="hidden" name="brukar_id" value="<?php echo $brukar['brukar_id']; ?>">
                                                        <button type="submit" name="slett_brukar" class="admin-btn admin-btn-danger"
                                                                onclick="return confirm('Er du sikker på at du vil slette denne brukaren?')">
                                                            Slett
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ordreadministrasjon -->
                <div class="admin-section">
                    <div class="admin-section-header">
                        <h3>Siste ordrar</h3>
                    </div>
                    <div class="admin-section-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Ordre #</th>
                                        <th>Brukar</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ordre = mysqli_fetch_assoc($ordre_resultat)): ?>
                                        <tr>
                                            <td>#<?php echo $ordre['ordre_id']; ?></td>
                                            <td><?php echo htmlspecialchars($ordre['brukarnamn']); ?></td>
                                            <td><?php echo number_format($ordre['total_pris'], 2); ?> kr</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
