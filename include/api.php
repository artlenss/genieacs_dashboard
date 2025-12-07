<?php
// C:\xampp\htdocs\genieacs\include\api.php

session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

define('GENIE_ACCESS', true);
require_once 'processor.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

// --- SEARCH ---
if ($action == 'search') {
    $keyword = $_GET['keyword'] ?? '';
    $queryStr = urlencode(json_encode(buildSearchQuery($keyword)));

    // UPDATE PROJECTION:
    // Kita ambil 'InternetGatewayDevice.WANDevice' SECARA UTUH (Tanpa .1.WANConnection...)
    // Agar kita bisa scan semua interface (internet, iptv, voip) di Javascript.
    $projection = implode(',', [
        '_id',
        '_tags',
        '_lastInform',
        '_ip', // IP Management
        'VirtualParameters', 
        'InternetGatewayDevice.DeviceInfo.ModelName',
        'InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.MACAddress',
        'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
        'InternetGatewayDevice.WANDevice' // <--- AMBIL SATU POHON WAN UTUH
    ]);
    
    $result = callGenieAPI("devices/?query=$queryStr&projection=$projection");
    echo json_encode($result['data'] ?? []);
    exit;
}

// --- DETAIL ---
if ($action == 'get_detail') {
    $id = $_GET['id'] ?? '';
    $projection = "_id,_tags,_lastInform,_ip,VirtualParameters,InternetGatewayDevice,Device"; 
    $result = callGenieAPI("devices/?query=%7B%22_id%22%3A%22$id%22%7D&projection=$projection");
    echo json_encode(!empty($result['data'][0]) ? $result['data'][0] : ['error' => 'Not Found']);
    exit;
}

// --- UPDATE WIFI & REBOOT (SAMA) ---
if ($action == 'update_wifi') {
    $id = $_POST['device_id'];
    $ssid = $_POST['ssid'];
    $pass = $_POST['password'];
    $tasks = [
        'name' => 'setParameterValues',
        'parameterValues' => [
            ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID', $ssid],
            ['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey', $pass]
        ]
    ];
    $result = callGenieAPI("devices/$id/tasks?connection_request", 'POST', $tasks);
    echo json_encode(['status' => ($result['code'] == 200) ? 'success' : 'error']);
    exit;
}
if ($action == 'reboot') {
    $id = $_POST['device_id'];
    $tasks = ['name' => 'reboot', 'parameterValues' => []];
    $result = callGenieAPI("devices/$id/tasks?connection_request", 'POST', $tasks);
    echo json_encode(['status' => ($result['code'] == 200) ? 'success' : 'error']);
    exit;
}
?>