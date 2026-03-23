<?php
// Simpele begroeting op basis van tijd
$hour = date('H');
if ($hour < 12) {
    $greeting = "Goedemorgen";
} elseif ($hour < 18) {
    $greeting = "Goedemiddag";
} else {
    $greeting = "Goedenavond";
}

// ===== DATABASE CONNECTION =====
$host = "localhost";
$user = "logger";
$password = "paswoord";
$database = "temperatures";
$hours = 24;

$connectdb = mysqli_connect($host, $user, $password, $database)
    or die("Cannot reach database");

// SQL query om de laatste $hours uur aan data op te halen
$sql = "SELECT * FROM temperaturedata WHERE dateandtime >= (NOW() - INTERVAL $hours HOUR) ORDER BY dateandtime ASC";
$temperatures = mysqli_query($connectdb, $sql);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Datalogger Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* ===== CSS VARIABLES ===== */
:root {
    --bg-color: #f4f6f8;
    --card-bg: #ffffff;
    --text-color: #1f2933;
    --accent-color: #7fbfff;
    --secondary-color: #cfd8dc;
    --nav-bg: #e5eaef;
}
body.dark {
    --bg-color: #0f0f0f;
    --card-bg: #1a1a1a;
    --text-color: #f2f2f2;
    --accent-color: #8b0000;
    --secondary-color: #2a2a2a;
    --nav-bg: #000000;
}

/* ===== BASE ===== */
body { margin:0; font-family: "Segoe UI", Tahoma, sans-serif; background-color: var(--bg-color); color: var(--text-color); transition: background-color 0.3s, color 0.3s; }
a { color: inherit; text-decoration: none; }

/* ===== NAVBAR ===== */
.navbar { background-color: var(--nav-bg); padding:15px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 8px rgba(0,0,0,0.1);}
.navbar h1{font-size:20px;margin:0;font-weight:600;}
.nav-links a{margin-right:20px;font-weight:500;cursor:pointer;}
.nav-links a.active{border-bottom:2px solid var(--accent-color);padding-bottom:4px;}

/* ===== THEME TOGGLE ===== */
.theme-toggle {display:flex;align-items:center;gap:10px;font-size:14px;}
.switch{position:relative;width:46px;height:24px;}
.switch input{opacity:0;width:0;height:0;}
.slider{position:absolute;cursor:pointer;inset:0;background-color:var(--secondary-color);border-radius:24px;transition:0.4s;}
.slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background-color:var(--accent-color);border-radius:50%;transition:0.4s;}
input:checked + .slider:before{transform:translateX(22px);}

