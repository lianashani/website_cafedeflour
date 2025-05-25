<?php
include '../config/koneksi.php';
$kd = $_GET['kd'];
$query = mysqli_query($conn, "SELECT * FROM makanan WHERE kd_makanan = '$kd'");
while($row = mysqli_fetch_assoc($query)){
?>
<div class="content-wrapper">
    <section class="content">
        <div class="containter-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h2>Form Edit Data Makanan</h2> 
                          
                        </div>
                        <div class="card-body">
                           <form action="makanan/update.php" method="POST">
                            <div class="mb-3">
                                <label for="" class="form-label">Kode Makanan</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="kd_makanan"
                                    value="<?php echo $row['kd_makanan'];?>"
                                />
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label">Nama</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="nama"
                                    value="<?php echo $row['nama'];?>"
                                />
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label">Jenis</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="jenis"
                                    value="<?php echo $row['jenis'];?>"
                                />
                            </div>


                            <button class="btn btn-primary" type="submit">Simpan Data</button>
                            <button class="btn btn-secondary" type="reset">Bersihkan</button>
                           </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
}
?>