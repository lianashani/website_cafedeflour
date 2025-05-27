<?php
include '../../../../config/koneksi.php';

$id = $_GET['id'];

// $id adalah angka untuk mencegah SQL injection dasar
$id = intval($id);

mysqli_query($conn, "DELETE FROM special_members WHERE id_member = $id");

header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-datamember.php");
exit;
