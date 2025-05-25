<?php
include '../../../../config/koneksi.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM pelanggan WHERE id_pelanggan = $id");
header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-datamember.php");
