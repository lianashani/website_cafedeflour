<?php
include '../../config/koneksi.php';

$id_detail = $_GET['kd'];

// Query untuk menghapus data detail transaksi berdasarkan id_detail
$query = "DELETE FROM detail_transaksi WHERE id_detail = '$id_detail'";

$sql = mysqli_query($conn, $query);

if($sql){
    echo "
    <script>
    alert('Data Berhasil di Hapus');
    window.location='../?menu=19';
    </script>
    ";
}else{
    echo "
    <script>
    alert('Data Gagal di Hapus');
    window.location='../?menu=19';
    </script>
    ";
}
?>
