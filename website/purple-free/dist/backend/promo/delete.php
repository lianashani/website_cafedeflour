<?php
include '../../../../config/koneksi.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM kode_promo WHERE id = $id");

header("Location: ../../pages/tables/basic-table-diskon.php");
exit;
?>
