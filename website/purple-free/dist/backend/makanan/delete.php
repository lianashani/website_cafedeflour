<?php
include '../../../../config/koneksi.php';

$id_menu = $_GET['id'];

$query = "DELETE from menu
WHERE id_menu = '$id_menu'";

$sql = mysqli_query($conn, $query);

if($sql){
    echo "
    <script>
    alert('Data Berhasil di Hapus');
    window.location='../../pages/tables/basic-table-daftarmenu.php';
    </script>
    ";
}else{
    echo "
    <script>
    alert('Data Gagal di Hapus');
    window.location='../../pages/tables/basic-table-daftarmenu.php';
    </script>
    ";
}
?>