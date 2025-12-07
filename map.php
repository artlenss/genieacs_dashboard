<?php
// C:\xampp\htdocs\genieacs\map.php

session_start();

// 1. Cek Login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Kunci Akses Config
define('GENIE_ACCESS', true);
require_once 'include/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Map Monitoring</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        body { margin: 0; padding: 0; overflow: hidden; font-family: sans-serif; }
        #map { height: 100vh; width: 100vw; z-index: 1; background: #222; }
        
        .back-btn {
            position: absolute; top: 20px; left: 60px; z-index: 999;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            border: 1px solid #444;
        }

        .info-box {
            position: absolute; bottom: 30px; left: 20px; z-index: 999;
            background: rgba(30, 30, 30, 0.9);
            color: #fff; padding: 15px; width: 220px;
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.6);
            border: 1px solid #444;
        }

        /* --- STYLE POPUP --- */
        .leaflet-popup-content-wrapper {
            background: rgba(15, 23, 42, 0.95);
            color: #fff; border-radius: 8px; border: 1px solid #444;
            padding: 0; overflow: hidden;
        }
        .leaflet-popup-content { margin: 0; min-width: 280px !important; }
        .leaflet-popup-tip { background: rgba(15, 23, 42, 0.95); }
        .leaflet-container a.leaflet-popup-close-button { color: #fff; }

        .mini-header { background: #0d6efd; padding: 8px 12px; font-weight: bold; font-size: 0.9rem; border-bottom: 1px solid #444; }
        .mini-body { padding: 10px; font-size: 0.85rem; }
        .mini-table { width: 100%; }
        .mini-table td { padding: 3px 0; vertical-align: top; }
        .mini-label { color: #aaa; width: 35%; }
        .mini-val { font-weight: 500; }
        .mini-footer { padding: 8px; background: rgba(0,0,0,0.2); text-align: center; }

        /* --- ANIMASI GARIS --- */
        .flow-line {
            stroke-dasharray: 8, 8;
            animation: flow 1s linear infinite;
        }
        @keyframes flow {
            from { stroke-dashoffset: 16; }
            to { stroke-dashoffset: 0; }
        }

        /* --- ICON STYLES (UPDATE WARNA MERAH) --- */
        .server-icon { font-size: 35px; color: #00bcd4; filter: drop-shadow(0 0 5px #00bcd4); }
        
        .ont-icon-online { 
            font-size: 20px; 
            color: #00e676; /* Hijau Terang */
            filter: drop-shadow(0 0 4px #00e676); 
        }
        
        .ont-icon-offline { 
            font-size: 22px; /* Lebih besar dikit biar kelihatan */
            color: #ff0000;  /* MERAH TOTAL */
            filter: drop-shadow(0 0 5px #ff0000); /* Efek Cahaya Merah */
        }
    </style>
</head>
<body>

<a href="index.php" class="btn btn-dark back-btn font-weight-bold"><i class="fas fa-arrow-left"></i> Dashboard</a>

<div class="info-box">
    <h6 class="font-weight-bold mb-3 text-info"><i class="fas fa-network-wired"></i> Status Jaringan</h6>
    <div class="small mb-2"><i class="fas fa-wifi text-success" style="color: #00e676;"></i> Online (< 5 menit)</div>
    <div class="small mb-2"><i class="fas fa-power-off text-danger" style="color: #ff0000;"></i> Offline / Mati</div>
    <div class="small"><i class="fas fa-server text-info" style="color: #00bcd4;"></i> Server Pusat</div>
    <hr class="my-2" style="border-top: 1px solid #555;">
    <div class="small">Terpetakan: <strong id="count-map" class="text-warning">0</strong> Device</div>
</div>

<div id="map"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- 1. KOORDINAT SERVER ---
    const serverLoc = [<?= defined('SERVER_LAT') ? SERVER_LAT : -6.175392 ?>, <?= defined('SERVER_LNG') ? SERVER_LNG : 106.827153 ?>];

    // --- 2. SETUP PETA ---
    var darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OSM &copy; CARTO', subdomains: 'abcd', maxZoom: 19
    });
    var streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OSM', maxZoom: 19
    });

    var map = L.map('map', {
        center: serverLoc, zoom: 14, layers: [darkLayer] 
    });

    L.control.layers({ "Mode Gelap (NOC)": darkLayer, "Mode Terang": streetLayer }).addTo(map);

    // Marker Server
    var serverIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<i class='fas fa-server server-icon'></i>",
        iconSize: [30, 42], iconAnchor: [15, 42]
    });
    L.marker(serverLoc, {icon: serverIcon}).addTo(map).bindPopup("<div class='text-dark p-2'><b>SERVER UTAMA</b><br>Pusat Jaringan</div>").openPopup();


    // --- 3. HELPER FUNCTIONS ---
    function clean(d, def = '-') {
        if (d === null || d === undefined || d === '') return def;
        if (typeof d === 'object') {
            if (d._value !== undefined && d._value !== null) return d._value;
            if (d.value !== undefined && d.value !== null) return d.value;
            return def;
        }
        return d;
    }

    function getSafe(obj, path, def = '-') {
        if (!obj) return def;
        let val = path.split('.').reduce((o, p) => (o ? o[p] : undefined), obj);
        return clean(val, def);
    }

    function findRemoteIp(d, vp, igd) {
        let ip = clean(vp.pppoeIP, '');
        if(!ip || ip==='-') ip = getSafe(igd, 'WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress', '');
        if(ip && ip !== '0.0.0.0') return ip;

        ip = getSafe(igd, 'WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.ExternalIPAddress', '');
        if(ip && ip !== '0.0.0.0' && ip !== '-') return ip;

        if (d._lastInform && d._lastInform.ip) return d._lastInform.ip;
        if (d._ip && d._ip !== '-') return d._ip;
        return '-';
    }


    // --- 4. LOAD DATA & RENDER ---
    function loadMapData() {
        $.get('include/api.php?action=search&keyword=', function(res) {
            if(!res || res.length === 0) return;
            let count = 0;
            
            res.forEach(d => {
                let gpsTag = null;
                if(d._tags && Array.isArray(d._tags)) {
                    gpsTag = d._tags.find(t => t.toUpperCase().replace(/\s/g, '').startsWith('GPS:'));
                }

                if(gpsTag) {
                    try {
                        let cleanTag = gpsTag.split(':')[1].replace(/\s/g, ''); 
                        let coords = cleanTag.split(',');
                        let lat = parseFloat(coords[0]);
                        let lng = parseFloat(coords[1]);

                        if(!isNaN(lat) && !isNaN(lng)) {
                            count++;
                            let deviceLoc = [lat, lng];
                            
                            // Hitung Status
                            let lastInformTime = d._lastInform ? new Date(d._lastInform).getTime() : 0;
                            let now = new Date().getTime();
                            let diffMinutes = (now - lastInformTime) / 1000 / 60;
                            let isOnline = diffMinutes < 5; // Batas Online 5 Menit

                            // --- LOGIKA ICON & WARNA ---
                            let iconHtml = isOnline ? 
                                "<i class='fas fa-wifi ont-icon-online'></i>" : // Icon Hijau
                                "<i class='fas fa-power-off ont-icon-offline'></i>"; // Icon Merah Power
                            
                            let lineColor = isOnline ? '#00e676' : '#ff0000'; // Garis Hijau / Merah

                            let deviceIcon = L.divIcon({
                                className: 'custom-div-icon', html: iconHtml,
                                iconSize: [20, 20], iconAnchor: [10, 10]
                            });

                            let marker = L.marker(deviceLoc, {icon: deviceIcon}).addTo(map);

                            // --- POPUP DINAMIS ---
                            marker.on('click', function() {
                                marker.unbindPopup(); // Hapus popup lama agar refresh
                                marker.bindPopup(`
                                    <div style="padding:10px; color:#333; text-align:center;">
                                        <div class="spinner-border text-primary spinner-border-sm"></div> Memuat Data...
                                    </div>
                                `).openPopup();

                                $.get('include/api.php?action=get_detail&id=' + d._id, function(detail) {
                                    if(detail.error) { 
                                        marker.getPopup().setContent("<div class='p-2 text-danger'>Gagal memuat data</div>"); 
                                        return; 
                                    }
                                    
                                    let vp = detail.VirtualParameters || {};
                                    let igd = detail.InternetGatewayDevice || detail.Device || {};
                                    let d_raw = detail; 

                                    // Data Processing
                                    let user = clean(vp.pppoeUsername, '');
                                    if(!user || user==='-') user = getSafe(igd, 'WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username', '');
                                    if(!user || user==='-') user = detail._id;

                                    let ssid = clean(vp.SSID, '') || clean(vp.SSID_ALL, '') || getSafe(igd, 'LANDevice.1.WLANConfiguration.1.SSID', '-');
                                    
                                    let rx = clean(vp.RXPower);
                                    let rxVal = parseFloat(rx);
                                    let rxColor = (!isNaN(rxVal) && rxVal < -27) ? '#ff4d4d' : '#00e676'; 

                                    let temp = clean(vp.gettemp);
                                    let clients = clean(vp.activedevices, '0');
                                    let uptime = clean(vp.getdeviceuptime);

                                    // IP & Remote Link
                                    let ip = findRemoteIp(d_raw, vp, igd);
                                    let remoteLink = (ip !== '-' && ip !== '0.0.0.0') ? 
                                        `<a href="http://${ip}" target="_blank" class="btn btn-sm btn-primary w-100 font-weight-bold">Remote Web</a>` : 
                                        `<button class="btn btn-sm btn-secondary w-100 disabled">IP Tidak Ada</button>`;

                                    let ipDisplay = (ip !== '-' && ip !== '0.0.0.0') ? `<a href="http://${ip}" target="_blank" style="color:#4dabf7;font-weight:bold">${ip}</a>` : '-';

                                    // --- HTML POPUP ---
                                    let statusBadge = isOnline ? 
                                        '<span class="badge badge-success">ONLINE</span>' : 
                                        '<span class="badge badge-danger">OFFLINE</span>';

                                    let content = `
                                        <div class="mini-header">
                                            <i class="fas fa-user-circle"></i> ${user}
                                        </div>
                                        <div class="mini-body">
                                            <table class="mini-table">
                                                <tr>
                                                    <td class="mini-label">Status</td>
                                                    <td class="mini-val">${statusBadge} <small class="text-muted">(${Math.floor(diffMinutes)}m ago)</small></td>
                                                </tr>
                                                <tr>
                                                    <td class="mini-label">IP Addr</td>
                                                    <td class="mini-val">${ipDisplay}</td>
                                                </tr>
                                                <tr>
                                                    <td class="mini-label">Signal</td>
                                                    <td class="mini-val" style="color:${rxColor}">${rx} dBm</td>
                                                </tr>
                                                <tr>
                                                    <td class="mini-label">Temp</td>
                                                    <td class="mini-val">${temp} °C</td>
                                                </tr>
                                                <tr>
                                                    <td class="mini-label">WiFi</td>
                                                    <td class="mini-val text-warning">${ssid}</td>
                                                </tr>
                                                <tr>
                                                    <td class="mini-label">Clients</td>
                                                    <td class="mini-val">${clients} Devices</td>
                                                </tr>
                                                <tr>
                                                    <td class="mini-label">Uptime</td>
                                                    <td class="mini-val small">${uptime}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="mini-footer">
                                            ${remoteLink}
                                        </div>
                                    `;
                                    
                                    if(marker.getPopup()) {
                                        marker.getPopup().setContent(content);
                                    }
                                });
                            });

                            // Garis Koneksi (Warna Dinamis)
                            L.polyline([serverLoc, deviceLoc], {
                                color: lineColor, // Merah jika offline
                                weight: isOnline ? 2 : 1, 
                                opacity: 0.7,
                                className: isOnline ? 'flow-line' : '' // Animasi hanya jika online
                            }).addTo(map);
                        }
                    } catch (e) { console.error("GPS Parse Error", e); }
                }
            });
            $('#count-map').text(count);
        });
    }

    $(document).ready(loadMapData);
</script>

</body>
</html>