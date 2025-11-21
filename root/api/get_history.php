<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Realtime Auto-Tracking</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial; padding: 20px; }
    #status { font-weight: bold; margin-top: 10px; }
    #log { background:#f0f0f0; padding:10px; border-radius:6px; height:200px; overflow:auto; }
    .warn { color:#b00; }
    .ok { color:#060; }
  </style>
</head>
<body>

<h2>Realtime Auto Tracker</h2>
<p>Halaman ini otomatis mengirim lokasi Anda begitu dibuka.</p>
<p class="warn">Jangan tutup tab jika ingin tetap mengirim lokasi.</p>

<div id="status">Menunggu izin lokasi…</div>
<pre id="log"></pre>

<script>
const statusEl = document.getElementById("status");
const logEl = document.getElementById("log");

let watchId = null;
let lastSendTs = 0;
let deviceId = "dev-" + Math.random().toString(36).substring(2,8);

// Log helper
function log(msg){
  logEl.textContent = new Date().toLocaleTimeString()+" → "+msg+"\n"+logEl.textContent;
}

// Auto request permission & run tracking
(async function startAutoTracking(){

  // 1. Minta izin langsung saat halaman dibuka
  try {
    await new Promise((resolve, reject) =>
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true, timeout: 8000
      })
    );
  } catch(e) {
    statusEl.textContent = "Izin lokasi ditolak atau gagal: " + e.message;
    statusEl.className = "warn";
    log("ERROR: " + e.message);
    return;
  }

  statusEl.textContent = "Tracking berjalan…";
  statusEl.className = "ok";
  log("Izin diberikan, mulai tracking");

  // 2. Mulai watchPosition (realtime)
  watchId = navigator.geolocation.watchPosition(pos => {
      const now = Date.now();

      // kirim max 1x tiap 700ms
      if(now - lastSendTs < 700) return;
      lastSendTs = now;

      sendToServer({
        device_id: deviceId,
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        acc: pos.coords.accuracy,
        ts: Date.now()
      });

      log(`Lokasi: ${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)} (acc: ${pos.coords.accuracy})`);

  }, err => {
      statusEl.textContent = "Error tracking: " + err.message;
      statusEl.className = "warn";
      log("ERROR: " + err.message);
  }, { enableHighAccuracy:true, maximumAge:1000, timeout:10000 });

})();

// 3. Kirim data ke server
function sendToServer(data){
  fetch("../api/update_location.php", {
    method: "POST",
    body: new URLSearchParams(data)
  }).catch(err => log("Send failed: " + err.message));
}

// 4. Jika tab ditutup → kirim offline
window.addEventListener("pagehide", () => {
  if(deviceId){
    navigator.sendBeacon(
      "../api/update_location.php",
      new URLSearchParams({ device_id: deviceId, status:"offline" })
    );
  }
});
</script>

</body>
</html>
