<?php
session_start();
require_once 'config.php';

$feilmelding = '';
$suksess = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brukarnamn = mysqli_real_escape_string($conn, $_POST['brukarnamn']);
    $epost = mysqli_real_escape_string($conn, $_POST['epost']);
    $passord = $_POST['passord'];
    $bekreft_passord = $_POST['bekreft_passord'];

    // Sjekk om brukarnamn allereie eksisterer
    $sjekk = mysqli_query($conn, "SELECT * FROM brukarar WHERE brukarnamn = '$brukarnamn'");
    if (mysqli_num_rows($sjekk) > 0) {
        $feilmelding = "Brukarnamnet er allereie i bruk";
    }
    // Sjekk om epost allereie eksisterer
    else if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM brukarar WHERE epost = '$epost'")) > 0) {
        $feilmelding = "E-postadressa er allereie i bruk";
    }
    // Sjekk om passorda er like
    else if ($passord != $bekreft_passord) {
        $feilmelding = "Passorda er ikkje like";
    }
    // Sjekk passordet sin lengde
    else if (strlen($passord) < 6) {
        $feilmelding = "Passordet må vere minst 6 teikn";
    }
    else {
        $kryptert_passord = password_hash($passord, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO brukarar (brukarnamn, passord, epost) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $brukarnamn, $kryptert_passord, $epost);
        
        if (mysqli_stmt_execute($stmt)) {
            $suksess = "Registrering vellukka! Du kan no logge inn.";
        } else {
            $feilmelding = "Noko gjekk galt. Prøv igjen seinare.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrer - Nettbutikk</title>
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
                <h2>Registrer deg</h2>
                <p>Velkommen! Opprett ein konto for å handle hos oss.</p>
            </div>

            <?php if ($feilmelding): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($feilmelding); ?></div>
            <?php endif; ?>
            <?php if ($suksess): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($suksess); ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="brukarnamn" class="form-label">Brukarnamn</label>
                    <input type="text" 
                           class="form-control" 
                           id="brukarnamn" 
                           name="brukarnamn" 
                           required 
                           autocomplete="username"
                           placeholder="Vel eit brukarnamn">
                </div>

                <div class="form-group">
                    <label for="epost" class="form-label">E-post</label>
                    <input type="email" 
                           class="form-control" 
                           id="epost" 
                           name="epost" 
                           required 
                           autocomplete="email"
                           placeholder="Di e-postadresse">
                </div>

                <div class="form-group">
                    <label for="passord" class="form-label">Passord</label>
                    <input type="password" 
                           class="form-control" 
                           id="passord" 
                           name="passord" 
                           required 
                           autocomplete="new-password"
                           placeholder="Vel eit passord">
                </div>

                <div class="form-group">
                    <label for="bekreft_passord" class="form-label">Bekreft passord</label>
                    <input type="password" 
                           class="form-control" 
                           id="bekreft_passord" 
                           name="bekreft_passord" 
                           required 
                           autocomplete="new-password"
                           placeholder="Skriv passordet på nytt">
                </div>

                <button type="submit" class="auth-btn">Registrer deg</button>

                <div class="auth-divider">
                    <span>eller</span>
                </div>

                <div class="auth-links">
                    <a href="logg_inn.php" class="auth-link">Har du allereie ein konto? Logg inn her</a>
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
