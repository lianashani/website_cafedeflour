<?php
include '../../../../../config/koneksi.php';

if (!empty($_GET['kategori'])) {
    $kategori = mysqli_real_escape_string($conn, $_GET['kategori']);
    $query = "SELECT id_menu, nama_menu FROM menu WHERE kategori = '$kategori'";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<label><input type="checkbox" name="id_menu[]" value="' . $row['id_menu'] . '"> ' . $row['nama_menu'] . '</label><br>';
    }
}
?>
