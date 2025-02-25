<?php
session_start();
require_once 'config.php';

// Sjekk om brukaren er innlogga (skjekker om $_SESSION er definert)
if (!isset($_SESSION['brukar_id']) || empty($_SESSION['handlekorg'])) {
    header("Location: index.php");
    exit();
}

$feilmelding = '';
$ordre_id = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $total_pris = 0;
        $ordre_produkt = array();

        // Sjekk lager og berekn total pris
        foreach ($_SESSION['handlekorg'] as $produkt_id => $antal) {
            $sql = "SELECT * FROM produkt WHERE produkt_id = ? FOR UPDATE";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $produkt_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $produkt = mysqli_fetch_assoc($result);

            if ($antal > $produkt['lager_antal']) {
                throw new Exception("Beklagar, det er ikkje nok {$produkt['namn']} på lager.");
            }

            $total_pris += $produkt['pris'] * $antal;
            $ordre_produkt[] = array(
                'produkt_id' => $produkt_id,
                'antal' => $antal,
                'pris_per_stk' => $produkt['pris']
            );
        }

        // Opprett ordre
        $sql = "INSERT INTO ordrar (brukar_id, total_pris) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "id", $_SESSION['brukar_id'], $total_pris);
        mysqli_stmt_execute($stmt);
        $ordre_id = mysqli_insert_id($conn);

        // Legg til ordre-detaljar og oppdater lager
        foreach ($ordre_produkt as $produkt) {
            // Legg til ordre-detaljar
            $sql = "INSERT INTO ordre_detaljar (ordre_id, produkt_id, antal, pris_per_stk) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiid", $ordre_id, $produkt['produkt_id'], $produkt['antal'], $produkt['pris_per_stk']);
            mysqli_stmt_execute($stmt);

            // Oppdater lager
            $sql = "UPDATE produkt SET lager_antal = lager_antal - ? WHERE produkt_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $produkt['antal'], $produkt['produkt_id']);
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($conn);
        unset($_SESSION['handlekorg']);
        header("Location: ordre_bekrefting.php?ordre_id=" . $ordre_id);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $feilmelding = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestill - Nettbutikk</title>
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
        <h2>Bestilling</h2>
        
        <?php if ($feilmelding): ?>
            <div class="alert alert-danger"><?php echo $feilmelding; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Ordresamandrag</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produkt</th>
                                    <th>Antal</th>
                                    <th>Pris</th>
                                    <th>Sum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
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
                                            <td><?php echo $antal; ?></td>
                                            <td>kr <?php echo number_format($produkt['pris'], 2, ',', ' '); ?></td>
                                            <td>kr <?php echo number_format($sum, 2, ',', ' '); ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>kr <?php echo number_format($total, 2, ',', ' '); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="post" action="">
                    <button type="submit" class="btn btn-success btn-lg">Bekreft bestilling</button>
                    <a href="handlekorg.php" class="btn btn-secondary btn-lg">Tilbake til handlekorg</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
