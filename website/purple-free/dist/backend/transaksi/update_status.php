<?php
include '../../../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $allowed_statuses = ['Belum Diproses', 'Sedang Disiapkan', 'Siap Diambil', 'Selesai'];

    if ($id > 0 && in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $stmt->close();

        header('Location: ../../backend/transaksi/antrian.php');
        exit();
    } else {
        echo "Status tidak valid atau ID kosong.";
    }
} else {
    echo "Akses tidak diizinkan.";
}
?>
