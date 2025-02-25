<?php
session_start();
require_once 'config.php';

// Vist brukar_id eller ordre_id ikkje er definert redirect til index
if (!isset($_SESSION['brukar_id']) || !isset($_GET['ordre_id'])) {
    header("Location: index.php");
    exit();
}

$ordre_id = $_GET['ordre_id'];

// Hent ordredetaljar
$sql = "SELECT o.*, b.brukarnamn, b.epost 
        FROM ordrar o 
        JOIN brukarar b ON o.brukar_id = b.brukar_id 
        WHERE o.ordre_id = ? AND o.brukar_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $ordre_id, $_SESSION['brukar_id']);
mysqli_stmt_execute($stmt);
$ordre = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$ordre) {
    header("Location: index.php");
    exit();
}

// Hent ordreliner
$sql = "SELECT od.*, p.namn, p.pris 
        FROM ordre_detaljar od 
        JOIN produkt p ON od.produkt_id = p.produkt_id 
        WHERE od.ordre_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $ordre_id);
mysqli_stmt_execute($stmt);
$ordre_detaljar = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordre Bekrefting - Nettbutikk</title>
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Ordrebekrefting</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h4>Takk for bestillinga di!</h4>
                    <p>Ordrenummer: <?php echo $ordre_id; ?></p>
                </div>

                <div class="col-md-6" style="margin-left: 1rem">
                    <h4>Kundedetaljar</h4>
                    <p>Brukarnamn: <?php echo htmlspecialchars($ordre['brukarnamn']); ?><br>
                    E-post: <?php echo htmlspecialchars($ordre['epost']); ?><br>
                    Dato: <?php echo date('d.m.Y H:i', strtotime($ordre['ordre_dato'])); ?></p>
                </div>

                <h4 style="margin-left: 1rem">Ordredetaljar</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Antal</th>
                            <th>Pris per stk</th>
                            <th>Sum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($linje = mysqli_fetch_assoc($ordre_detaljar)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($linje['namn']); ?></td>
                                <td><?php echo $linje['antal']; ?></td>
                                <td>kr <?php echo number_format($linje['pris_per_stk'], 2, ',', ' '); ?></td>
                                <td>kr <?php echo number_format($linje['antal'] * $linje['pris_per_stk'], 2, ',', ' '); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>kr <?php echo number_format($ordre['total_pris'], 2, ',', ' '); ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary">Tilbake til butikken</a>
                    <a href="ordrar.php" class="btn btn-secondary">Sjå alle ordrar</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
