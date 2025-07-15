<?php
function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    elseif ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    elseif ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    else return $bytes . ' B';
}

// Gunakan path lengkap ke Ghostscript
$gs_cmd = '"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe"';

if (!file_exists(str_replace('"', '', $gs_cmd))) {
    die("❌ Ghostscript tidak ditemukan. Periksa kembali path: $gs_cmd");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['ijazah'])) {
    $file = $_FILES['ijazah'];
    $level = isset($_POST['level']) ? floatval($_POST['level']) : 0.7;

    $upload_dir = "uploads/";
    $kompres_dir = "kompresi/";

    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    if (!file_exists($kompres_dir)) mkdir($kompres_dir, 0777, true);

    $original_path = $upload_dir . basename($file["name"]);
    $compressed_path = $kompres_dir . "kompres_" . basename($file["name"]);

    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        die("❌ File yang diunggah bukan PDF.");
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        die("❌ Terjadi kesalahan saat mengunggah file.");
    }

    if (move_uploaded_file($file["tmp_name"], $original_path)) {
        $original_size = filesize($original_path);

        // Tentukan tingkat kualitas
        $gs_setting = "/ebook";
        if ($level >= 0.9) $gs_setting = "/printer";
        elseif ($level <= 0.5) $gs_setting = "/screen";

        // Perintah kompresi dengan Ghostscript
        $cmd = "$gs_cmd -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=$gs_setting -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($compressed_path) . " " . escapeshellarg($original_path);
        exec($cmd, $output, $status);

        if ($status !== 0 || !file_exists($compressed_path)) {
            die("❌ Gagal melakukan kompresi PDF.");
        }

        $compressed_size = filesize($compressed_path);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Kompresi</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f5f9;
            text-align: center;
            padding: 50px;
        }

        .card {
            background: white;
            padding: 30px;
            max-width: 500px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.15);
        }

        .progress-bar {
            width: 100%;
            background-color: #ddd;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress {
            width: 0%;
            height: 20px;
            background-color: #28a745;
            text-align: center;
            color: white;
            line-height: 20px;
            transition: width 1s ease;
        }

        a button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        a button:hover {
            background-color: #218838;
        }

        .info {
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>✅ File Berhasil Dikompres</h2>

    <div class="progress-bar">
        <div class="progress" id="bar">0%</div>
    </div>

    <div class="info">Ukuran Sebelum: <strong><?= formatSize($original_size); ?></strong></div>
    <div class="info">Ukuran Sesudah: <strong><?= formatSize($compressed_size); ?></strong></div>
    <div class="info">Level Ghostscript: <strong><?= $gs_setting ?></strong></div>

    <a href="<?= $compressed_path ?>" download>
        <button>📥 Unduh File Kompresi</button>
    </a>
</div>

<script>
    let bar = document.getElementById("bar");
    let width = 0;
    let interval = setInterval(() => {
        if (width >= 100) {
            clearInterval(interval);
        } else {
            width += 10;
            bar.style.width = width + "%";
            bar.innerHTML = width + "%";
        }
    }, 100);
</script>

</body>
</html>
<?php
    } else {
        echo "❌ Gagal mengunggah file.";
    }
} else {
    echo "⛔ Tidak ada file yang diunggah.";
}
?>
