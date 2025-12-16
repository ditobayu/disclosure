<?php
require 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit;
}

$student_id = intval($_POST['student_id'] ?? 0);
$mapel_id = intval($_POST['mapel_id'] ?? 0); // ambil mapel dari form

if (!$student_id || !$mapel_id) {
    echo json_encode(['success'=>false,'error'=>'student_id dan mapel_id wajib diisi']);
    exit;
}

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


// fungsi untuk buat deskripsi nilai
function get_desc_for_score($score, $topic) {
    if ($score >= 88) return "memiliki kemampuan sangat baik dalam {$topic}";
    if ($score >= 75) return "baik dalam {$topic}";
    if ($score >= 38) return "cukup dalam {$topic}";
    return "perlu bimbingan dalam {$topic}";
}

// ambil nilai dari input
$fields = ['sumatif_a','sumatif_b','sumatif_c','sumatif_d','sumatif_e','asts','asas','avg_sumatif','avg_asts_asas','raport_score'];
$data = [];
foreach ($fields as $f) {
    $v = $_POST[$f] ?? null;
    $data[$f] = ($v === '' || $v === null) ? null : (float)$v;
}

// buat deskripsi akhir berdasarkan mapel
$desc_map = $mapel_descriptions[$mapel_id] ?? [];
$descriptions = [];

foreach ($desc_map as $key => $topic) {
    if (!empty($data[$key])) {
        $descriptions[] = get_desc_for_score($data[$key], $topic);
    }
}
$final_description = implode('. ', $descriptions);

// simpan ke database
try {
    $stmt = $pdo->prepare("SELECT id FROM scores WHERE student_id=? AND mapel_id=?");
    $stmt->execute([$student_id, $mapel_id]);
    $exists = $stmt->fetch();

    if ($exists) {
        $sql = "UPDATE scores SET sumatif_a=:sumatif_a, sumatif_b=:sumatif_b, sumatif_c=:sumatif_c, 
                sumatif_d=:sumatif_d, sumatif_e=:sumatif_e, asts=:asts, asas=:asas,
                avg_sumatif=:avg_sumatif, avg_asts_asas=:avg_asts_asas, raport_score=:raport_score,
                description=:description WHERE student_id=:student_id AND mapel_id=:mapel_id";
    } else {
        $sql = "INSERT INTO scores (student_id, mapel_id, sumatif_a, sumatif_b, sumatif_c, sumatif_d, sumatif_e,
                asts, asas, avg_sumatif, avg_asts_asas, raport_score, description)
                VALUES (:student_id, :mapel_id, :sumatif_a, :sumatif_b, :sumatif_c, :sumatif_d, :sumatif_e,
                :asts, :asas, :avg_sumatif, :avg_asts_asas, :raport_score, :description)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':student_id' => $student_id,
        ':mapel_id' => $mapel_id,
        ':sumatif_a' => $data['sumatif_a'],
        ':sumatif_b' => $data['sumatif_b'],
        ':sumatif_c' => $data['sumatif_c'],
        ':sumatif_d' => $data['sumatif_d'],
        ':sumatif_e' => $data['sumatif_e'],
        ':asts' => $data['asts'],
        ':asas' => $data['asas'],
        ':avg_sumatif' => $data['avg_sumatif'],
        ':avg_asts_asas' => $data['avg_asts_asas'],
        ':raport_score' => $data['raport_score'],
        ':description' => $final_description,
        ':mapel_id' => $mapel_id
    ]);

    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
