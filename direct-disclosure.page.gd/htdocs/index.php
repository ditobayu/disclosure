<?php
require 'config.php';
if (!isset($_SESSION['teacher_id'])) header('Location: login.php');

// --- Dropdown Kelas (urut abjad) ---
$stmt = $pdo->query("SELECT * FROM classes ORDER BY name ASC");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Ambil semua mapel dari database ---
$stmt = $pdo->query("SELECT * FROM mapel ORDER BY nama_mapel ASC");
$mapels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Default kelas & mapel ---
$default_class = null;
foreach($classes as $c){
    if (strtoupper($c['name']) === "7A") {
        $default_class = $c;
        break;
    }
}
if (!$default_class && count($classes)) $default_class = $classes[0];

$selected_class_id = intval($_GET['class_id'] ?? $default_class['id']);
$selected_mapel_id = intval($_GET['mapel_id'] ?? 0);

// --- Jika mapel_id tidak ada, ambil mapel pertama sebagai default ---
if (!$selected_mapel_id && count($mapels)) $selected_mapel_id = $mapels[0]['id'];

// Ambil nama mapel terpilih
$selected_mapel_name = "";
foreach($mapels as $m){
    if ($m['id'] == $selected_mapel_id) {
        $selected_mapel_name = $m['nama_mapel'];
        break;
    }
}

// --- Ambil kelas terpilih ---
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$selected_class_id]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$class) die("Kelas tidak ditemukan.");

