<?php
include '../../../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kasir = $_POST['nama_kasir'];
    $alamat = $_POST['alamat'];
    $password = $_POST['password'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    $query = "INSERT INTO kasir (nama_kasir, alamat, password, jenis_kelamin) 
              VALUES ('$nama_kasir', '$alamat', '$password', '$jenis_kelamin')";

    if (mysqli_query($conn, $query)) {
        header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-kasir.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
} else {
    echo "Metode tidak diizinkan.";
}
?>
