<div class="content-wrapper">
    <section class="content">
        <div class="containter-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h2>Halaman Data Detail</h2> 
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#TambahData">
                                </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <th>ID Detail</th>
                                        <th>No Bon</th>
                                        <th>Kode Makanan</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include '../config/koneksi.php';
                                        $query=mysqli_query($conn, 'SELECT * FROM detail_transaksi');
                                        while($row=mysqli_fetch_assoc($query)){
                                            
                                            ?>
                                                <tr>
                                                    <td><?php echo $row['id_detail'];?></td>
                                                    <td><?php echo $row['nobon'];?></td>
                                                    <td><?php echo $row['kd_makanan'];?></td>
                                                    <td><?php echo 'Rp ' . number_format($row['harga'], 0, ',', '.');?></td>
                                                    <td><?php echo $row['jumlah'];?></td>
                                                    <td><a href="?menu=19&kd=<?php echo $row['id_detail'];?>" class="btn btn-warning">Edit</a>
                                                    <a href="detailtransaksi/delete.php?kd=<?php echo $row['id_detail'];?>" class="btn btn-danger">Hapus</a>   </td>
                                                </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

