<?php
include '../../../../config/koneksi.php';

$id = $_POST['id_member'];
$nama = $_POST['nama_member'];
$nomor_hp = $_POST['nomor_hp'];
$tingkatan = $_POST['tingkatan'];

$query = "UPDATE special_members SET 
          nama_member = '$nama',
          nomor_hp = '$nomor_hp',
          tingkatan = '$tingkatan'
          WHERE id_member = $id";

if (mysqli_query($conn, $query)) {
    header("Location: ../../pages/tables/basic-table-datamember.php?update=success");
} else {
    echo "Gagal update member: " . mysqli_error($conn);
}
