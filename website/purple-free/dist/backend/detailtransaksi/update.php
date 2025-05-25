<?php
include '../../config/koneksi.php';

$kd_makanan = $_POST['kd_makanan'];
$nama = $_POST['nama'];
$jenis = $_POST['jenis'];
$stok = $_POST['stok'];


$query = "UPDATE makanan SET
nama = '$nama',
jenis = '$jenis',
stok = '$stok'
WHERE kd_makanan = '$kd_makanan'";

$sql = mysqli_query($conn, $query);

if($sql){
    echo "
    <script>
    alert('Data Berhasil di Rubah');
    window.location='../?menu=7';
    </script>
    ";
}else{
    echo "
    <script>
    alert('Data Gagal di Rubah');
    window.location='../?menu=9&kd=$kd_makanan';
    </script>
    ";
}
?>