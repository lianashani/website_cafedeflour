<?php
include '../../../../config/koneksi.php';

$id = $_POST['id_pelanggan'];
$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$nomor = $_POST['nomor_pelanggan'];

mysqli_query($conn, "UPDATE pelanggan SET nama='$nama', alamat='$alamat', nomor_pelanggan='$nomor' WHERE id_pelanggan=$id");
header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-datamember.php");
