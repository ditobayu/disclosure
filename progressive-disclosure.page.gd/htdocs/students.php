<?php
// students.php
require 'config.php';
if (!isset($_SESSION['teacher_id'])) header('Location: login.php');

$class_id = intval($_GET['class_id'] ?? 0);
$mapel_id = intval($_GET['mapel_id'] ?? 0);
// basic security: check class belongs to teacher
$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ? AND teacher_id = ?");
$stmt->execute([$class_id, $_SESSION['teacher_id']]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$class) {
    die("Kelas tidak ditemukan atau Anda tidak berhak mengaksesnya.");
}

// get students (urut nama A-Z)
$stmt = $pdo->prepare("SELECT * FROM students WHERE class_id = ? ORDER BY name ASC");
$stmt->execute([$class_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ambil nama mapel
$stmt = $pdo->prepare("SELECT nama_mapel FROM mapel WHERE id = ?");
$stmt->execute([$mapel_id]);
$mapel = $stmt->fetchColumn();


?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Siswa - <?=$class['name']?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm py-3">
  <div class="container">
    <a href="mapel.php?class_id=<?=$class_id?> &mapel_id=<?= $mapel_id ?>" class="navbar-brand">← Kembali ke Mapel</a>
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
    <div class="rounded-circle d-flex justify-content-center align-items-center" 
        style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
      3
    </div>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#ccc;"></div>

    <!-- Step 4 -->
    <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold" 
        style="width:45px;height:45px;background-color:#ccc">
      4
    </div>

  </div>
</div>


<div class="container py-4">
  <h5>Pilih Siswa (Kelas <?=$class['name']?>)</h5>
  <p class="text-muted">Klik nama siswa untuk mengisi atau mengedit nilainya.</p>

  <div class="list-group">
    <?php foreach($students as $s): ?>
      <a href="score.php?student_id=<?=$s['id']?>&class_id=<?=$class_id?>&mapel_id=<?=$mapel_id?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
        <?=htmlspecialchars($s['name'])?>
        <?php
        // check whether score exists
        $st = $pdo->prepare("SELECT id FROM scores WHERE student_id = ? AND mapel_id = ?");
        $st->execute([$s['id'], $mapel_id]);
        if ($st->fetch()) {
            echo '<span class="badge bg-success">Sudah Ada</span>';
        } else {
            echo '<span class="badge bg-secondary">Belum</span>';
        }
        ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
