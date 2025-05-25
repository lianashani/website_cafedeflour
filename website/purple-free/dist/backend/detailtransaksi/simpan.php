<?php
include '../../config/koneksi.php';

// Ambil data dari form
$id_detail = $_POST['id_detail'];
$nobon = $_POST['nobon'];
$kd_makanan = $_POST['kd_makanan'];
$jumlah = $_POST['jumlah'];

// Inisialisasi total transaksi
$total_transaksi = 0;

// Ambil total transaksi dari database
$query_total_transaksi = "SELECT total FROM transaksi WHERE nobon = '$nobon'";
$result_total_transaksi = mysqli_query($conn, $query_total_transaksi);
$row_total_transaksi = mysqli_fetch_assoc($result_total_transaksi);
$total_transaksi = $row_total_transaksi['total'];

// Loop untuk setiap item makanan
foreach ($kd_makanan as $key => $value) {
    // Ambil harga dari database berdasarkan kode makanan
    $harga_query = mysqli_query($conn, "SELECT harga FROM makanan WHERE kd_makanan = '$value'");
    if ($row_harga = mysqli_fetch_assoc($harga_query)) {
        $harga = $row_harga['harga'];
        
        // Hitung subtotal untuk item ini
        $subtotal = $harga * $jumlah[$key];
        
        // Tambahkan subtotal ke total transaksi
        $total_transaksi += $subtotal;

        // Insert data detail transaksi
        $query_insert = "INSERT INTO detail_transaksi (id_detail, nobon, kd_makanan, harga, jumlah) VALUES ('$id_detail','$nobon','$value','$harga','$jumlah[$key]')";
        $sql = mysqli_query($conn, $query_insert);
        if (!$sql) {
            echo "
            <script>
            alert('Gagal menyimpan detail transaksi');  
            window.location='../?menu=19';
            </script>
            ";
            exit();
        }
    } else {
        echo "
        <script>
        alert('Kode Makanan tidak valid');
        window.location='../?menu=19';
        </script>
        ";
        exit();
    }
}

// Update total transaksi
$query_update_total = "UPDATE transaksi SET total = '$total_transaksi' WHERE nobon = '$nobon'";
$sql_update_total = mysqli_query($conn, $query_update_total);

if ($sql_update_total) {
    echo "
    <script>
    alert('Data Berhasil di Simpan');
    window.location='../?menu=19';
    </script>
    ";
} else {
    echo "
    <script>
    alert('Gagal menyimpan total transaksi');
    window.location='../?menu=19';
    </script>
    ";
}
?>
