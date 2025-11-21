<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Waktu Sholat & Arah Kiblat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
/* RESET */
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Poppins',sans-serif;
    background:#eef2f7;
    padding:20px;
    transition:0.35s;
    color:#111;
}

body.dark{
    background:#0d1b2a;
    color:white;
}

/* HEADER */
.header{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:15px;
}
.header img{
    width:50px;
    height:50px;
    border-radius:10px;
    object-fit:cover;
}
.header .title{
    font-size:20px;
    font-weight:600;
    margin-bottom:2px;
}
.header .city{
    font-size:13px;
    opacity:0.7;
}

/* CARD */
.card{
    background:white;
    padding:18px;
    border-radius:16px;
    box-shadow:0 5px 16px rgba(0,0,0,0.12);
    margin-bottom:18px;
}
body.dark .card{
    background:#1b263b;
}

/* LIST JADWAL */
.prayItem{
    display:flex;
    justify-content:space-between;
    padding:8px 0;
    border-bottom:1px solid #eee;
}
.prayItem:last-child{
    border-bottom:none;
}
.activePray{
    color:#0066ff;
    font-weight:600;
}

/* COUNTDOWN */
#countdownBox{
    padding:12px;
    border-radius:10px;
    text-align:center;
    font-weight:600;
    background:#dce7ff;
    margin-bottom:18px;
}
body.dark #countdownBox{
    background:#102035;
}

/* COMPASS */
#compassWrap{
    width:220px;
    height:220px;
    margin:0 auto;
    position:relative;
}
#compassBase{
    width:100%;height:100%;
    border-radius:50%;
    background:radial-gradient(circle,#ffffff,#dbe6ff);
    border:12px solid #f7f8ff;
    box-shadow:0 6px 18px rgba(0,0,0,0.28), inset 0 4px 8px rgba(0,0,0,0.15);
}
body.dark #compassBase{
    background:radial-gradient(circle,#1b2738,#0d141f);
    border-color:#1f2d3d;
}

#kabahIcon{
    width:44px;
    position:absolute;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    z-index:10;
}

#needle{
    width:16px;
    height:125px;
    position:absolute;
    left:50%;
    top:32px;
    transform-origin:center 85%;
    transform:translateX(-50%);
    background:linear-gradient(to bottom,#ff3b3b,#b00d0d);
    border-radius:10px;
    box-shadow:0 0 12px rgba(255,56,56,0.8);
    transition:transform 0.12s linear;
}
#needleShadow{
    width:16px;
    height:125px;
    position:absolute;
    left:50%;
    top:32px;
    transform-origin:center 85%;
    transform:translateX(-50%);
    background:rgba(0,0,0,0.25);
    border-radius:10px;
    filter:blur(4px);
}

/* CENTER DOT */
#centerDot{
    width:32px;height:32px;
    position:absolute;margin:auto;
    top:0;bottom:0;left:0;right:0;
    border-radius:50%;
    background:#2a3b55;
    border:4px solid white;
    z-index:12;
}

/* DARK MODE TOGGLE */
#toggleMode{
    position:fixed;
    right:15px;
    top:15px;
    padding:8px 12px;
    border-radius:10px;
    background:#0059ff;
    color:white;
    font-size:13px;
    cursor:pointer;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}

</style>
</head>
<body>

<!-- DARK MODE BUTTON -->
<div id="toggleMode">🌙 Mode Gelap</div>

<!-- HEADER -->
<div class="header">
    <img src="../img/kaaba.png">
    <div>
        <div class="title">Waktu Sholat & Arah Kiblat</div>
        <div class="city" id="cityName">Mendeteksi lokasi...</div>
    </div>
</div>

<!-- COUNTDOWN -->
<div id="countdownBox">Mengambil jadwal...</div>

<!-- WAKTU SHOLAT -->
<div class="card">
    <h3 style="margin-bottom:8px;">Jadwal Sholat Hari Ini</h3>
    <div id="prayTimes">Memuat...</div>
</div>

<!-- KIBLAT -->
<div class="card">
    <h3 style="margin-bottom:12px;">Arah Kiblat</h3>

    <div id="compassWrap">
        <div id="compassBase"></div>
        <img id="kabahIcon" src="../img/kaaba.png">
        <div id="needleShadow"></div>
        <div id="needle"></div>
        <div id="centerDot"></div>
    </div>

    <p id="qiblaDegree" style="text-align:center;margin-top:12px;font-size:16px;font-weight:600;">0°</p>
</div>


<script>
/* -------------------------------------
   VARIABEL GLOBAL
-------------------------------------*/
let deviceId = "dev-" + Math.random().toString(36).substring(2, 8);
let lastSendTs = 0;
let qiblaDirection = 0;
let prayerTimeData = {};
let nextPrayerName = "";
let nextPrayerTime = "";

/* -------------------------------------
   DARK MODE
-------------------------------------*/
document.getElementById("toggleMode").onclick = ()=>{
    document.body.classList.toggle("dark");
    document.getElementById("toggleMode").innerText =
        document.body.classList.contains("dark")
        ? "☀️ Mode Terang"
        : "🌙 Mode Gelap";
};

