<?php
// C:\xampp\htdocs\genieacs\index.php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenieACS Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-size: 0.8rem; }
        .table td { vertical-align: middle; padding: 0.4rem; }
        .badge-tag { font-size: 85%; }
        .ip-link { color: #2362a5; font-weight: bold; text-decoration: none; }
        .ip-link:hover { text-decoration: underline; }
        .limit-text {
            max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary shadow mb-4 justify-content-between py-1">
    <a class="navbar-brand font-weight-bold" href="#" style="font-size: 1.2rem;"><i class="fas fa-satellite-dish"></i> GenieACS Manager</a>
    <a href="logout.php" class="btn btn-sm btn-danger shadow-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="container-fluid px-3">
    <div class="card shadow">
        <div class="card-header bg-white py-2">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari: Username PPPoE, IP, SSID, Serial Number..." onkeyup="if(event.keyCode===13) searchDevice()">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100" onclick="searchDevice()">Cari</button>
                </div><div class="col-md-2">
                    <button class="btn btn-secondary btn-sm w-100" onclick="searchDevice()">Refresh</button>
                </div>
                <div class="col-md-1">
                    <a href="map.php" class="btn btn-success btn-sm w-100" title="Lihat Peta"><i class="fas fa-map-marked-alt"></i> Peta</a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" id="deviceTable">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th width="12%">TAG</th>
                            <th width="5%">Mode</th>
                            <th width="15%">Username / SN</th>
                            <th width="10%">IP Service</th> <th width="10%">IP Mgmt</th>    <th width="12%">SSID</th>
                            <th width="5%">Client</th>
                            <th width="7%">RX (dBm)</th>
                            <th width="5%">Temp</th>
                            <th width="8%">Uptime</th>
                            <th width="1%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header bg-info text-white py-2"><h5 class="modal-title">Detail: <span id="detTitle"></span></h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row"><div class="col-md-6"><h6 class="text-primary font-weight-bold">Info Perangkat</h6><table class="table table-sm table-bordered"><tr><th width="35%">Model</th><td id="d_model">-</td></tr><tr><th>Serial Number</th><td id="d_sn">-</td></tr><tr><th>Software</th><td id="d_sw">-</td></tr><tr><th>Uptime</th><td id="d_uptime">-</td></tr><tr><th>Clients</th><td id="d_clients">-</td></tr></table><h6 class="text-primary font-weight-bold mt-3">Optik (PON)</h6><div class="row text-center"><div class="col-4"><div class="border p-2 rounded">RX<br><strong id="d_rx">-</strong></div></div><div class="col-4"><div class="border p-2 rounded">Temp<br><strong id="d_temp">-</strong></div></div><div class="col-4"><div class="border p-2 rounded">Mode<br><strong id="d_mode">-</strong></div></div></div></div><div class="col-md-6"><h6 class="text-primary font-weight-bold" id="wan_title">WAN</h6><table class="table table-sm table-bordered"><tr><th width="35%">IP Address</th><td id="d_ip" class="font-weight-bold text-success">-</td></tr><tr><th id="lbl_user_gw">Username</th><td id="d_user_gw">-</td></tr><tr><th id="lbl_pass_mask">Password</th><td id="d_pass_mask">-</td></tr><tr><th>MAC</th><td id="d_mac">-</td></tr></table><h6 class="text-primary font-weight-bold mt-3">WLAN</h6><div class="alert alert-secondary py-2"><strong>SSID:</strong> <span id="d_ssid_all">-</span><br><strong>Pass:</strong> <code id="d_wifi_pass">-</code></div></div></div></div><div class="modal-footer bg-light py-2"><a href="#" id="btn_webui" target="_blank" class="btn btn-outline-primary btn-sm">Remote Web</a><button onclick="rebootDevice()" class="btn btn-danger btn-sm">Reboot</button><button data-dismiss="modal" class="btn btn-secondary btn-sm">Tutup</button></div></div></div></div>
<div class="modal fade" id="wifiModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-warning py-2"><h5 class="modal-title">Ganti WiFi</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><input type="hidden" id="editDeviceId"><div class="form-group"><label>SSID</label><input type="text" id="editSSID" class="form-control"></div><div class="form-group"><label>Password</label><input type="text" id="editPass" class="form-control"></div></div><div class="modal-footer py-2"><button onclick="saveWifi()" class="btn btn-primary">Simpan</button></div></div></div></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const API = 'include/api.php';
    let curId = '';

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

    // --- FUNGSI SCANNING IP REKURSIF (Sapu Jagat) ---
    // Mencari IP di semua index WAN (1, 2, 3...)
    function scanAllIps(igd) {
        let result = { ip: '', type: '', user: '' };
        
        let wanDevices = igd.WANDevice || {};
        
        // Loop WANDevice (biasanya index 1)
        for (let wKey in wanDevices) {
            if(wKey === '_object') continue;
            let connDevices = wanDevices[wKey].WANConnectionDevice || {};
            
            // Loop WANConnectionDevice (index 1, 2, 3...)
            for (let cKey in connDevices) {
                if(cKey === '_object') continue;
                let conns = connDevices[cKey];

                // Cek PPPoE
                let ppps = conns.WANPPPConnection || {};
                for (let pKey in ppps) {
                    let pObj = ppps[pKey];
                    let ip = clean(pObj.ExternalIPAddress, '');
                    if (ip && ip !== '0.0.0.0' && ip !== '-') {
                        return { ip: ip, type: 'PPPoE', user: clean(pObj.Username, '') };
                    }
                }

                // Cek IP/Static/DHCP
                let ips = conns.WANIPConnection || {};
                for (let iKey in ips) {
                    let iObj = ips[iKey];
                    let ip = clean(iObj.ExternalIPAddress, '');
                    if (ip && ip !== '0.0.0.0' && ip !== '-') {
                        // Simpan tapi jangan return dulu, prioritaskan PPPoE jika ada di iterasi lain
                        if(!result.ip) {
                            result = { ip: ip, type: 'Static/DHCP', user: '' };
                        }
                    }
                }
            }
        }
        return result;
    }

    function searchDevice() {
        let k = $('#searchInput').val();
        $('#deviceTable tbody').html('<tr><td colspan="11" class="text-center">Loading...</td></tr>');

        $.get(API + '?action=search&keyword=' + k, function(res) {
            let html = '';
            if(res.status === 'error') { window.location.href='login.php'; return; }

            if(!res || res.length === 0) html = '<tr><td colspan="11" class="text-center">Data kosong</td></tr>';
            else {
                res.forEach(d => {
                    let id = d._id; 
                    let tag = d._tags ? d._tags.join(', ') : '-';
                    let vp = d.VirtualParameters || {};
                    let igd = d.InternetGatewayDevice || {};

                    // --- 1. SCANNING IP & USERNAME OTOMATIS ---
                    let scan = scanAllIps(igd);
                    let serviceIp = scan.ip; // IP Hasil Scan (PPPoE/Static)
                    
                    // Fallback IP jika scan gagal, coba ambil dari VP
                    if(!serviceIp) serviceIp = clean(vp.pppoeIP, '');
                    if(serviceIp === '0.0.0.0') serviceIp = '';

                    // Username Logic (Cleaned HTML issue)
                    let userDisplay = '-';
                    if (scan.user) {
                        userDisplay = scan.user;
                    } else {
                        // Cek VP Username
                        let vpUser = clean(vp.pppoeUsername, '');
                        if (vpUser && vpUser !== '-') userDisplay = vpUser;
                        else userDisplay = id; // Fallback ke Serial Number
                    }

                    // --- 2. IP MANAGEMENT / WAN (Source IP) ---
                    // Menggunakan d._ip (IP Source Packet) atau d._lastInform.ip
                    let mgmtIp = clean(d._ip, '');
                    if(!mgmtIp || mgmtIp==='-') mgmtIp = (d._lastInform && d._lastInform.ip) ? d._lastInform.ip : '-';

                    // --- 3. IP REMOTE LINK ---
                    // Prioritas: Service IP > Mgmt IP
                    let remoteIp = serviceIp || mgmtIp;
                    if(remoteIp === '-') remoteIp = '';

                    // --- DATA LAIN ---
                    let ssid = clean(vp.SSID, '');
                    if(!ssid || ssid==='-') ssid = clean(vp.SSID_ALL, '');
                    if(!ssid || ssid==='-') ssid = getSafe(igd, 'LANDevice.1.WLANConfiguration.1.SSID', '-');

                    let clients = clean(vp.activedevices, '0');
                    let rx = clean(vp.RXPower);
                    let temp = clean(vp.gettemp);
                    let ponMode = clean(vp.getponmode, 'Unknown');
                    let uptime = clean(vp.getdeviceuptime);
                    
                    let rxVal = parseFloat(rx);
                    let rxColor = (!isNaN(rxVal) && rxVal < -27) ? 'text-danger font-weight-bold' : 'text-success';

                    // Tombol Remote Logic
                    let btnRemote = (remoteIp) ? `<a href="http://${remoteIp}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-globe"></i></a>` : `<button class="btn btn-sm btn-secondary disabled"><i class="fas fa-globe"></i></button>`;
                    
                    // Link Text Logic
                    let linkService = (serviceIp) ? `<a href="http://${serviceIp}" target="_blank" class="ip-link small">${serviceIp}</a>` : `<span class="text-muted small">-</span>`;
                    let linkMgmt = (mgmtIp !== '-') ? `<a href="http://${mgmtIp}" target="_blank" class="ip-link small text-secondary">${mgmtIp}</a>` : `<span class="text-muted small">-</span>`;

                    html += `<tr>
                        <td><small class="limit-text" title="${tag}"><span class="badge badge-info badge-tag">${tag}</span></small></td>
                        <td>${ponMode}</td>
                        <td><span class="limit-text font-weight-bold text-dark" title="${userDisplay}">${userDisplay}</span></td>
                        <td>${linkService}</td>
                        <td>${linkMgmt}</td>
                        <td><span class="limit-text" title="${ssid}">${ssid}</span></td>
                        <td class="font-weight-bold text-primary">${clients}</td>
                        <td class="${rxColor}">${rx}</td>
                        <td>${temp}</td>
                        <td><small>${uptime}</small></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-info" onclick="det('${id}')"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-warning" onclick="wifi('${id}','${ssid}')"><i class="fas fa-key"></i></button>
                                ${btnRemote}
                            </div>
                        </td>
                    </tr>`;
                });
            }
            $('#deviceTable tbody').html(html);
        });
    }

    // Modal Detail Logic (Sederhana)
    function det(id) {
        curId = id; $('#detTitle').text(id); $('#detailModal').modal('show');
        $.get(API + '?action=get_detail&id='+id, function(d) {
            let vp = d.VirtualParameters || {};
            let igd = d.InternetGatewayDevice || {};
            let dev = d.Device || {};

            let model = clean(igd.DeviceInfo?.ModelName) || clean(dev.DeviceInfo?.ModelName, '-');
            $('#d_model').text(model);
            $('#d_sn').text(clean(vp.getSerialNumber));
            $('#d_sw').text(clean(igd.DeviceInfo?.SoftwareVersion));
            $('#d_uptime').text(clean(vp.getdeviceuptime));
            $('#d_clients').text(clean(vp.activedevices, '0'));
            
            let rx = clean(vp.RXPower);
            $('#d_rx').text(rx).css('color', parseFloat(rx) < -27 ? 'red':'green');
            $('#d_temp').text(clean(vp.gettemp)); $('#d_mode').text(clean(vp.getponmode));

            // Scan lagi untuk detail
            let scan = scanAllIps(igd);
            let ip = scan.ip || clean(vp.pppoeIP, '');
            let user = scan.user || clean(vp.pppoeUsername, '-');

            $('#d_ip').text(ip || '-');
            $('#d_user_gw').text(user);
            $('#d_pass_mask').text('******'); // Password hidden
            $('#d_mac').text('-'); 

            if(ip) $('#btn_webui').attr('href', 'http://'+ip).removeClass('disabled');
            else $('#btn_webui').addClass('disabled');

            $('#d_ssid_all').text(clean(vp.SSID_ALL));
            $('#d_wifi_pass').text(clean(vp.WlanPassword));
        });
    }
    
    function wifi(id, s) { $('#editDeviceId').val(id); $('#editSSID').val(s); $('#wifiModal').modal('show'); }
    function saveWifi() { 
        let id=$('#editDeviceId').val(), s=$('#editSSID').val(), p=$('#editPass').val();
        if(!p && !confirm('Pass kosong?')) return;
        $.post(API+'?action=update_wifi', {device_id:id, ssid:s, password:p}, ()=> { alert('Sent'); $('#wifiModal').modal('hide'); searchDevice(); });
    }
    function rebootDevice() { if(confirm('Reboot?')) $.post(API+'?action=reboot', {device_id:curId}, ()=> { alert('Sent'); $('#detailModal').modal('hide'); }); }

    $(document).ready(searchDevice);
</script>

</body>
</html>