/* ===== CONTENT ===== */
.container{max-width:1100px;margin:30px auto;padding:0 20px;}
.card{background-color:var(--card-bg);border-radius:12px;padding:25px;margin-bottom:30px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.card h2{margin-top:0;color:var(--accent-color);}
.card p{line-height:1.6;}

/* ===== DASHBOARD TABLE ===== */
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{border:1px solid var(--secondary-color);padding:8px;text-align:center;}
th{background-color:var(--accent-color);color:#fff;}

/* ===== GRAPH GRID ===== */
.graph-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px;}
.graph-link{position:relative;height:240px;border-radius:14px;background-size:cover;background-position:center;border:1px solid var(--secondary-color);box-shadow:0 6px 14px rgba(0,0,0,0.15);display:flex;align-items:flex-end;transition: transform 0.25s ease, box-shadow 0.25s ease;overflow:hidden;cursor:pointer;}
.graph-link:hover{transform:translateY(-4px) scale(1.01);box-shadow:0 10px 24px rgba(0,0,0,0.3);}
.graph-link span{width:100%;padding:12px;font-weight:600;text-align:center;background:linear-gradient(to top, rgba(0,0,0,0.75), rgba(0,0,0,0.3));color:#fff;letter-spacing:0.3px;}

/* ===== ACCESSIBILITY ===== */
.graph-link:focus{outline:2px solid var(--accent-color);outline-offset:3px;}

/* ===== LIGHTBOX ===== */
.lightbox{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);justify-content:center;align-items:center;z-index:9999;}
.lightbox img{max-width:90%;max-height:90%;border-radius:10px;}
.lightbox .close{position:absolute;top:20px;right:30px;font-size:30px;color:#fff;cursor:pointer;font-weight:bold;}

/* ===== HIDE SECTIONS ===== */
.section{display:none;}
.section.active{display:block;}

/* ===== BACKGROUND IMAGES DAG/WEEK/MAAND ===== */
.graph-link.day.temp{background-image:url("Raspi16DAGtemperatuur.png");}
.graph-link.day.humidity{background-image:url("Raspi16DAGvochtigheid.png");}
.graph-link.week.temp{background-image:url("Raspi16WEEKtemperatuur.png");}
.graph-link.week.humidity{background-image:url("Raspi16WEEKvochtigheid.png");}
.graph-link.month.temp{background-image:url("Raspi16MAANDtemperatuur.png");}
.graph-link.month.humidity{background-image:url("Raspi16MAANDvochtigheid.png");}
</style>
</head>

<body>
<!-- NAVBAR -->
<div class="navbar">
<h1>📊 Datalogger L004</h1>
<div class="nav-links">
<a onclick="showSection('dashboard')" class="active">Dashboard</a>
<a onclick="showSection('day')">Dag</a>
<a onclick="showSection('week')">Week</a>
<a onclick="showSection('month')">Maand</a>
</div>
<div class="theme-toggle">
<span>Light</span>
<label class="switch">
<input type="checkbox" id="themeSwitch">
<span class="slider"></span>
</label>
<span>Dark</span>
</div>
</div>

<div class="container">

<!-- DASHBOARD -->
<div id="dashboard" class="section active">
<div class="card">
<h2><?= $greeting; ?> 👋</h2>
<p>Hieronder zie je de actuele temperatuur- en vochtigheidsdata van de afgelopen <?= $hours ?> uur.</p>

<table>
<tr><th>Date & Time</th><th>Temperature (°C)</th><th>Humidity (%)</th></tr>
<?php while($temperature = mysqli_fetch_assoc($temperatures)): ?>
<tr>
<td><?= $temperature['dateandtime']; ?></td>
<td><?= $temperature['temperature']; ?></td>
<td><?= $temperature['humidity']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

<!-- DAG -->
<div id="day" class="section">
<div class="card">
<h2>Dagoverzicht</h2>
<p>Temperatuur en luchtvochtigheid van vandaag.</p>
<div class="graph-container graph-grid">
<a class="graph-link temp day" data-img="DAGtemperatuur.png"><span>Temperatuur (dag)</span></a>
<a class="graph-link humidity day" data-img="DAGvochtigheid.png"><span>Luchtvochtigheid (dag)</span></a>
</div>
</div>
</div>

<!-- WEEK -->
<div id="week" class="section">
<div class="card">
<h2>Weekoverzicht</h2>
<p>Gemiddelden en trends van de afgelopen week.</p>
<div class="graph-container graph-grid">
<a class="graph-link temp week" data-img="WEEKtemperatuur.png"><span>Temperatuur (week)</span></a>
<a class="graph-link humidity week" data-img="WEEKvochtigheid.png"><span>Luchtvochtigheid (week)</span></a>
</div>
</div>
</div>

<!-- MAAND -->
<div id="month" class="section">
<div class="card">
<h2>Maandoverzicht</h2>
<p>Langetermijnevolutie van temperatuur en luchtvochtigheid.</p>
<div class="graph-container graph-grid">
<a class="graph-link temp month" data-img="MAANDtemperatuur.png"><span>Temperatuur (maand)</span></a>
<a class="graph-link humidity month" data-img="MAANDvochtigheid.png"><span>Luchtvochtigheid (maand)</span></a>
</div>
</div>
</div>

</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
<span class="close" onclick="closeLightbox()">×</span>
<img src="" id="lightbox-img">
</div>

<footer>© <?= date('Y'); ?> Datalogger Lokaal – Automatisch gegenereerde meetgegevens</footer>

<script>
// NAV SECTIONS
function showSection(id){
document.querySelectorAll('.section').forEach(sec=>sec.classList.remove('active'));
document.getElementById(id).classList.add('active');
document.querySelectorAll('.nav-links a').forEach(link=>link.classList.remove('active'));
event.target.classList.add('active');
}

// THEME SWITCH
const switcher=document.getElementById('themeSwitch');
if(localStorage.getItem('theme')==='dark'){document.body.classList.add('dark');switcher.checked=true;}
switcher.addEventListener('change',()=>{document.body.classList.toggle('dark');localStorage.setItem('theme',document.body.classList.contains('dark')?'dark':'light');});

// LIGHTBOX
const lightbox=document.getElementById('lightbox');
const lightboxImg=document.getElementById('lightbox-img');
document.querySelectorAll('.graph-link').forEach(link=>{
link.addEventListener('click',()=>{
lightboxImg.src=link.getAttribute('data-img');
lightbox.style.display="flex";
});
});
function closeLightbox(){lightbox.style.display="none";lightboxImg.src="";}
</script>

</body>
</html>
