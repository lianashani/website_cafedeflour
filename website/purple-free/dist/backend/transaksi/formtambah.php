<?php

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['nama_kasir'])) {
    echo "Kasir belum login, silakan login terlebih dahulu!";
    exit;
}

include '../../../../../config/koneksi.php';

$menuQuery = "SELECT * FROM menu";
$menuResult = mysqli_query($conn, $menuQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan = !empty($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : 'NULL';
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $tanggal = date('Y-m-d');
    $waktu = date('H:i:s');
    $id_menu = $_POST['id_menu'];
    $jumlah = $_POST['jumlah'];
    $bayar = $_POST['bayar'];
    $nama_kasir = $_SESSION['nama_kasir'];

    $hargaQuery = "SELECT harga, nama_menu FROM menu WHERE id_menu = $id_menu";
    $hargaResult = mysqli_query($conn, $hargaQuery);
    $menuData = mysqli_fetch_assoc($hargaResult);
    $harga = $menuData['harga'];

    $total = $harga * $jumlah;
    $kembali = $bayar - $total;
    if ($kembali < 0) $kembali = 0;

    $query = "INSERT INTO transaksi (tanggal, waktu, id_menu, jumlah, total_harga, bayar, kembali, nama_kasir, id_pelanggan, nama_pelanggan, catatan, lokasi) 
    VALUES ('$tanggal', '$waktu', $id_menu, $jumlah, $total, $bayar, $kembali, '$nama_kasir', " . ($id_pelanggan === 'NULL' ? "NULL" : $id_pelanggan) . ", '$nama_pelanggan', '$catatan', '$lokasi')";

    if (mysqli_query($conn, $query)) {
        $last_id = mysqli_insert_id($conn);
        header("Location: transaksi.php?sukses=1&id=$last_id");
        exit;
    } else {
        echo "Gagal menambahkan transaksi: " . mysqli_error($conn);
    }
}
