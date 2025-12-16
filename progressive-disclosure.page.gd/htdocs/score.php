<?php
// score.php
require 'config.php';
if (!isset($_SESSION['teacher_id'])) header('Location: login.php');

$student_id = intval($_GET['student_id'] ?? 0);
$class_id = intval($_GET['class_id'] ?? 0);
$mapel_id = intval($_GET['mapel_id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, c.name as class_name FROM students s JOIN classes c ON s.class_id=c.id WHERE s.id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) die("Siswa tidak ditemukan.");

// load existing scores if ada
$stmt = $pdo->prepare("SELECT * FROM scores WHERE student_id = ? AND mapel_id = ?");
$stmt->execute([$student_id, $mapel_id]);
$scores = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$mapel_id) {
    die("Mapel belum dipilih. Silakan buka halaman melalui daftar mapel.");
}

// ambil nama mapel untuk ditampilkan
$stmt = $pdo->prepare("SELECT nama_mapel FROM mapel WHERE id=?");
$stmt->execute([$mapel_id]);
$mapel = $stmt->fetchColumn();

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Input Nilai - <?=htmlspecialchars($student['name'])?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .small-muted { font-size: .9rem; color: #6c757d; }
    .result-box { font-weight:600; font-size:1.1rem; }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm py-3">
  <div class="container">
    <a href="students.php?class_id=<?=$student['class_id']?> &mapel_id=<?= $mapel_id ?>" class="navbar-brand">← Kembali ke Siswa</a>
    <!-- <a href="#" id="backToStudents" class="navbar-brand">← Kembali ke Siswa</a> -->
    <div>
      <span class="me-3 text-white">Hai, <?=htmlspecialchars($_SESSION['teacher_name'])?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-center align-items-center" style="max-width:500px;margin:auto;">
    
     <!-- Step 1 (aktif) -->
    <a href="index.php" style="text-decoration: none;">
  <div class="rounded-circle d-flex justify-content-center align-items-center" 
       style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
    1
  </div>
</a>

    <div class="mx-2 flex-fill" style="height:4px;background-color:#0d6efd;"></div>

    <!-- Step 2 -->
     <a href="mapel.php?class_id=<?=$class_id?> &mapel_id=<?= $mapel_id ?> style="text-decoration: none;">
  <div class="rounded-circle d-flex justify-content-center align-items-center" 
       style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
    2
  </div>
</a>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#0d6efd;"></div>

    <!-- Step 3 -->
      <a href="students.php?class_id=<?=$student['class_id']?> &mapel_id=<?= $mapel_id ?>" style="text-decoration: none;">
  <div class="rounded-circle d-flex justify-content-center align-items-center" 
       style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
    3
  </div>
</a>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#0d6efd;"></div>

    <!-- Step 4 -->
    <div class="rounded-circle d-flex justify-content-center align-items-center" 
        style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
      4
    </div>

  </div>
</div>

<div class="container py-4">
<h5>Masukkan Nilai untuk <?=htmlspecialchars($student['name'])?> - Mapel <?=htmlspecialchars($mapel)?></h5>  
<p class="small-muted">Masukkan semua nilai siswa.</p>

  <div class="card mb-3">
    <div class="card-body">
      <form id="scoreForm">
        <input type="hidden" name="mapel_id" value="<?=$mapel_id?>">
        <input type="hidden" name="student_id" value="<?=$student_id?>">

        <h6>Nilai Sumatif (5 nilai)</h6>
        <?php
        $sum_keys = ['sumatif_a','sumatif_b','sumatif_c','sumatif_d','sumatif_e'];
        foreach ($sum_keys as $k):
            $val = $scores[$k] ?? '';
        ?>
        <div class="mb-3 row align-items-center">
          <label class="col-sm-2 col-form-label">
          	<?=strtoupper(str_replace('_',' ', $k))?>
		  </label>
          <div class="col-sm-4">
            <input type="number" min="0" max="100" step="1" class="form-control score-input" name="<?=$k?>" id="<?=$k?>" value="<?=htmlspecialchars($val)?>">
          </div>
        </div>
        <?php endforeach; ?>

        <h6>Nilai ASTS & ASAS</h6>
        <div class="mb-3 row">
          <label class="col-sm-2 col-form-label">ASTS</label>
          <div class="col-sm-4">
            <input type="number" min="0" max="100" step="1" class="form-control score-input" name="asts" id="asts" value="<?=htmlspecialchars($scores['asts'] ?? '')?>">
          </div>
        </div>
        <div class="mb-3 row">
          <label class="col-sm-2 col-form-label">ASAS</label>
          <div class="col-sm-4">
            <input type="number" min="0" max="100" step="1" class="form-control score-input" name="asas" id="asas" value="<?=htmlspecialchars($scores['asas'] ?? '')?>">
          </div>
        </div>

        <hr>
        <h6>Hasil Perhitungan (Realtime)</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <div class="p-3 bg-white border rounded">
              <div class="small-muted">S (Rata-Rata Sumatif)</div>
              <div id="avgSumatif" class="result-box"><?= $scores['avg_sumatif'] ?? '-' ?></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-white border rounded">
              <div class="small-muted">AS (Rata-Rata ASTS & ASAS)</div>
              <div id="avgAstsAsas" class="result-box"><?= $scores['avg_asts_asas'] ?? '-' ?></div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-white border rounded">
              <div class="small-muted">Nilai Raport (60% S + 40% AS)</div>
              <div id="raport" class="result-box"><?= $scores['raport_score'] ?? '-' ?></div>
            </div>
          </div>
        </div>

        <div class="mt-4 d-flex">
          <!-- <button type="button" id="prevBtn" class="btn btn-secondary me-2">Sebelumnya</button> -->
          <!-- <button type="button" id="saveDraftBtn" class="btn btn-outline-primary me-2">Simpan Sementara</button> -->
          <button type="button" id="confirmBtn" class="btn btn-success">Simpan & Konfirmasi</button>
        </div>
        <div class= "mt-4 d-flex">
          <a href="report.php?student_id=<?= $student_id ?>&mapel_id=<?= $mapel_id ?>" class="btn btn-info">Lihat Report</a>

        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Simpan Nilai</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menyimpan nilai ini untuk <strong><?=htmlspecialchars($student['name'])?></strong>?</p>
        <div id="confirmSummary"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button id="confirmSave" class="btn btn-success">Ya, Simpan</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Client-side JS: real-time calc, validation, save via AJAX
const sumKeys = ['sumatif_a','sumatif_b','sumatif_c','sumatif_d','sumatif_e'];

function parseVal(id) {
  const v = document.getElementById(id).value;
  return v === '' ? null : Number(v);
}

function validScore(v) {
  return v === null || (Number.isInteger(v) && v >= 0 && v <= 100);
}

function compute() {
  // average sumatif
  let sumVals = [];
  for (let k of sumKeys) {
    let v = parseVal(k);
    if (v !== null) sumVals.push(v);
  }
  let avgSum = sumVals.length ? (sumVals.reduce((a,b)=>a+b,0)/sumVals.length) : null;
  // avg asts/asas
  let asts = parseVal('asts');
  let asas = parseVal('asas');
  let avgA = null;
  if (asts !== null && asas !== null) avgA = (asts + asas) / 2;
  // raport = 60% avgSum + 40% avgA
  let raport = null;
  if (avgSum !== null && avgA !== null) {
    raport = (avgSum * 0.6) + (avgA * 0.4);
  }
  document.getElementById('avgSumatif').innerText = avgSum === null ? '-' : avgSum.toFixed(2);
  document.getElementById('avgAstsAsas').innerText = avgA === null ? '-' : avgA.toFixed(2);
  document.getElementById('raport').innerText = raport === null ? '-' : raport.toFixed(2);
  return { avgSum, avgA, raport };
}

document.querySelectorAll('.score-input').forEach(el => {
  el.addEventListener('input', () => {
    // ensure numeric and clamp
    if (el.value !== '') {
      if (isNaN(el.value) || el.value < 0) el.value = 0;
      if (el.value > 100) el.value = 100;
      el.value = Math.floor(Number(el.value));
    }
    compute();
  });
});

compute();

// Save (draft or final) via AJAX
async function saveData(final=false) {
  const form = document.getElementById('scoreForm');
  const formData = new FormData(form);
  const computed = compute();
  if (computed.avgSum !== null) formData.append('avg_sumatif', computed.avgSum.toFixed(2));
  if (computed.avgA !== null) formData.append('avg_asts_asas', computed.avgA.toFixed(2));
  if (computed.raport !== null) formData.append('raport_score', computed.raport.toFixed(2));
  formData.append('final', final ? '1' : '0');

  // client-side validation: numeric 0-100 for all filled fields
  for (let pair of formData.entries()) {
    const k = pair[0], v = pair[1];
    if (['student_id','final','avg_sumatif','avg_asts_asas','raport_score'].includes(k)) continue;
    if (v === '') continue;
    const num = Number(v);
    if (!Number.isFinite(num) || num < 0 || num > 100) {
      alert('Nilai harus angka antara 0 dan 100.');
      return false;
    }
  }

  const resp = await fetch('save_score.php', { method:'POST', body: formData });
  const j = await resp.json();
  if (j.success) {
    alert('Data berhasil disimpan.');
    // reload saved info box
    location.reload();
  } else {
    alert('Gagal menyimpan: ' + (j.error || 'Unknown'));
  }
}

// // Buttons
// document.getElementById('saveDraftBtn').addEventListener('click', ()=> saveData(false));

const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
document.getElementById('confirmBtn').addEventListener('click', ()=>{
  // prepare summary
  const summary = [];
  sumKeys.forEach(k => {
    const v = parseVal(k);
    if (v !== null) summary.push(`${k.toUpperCase()}: ${v}`);
  });
  const asts = parseVal('asts'), asas = parseVal('asas');
  if (asts !== null) summary.push(`ASTS: ${asts}`);
  if (asas !== null) summary.push(`ASAS: ${asas}`);
  const comp = compute();
  summary.push(`Rata-rata Sumatif: ${comp.avgSum === null ? '-' : comp.avgSum.toFixed(2)}`);
  summary.push(`Rata-rata ASTS&ASAS: ${comp.avgA === null ? '-' : comp.avgA.toFixed(2)}`);
  summary.push(`Nilai Raport: ${comp.raport === null ? '-' : comp.raport.toFixed(2)}`);

  document.getElementById('confirmSummary').innerHTML = '<ul>' + summary.map(x => `<li>${x}</li>`).join('') + '</ul>';
  confirmModal.show();
});

document.getElementById('confirmSave').addEventListener('click', async ()=>{
  confirmModal.hide();
  await saveData(true);
});

// // Prev button (kembali ke daftar siswa). Before leaving, save draft automatically.
// document.getElementById('prevBtn').addEventListener('click', async ()=>{
//   // save draft then navigate back
//   await saveData(false);
//   window.location.href = 'students.php?class_id=<?=$student['class_id']?>';
// });

document.getElementById('backToStudents').addEventListener('click', async (e)=>{
  e.preventDefault(); // cegah langsung pindah halaman
  await saveData(false); // simpan draft
  window.location.href = 'students.php?class_id=<?=$student['class_id']?>'; // lalu kembali
});

</script>
</body>
</html>
