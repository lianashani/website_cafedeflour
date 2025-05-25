<?php
include '../../../../config/koneksi.php';

$kode = $_GET['kode'] ?? '';

$data = null;
if ($kode != '') {
    $q = mysqli_query($conn, "SELECT * FROM transaksi WHERE kode_transaksi = '$kode'");
    $data = mysqli_fetch_assoc($q);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Pesanan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f1f3f5;
        }

        .container {
            max-width: 650px;
            margin: auto;
        }

        .card {
            border: none;
            border-radius: 14px;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }

        .input-group {
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .badge-status {
            font-size: 0.9rem;
            padding: 0.5em 0.75em;
            border-radius: 12px;
        }

        .label-title {
            font-weight: 500;
            color: #343a40;
        }

        .value-text {
            color: #495057;
        }

        #preview {
            max-width: 100%;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body class="py-5">

<div class="container">
    <h2 class="mb-4 text-center fw-semibold">Tracking Status Pesanan</h2>

    <form method="get" class="mb-4" id="formTracking">
        <div class="input-group mb-2">
            <input type="text" name="kode" id="kodeInput" value="<?= htmlspecialchars($kode) ?>" class="form-control" placeholder="Masukkan Kode Transaksi" required>
            <button class="btn btn-primary" type="submit">Lacak</button>
        </div>
        <a href="../../backend/transaksi/tracking.php">Reset</a>
    </form>

    <!-- Upload QR Image -->
    <div class="mb-4">
        <label for="qrImage" class="form-label">Atau unggah gambar QR Code:</label>
        <input type="file" class="form-control" id="qrImage" accept="image/*">
        <img id="preview" src="#" alt="Preview QR">
        <div class="text-muted mt-2" id="qrResult"></div>
    </div>

    <?php if ($data): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Kode Transaksi: <span class="text-primary"><?= $data['kode_transaksi'] ?></span></h5>
                <div class="mb-2"><span class="label-title">Nama:</span> <span class="value-text"><?= $data['nama_pelanggan'] ?></span></div>
                <div class="mb-2"><span class="label-title">Tanggal:</span> <span class="value-text"><?= $data['tanggal'] ?> <?= $data['waktu'] ?></span></div>
                <div class="mb-2"><span class="label-title">Lokasi:</span> <span class="value-text"><?= $data['lokasi'] ?></span></div>
                <div class="mb-2"><span class="label-title">Catatan:</span> <span class="value-text"><?= $data['catatan'] ?></span></div>
                <div><span class="label-title">Status:</span> <span class="badge bg-info"><?= $data['status'] ?></span></div>
            </div>
        </div>
    <?php elseif ($kode): ?>
        <div class="alert alert-danger mt-3 text-center shadow-sm">Pesanan dengan kode <strong><?= htmlspecialchars($kode) ?></strong> tidak ditemukan.</div>
    <?php endif; ?>
</div>

<!-- JS QR Reader -->
<script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
<script>
document.getElementById('qrImage').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const qrResult = document.getElementById('qrResult');

    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            context.drawImage(img, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, canvas.width, canvas.height);

            if (code) {
                qrResult.innerText = 'QR berhasil dibaca: ' + code.data;
                document.getElementById('kodeInput').value = code.data;
                document.getElementById('formTracking').submit();
            } else {
                qrResult.innerText = 'QR tidak terbaca. Coba gambar lain.';
            }
        };
        img.src = e.target.result;
        preview.src = img.src;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>

</body>
</html>
