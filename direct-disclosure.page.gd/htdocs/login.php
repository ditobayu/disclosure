<?php
// login.php
require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher && password_verify($password, $teacher['password'])) {
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['name'];
        header('Location: index.php');
        exit;
    } else {
        $err = "Username atau password salah.";
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login Guru - Penilaian Akademik</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #4e73df, #224abe);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      border: none;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0,0,0,.1);
      animation: fadeIn 0.7s ease-in-out;
    }
    .card-body {
      padding: 2rem;
    }
    .btn-primary {
      background: #4e73df;
      border: none;
      border-radius: 8px;
      transition: 0.3s;
    }
    .btn-primary:hover {
      background: #224abe;
      transform: translateY(-2px);
    }
    h4 {
      font-weight: 600;
      color: #333;
    }
    .form-control {
      border-radius: 8px;
    }
    .sample-login {
      font-size: 0.85rem;
      color: #6c757d;
    }
    @keyframes fadeIn {
      from {opacity:0; transform: translateY(20px);}
      to {opacity:1; transform: translateY(0);}
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card">
          <div class="card-body">
            <div class="text-center mb-4">
              <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Logo" width="70">
              <h4 class="mt-3">Login Guru</h4>
              <p class="text-muted">Sistem Penilaian Akademik</p>
            </div>
            <?php if($err): ?>
              <div class="alert alert-danger text-center"><?=htmlspecialchars($err)?></div>
            <?php endif; ?>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input name="username" class="form-control" placeholder="Masukkan username" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" placeholder="Masukkan password" required>
              </div>
              <button class="btn btn-primary w-100 py-2">Login</button>
            </form>
            <hr>
            <div class="sample-login text-center">
              Sample login: <strong>guru1</strong> / <strong>password123</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>