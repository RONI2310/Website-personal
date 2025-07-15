<!DOCTYPE html>
<html>
<head>
    <title>Kompresi Ijazah dengan PHP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            width: 420px;
            position: relative;
        }

        h2 {
            color: #2563eb;
            margin-bottom: 20px;
        }

        .custom-file-upload {
            border: 2px dashed #2563eb;
            display: inline-block;
            padding: 16px 20px;
            cursor: pointer;
            border-radius: 10px;
            background-color: #eef2ff;
            color: #2563eb;
            font-weight: bold;
            transition: 0.3s;
        }

        .custom-file-upload:hover {
            background-color: #dbeafe;
        }

        input[type="file"] {
            display: none;
        }

        .file-info {
            margin-top: 15px;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }

        .select-wrapper {
            margin-top: 20px;
            text-align: left;
        }

        select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            margin-top: 8px;
        }

        button {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #1d4ed8;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .desc {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }

        .help-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: transparent;
            border: none;
            font-size: 20px;
            color: #2563eb;
            cursor: pointer;
        }

        #helpModal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        #helpModalContent {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .close-btn {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <button class="help-btn" onclick="openHelp()" title="Bantuan">❓</button>

    <h2>📄 Kompresi Ijazah</h2>
    <form action="kompres.php" method="post" enctype="multipart/form-data">
        <label class="custom-file-upload">
            <input type="file" name="ijazah" id="ijazah" accept="application/pdf" required>
            📎 Pilih File PDF
        </label>
        <div class="file-info" id="file-name" style="display:none;"></div>

        <div style="margin-top: 15px; display: flex; justify-content: space-around;">
            <button type="button" onclick="setMode('bright')" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #2563eb; color: #2563eb; background: #e0f2fe; cursor: pointer;">🔆 Brightness</button>
            <button type="button" onclick="setMode('dark')" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #1e293b; color: #1e293b; background: #f1f5f9; cursor: pointer;">🌑 Darkness</button>
        </div>
        <input type="hidden" name="adjust_mode" id="adjust_mode" value="none">

        <div class="select-wrapper">
            <label for="level">Level Kompresi:</label>
            <select name="level" id="level">
                <option value="0.9">🔵 Rendah (90%)</option>
                <option value="0.7" selected>🟡 Sedang (70%)</option>
                <option value="0.5">🔴 Tinggi (50%)</option>
            </select>
        </div>

        <button type="submit">🔧 Kompres Sekarang</button>
    </form>
    <div class="desc">Pilih level kompresi dan unggah file PDF ijazah.</div>
</div>

<!-- Modal Help -->
<div id="helpModal">
    <div id="helpModalContent">
        <h3>📘 Langkah-Langkah Perhitungan Manual Kompresi Ijazah</h3>
        <ol style="text-align:left;">
            <li><strong>Identifikasi Ukuran Asli:</strong> hitung ukuran awal PDF dalam byte.</li>
            <li><strong>Tentukan Level Kompresi:</strong> misalnya 70% berarti 0.7.</li>
            <li><strong>Hitung Ukuran Target:</strong>
                <pre>Ukuran_Terkompresi = Ukuran_Asli × Level_Kompresi</pre>
            </li>
            <li><strong>Gunakan Ghostscript:</strong>
                <pre>
"C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe" \
 -sDEVICE=pdfwrite \
 -dCompatibilityLevel=1.4 \
 -dPDFSETTINGS=/ebook \
 -dNOPAUSE -dQUIET -dBATCH \
 -sOutputFile=output.pdf input.pdf
                </pre>
            </li>
            <li><strong>Verifikasi hasil:</strong> pastikan file bisa dibuka & terbaca.</li>
        </ol>
        <button class="close-btn" onclick="closeHelp()">Tutup</button>
    </div>
</div>

<script>
    const input = document.getElementById("ijazah");
    const fileNameDisplay = document.getElementById("file-name");

    input.addEventListener("change", function () {
        const name = this.files[0]?.name || "";
        fileNameDisplay.textContent = "📘 File terpilih: " + name;
        fileNameDisplay.style.display = "block";
    });

    function openHelp() {
        document.getElementById("helpModal").style.display = "block";
    }

    function closeHelp() {
        document.getElementById("helpModal").style.display = "none";
    }

    function setMode(mode) {
        document.getElementById("adjust_mode").value = mode;
        alert("Mode " + (mode === 'bright' ? "brightness" : "darkness") + " dipilih.");
    }
</script>

</body>
</html>