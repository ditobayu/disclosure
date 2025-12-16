<?php
// index.php - select class
require 'config.php';
if (!isset($_SESSION['teacher_id'])) header('Location: login.php');

$teacher_id = $_SESSION['teacher_id'];
// get classes for this teacher
$stmt = $pdo->prepare("SELECT * FROM classes WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Pilih Kelas - Penilaian Akademik</title>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary shadow-sm py-3">
  <div class="container">
    <span class="navbar-brand mb-0 h1 text-white">Penilaian Akademik</span>
    <div>
      <span class="me-3 text-white">Hai, <?=htmlspecialchars($_SESSION['teacher_name'])?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-center align-items-center" style="max-width:500px;margin:auto;">
    
    <!-- Step 1 (aktif) -->
    <div class="rounded-circle d-flex justify-content-center align-items-center" 
        style="width:45px;height:45px;background-color:#0d6efd;color:#fff;font-weight:600;">
      1
    </div>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#ccc;"></div>

    <!-- Step 2 -->
    <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold" 
         style="width:45px;height:45px;background-color:#ccc;">
      2
    </div>
    <div class="mx-2 flex-fill" style="height:4px;background-color:#ccc;"></div>

    <!-- Step 3 -->
    <div class="rounded-circle d-flex justify-content-center align-items-center text-white fw-bold" 
         style="width:45px;height:45px;background-color:#ccc;">
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
  <h5>Pilih Kelas</h5>
  <p class="text-muted">Pilih kelas yang anda ajar</p>

  <div class="accordion" id="kelasAccordion">
  <?php 
  $levels = [7,8,9]; // tingkatan kelas
  foreach($levels as $i => $lvl): 
    $accordionId = "collapse".$lvl;
    $isOpen = ($lvl == 7); // default buka kelas 7
  ?>
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading<?=$lvl?>">
        <button class="accordion-button <?=$isOpen ? '' : 'collapsed'?>" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#<?=$accordionId?>" 
                aria-expanded="<?=$isOpen ? 'true' : 'false'?>" 
                aria-controls="<?=$accordionId?>">
          Kelas <?=$lvl?>
        </button>
      </h2>
      <div id="<?=$accordionId?>" 
           class="accordion-collapse collapse <?=$isOpen ? 'show' : ''?>" 
           aria-labelledby="heading<?=$lvl?>" 
           data-bs-parent="#kelasAccordion">
        <div class="accordion-body">
          <div class="row">
            <?php 
            $found = false;
            foreach($classes as $c): 
              if(str_starts_with($c['name'], (string)$lvl)): 
                $found = true;
            ?>
              <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title"><?=htmlspecialchars($c['name'])?></h5>
                    <!-- <a href="students.php?class_id=<?=$c['id']?>" class="btn btn-primary btn-sm">Pilih Kelas</a> -->
                     <a href="mapel.php?class_id=<?=$c['id']?>" class="btn btn-primary btn-sm">Pilih Kelas</a>

                  </div>
                </div>
              </div>
            <?php 
              endif; 
            endforeach; 
            if(!$found): ?>
              <p class="text-muted">Tidak ada kelas untuk tingkatan ini.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

</div>


</body>
</html>
