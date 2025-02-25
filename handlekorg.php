<?php
session_start();
require_once 'config.php';

// Sjekk om brukaren er innlogga (skjekker om $_SESSION er definert)
if (!isset($_SESSION['brukar_id'])) {
    header("Location: logg_inn.php");
    exit();
}

if (!isset($_SESSION['handlekorg'])) {
    $_SESSION['handlekorg'] = array();
}

// Oppdater antall
if (isset($_POST['oppdater'])) {
    foreach ($_POST['antal'] as $produkt_id => $antal) {
        if ($antal > 0) {
            $_SESSION['handlekorg'][$produkt_id] = $antal;
        } else {
            unset($_SESSION['handlekorg'][$produkt_id]);
        }
    }
}

// Fjern produkt
if (isset($_GET['fjern'])) {
    $produkt_id = $_GET['fjern'];
    unset($_SESSION['handlekorg'][$produkt_id]);
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handlekorg - Nettbutikk</title>
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
                    <a href="index.php" class="nav-link">Heim</a>
                </li>
                <?php if (isset($_SESSION['brukar_id'])): ?>
                    <li class="nav-item">
                        <a href="handlekorg.php" class="nav-link">Handlekorg</a>
                    </li>
                    <li class="nav-item">
                        <a href="ordrar.php" class="nav-link">Mine ordrar</a>
                    </li>
                    <?php if ($_SESSION['er_admin']): ?>
                        <li class="nav-item">
                            <a href="admin.php" class="nav-link">Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="logg_ut.php" class="nav-link">Logg ut</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="logg_inn.php" class="nav-link">Logg inn</a>
                    </li>
                    <li class="nav-item">
                        <a href="registrer.php" class="nav-link">Registrer</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Din handlekorg</h2>
        
        <?php if (empty($_SESSION['handlekorg'])): ?>
            <p>Handlekorga di er tom.</p>
            <a href="index.php" class="btn btn-primary">Fortsett å handle</a>
        <?php else: ?>
            <form method="post" action="">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Pris</th>
                            <th>Antal</th>
                            <th>Sum</th>
                            <th>Handling</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($_SESSION['handlekorg'] as $produkt_id => $antal) {
                            $sql = "SELECT * FROM produkt WHERE produkt_id = ?";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "i", $produkt_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            
                            if ($produkt = mysqli_fetch_assoc($result)) {
                                $sum = $produkt['pris'] * $antal;
                                $total += $sum;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($produkt['namn']); ?></td>
                                    <td>kr <?php echo number_format($produkt['pris'], 2, ',', ' '); ?></td>
                                    <td>
                                        <input type="number" name="antal[<?php echo $produkt_id; ?>]" 
                                               value="<?php echo $antal; ?>" min="0" max="<?php echo $produkt['lager_antal']; ?>" 
                                               class="form-control">
                                    </td>
                                    <td>kr <?php echo number_format($sum, 2, ',', ' '); ?></td>
                                    <td>
                                        <a href="?fjern=<?php echo $produkt_id; ?>" class="btn btn-danger btn-sm">Fjern</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>kr <?php echo number_format($total, 2, ',', ' '); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" name="oppdater" class="btn btn-secondary">Oppdater handlekorg</button>
                    <a href="bestill.php" class="btn btn-success">Gå til kasse</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p class="footer-text">&copy; <?php echo date('Y'); ?> Nettbutikk</p>
            </div>
        </div>
    </footer>
</body>
</html>
