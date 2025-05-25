<?php
include '../../../../config/koneksi.php';

$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$nomor = $_POST['nomor_pelanggan'];

mysqli_query($conn, "INSERT INTO pelanggan (nama, alamat, nomor_pelanggan) VALUES ('$nama', '$alamat', '$nomor')");
header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-datamember.php");
