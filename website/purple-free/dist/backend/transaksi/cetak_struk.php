<?php
include '../../../../config/koneksi.php';

if (!isset($_GET['kode'])) {
    die('Kode transaksi tidak ditemukan.');
}

$kode = mysqli_real_escape_string($conn, $_GET['kode']);

// Ambil data transaksi + relasi special_members dan kasir
$transaksiQuery = "
SELECT t.*, 
       COALESCE(sm.nama_member, t.nama_pelanggan, '-') AS nama_pelanggan,
       k.nama_kasir
FROM transaksi t
LEFT JOIN special_members sm ON t.id_pelanggan = sm.id_member
LEFT JOIN kasir k ON t.id_kasir = k.id_kasir
WHERE t.kode_transaksi = '$kode'
LIMIT 1";
$transaksiResult = mysqli_query($conn, $transaksiQuery);
$data = mysqli_fetch_assoc($transaksiResult);

// Ambil semua menu yang dibeli
$menuQuery = "
SELECT m.nama_menu, td.jumlah, td.harga_saat_transaksi
FROM transaksi_detail td
JOIN menu m ON td.id_menu = m.id_menu
JOIN transaksi t ON t.id_transaksi = td.id_transaksi
WHERE t.kode_transaksi = '$kode'";
$menuResult = mysqli_query($conn, $menuQuery);

// QR Code (ganti sesuai kebutuhan)
$qrPath = '/website/purple-free/dist/assets/images/umpan.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
body {
    font-family: 'Courier New', monospace;
    width: 300px;
    margin: 0 auto;
    background: white;
    color: black;
    font-size: 12px;
    padding: 10px;
}
h2 {
    text-align: center;
    margin-bottom: 10px;
}
p {
    margin: 5px 0;
}
hr {
    border: 0;
    border-top: 1px dashed #000;
    margin: 10px 0;
}
strong {
    font-weight: bold;
}
.logo-container {
    display: flex;
    justify-content: center;
    align-items: center;
}
.logo {
    width: 70px;
    height: auto;
}
.qr {
    text-align: center;
    margin-top: 10px;
}
@media print {
    body {
        margin: 0;
        padding: 0;
        width: 100%;
    }
}
    </style>
</head>
<body onload="window.print()">
    <div class="logo-container">
        <img src="/assets/img/logo.png" alt="Logo Café" class="logo">
    </div>
    <h2>Café de Flour</h2>
    <p style="text-align:center; font-size:0.9em;">Jl Sayati Marhas, Kopo Sayati Bandung</p>
    <hr>
    <p>Nama: <?= htmlspecialchars($data['nama_pelanggan']) ?></p>
    <p>Tanggal: <?= htmlspecialchars($data['tanggal']) ?></p>
    <p>Kasir: <?= htmlspecialchars($data['nama_kasir']) ?></p>
    <?php if (!empty($data['catatan'])): ?>
        <p>Catatan: <?= htmlspecialchars($data['catatan']) ?></p>
    <?php endif; ?>
    <hr>
    <?php 
    $total = 0;
    while ($menu = mysqli_fetch_assoc($menuResult)) : 
        $subtotal = $menu['jumlah'] * $menu['harga_saat_transaksi'];
        $total += $subtotal;
    ?>
        <p><?= $menu['nama_menu'] ?> (<?= $menu['jumlah'] ?>)</p>
        <p>Rp<?= number_format($menu['harga_saat_transaksi'], 0, ',', '.') ?> x <?= $menu['jumlah'] ?> = Rp<?= number_format($subtotal, 0, ',', '.') ?></p>
    <?php endwhile; ?>
        <hr>
    <p><strong>Subtotal: Rp<?= number_format($total, 0, ',', '.') ?></strong></p>

    <?php 
    $diskon = isset($data['diskon']) ? (int)$data['diskon'] : 0;
    $totalSetelahDiskon = $total - $diskon;
    ?>
    <?php if ($diskon > 0): ?>
        <p>Diskon: Rp<?= number_format($diskon, 0, ',', '.') ?></p>
        <p><strong>Total: Rp<?= number_format($totalSetelahDiskon, 0, ',', '.') ?></strong></p>
    <?php endif; ?>

    <p>Bayar: Rp<?= number_format($data['bayar'], 0, ',', '.') ?></p>
    <p>Kembali: Rp<?= number_format($data['kembali'], 0, ',', '.') ?></p>
    <?php
    // Jika pelanggan adalah member, tampilkan poin
    if (!empty($data['id_pelanggan'])) {
        $poinResult = mysqli_query($conn, "SELECT poin FROM special_members WHERE id_member = '{$data['id_pelanggan']}'");
        $poinData = mysqli_fetch_assoc($poinResult);
        echo "<p>Poin saat ini: " . (int)$poinData['poin'] . "</p>";
    }
    ?>


    <p style="text-align:center;">Terima kasih telah berbelanja 🍰</p>
    <div class="qr">
        <img src="<?= $qrPath ?>" width="60" height="60" alt="QR Code">
    </div>
</body>
</html>