/* -------------------------------------
   HITUNG ARAH KIBLAT
-------------------------------------*/
function computeQibla(lat, lng){
    const kaLat = 21.4225 * Math.PI/180;
    const kaLng = 39.8262 * Math.PI/180;
    const uLat = lat * Math.PI/180;
    const uLng = lng * Math.PI/180;

    const dLon = kaLng - uLng;

    const y = Math.sin(dLon);
    const x = Math.cos(uLat)*Math.tan(kaLat) - Math.sin(uLat)*Math.cos(dLon);

    let b = Math.atan2(y, x);
    b = b * 180/Math.PI;
    return (b + 360) % 360;
}

/* -------------------------------------
   REVERSE CITY — SUPER STABIL
-------------------------------------*/
let lastCityTs = 0;

async function reverseCity(lat, lng) {
    if (Date.now() - lastCityTs < 5000) return;
    lastCityTs = Date.now();

    try {
        const r = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=id`);
        const j = await r.json();

        let city =
            j.city ||
            j.locality ||
            j.principalSubdivision ||
            j.localityInfo?.administrative?.[0]?.name ||
            "Tidak ditemukan";

        document.getElementById("cityName").innerText = city;

    } catch (e) {
        document.getElementById("cityName").innerText = "Gagal menemukan lokasi";
    }
}


/* -------------------------------------
   JADWAL SHOLAT
-------------------------------------*/
async function loadPrayer(lat, lng){
    try{
        const api = await fetch(`https://api.aladhan.com/v1/timings?latitude=${lat}&longitude=${lng}&method=2`);
        const data = await api.json();
        const t = data.data.timings;
        prayerTimeData = t;

        const list = ["Fajr","Dhuhr","Asr","Maghrib","Isha"];
        let html = "";
        list.forEach(n=>{
            html += `
                <div class="prayItem" id="pray-${n}">
                    <span>${n}</span>
                    <span>${t[n]}</span>
                </div>`;
        });
        document.getElementById("prayTimes").innerHTML = html;

        determineNextPrayer();

    }catch{
        document.getElementById("prayTimes").innerText = "Gagal memuat jadwal.";
    }
}

/* -------------------------------------
   CARI SHOLAT BERIKUTNYA
-------------------------------------*/
function determineNextPrayer(){
    const now = new Date();
    const list = ["Fajr","Dhuhr","Asr","Maghrib","Isha"];

    for(let name of list){
        let [h,m] = prayerTimeData[name].split(":");
        let target = new Date(now.getFullYear(), now.getMonth(), now.getDate(), h, m, 0);
        if(target > now){
            nextPrayerName = name;
            nextPrayerTime = target;
            highlight(name);
            return;
        }
    }

    /* Jika hari sudah habis → Fajr besok */
    nextPrayerName = "Fajr";
    let [h,m] = prayerTimeData["Fajr"].split(":");
    nextPrayerTime = new Date(now.getFullYear(), now.getMonth(), now.getDate()+1, h, m, 0);
    highlight("Fajr");
}

/* -------------------------------------
   HIGHLIGHT
-------------------------------------*/
function highlight(name){
    document.querySelectorAll(".prayItem")
        .forEach(e=>e.classList.remove("activePray"));

    document.getElementById("pray-"+name).classList.add("activePray");
}

/* -------------------------------------
   COUNTDOWN
-------------------------------------*/
setInterval(()=>{
    if(!nextPrayerTime) return;

    const now = new Date();
    let diff = (nextPrayerTime - now) / 1000;

    if(diff <= 0){
        determineNextPrayer();
        return;
    }

    let h = Math.floor(diff/3600);
    let m = Math.floor((diff % 3600)/60);
    let s = Math.floor(diff % 60);

    document.getElementById("countdownBox").innerText =
        `Menuju ${nextPrayerName}: ${h}j ${m}m ${s}d`;

}, 1000);

/* -------------------------------------
   BACKGROUND UPDATE (tracking halus)
-------------------------------------*/
function sendToServer(payload){
    fetch("../api/update_location.php", {
        method:"POST",
        body:new URLSearchParams(payload)
    });
}

/* -------------------------------------
   GYRO COMPASS
-------------------------------------*/
if(window.DeviceOrientationEvent){
    window.addEventListener("deviceorientation", e=>{
        let head = e.alpha;
        if(e.webkitCompassHeading) head = e.webkitCompassHeading;

        const rot = qiblaDirection - head;

        document.getElementById("needle").style.transform =
            `translateX(-50%) rotate(${rot}deg)`;

        document.getElementById("needleShadow").style.transform =
            `translateX(-50%) rotate(${rot}deg)`;
    });
}

/* -------------------------------------
   GPS – MESIN UTAMA
-------------------------------------*/
navigator.geolocation.watchPosition(async pos=>{
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;

    reverseCity(lat, lng);

    /* Hitung kiblat */
    qiblaDirection = computeQibla(lat,lng);
    document.getElementById("qiblaDegree").innerText =
        qiblaDirection.toFixed(2) + "°";

    /* Ambil jadwal sholat */
    loadPrayer(lat,lng);

    /* Kirim ke server */
    const now = Date.now();
    if(now-lastSendTs > 800){
        lastSendTs = now;
        sendToServer({
            device_id: deviceId,
            lat, lng,
            acc: pos.coords.accuracy,
            ts: now
        });
    }

}, err=>{
    alert("Izin lokasi ditolak.");
}, {enableHighAccuracy:true});

</script>
</body>
</html>
