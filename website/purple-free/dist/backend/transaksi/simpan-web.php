<?php
session_start();
include '../../../../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi menu
    if (!isset($_POST['menu']) || !is_array($_POST['menu']) || count($_POST['menu']) === 0) {
        $_SESSION['error'] = 'Menu tidak boleh kosong.';
        header('Location: ../index.php');
        exit;
    }

    $menus = $_POST['menu'];
    $tanggal = date('Y-m-d');
    $waktu = date('H:i:s');
    $nama_kasir = $_SESSION['nama_kasir'] ?? 'Web Customer';
    $kasir = 'Web Customer';

    // Cari id_kasir jika bukan Web Customer
    $id_kasir = null;
    if ($nama_kasir !== 'Web Customer') {
        $kasir_result = mysqli_query($conn, "SELECT id_kasir FROM kasir WHERE nama_kasir = '" . mysqli_real_escape_string($conn, $nama_kasir) . "'");
        if ($kasir_data = mysqli_fetch_assoc($kasir_result)) {
            $id_kasir = $kasir_data['id_kasir'];
            $kasir = $nama_kasir;
        }
    }

    $id_pelanggan = isset($_POST['id_pelanggan']) && $_POST['id_pelanggan'] !== '' ? intval($_POST['id_pelanggan']) : null;
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan'] ?? 'Pelanggan');
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi'] ?? 'Tidak Diketahui');
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
    $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode'] ?? 'Tunai');
    $bayar = isset($_POST['bayar']) ? intval($_POST['bayar']) : 0;

    if ($bayar <= 0) {
        $_SESSION['error'] = 'Jumlah pembayaran tidak valid.';
        header('Location: ../index.php');
        exit;
    }

    $kode_transaksi = 'TRX' . time();
    $total_harga = 0;
    $detail_menu = [];

    // Hitung total harga semua menu
    foreach ($menus as $menu) {
        if (!isset($menu['id_menu'], $menu['jumlah'])) continue;

        $id_menu = intval($menu['id_menu']);
        $jumlah = intval($menu['jumlah']);
        if ($jumlah <= 0) continue;

        $menuQuery = mysqli_query($conn, "SELECT nama_menu, harga FROM menu WHERE id_menu = $id_menu");
        if (!$menuData = mysqli_fetch_assoc($menuQuery)) {
            $_SESSION['error'] = "Menu dengan ID $id_menu tidak ditemukan.";
            header('Location: ../index.php');
            exit;
        }

        $nama_menu = $menuData['nama_menu'];
        $harga = intval($menuData['harga']);
        $subTotal = $harga * $jumlah;
        $total_harga += $subTotal;

        $detail_menu[] = [
            'id_menu' => $id_menu,
            'nama_menu' => $nama_menu,
            'harga' => $harga,
            'jumlah' => $jumlah
        ];
    }

    // Cek dan terapkan diskon jika ada kode promo
    $kode_promo = strtoupper($_POST['kode_promo'] ?? '');
    $diskon = 0;

    if ($kode_promo) {
        $cek = mysqli_query($conn, "
            SELECT * FROM kode_promo
            WHERE kode = '$kode_promo'
              AND aktif = 1
              AND (CURDATE() BETWEEN tanggal_mulai AND tanggal_akhir)
            LIMIT 1
        ");
        if ($row = mysqli_fetch_assoc($cek)) {
            if ($row['jenis'] === 'persen') {
                $diskon = floor($total_harga * $row['nilai'] / 100);
            } else {
                $diskon = $row['nilai'];
            }
        }
    }

    $total_setelah_diskon = max($total_harga - $diskon, 0);
    $kembali = max($bayar - $total_setelah_diskon, 0);
    $status_awal = ($kasir === 'Web Customer') ? 'Belum Diambil' : 'Diproses';

    // Simpan transaksi ke database
    $insertTransaksi = "
        INSERT INTO transaksi (
            kode_transaksi, tanggal, waktu, total_harga, bayar, kembali,
            id_kasir, id_pelanggan, catatan, lokasi, metode_pembayaran,
            status, nama_pelanggan, kode_promo, diskon, total_setelah_diskon
        ) VALUES (
            '$kode_transaksi', '$tanggal', '$waktu', $total_harga, $bayar, $kembali,
            " . ($id_kasir !== null ? $id_kasir : 'NULL') . ",
            " . ($id_pelanggan !== null ? $id_pelanggan : 'NULL') . ",
            '$catatan', '$lokasi', '$metode_pembayaran', '$status_awal',
            '$nama_pelanggan', " . ($kode_promo ? "'$kode_promo'" : "NULL") . ", $diskon ,
            $total_setelah_diskon
        )
    ";

    if (!mysqli_query($conn, $insertTransaksi)) {
        $_SESSION['error'] = 'Gagal menyimpan transaksi: ' . mysqli_error($conn);
        header('Location: ../index.php');
        exit;
    }

    $id_transaksi = mysqli_insert_id($conn);

    // Simpan detail menu
    foreach ($detail_menu as $item) {
        $id_menu = $item['id_menu'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];

        $insertDetail = "INSERT INTO transaksi_detail (id_transaksi, id_menu, jumlah, harga_saat_transaksi)
                         VALUES ($id_transaksi, $id_menu, $jumlah, $harga)";
        mysqli_query($conn, $insertDetail);
    }

    // Simpan ke session untuk ditampilkan di struk
    $_SESSION['bukti'] = [
        'kode_transaksi' => $kode_transaksi,
        'nama' => $nama_pelanggan,
        'tanggal' => "$tanggal $waktu",
        'metode' => $metode_pembayaran,
        'lokasi' => $lokasi,
        'catatan' => $catatan,
        'bayar' => $bayar,
        'total' => $total_setelah_diskon,
        'kembali' => $kembali,
        'diskon' => $diskon,
        'menu' => $detail_menu,
        'instruksi' => 'Silakan datang ke outlet dan tunjukkan bukti ini.',
        'status_pesanan' => $status_awal
    ];

    // Redirect ke struk
    header('Location: ../../pages/struk/struk-web.php');
    exit;
}
?>
