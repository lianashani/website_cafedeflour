<?php
if (!isset($_GET['kode'])) {
    exit("Kode tidak ditemukan.");
}

$kode = urlencode($_GET['kode']);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?data={$kode}&size=100x100";

// Ambil gambar dari API
$qr_image = file_get_contents($qr_url);

if ($qr_image === false) {
    exit("Gagal mengambil QR Code.");
}

// Kirim header agar file langsung diunduh
header('Content-Type: image/png');
header("Content-Disposition: attachment; filename=\"qr-{$kode}.png\"");
echo $qr_image;
