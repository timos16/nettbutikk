<?php
session_start();
require_once 'config.php';

// Sjekk om brukaren er innlogga (skjekker om brukar_id er definert)
if (!isset($_SESSION['brukar_id'])) {
    header("Location: logg_inn.php");
    exit();
}

// Hent alle ordrar for brukaren
$sql = "SELECT * FROM ordrar WHERE brukar_id = ? ORDER BY ordre_dato DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['brukar_id']);
mysqli_stmt_execute($stmt);
$ordrar = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordrar - Nettbutikk</title>
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
        <h2>Mine ordrar</h2>
        
        <?php if (mysqli_num_rows($ordrar) == 0): ?>
            <p>Du har ingen ordrar enno.</p>
            <a href="index.php" class="btn btn-primary">Gå til butikken</a>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ordrenr</th>
                            <th>Dato</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Handling</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ordre = mysqli_fetch_assoc($ordrar)): ?>
                            <tr>
                                <td>#<?php echo $ordre['ordre_id']; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($ordre['ordre_dato'])); ?></td>
                                <td>kr <?php echo number_format($ordre['total_pris'], 2, ',', ' '); ?></td>
                                <td><?php echo htmlspecialchars($ordre['status']); ?></td>
                                <td>
                                    <a href="ordre_detaljar.php?ordre_id=<?php echo $ordre['ordre_id']; ?>" 
                                       class="btn btn-info btn-sm">Vis detaljar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
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