// --- Ambil siswa ---
$stmt = $pdo->prepare("SELECT * FROM students WHERE class_id = ? ORDER BY name ASC");
$stmt->execute([$class['id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Ambil nilai existing (sesuai mapel_id) ---
$scores_stmt = $pdo->prepare("SELECT * FROM scores WHERE student_id = ? AND mapel_id = ?");
$mapel_descriptions = [
    1 => [ // Bahasa Indonesia
        'sumatif_a' => 'memahami isi teks bacaan',
        'sumatif_b' => 'menulis teks dengan struktur yang benar',
        'sumatif_c' => 'mengidentifikasi unsur kebahasaan',
        'sumatif_d' => 'menyusun paragraf dengan ejaan yang tepat',
        'sumatif_e' => 'menyampaikan pendapat secara lisan dengan jelas'
    ],
    2 => [ // Bahasa Inggris
        'sumatif_a' => 'memahami kosakata dasar',
        'sumatif_b' => 'mengenali struktur kalimat sederhana',
        'sumatif_c' => 'menulis kalimat dengan grammar tepat',
        'sumatif_d' => 'memahami teks bacaan pendek',
        'sumatif_e' => 'berkomunikasi dalam situasi sederhana'
    ],
    3 => [ // Informatika
        'sumatif_a' => 'memahami konsep dasar algoritma dan logika',
        'sumatif_b' => 'menggunakan perangkat lunak pengolah kata dan angka',
        'sumatif_c' => 'membuat program sederhana sesuai perintah',
        'sumatif_d' => 'menerapkan etika dan keamanan digital',
        'sumatif_e' => 'memecahkan masalah menggunakan teknologi informasi'
    ],
    4 => [ // IPA
        'sumatif_a' => 'memahami konsep dasar gaya, energi, dan materi',
        'sumatif_b' => 'melakukan pengamatan dan percobaan sederhana',
        'sumatif_c' => 'menganalisis hubungan antar makhluk hidup dan lingkungannya',
        'sumatif_d' => 'menjelaskan perubahan wujud benda dan prosesnya',
        'sumatif_e' => 'menerapkan konsep ilmiah dalam kehidupan sehari-hari'
    ],
    5 => [ // IPS
        'sumatif_a' => 'memahami interaksi sosial dalam kehidupan masyarakat',
        'sumatif_b' => 'menjelaskan peristiwa sejarah dan pengaruhnya',
        'sumatif_c' => 'menganalisis kegiatan ekonomi masyarakat',
        'sumatif_d' => 'mengidentifikasi kondisi geografis wilayah Indonesia',
        'sumatif_e' => 'menunjukkan sikap tanggung jawab sebagai warga negara'
    ],
    6 => [ // Matematika
        'sumatif_a' => 'memahami konsep bangun ruang',
        'sumatif_b' => 'menerapkan rumus keliling dan luas',
        'sumatif_c' => 'menginterpretasikan data statistika sederhana',
        'sumatif_d' => 'menganalisis hubungan sebab akibat dalam persamaan',
        'sumatif_e' => 'menerapkan konsep persamaan linear'
    ],
    7 => [ // PJOK
        'sumatif_a' => 'mempraktikkan gerak dasar dalam permainan olahraga',
        'sumatif_b' => 'menunjukkan sikap sportivitas dalam kegiatan fisik',
        'sumatif_c' => 'memahami manfaat aktivitas fisik bagi kesehatan',
        'sumatif_d' => 'menjaga kebugaran tubuh melalui olahraga teratur',
        'sumatif_e' => 'menerapkan keselamatan dan kesehatan dalam berolahraga'
    ],
    8 => [ // PPKN
        'sumatif_a' => 'memahami nilai-nilai Pancasila dalam kehidupan sehari-hari',
        'sumatif_b' => 'menunjukkan perilaku sesuai norma dan hukum',
        'sumatif_c' => 'menghargai keberagaman dalam masyarakat',
        'sumatif_d' => 'menjelaskan hak dan kewajiban sebagai warga negara',
        'sumatif_e' => 'berpartisipasi aktif dalam kegiatan sekolah dan masyarakat'
    ],
    9 => [ // Prakarya
        'sumatif_a' => 'memahami proses pembuatan produk kerajinan sederhana',
        'sumatif_b' => 'menggunakan alat dan bahan dengan benar',
        'sumatif_c' => 'menerapkan kreativitas dalam pembuatan produk',
        'sumatif_d' => 'menunjukkan sikap teliti dan tanggung jawab dalam berkarya',
        'sumatif_e' => 'menyajikan hasil karya dengan percaya diri'
    ],
    10 => [ // Seni Budaya
        'sumatif_a' => 'mengapresiasi karya seni musik, tari, rupa, dan teater',
        'sumatif_b' => 'mengekspresikan diri melalui karya seni',
        'sumatif_c' => 'memahami unsur dan prinsip dalam berkarya seni',
        'sumatif_d' => 'menghargai keberagaman budaya daerah dan nasional',
        'sumatif_e' => 'menampilkan hasil karya seni dengan percaya diri'
    ]
];


?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - <?=$class['name']?> (<?=$selected_mapel_name?>)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    table th, table td { text-align:center; vertical-align:middle; }
    .score-input { width:70px; }
    .auto-cell { font-weight:600; color:#0d6efd; }
    .desc-cell { text-align:left; font-size:0.9rem; }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm py-3">
  <div class="container">
    <span class="navbar-brand">📊 Dashboard Penilaian</span>
    <div>
      <span class="me-3 text-white">Hai, <?=htmlspecialchars($_SESSION['teacher_name'])?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <form method="get" class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Pilih Kelas</label>
      <select name="class_id" class="form-select" onchange="this.form.submit()">
        <?php foreach($classes as $c): ?>
          <option value="<?=$c['id']?>" <?=$c['id']==$selected_class_id?"selected":""?>><?=$c['name']?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Pilih Mapel</label>
      <select name="mapel_id" class="form-select" onchange="this.form.submit()">
        <?php foreach($mapels as $m): ?>
          <option value="<?=$m['id']?>" <?=$m['id']==$selected_mapel_id?"selected":""?>><?=$m['nama_mapel']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <h5>Input Nilai - <?=$class['name']?> (<?=$selected_mapel_name?>)</h5>
  <form id="allScoresForm">
    <input type="hidden" name="mapel_id" value="<?=$selected_mapel_id?>">
    <table class="table table-bordered table-sm bg-white">
      <thead class="table-light">
        <tr>
          <th>No</th><th>Nama</th>
          <th>Sum A</th><th>Sum B</th><th>Sum C</th><th>Sum D</th><th>Sum E</th>
          <th>ASTS</th><th>ASAS</th>
          <th>NA S</th><th>NA AS</th><th>Raport</th>
          <th>Deskripsi</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($students as $s): 
          $scores_stmt->execute([$s['id'], $selected_mapel_id]);
          $sc = $scores_stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <tr data-student="<?=$s['id']?>">
          <td><?=$i++?></td>
          <td class="text-start"><?=htmlspecialchars($s['name'])?></td>
          <?php for($k=1;$k<=5;$k++): 
            $col = "sumatif_".chr(96+$k); 
          ?>
          <td><input type="number" class="form-control score-input" name="<?=$col?>[<?=$s['id']?>]" value="<?=htmlspecialchars($sc[$col] ?? '')?>"></td>
          <?php endfor; ?>
          <td><input type="number" class="form-control score-input" name="asts[<?=$s['id']?>]" value="<?=htmlspecialchars($sc['asts'] ?? '')?>"></td>
          <td><input type="number" class="form-control score-input" name="asas[<?=$s['id']?>]" value="<?=htmlspecialchars($sc['asas'] ?? '')?>"></td>
          <td class="auto-cell na-s">-</td>
          <td class="auto-cell na-as">-</td>
          <td class="auto-cell raport">-</td>
          <td class="desc-cell">-</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="text-end">
      <button type="button" id="saveAllBtn" class="btn btn-success">💾 Simpan Semua</button>
    </div>
  </form>
</div>

<script>
// --- Deskripsi tiap mapel (sesuai mapel_id) ---
const mapel_descriptions = <?= json_encode($mapel_descriptions) ?>;

// mapping deskripsi per row
function getDesc(label, score, mapel_id) {
  const topics = mapel_descriptions[mapel_id] || {};
  const topic = topics[label] || "capaian pembelajaran";

  if (score >= 88) return `sangat baik dalam ${topic}`;
  if (score >= 75) return `baik dalam ${topic}`;
  if (score >= 38) return `cukup dalam ${topic}`;
  return `perlu bimbingan dalam ${topic}`;
}

function computeRow(tr){
  let inputs = tr.querySelectorAll("input.score-input");
  let vals = {};
  inputs.forEach(inp=>{ if(inp.value!=="") vals[inp.name.split("[")[0]] = Number(inp.value) });

  let sumVals = ['sumatif_a','sumatif_b','sumatif_c','sumatif_d','sumatif_e'].map(k=>vals[k]).filter(v=>!isNaN(v));
  let avgS = sumVals.length ? sumVals.reduce((a,b)=>a+b,0)/sumVals.length : null;
  let avgA = (vals.asts!=null && vals.asas!=null) ? (vals.asts+vals.asas)/2 : null;
  let raport = (avgS!=null && avgA!=null) ? (avgS*0.6 + avgA*0.4) : null;

  tr.querySelector(".na-s").innerText = avgS? avgS.toFixed(2):"-";
  tr.querySelector(".na-as").innerText = avgA? avgA.toFixed(2):"-";
  tr.querySelector(".raport").innerText = raport? raport.toFixed(2):"-";

  let descs = [];
  const mapel_id = <?= $selected_mapel_id ?>; // ambil dari PHP
  ['sumatif_a','sumatif_b','sumatif_c','sumatif_d','sumatif_e'].forEach(k=>{
    if(vals[k]!=null) descs.push(getDesc(k, vals[k], mapel_id)); // kirim mapel_id
  });
  tr.querySelector(".desc-cell").innerText = descs.length? descs.join("; "):"-";
}


document.querySelectorAll("tbody tr").forEach(tr=>{
  tr.querySelectorAll("input.score-input").forEach(inp=>{
    inp.addEventListener("input", ()=>{ 
      if(inp.value!=="" && (inp.value<0 || inp.value>100)) inp.value="";
      computeRow(tr);
    });
  });
  computeRow(tr);
});

// save semua
document.getElementById("saveAllBtn").addEventListener("click", async ()=>{
  let form = document.getElementById("allScoresForm");
  let formData = new FormData(form);

  // --- Tambahkan deskripsi per siswa ---
  document.querySelectorAll("tbody tr").forEach(tr=>{
    let sid = tr.dataset.student;
    let desc = tr.querySelector(".desc-cell").innerText;
    formData.append("description["+sid+"]", desc);
  });

  try {
    const resp = await fetch("save_score.php", {
      method:"POST",
      body: formData
    });
    const text = await resp.text();
    let j = {};
    try { j = JSON.parse(text); } catch(e){ alert("Respon server bukan JSON:\n\n"+text); return; }
    if(j.success) alert("Semua nilai berhasil disimpan!");
    else alert("Gagal: "+j.error);
  } catch(err) {
    alert("Terjadi error saat menghubungi server.");
  }
});
</script>
</body>
</html>
