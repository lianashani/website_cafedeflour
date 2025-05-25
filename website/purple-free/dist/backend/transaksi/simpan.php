<?php
session_start();
include '../../../../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $menus = $_POST['menu'];
  $tanggal = date('Y-m-d');
  $waktu = date('H:i:s');
  $nama_kasir = $_SESSION['nama_kasir'];
  $kasir = 'Web Customer'; // untuk transaksi dari pelanggan langsung

  // Ambil id_kasir
  $id_kasir_query = mysqli_query($conn, "SELECT id_kasir FROM kasir WHERE nama_kasir = '$nama_kasir'");
  $id_kasir_data = mysqli_fetch_assoc($id_kasir_query);
  $id_kasir = $id_kasir_data['id_kasir'];

  // Penanganan data pelanggan
  $id_pelanggan = !empty($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : null;
  $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
  $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
  $bayar = $_POST['bayar'];

  $totalHarga = 0;
  $kode_transaksi = 'TRX' . time();

  // Hitung total harga
  foreach ($menus as $menu) {
    $id_menu = $menu['id_menu'];
    $jumlah = $menu['jumlah'];

    $hargaQuery = mysqli_query($conn, "SELECT harga FROM menu WHERE id_menu = $id_menu");
    $hargaData = mysqli_fetch_assoc($hargaQuery);
    $harga = $hargaData['harga'];

    $totalHarga += $harga * $jumlah;
  }

  $kembali = $bayar - $totalHarga;
  if ($kembali < 0) $kembali = 0;

  // Simpan transaksi
  $insertTransaksi = "INSERT INTO transaksi 
    (kode_transaksi, tanggal, waktu, total_harga, bayar, kembali, id_kasir, id_pelanggan, catatan, lokasi, status) 
    VALUES (
      '$kode_transaksi', 
      '$tanggal', 
      '$waktu', 
      $totalHarga, 
      $bayar, 
      $kembali, 
      $id_kasir, 
      " . ($id_pelanggan === null ? "NULL" : $id_pelanggan) . ", 
      '$catatan', 
      '$lokasi', 
      'Belum Diproses'
    )";
  mysqli_query($conn, $insertTransaksi);

  $id_transaksi = mysqli_insert_id($conn);

  // Simpan detail transaksi
  foreach ($menus as $menu) {
    $id_menu = $menu['id_menu'];
    $jumlah = $menu['jumlah'];

    $hargaQuery = mysqli_query($conn, "SELECT harga FROM menu WHERE id_menu = $id_menu");
    $hargaData = mysqli_fetch_assoc($hargaQuery);
    $harga = $hargaData['harga'];

    $insertDetail = "INSERT INTO transaksi_detail (id_transaksi, id_menu, jumlah, harga_saat_transaksi)
                     VALUES ($id_transaksi, $id_menu, $jumlah, $harga)";
    mysqli_query($conn, $insertDetail);
  }

  // Proses update special_members jika ada id_pelanggan
  if ($id_pelanggan !== null) {
    // Cek apakah member sudah ada di special_members
    $cek = mysqli_query($conn, "SELECT * FROM special_members WHERE id_member = '$id_pelanggan'");
    if (mysqli_num_rows($cek) === 0) {
      // Jika belum ada, buat baris awal
      mysqli_query($conn, "INSERT INTO special_members (id_member, total_transaksi, poin, tingkatan) 
        VALUES ('$id_pelanggan', 0, 0, 'Bronze')");
    }

    // Update total transaksi
    mysqli_query($conn, "UPDATE special_members 
      SET total_transaksi = COALESCE(total_transaksi, 0) + $totalHarga 
      WHERE id_member = '$id_pelanggan'");

    // Update tingkatan
    mysqli_query($conn, "
      UPDATE special_members
      SET tingkatan = CASE
        WHEN total_transaksi >= 300000 THEN 'Platinum'
        WHEN total_transaksi >= 200000 THEN 'Gold'
        WHEN total_transaksi >= 100000 THEN 'Silver'
        ELSE 'Bronze'
      END
      WHERE id_member = '$id_pelanggan'
    ");

    // Tambah 20 poin tetap setiap transaksi
      $poinTambahan = 20;
      mysqli_query($conn, "UPDATE special_members 
        SET poin = COALESCE(poin, 0) + $poinTambahan 
        WHERE id_member = $id_pelanggan");

  }

  header("Location: ../../pages/charts/chartjs.php?sukses=1");
  exit;
}
?>
