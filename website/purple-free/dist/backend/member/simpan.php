<?php
include '../../../../config/koneksi.php';

$nama = $_POST['nama_member'];
$nomor = $_POST['nomor_hp'];

mysqli_query($conn, "INSERT INTO special_members (nama_member, nomor_hp) VALUES ('$nama', '$nomor')");
header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-datamember.php");
