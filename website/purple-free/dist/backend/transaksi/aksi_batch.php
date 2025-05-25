<?php
session_start();
include '../../../../config/koneksi.php';

if (isset($_POST['hapus_terpilih']) && isset($_POST['kode_transaksi'])) {
    $kodeList = $_POST['kode_transaksi'];

    foreach ($kodeList as $kode) {
        $kode = mysqli_real_escape_string($conn, $kode);

        // Cari id_transaksi dari kode_transaksi
        $result = mysqli_query($conn, "SELECT id_transaksi FROM transaksi WHERE kode_transaksi = '$kode'");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $id_transaksi = $row['id_transaksi'];

            // Hapus transaksi_detail
            mysqli_query($conn, "DELETE FROM transaksi_detail WHERE id_transaksi = $id_transaksi");

            // Hapus transaksi
            mysqli_query($conn, "DELETE FROM transaksi WHERE id_transaksi = $id_transaksi");
        }
    }

    header("Location: ../../pages/charts/chartjs.php?hapus_berhasil=1");
    exit;
} else {
    header("Location: ../../pages/charts/chartjs.php?hapus_berhasil=0");
    exit;
}
