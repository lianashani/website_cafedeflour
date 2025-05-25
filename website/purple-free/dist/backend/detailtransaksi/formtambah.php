    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <br>
                        <div class="card">
                            <div class="card-header">
                                <h2>Form Tambah Data Detail</h2>
                            </div>
                            <div class="card-body">
                                <form action="detailtransaksi/simpan.php" method="POST" enctype="multipart/form-data">
                                    <?php include '../config/autonumber.php'; ?>
                                    <div class="mb-3">
                                        <label for="" class="form-label">Id Detail</label>
                                        <input 
                                            type="hidden" 
                                            class="form-control" 
                                            name="id_detail" 
                                            value="<?php echo autonumber('detail_transaksi','id_detail',2,'D') ?>"
                                        />
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            name="id_detail" 
                                            value="<?php echo autonumber('detail_transaksi','id_detail',2,'D') ?>" 
                                            readonly
                                        />
                                    </div>
                                    <div class="mb-3">
                                        <label for="" class="form-label">NOBON</label>
                                        <select name="nobon" id="nobon" class="form-control" required>
                                            <option value="">Pilih Transaksi</option>
                                            <?php
                                            include '../config/koneksi.php';
                                            $q2 = mysqli_query($conn, "SELECT * FROM transaksi");
                                            while ($d2 = mysqli_fetch_assoc($q2)) {
                                                echo "<option value='{$d2['nobon']}'>{$d2['nobon']} - {$d2['atas_nama']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div id="makananFields">
                                        <div class="form-row">
                                            <div class="col">
                                                <label for="kd_makanan">Kode Makanan</label>
                                                <select name="kd_makanan[]" class="form-control kd_makanan" required>
                                                    <option value="">Pilih Makanan</option>
                                                    <?php
                                                    $q2 = mysqli_query($conn, "SELECT * FROM makanan");
                                                    while ($d2 = mysqli_fetch_assoc($q2)) {
                                                        echo "<option value='{$d2['kd_makanan']}' data-harga='{$d2['harga']}'>{$d2['kd_makanan']} - {$d2['nama']}</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <label for="harga">Harga</label>
                                                <input type="text" class="form-control harga" name="harga[]" odrequired readonly>
                                            </div>
                                            <div class="col">
                                                <label for="jumlah">Jumlah</label>
                                                <input type="number" class="form-control" name="jumlah[]" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                                <label for="total" class="form-label">Total</label>
                                                <input type="text" class="form-control" id="total" name="total" readonly>
                                            </div>
                                    </div>
                                    <div style="margin-top: 20px;">
                                        <button class="btn btn-primary" type="submit">Simpan Data</button>
                                        <button class="btn btn-secondary" type="reset">Bersihkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
    function formatRupiah(angka) {
        var numberString = angka.toString();
        var sisa = numberString.length % 3;
        var rupiah = numberString.substr(0, sisa);
        var ribuan = numberString.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return 'Rp ' + rupiah;
    }

    function attachChangeEventToSelect(selectElement) {
        selectElement.addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            var harga = selectedOption.getAttribute('data-harga');
            var hargaInput = this.closest('.form-row').querySelector('.harga');
            hargaInput.value = formatRupiah(harga);
        });
    }

    document.querySelectorAll('.kd_makanan').forEach(function (selectElement) {
        attachChangeEventToSelect(selectElement);
    });

    // Mendapatkan semua elemen input dengan class "harga" dan "jumlah"
    var inputs = document.querySelectorAll('.harga, .jumlah');

    // Menambahkan event listener untuk setiap input
    inputs.forEach(function (input) {
        input.addEventListener('input', hitungTotal);
    });

    // Fungsi untuk menghitung total pembelian
    function hitungTotal() {
    var total = 0;

    // Menggunakan querySelectorAll karena mungkin ada beberapa baris detail
    var rows = document.querySelectorAll('.form-row');

    // Iterasi melalui setiap baris detail
    rows.forEach(function (row) {
        var harga = row.querySelector('.harga').value.replace(/\D/g, '');
        var jumlah = row.querySelector('.jumlah').value;

        // Jika harga atau jumlah tidak valid, lewati perhitungan untuk baris ini
        if (!harga || !jumlah) return;

        total += parseInt(harga) * parseInt(jumlah);
    });

    // Memformat total dalam format Rupiah
    var formattedTotal = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);

    // Memperbarui nilai input total dengan total yang dihitung
    document.getElementById('total').value = formattedTotal;
}
</script>