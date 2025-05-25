<?php
// File: deletekasir.php
include '../../../../config/koneksi.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM kasir WHERE id_kasir = $id");
header("Location: ../../pages/tables/basic-table-kasir.php");
exit;
?>
