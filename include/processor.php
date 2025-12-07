<?php
// C:\xampp\htdocs\genieacs\include\processor.php

if (!defined('GENIE_ACCESS')) { die('Akses ditolak.'); }

require_once 'config.php';

// Fungsi Request ke API GenieACS
function callGenieAPI($endpoint, $method = 'GET', $data = null) {
    $url = GENIE_URL . '/' . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, GENIE_TIMEOUT);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, GENIE_USER . ":" . GENIE_PASS);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode == 401) return ['code' => 401, 'data' => ['error' => 'Gagal Login ke GenieACS (Cek config.php)']];
    if ($curlError) return ['code' => 500, 'data' => ['error' => 'Koneksi Error: ' . $curlError]];

    return ['code' => $httpCode, 'data' => json_decode($response, true)];
}

// Fungsi Build Query Search (Case Insensitive)
function buildSearchQuery($keyword) {
    // Bersihkan input
    $cleanKey = preg_replace('/[^a-zA-Z0-9\.\-\_\:\s@]/', '', $keyword);
    
    // Gunakan $options => i agar tidak peduli huruf besar/kecil
    return [
        '$or' => [
            ['_id' => ['$regex' => $cleanKey, '$options' => 'i']],
            ['_tags' => ['$regex' => $cleanKey, '$options' => 'i']],
            ['VirtualParameters.pppoeIP' => ['$regex' => $cleanKey, '$options' => 'i']],
            ['VirtualParameters.pppoeUsername' => ['$regex' => $cleanKey, '$options' => 'i']],
            ['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.ExternalIPAddress' => ['$regex' => $cleanKey, '$options' => 'i']]
        ]
    ];
}
?>