<?php
require 'config.php';
if (!isset($_SESSION['teacher_id'])) header('Location: login.php');

// --- Ambil parameter dari URL ---
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$mapel_id   = isset($_GET['mapel_id']) ? intval($_GET['mapel_id']) : 0;

// --- Validasi parameter wajib ---
if ($student_id <= 0 || $mapel_id <= 0) {
    die("Parameter student_id atau mapel_id tidak valid.");
}

// --- Ambil data siswa dan kelas ---
$stmt = $pdo->prepare("
    SELECT s.*, c.name AS class_name 
    FROM students s 
    JOIN classes c ON s.class_id = c.id 
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) die("Siswa tidak ditemukan.");

// --- Ambil nilai dan deskripsi untuk mapel tertentu ---
$stmt = $pdo->prepare("
    SELECT sc.*, m.nama_mapel AS mapel_name 
    FROM scores sc 
    JOIN mapel m ON sc.mapel_id = m.id 
    WHERE sc.student_id = ? AND sc.mapel_id = ?
");
$stmt->execute([$student_id, $mapel_id]);
$scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Helper: pecah deskripsi berdasarkan titik ---
function parse_description_parts($desc) {
    if (!$desc) return [];
    $parts = preg_split('/\.\s*/u', trim($desc));
    $parts = array_filter(array_map('trim', $parts), fn($p) => $p !== '');
    return array_values($parts);
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Report - <?=htmlspecialchars($student['name'])?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm py-3">
  <div class="container">
    <a href="score.php?student_id=<?= $student_id ?>&class_id=<?= $student['class_id'] ?>&mapel_id=<?= $mapel_id ?>" 
   class="navbar-brand">
  ← Kembali ke Siswa
</a>


    <div>
      <span class="me-3 text-white">Hai, <?=htmlspecialchars($_SESSION['teacher_name'])?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h5>Laporan Nilai: <?=htmlspecialchars($student['name'])?> (<?=htmlspecialchars($student['class_name'])?>)</h5>

  <?php if (!$scores): ?>
    <div class="alert alert-warning">Belum ada nilai yang disimpan untuk siswa ini.</div>
  <?php else: ?>
    <?php foreach ($scores as $row): ?>
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">
          Mata Pelajaran: <?=htmlspecialchars($row['mapel_name'])?>
        </div>
        <div class="card-body bg-white">
          <table class="table table-bordered mb-3">
            <thead class="table-light">
              <tr>
                <th>Komponen</th>
                <th>Nilai</th>
                <th>Deskripsi (per Sumatif)</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // daftar sumatif
              $sumatif_keys = ['sumatif_a','sumatif_b','sumatif_c','sumatif_d','sumatif_e'];
              // pecah description berdasarkan titik
              $parts = parse_description_parts($row['description'] ?? '');
              foreach ($sumatif_keys as $idx => $k):
                $val = $row[$k];
                $part_desc = $parts[$idx] ?? '-';
              ?>
                <tr>
                  <td><?= strtoupper(str_replace('_', ' ', $k)) ?></td>
                  <td><?= $val === null ? '-' : intval($val) ?></td>
                  <td><?= htmlspecialchars($part_desc) ?></td>
                </tr>
              <?php endforeach; ?>

              <tr>
                <td>ASTS</td>
                <td><?= $row['asts'] ?? '-' ?></td>
                <td>-</td>
              </tr>
              <tr>
                <td>ASAS</td>
                <td><?= $row['asas'] ?? '-' ?></td>
                <td>-</td>
              </tr>
              <tr class="fw-bold">
                <td>S (Rata-rata Sumatif)</td>
                <td><?= $row['avg_sumatif'] ?? '-' ?></td>
                <td>-</td>
              </tr>
              <tr class="fw-bold">
                <td>AS (Rata-rata ASTS & ASAS)</td>
                <td><?= $row['avg_asts_asas'] ?? '-' ?></td>
                <td>-</td>
              </tr>
              <tr class="fw-bold">
                <td>Nilai Raport</td>
                <td><?= $row['raport_score'] ?? '-' ?></td>
                <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
              </tr>
            </tbody>
          </table>

          <a href="score.php?student_id=<?= $student_id ?>&mapel_id=<?= $row['mapel_id'] ?>" class="btn btn-primary">Edit Nilai</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>
