<?php
// mapel.php - pilih mapel
require 'config.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit;
}

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    header('Location: index.php');
    exit;
}

// Ambil daftar mapel dari database (urutkan berdasarkan id)
$stmt = $pdo->query("SELECT id, nama_mapel FROM mapel ORDER BY id ASC");
$mapel_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Pilih Mapel - Penilaian Akademik</title>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm py-3">
  <div class="container">
    <a href="index.php" class="navbar-brand">← Kembali ke Kelas</a>
    <div>
      <span class="me-3 text-white">Hai, <?= htmlspecialchars($_SESSION['teacher_name']) ?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-center align-items-center" style="max-width:500px;margin:auto;">
    <!-- Step progress indicator -->
    <a href="index.php" style="text-decoration: none;">
  <div class="rounded-circle d-flex justify-content-center align-items-center" 
       style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
    1
  </div>
</a>

    <div class="mx-2 flex-fill" style="height:4px;background-color:#0d6efd;"></div>

    <div class="rounded-circle d-flex justify-content-center align-items-center" 
        style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
      2
    </div>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#ccc;"></div>

    <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold" 
        style="width:45px;height:45px;background-color:#ccc;">
      3
    </div>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#ccc;"></div>

    <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold" 
        style="width:45px;height:45px;background-color:#ccc">
      4
    </div>
  </div>
</div>

<div class="container py-4">
  <h5>Pilih Mata Pelajaran</h5>
  <p class="text-muted">Pilih mata pelajaran untuk kelas ini</p>

  <div class="row">
    <?php foreach ($mapel_list as $m): ?>
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h6 class="card-title"><?= htmlspecialchars($m['nama_mapel']) ?></h6>
            <a href="students.php?class_id=<?= $class_id ?>&mapel_id=<?= $m['id'] ?>" 
               class="btn btn-primary btn-sm">Pilih</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
