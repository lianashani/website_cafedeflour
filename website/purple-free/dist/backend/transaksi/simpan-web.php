<?php
session_start();
include '../../../../../config/koneksi.php';

// Check if this is a member order
$is_member_order = isset($_SESSION['member_order_data']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $is_member_order) {
    // Use member order data if available, otherwise use POST data
    if ($is_member_order) {
        $order_data = $_SESSION['member_order_data'];
        $menus = $order_data['menu'];
        $nama_pelanggan = $order_data['nama_pelanggan'];
        $lokasi = $order_data['lokasi'];
        $catatan = $order_data['catatan'];
        $metode_pembayaran = $order_data['metode'];
        $bayar = $order_data['bayar'];
        $id_pelanggan = $order_data['id_pelanggan'];
        $kode_promo = $order_data['kode_promo'];
        $member_id = $order_data['member_id'] ?? null;
        $member_discount = $order_data['member_discount'] ?? 0;
        $original_total = $order_data['original_total'] ?? 0;
        
        // Clear the session data
        unset($_SESSION['member_order_data']);
    } else {
        // Regular web order processing
        if (!isset($_POST['menu']) || !is_array($_POST['menu']) || count($_POST['menu']) === 0) {
            $_SESSION['error'] = 'Menu tidak boleh kosong.';
            header('Location: ../index.php');
            exit;
        }
        
        $menus = $_POST['menu'];
        $id_pelanggan = isset($_POST['id_pelanggan']) && $_POST['id_pelanggan'] !== '' ? intval($_POST['id_pelanggan']) : null;
        $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan'] ?? 'Pelanggan');
        $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi'] ?? 'Tidak Diketahui');
        $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
        $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode'] ?? 'Tunai');
        $bayar = isset($_POST['bayar']) ? intval($_POST['bayar']) : 0;
        $kode_promo = strtoupper($_POST['kode_promo'] ?? '');
        $member_id = null;
        $member_discount = 0;
        $original_total = 0;
    }

    $tanggal = date('Y-m-d');
    $waktu = date('H:i:s');
    $nama_kasir = $_SESSION['nama_kasir'] ?? 'Web Customer';
    $kasir = 'Web Customer';

    $id_kasir = null;
    if ($nama_kasir !== 'Web Customer') {
        $kasir_result = mysqli_query($conn, "SELECT id_kasir FROM kasir WHERE nama_kasir = '" . mysqli_real_escape_string($conn, $nama_kasir) . "'");
        if ($kasir_data = mysqli_fetch_assoc($kasir_result)) {
            $id_kasir = $kasir_data['id_kasir'];
            $kasir = $nama_kasir;
        }
    }

    if (!$is_member_order && $bayar <= 0) {
        $_SESSION['error'] = 'Jumlah pembayaran tidak valid.';
        header('Location: ../index.php');
        exit;
    }

    $kode_transaksi = ($is_member_order ? 'MBR' : 'TRX') . time();
    $total_harga = $is_member_order ? $original_total : 0;
    $detail_menu = [];

    foreach ($menus as $menu) {
        if (!isset($menu['id_menu'], $menu['jumlah'])) continue;

        $id_menu = intval($menu['id_menu']);
        $jumlah = intval($menu['jumlah']);
        if ($jumlah <= 0) continue;

        $menuQuery = mysqli_query($conn, "SELECT nama_menu, harga, stok FROM menu WHERE id_menu = $id_menu");
        if (!$menuData = mysqli_fetch_assoc($menuQuery)) {
            $_SESSION['error'] = "Menu dengan ID $id_menu tidak ditemukan.";
            if ($is_member_order) {
                $_SESSION['member_order_error'] = $_SESSION['error'];
                header('Location: /member_dashboard/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        }

        // Cek stok
        $stok = (int) $menuData['stok'];
        if ($stok < $jumlah) {
            $_SESSION['error'] = "Stok untuk {$menuData['nama_menu']} tidak mencukupi. Tersisa: $stok.";
            if ($is_member_order) {
                $_SESSION['member_order_error'] = $_SESSION['error'];
                header('Location: /member_dashboard/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        }

        $nama_menu = $menuData['nama_menu'];
        $harga = intval($menuData['harga']);
        $subTotal = $harga * $jumlah;
        
        if (!$is_member_order) {
            $total_harga += $subTotal;
        }

        $detail_menu[] = [
            'id_menu' => $id_menu,
            'nama_menu' => $nama_menu,
            'harga' => $harga,
            'jumlah' => $jumlah
        ];
    }

    // Handle promo codes for non-member orders
    $diskon = $is_member_order ? $member_discount : 0;
    
    if (!$is_member_order && $kode_promo) {
        $cek = mysqli_query($conn, "
            SELECT * FROM kode_promo
            WHERE kode = '$kode_promo'
              AND aktif = 1
              AND (CURDATE() BETWEEN tanggal_mulai AND tanggal_akhir)
            LIMIT 1
        ");
        if ($row = mysqli_fetch_assoc($cek)) {
            $diskon = ($row['jenis'] === 'persen')
                ? floor($total_harga * $row['nilai'] / 100)
                : $row['nilai'];
        }
    }

    $total_setelah_diskon = max($total_harga - $diskon, 0);
    $kembali = max($bayar - $total_setelah_diskon, 0);
    $status_awal = ($kasir === 'Web Customer') ? 'Belum Diproses' : 'Diproses';

    // Simpan transaksi
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
            '$nama_pelanggan', " . ($kode_promo ? "'$kode_promo'" : "NULL") . ", $diskon,
            $total_setelah_diskon
        )
    ";

    if (!mysqli_query($conn, $insertTransaksi)) {
        $_SESSION['error'] = 'Gagal menyimpan transaksi: ' . mysqli_error($conn);
        if ($is_member_order) {
            $_SESSION['member_order_error'] = $_SESSION['error'];
            header('Location: /member_dashboard/dashboard.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    }

    $id_transaksi = mysqli_insert_id($conn);

    // Simpan detail transaksi dan kurangi stok
    foreach ($detail_menu as $item) {
        $id_menu = $item['id_menu'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];

        $insertDetail = "INSERT INTO transaksi_detail (id_transaksi, id_menu, jumlah, harga_saat_transaksi)
                         VALUES ($id_transaksi, $id_menu, $jumlah, $harga)";
        mysqli_query($conn, $insertDetail);

        // Kurangi stok menu
        mysqli_query($conn, "UPDATE menu SET stok = stok - $jumlah WHERE id_menu = $id_menu");
    }

    // Update member points and spending if this is a member order
    if ($is_member_order && $member_id) {
        $points_earned = floor($total_setelah_diskon / 1000);
        
        // Update member data using mysqli
        $update_member = "UPDATE special_members SET 
                         poin = poin + $points_earned, 
                         total_transaksi = total_transaksi + $total_setelah_diskon 
                         WHERE id_member = $member_id";
        mysqli_query($conn, $update_member);
        
        // Check for tier upgrade
        $member_query = mysqli_query($conn, "SELECT * FROM special_members WHERE id_member = $member_id");
        $member_data = mysqli_fetch_assoc($member_query);
        $new_total = $member_data['total_transaksi'];
        $current_tier = $member_data['tingkatan'];
        $new_tier = $current_tier;
        
        if ($new_total >= 1000000 && $current_tier !== 'Platinum') {
            $new_tier = 'Platinum';
        } elseif ($new_total >= 500000 && in_array($current_tier, ['Bronze', 'Silver'])) {
            $new_tier = 'Gold';
        } elseif ($new_total >= 100000 && $current_tier === 'Bronze') {
            $new_tier = 'Silver';
        }
        
        if ($new_tier !== $current_tier) {
            mysqli_query($conn, "UPDATE special_members SET tingkatan = '$new_tier' WHERE id_member = $member_id");
            $tier_upgrade_message = " Congratulations! You've been upgraded to " . $new_tier . " tier!";
        } else {
            $tier_upgrade_message = "";
        }
    }

    // Simpan untuk struk
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
        'status_pesanan' => $status_awal,
        'is_member_order' => $is_member_order,
        'points_earned' => $is_member_order ? floor($total_setelah_diskon / 1000) : 0
    ];

    if ($is_member_order) {
        // Redirect back to member dashboard with success message
        $_SESSION['member_order_success'] = "Order placed successfully! Transaction code: " . $kode_transaksi . 
                                          ". You earned " . floor($total_setelah_diskon / 1000) . " points!" . 
                                          ($tier_upgrade_message ?? "");
        header('Location: /member_dashboard/dashboard.php');
        exit;
    } else {
        header('Location: ../../pages/struk/struk-web.php');
        exit;
    }
}
?>
