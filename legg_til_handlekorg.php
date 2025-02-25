<?php
session_start();
require_once 'config.php';

// Sjekk om brukaren er innlogga (skjekker om $_SESSION er definert)
if (!isset($_SESSION['brukar_id'])) {
    header("Location: logg_inn.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['produkt_id']) && isset($_POST['antal'])) {
    $produkt_id = $_POST['produkt_id'];
    $antal = (int)$_POST['antal'];

    // Sjekk om produktet finst og har nok på lager
    $sql = "SELECT lager_antal FROM produkt WHERE produkt_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $produkt_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($produkt = mysqli_fetch_assoc($result)) {
        if ($antal > 0 && $antal <= $produkt['lager_antal']) {
            if (!isset($_SESSION['handlekorg'])) {
                $_SESSION['handlekorg'] = array();
            }
            
            // Oppdater antal om produktet allereie er i handlekorga
            if (isset($_SESSION['handlekorg'][$produkt_id])) {
                $nytt_antal = $_SESSION['handlekorg'][$produkt_id] + $antal;
                if ($nytt_antal <= $produkt['lager_antal']) {
                    $_SESSION['handlekorg'][$produkt_id] = $nytt_antal;
                }
            } else {
                $_SESSION['handlekorg'][$produkt_id] = $antal;
            }
        }
    }
}

header("Location: handlekorg.php");
exit();
?>
