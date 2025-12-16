<?php
require 'config.php';
header('Content-Type: application/json');

$mapel_id = intval($_POST['mapel_id'] ?? 0);

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit;
}

try {
    if (empty($_POST['sumatif_a'])) {
        throw new Exception("Data kosong, pastikan form terkirim.");
    }

    foreach ($_POST['sumatif_a'] as $student_id => $val) {
        $student_id = intval($student_id);

        // Konversi string ke float/null
        $toNum = fn($v) => ($v === '' || $v === null) ? null : floatval($v);

        // Ambil semua nilai dari form
        $data = [
            'sumatif_a' => $toNum($_POST['sumatif_a'][$student_id] ?? null),
            'sumatif_b' => $toNum($_POST['sumatif_b'][$student_id] ?? null),
            'sumatif_c' => $toNum($_POST['sumatif_c'][$student_id] ?? null),
            'sumatif_d' => $toNum($_POST['sumatif_d'][$student_id] ?? null),
            'sumatif_e' => $toNum($_POST['sumatif_e'][$student_id] ?? null),
            'asts'      => $toNum($_POST['asts'][$student_id] ?? null),
            'asas'      => $toNum($_POST['asas'][$student_id] ?? null),
            'description' => $_POST['description'][$student_id] ?? null
        ];

        // ❌ Skip siswa jika semua nilai null / kosong
        $filled = array_filter($data, fn($v) => $v !== null && $v !== '');
        if (empty($filled)) continue;

        // Hitung rata-rata sumatif & asesmen
        $sumatif_vals = array_filter([
            $data['sumatif_a'], $data['sumatif_b'], $data['sumatif_c'],
            $data['sumatif_d'], $data['sumatif_e']
        ], fn($v) => $v !== null);

        $avgS = $sumatif_vals ? array_sum($sumatif_vals) / count($sumatif_vals) : null;
        $avgA = ($data['asts'] !== null && $data['asas'] !== null)
            ? ($data['asts'] + $data['asas']) / 2 : null;

        $raport = ($avgS !== null && $avgA !== null)
            ? ($avgS * 0.6 + $avgA * 0.4) : null;

        // Cek apakah siswa ini sudah punya nilai di mapel tersebut
        $stmt = $pdo->prepare("SELECT id FROM scores WHERE student_id=? AND mapel_id=?");
        $stmt->execute([$student_id, $mapel_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            // 📝 UPDATE jika sudah ada
            $sql = "UPDATE scores SET 
                sumatif_a=?, sumatif_b=?, sumatif_c=?, sumatif_d=?, sumatif_e=?,
                asts=?, asas=?, avg_sumatif=?, avg_asts_asas=?, raport_score=?, description=?
                WHERE student_id=? AND mapel_id=?";
            $pdo->prepare($sql)->execute([
                $data['sumatif_a'],$data['sumatif_b'],$data['sumatif_c'],$data['sumatif_d'],$data['sumatif_e'],
                $data['asts'],$data['asas'],$avgS,$avgA,$raport,$data['description'],$student_id,$mapel_id
            ]);
        } else {
            // ➕ INSERT hanya jika ada nilai diisi
            $sql = "INSERT INTO scores 
                (student_id,mapel_id,sumatif_a,sumatif_b,sumatif_c,sumatif_d,sumatif_e,
                asts,asas,avg_sumatif,avg_asts_asas,raport_score,description)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([
                $student_id,$mapel_id,
                $data['sumatif_a'],$data['sumatif_b'],$data['sumatif_c'],$data['sumatif_d'],$data['sumatif_e'],
                $data['asts'],$data['asas'],$avgS,$avgA,$raport,$data['description']
            ]);
        }
    }

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
?>
