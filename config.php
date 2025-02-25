<?php
// Databasekonfigurasjon
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'nettbutikk');

// Opprett databasetilkopling
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Tilkobling mislukkast: " . mysqli_connect_error());
}
?>
