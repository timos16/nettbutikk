<?php
session_start();
require_once 'config.php';

// Sjekk om brukaren er innlogga (skjekker om $_SESSION er definert og om bruker er definert som admin i database)
$er_innlogga = isset($_SESSION['brukar_id']);
$er_admin = isset($_SESSION['er_admin']) && $_SESSION['er_admin'];

// Hent alle produkt frå databasen
$sql = "SELECT * FROM produkt ORDER BY namn ASC";
$resultat = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nettbutikk</title>
    <link href="/css/style.css" rel="stylesheet">
</head><body>
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

    <!-- Tittel og undertekst -->
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center">Velkommen til vår nettbutikk</h1>
                <p class="text-center lead">Utforsk vårt utval av kvalitetsprodukt</p>
            </div>
        </div>
        
        <!-- Produkt-tabell -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($produkt = mysqli_fetch_assoc($resultat)): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($produkt['bilde_url']): ?>
                            <img src="produkt_bilder/<?php echo htmlspecialchars($produkt['bilde_url']); ?>" 
                                 class="card-img-top product-img" 
                                 alt="<?php echo htmlspecialchars($produkt['namn']); ?>">
                        <?php else: ?>
                            <div class="card-img-top product-img bg-secondary d-flex align-items-center justify-content-center">
                                <span class="text-white">Inga bilete</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($produkt['namn']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($produkt['beskriving']); ?></p>
                            <div class="mt-auto">
                                <p class="card-text">
                                    <strong>Pris:</strong> kr <?php echo number_format($produkt['pris'], 2, ',', ' '); ?><br>
                                    <strong>På lager:</strong> <?php echo $produkt['lager_antal']; ?>
                                </p>
                                
                                <?php if ($er_innlogga): ?>
                                    <form action="legg_til_handlekorg.php" method="post">
                                        <input type="hidden" name="produkt_id" value="<?php echo $produkt['produkt_id']; ?>">
                                        <div class="input-group mb-3">
                                            <input type="number" name="antal" value="1" min="1" 
                                                   max="<?php echo $produkt['lager_antal']; ?>" 
                                                   class="form-control">
                                            <button type="submit" class="btn btn-primary">Legg i handlekorg</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <a href="logg_inn.php" class="btn btn-info w-100">Logg inn for å handle</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
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
