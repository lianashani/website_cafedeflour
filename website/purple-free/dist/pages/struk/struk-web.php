<?php
session_start();
if (!isset($_SESSION['bukti'])) {
    echo "Data struk tidak ditemukan.";
    exit;
}

$bukti = $_SESSION['bukti'];
include '../../../../config/koneksi.php';

$kode_transaksi = $bukti['kode_transaksi'] ?? '';
$subtotal = $bukti['subtotal'] ?? array_sum(array_map(fn($m) => $m['harga'] * $m['jumlah'], $bukti['menu']));
$diskon = $bukti['diskon'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            color: #000;
        }
        .header, .footer { text-align: center; }
        .header h2 { margin: 0; }
        .logo { max-width: 60px; margin-bottom: 8px; }
        .line { border-top: 1px dashed #000; margin: 8px 0; }
        .item { display: flex; justify-content: space-between; font-size: 14px; }
        .item-name { flex: 1; }
        .item-qty { text-align: center; width: 30px; }
        .item-price { text-align: right; width: 60px; }
        .total-section { margin-top: 10px; font-size: 14px; }
        .total-section div { display: flex; justify-content: space-between; }
        .info, .thanks, .contact { font-size: 12px; margin-top: 10px; text-align: center; }
        .qr { text-align: center; margin: 10px 0; }
        .qr img { width: 80px; }
        button { margin-top: 15px; width: 100%; padding: 8px; background: black; color: white; border: none; cursor: pointer; }
        .download-link { display: block; text-align: center; margin-top: 5px; font-size: 12px; }
        @media print { button, .download-link, .btn-info { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <img src="/assets/img/logo.png" class="logo" alt="Logo Café">
        <h2>Café De Flour</h2>
        <div><?= $bukti['lokasi'] ?></div>
        <div><?= $bukti['tanggal'] ?></div>
        <div><?= $kode_transaksi ?></div>
    </div>

    <div class="line"></div>

    <div>
        <?php foreach ($bukti['menu'] as $item): ?>
            <div class="item">
                <div class="item-name"><?= $item['nama_menu'] ?></div>
                <div class="item-qty">x<?= $item['jumlah'] ?></div>
                <div class="item-price">Rp<?= number_format($item['harga'] * $item['jumlah']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="line"></div>

    <div class="total-section">
        <div><span>Subtotal</span><span>Rp<?= number_format($subtotal) ?></span></div>
        <div><span>Diskon</span><span>-Rp<?= number_format($diskon) ?></span></div>
        <div><span>Total</span><span>Rp<?= number_format($bukti['total']) ?></span></div>
        <div><span>Bayar</span><span>Rp<?= number_format($bukti['bayar']) ?></span></div>
        <div><span>Kembali</span><span>Rp<?= number_format($bukti['kembali']) ?></span></div>
    </div>

    <div class="line"></div>

    <div class="info">
        Metode: <?= $bukti['metode'] ?><br>
        Nama: <?= $bukti['nama'] ?><br>
        Catatan: <?= $bukti['catatan'] ?><br>
        Status: <?= $bukti['status_pesanan'] ?>
    </div>

    <div class="qr">
        <?php $qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($kode_transaksi) . "&size=100x100"; ?>
        <img src="<?= $qr_url ?>" alt="QR Code">
        <a href="download_qr.php?kode=<?= urlencode($kode_transaksi) ?>" class="download-link">
            ⬇️ Download QR Code
        </a>
    </div>

    <div class="thanks">
        ☕ Terima kasih atas pesanan Anda!<br>
        Silahkan datang ke outlet terdekat dan tunjukkan bukti pemesanan
        ini untuk mengambil pesanan Anda.<br>
        <strong>Jangan lupa untuk memberikan ulasan!</strong>
    </div>

    <div class="contact">
        IG: @cafedeflour | WA: 089636203894    </div>

    <div class="footer">
        <small>Powered by Café De Flour System</small>
    </div>

    <button onclick="window.print()">🖨 Cetak Struk</button>
    <p>
        <a href="../../backend/transaksi/tracking.php?kode=<?= urlencode($kode_transaksi) ?>" 
            class="btn btn-sm btn-info mt-2">
            🔍 Lacak Pesanan Ini
        </a>
    </p>

    <?php unset($_SESSION['bukti']); ?>
</body>
</html>
