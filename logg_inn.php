<?php
session_start();
require_once 'config.php';

$feilmelding = '';

// Handter innloggingsforsøk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brukarnamn = mysqli_real_escape_string($conn, $_POST['brukarnamn']);
    $passord = $_POST['passord'];
    
    // Hent brukar frå database
    $sql = "SELECT brukar_id, brukarnamn, passord, er_admin FROM brukarar WHERE brukarnamn = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $brukarnamn);
    mysqli_stmt_execute($stmt);
    $resultat = mysqli_stmt_get_result($stmt);
    
    if ($brukar = mysqli_fetch_assoc($resultat)) {
        // Verifiser passord
        if (password_verify($passord, $brukar['passord'])) {
            // Set sesjonsvariablar
            $_SESSION['brukar_id'] = $brukar['brukar_id'];
            $_SESSION['brukarnamn'] = $brukar['brukarnamn'];
            $_SESSION['er_admin'] = $brukar['er_admin'];
            
            // Omdiriger til hovudside
            header("Location: index.php");
            exit();
        } else {
            $feilmelding = "Feil passord";
        }
    } else {
        $feilmelding = "Brukarnamn finst ikkje";
    }
}
?>

<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logg inn - Nettbutikk</title>
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

    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h2>Logg inn</h2>
                <p>Velkommen tilbake! Logg inn for å handle hos oss.</p>
            </div>

            <?php if ($feilmelding): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($feilmelding); ?></div>
            <?php endif; ?>

            <form action="logg_inn.php" method="post">
                <div class="form-group">
                    <label for="brukarnamn" class="form-label">Brukarnamn</label>
                    <input type="text" 
                           class="auth-form-control" 
                           id="brukarnamn" 
                           name="brukarnamn" 
                           required 
                           autocomplete="username"
                           placeholder="Skriv inn brukarnamn">
                </div>

                <div class="form-group">
                    <label for="passord" class="form-label">Passord</label>
                    <input type="password" 
                           class="auth-form-control" 
                           id="passord" 
                           name="passord" 
                           required 
                           autocomplete="current-password"
                           placeholder="Skriv inn passord">
                </div>

                <button type="submit" class="auth-btn">Logg inn</button>

                <div class="auth-divider">
                    <span>eller</span>
                </div>

                <div class="auth-links">
                    <a href="registrer.php">Har du ikkje konto? Registrer deg her</a>
                </div>
            </form>
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
