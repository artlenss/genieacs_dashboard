<?php
// C:\xampp\htdocs\genieacs\include\config.php

// Mencegah akses langsung tanpa melalui file induk yang sah
if (!defined('GENIE_ACCESS')) {
    die('Akses langsung ditolak.');
}

// --- KONEKSI KE SERVER GENIEACS ---
define('GENIE_HOST', '172.20.200.101'); // IP Server GenieACS
define('GENIE_PORT', '7557');
define('GENIE_URL', 'http://' . GENIE_HOST . ':' . GENIE_PORT);
define('GENIE_TIMEOUT', 10);

// Credential Login ke GenieACS (NBI)
define('GENIE_USER', 'admin'); 
define('GENIE_PASS', 'admin'); 

// --- KONEKSI LOGIN DASHBOARD WEB INI ---
define('DASH_USER', 'admin');     // User untuk login web dashboard
define('DASH_PASS', '123456');    // Pass untuk login web dashboard
// KOORDINAT SERVER / KANTOR PUSAT (Titik Tengah)
define('SERVER_LAT', -7.48482);  // Ganti dengan Latitude kantor anda
define('SERVER_LNG', 110.10768); // Ganti dengan Longitude kantor anda
?>
