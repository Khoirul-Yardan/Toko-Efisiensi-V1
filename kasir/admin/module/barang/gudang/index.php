<h4>Data Barang Gudang</h4>
<br />
<?php if(isset($_GET['success-stok'])){?>
<div class="alert alert-success">
    <p>Tambah Stok Berhasil !</p>
</div>
<?php }?>
<?php if(isset($_GET['success'])){?>
<div class="alert alert-success">
    <p>Tambah Data Berhasil !</p>
</div>
<?php }?>
<?php if(isset($_GET['remove'])){?>
<div class="alert alert-danger">
    <p>Hapus Data Berhasil !</p>
</div>
<?php }?>

<!-- Similar warning for low stock -->
<?php 
$sql=" select * from barang where stok_gudang <= 3";
$row = $config -> prepare($sql);
$row -> execute();
$r = $row -> rowCount();
if($r > 0){
    echo "
    <div class='alert alert-warning'>
        <span class='glyphicon glyphicon-info-sign'></span> Ada <span style='color:red'>$r</span> barang yang Stok tersisa sudah kurang dari 3 items. silahkan pesan lagi !!
        <span class='pull-right'><a href='index.php?page=barang/gudang&stok_barang=yes'>Cek Barang <i class='fa fa-angle-double-right'></i></a></span>
    </div>
    ";    
}
?>

<!-- Trigger the modal with a button -->
<a href="index.php?page=barang" class="btn btn-success btn-md mr-2">
<i class="fa fa-back"></i> Back</a>
<button type="button" class="btn btn-primary btn-md mr-2" data-toggle="modal" data-target="#myModal">
    <i class="fa fa-plus"></i> Insert Data</button>
<a href="index.php?page=barang/gudang&stok=yes" class="btn btn-warning btn-md mr-2">
    <i class="fa fa-list"></i> Sortir Stok Kurang</a>
<a href="index.php?page=barang/gudang" class="btn btn-success btn-md">
    <i class="fa fa-refresh"></i> Refresh Data</a>
    <a href="index.php?page=barang/barcode" class="btn btn-info btn-md mr-2">
    <i class="fa fa-barcode"></i> Barcode
</a>

<div class="clearfix"></div>
<br />
<!-- view barang -->
<div class="card card-body" id="barangTable" style="display: none;">
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="example1">
            <thead>
                <tr style="background:#DFF0D8;color:#333;">
                    <th>No.</th>
                    <th>ID Barang</th>
                    <th>Kategori</th>
                    <th>Nama Barang</th>
                    <th>Merk</th>
                    <th>Stok Gudang</th>
                    <th>Stok</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Satuan</th>
                    <th>Tanggal Input</th> <!-- New column for date input -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
<?php 
$totalBeli = 0;
$totalJual = 0;
$totalStok = 0;
if ($_GET['stok'] == 'yes') {
    $hasil = $lihat->barang_stok();
} else {
    $hasil = $lihat->barang();
}
$no = 1;
foreach ($hasil as $isi) {
?>
    <tr>
        <td><?php echo $no; ?></td>
        <td><?php echo $isi['id_barang']; ?></td>
        <td><?php echo $isi['nama_kategori']; ?></td>
        <td><?php echo $isi['nama_barang']; ?></td>
        <td><?php echo $isi['merk']; ?></td>
        <td><?php echo $isi['stok_gudang']; ?></td>
        <td>
            <?php if ($isi['stok'] == '0') { ?>
                <button class="btn btn-danger">Habis</button>
            <?php } else { ?>
                <?php echo $isi['stok']; ?>
            <?php } ?>
        </td>
        <td>Rp.<?php echo number_format($isi['harga_beli']); ?>,-</td>
        <td>Rp.<?php echo number_format($isi['harga_jual']); ?>,-</td>
        <td><?php echo $isi['satuan_barang']; ?></td>
        <td><?php echo date("j F Y, G:i", strtotime($isi['tgl_input'])); ?></td> <!-- Displaying the date input -->
        <td>
            <!-- Tombol Detail -->
            <a href="index.php?page=barang/details&barang=<?php echo $isi['id_barang']; ?>">
                <button class="btn btn-primary btn-xs">Details</button>
            </a>

            <!-- Tombol Edit -->
            <a href="index.php?page=barang/edit&barang=<?php echo $isi['id_barang']; ?>">
                <button class="btn btn-warning btn-xs">Edit</button>
            </a>

            <!-- Tombol Hapus -->
            <a href="fungsi/hapus/hapus.php?barang=hapus&id=<?php echo $isi['id_barang']; ?>"
               onclick="return confirm('Hapus Data barang ?');">
                <button class="btn btn-danger btn-xs">Hapus</button>
            </a>

            <!-- Form Restok -->
            <form method="POST" action="fungsi/edit/edit.php?restok_gudang=edit" style="display:inline-block;">
    <input type="hidden" name="id" value="<?php echo $isi['id_barang']; ?>">
    <input type="number" name="restok" class="form-control" placeholder="Jumlah Restok" required>
    <input type="text" name="faktur" class="form-control" placeholder="Nomor Faktur" required>
    <input type="date" name="tgl_masuk" class="form-control" required>
    <button type="submit" class="btn btn-success btn-xs">Restok Gudang</button>
</form>
        </td>
    </tr>
<?php 
    $no++; 
    $totalBeli += $isi['harga_beli'] * $isi['stok_gudang']; 
    $totalJual += $isi['harga_jual'] * $isi['stok_gudang'];
    $totalStok += $isi['stok_gudang'];
}
?>
</tbody>

            <tfoot>
                <tr>
                    <th colspan="5">Total </th>
                    <th><?php echo $totalStok;?></th>
                    <th></th>
                    <th>Rp.<?php echo number_format($totalBeli);?>,-</th>
                    <th>Rp.<?php echo number_format($totalJual);?>,-</th>
                    <th colspan="2" style="background:#ddd"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<!-- end view barang -->
<!-- tambah barang MODALS-->
<!-- Modal -->

<!-- Modal for Adding New Item -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="border-radius:0px;">
            <div class="modal-header" style="background:#285c64;color:#fff;">
                <h5 class="modal-title"><i class="fa fa-plus"></i> Tambah Barang ke Gudang</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="fungsi/tambah/tambah.php?barang=tambah" method="POST">
                <div class="modal-body">
                    <table class="table table-striped bordered">
                        <?php
                            $format = $lihat->barang_id(); // Assuming this function generates a new ID
                        ?>
                        <tr>
                            <td>ID Barang</td>
                            <td><input type="text" readonly="readonly" required value="<?php echo $format; ?>"
                                    class="form-control" name="id"></td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>
                                <select name="kategori" class="form-control" required>
                                    <option value="#">Pilih Kategori</option>
                                    <?php $kat = $lihat->kategori(); foreach($kat as $isi){ ?>
                                    <option value="<?php echo $isi['id_kategori'];?>">
                                        <?php echo $isi['nama_kategori'];?></option>
                                    <?php }?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Nama Barang</td>
                            <td><input type="text" placeholder="Nama Barang" required class="form-control"
                                    name="nama"></td>
                        </tr>
                        <tr>
                            <td>Merk Barang</td>
                            <td><input type="text" placeholder="Merk Barang" required class="form-control"
                                    name="merk"></td>
                        </tr>
                        <tr>
                            <td>Harga Beli</td>
                            <td><input type="number" placeholder="Harga beli" required class="form-control"
                                    name="beli"></td>
                        </tr>
                        <tr>
                            <td>Harga Jual</td>
                            <td><input type="number" placeholder="Harga Jual" required class="form-control"
                                    name="jual"></td>
                        </tr>
                        <tr>
                            <td>Satuan Barang</td>
                            <td>
                                <select name="satuan" class="form-control" required>
                                    <option value="#">Pilih Satuan</option>
                                    <option value="PCS">PCS</option>
                                    <option value="PACK">PACK</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Stok</td>
                            <td><input type="number" required placeholder="Stok" class="form-control"
                                    name="stok"></td>
                        </tr>
                        <tr>
                            <td>Stok Gudang</td>
                            <td><input type="number" required placeholder="Stok Gudang" class="form-control"
                                    name="stok_gudang"></td>
                        </tr>
                        <tr>
                            <td>Tanggal Input</td>
                            <td><input type="text" required readonly="readonly" class="form-control"
                                    value="<?php echo date("j F Y, G:i");?>" name="tgl"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Insert Data</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

                                    <!--restok faktur-->
                                    <div id="restokModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="border-radius:0px;">
            <div class="modal-header" style="background:#285c64;color:#fff;">
                <h5 class="modal-title"><i class="fa fa-plus"></i> Restok Barang</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="fungsi/edit/edit.php?restok_gudang=edit" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="restokId">
                    <div class="form-group">
                        <label>Jumlah Restok</label>
                        <input type="number" name="restok" class="form-control" placeholder="Jumlah Restok" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Faktur</label>
                        <input type="text" name="faktur" class="form-control" placeholder="Nomor Faktur" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Masuk</label>
                        <input type="date" name="tgl_masuk" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                   <!-- Tombol Restok -->
<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#restokModal" onclick="setRestokId('<?php echo $isi['id_barang']; ?>')">Restok</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Tabel untuk menampilkan informasi restok barang -->
<h4>Informasi Restok Barang</h4>
<div class="card card-body">
    <button class="btn btn-danger mb-3" id="resetButton">Reset Riwayat Restok</button>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="example2">
            <thead>
                <tr style="background:#DFF0D8;color:#333;">
                    <th>No.</th>
                    <th>ID Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Restok</th>
                    <th>Faktur</th>
                    <th>Tanggal Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ambil informasi restok dari database
                $sqlRestock = "SELECT r.id_barang, b.nama_barang, r.jumlah_restok, r.faktur, r.tgl_masuk 
                               FROM riwayat_restok r 
                               JOIN barang b ON r.id_barang = b.id_barang 
                               ORDER BY r.tgl_masuk DESC"; // Sesuaikan query sesuai kebutuhan
                $rowRestock = $config->prepare($sqlRestock);
                $rowRestock->execute();
                $restockData = $rowRestock->fetchAll();

                $no = 1;
                foreach ($restockData as $restock) {
                    echo "<tr>
                            <td>{$no}</td>
                            <td>{$restock['id_barang']}</td>
                            <td>{$restock['nama_barang']}</td>
                            <td>{$restock['jumlah_restok']}</td>
                            <td>{$restock['faktur']}</td>
                            <td>" . date("j F Y", strtotime($restock['tgl_masuk'])) . "</td>
                          </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>

document.getElementById('resetButton').addEventListener('click', function() {
    if (confirm("Apakah Anda yakin ingin menghapus semua riwayat restok?")) {
        // Menggunakan AJAX untuk menghapus riwayat
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fungsi/hapus/hapus.php?reset_riwayat=1", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Jika berhasil, tampilkan alert dan refresh halaman
                alert("Riwayat restok berhasil dihapus.");
                location.reload(); // Refresh halaman
            }
        };
        xhr.send();
    }
});

// Fungsi untuk mengambil data terbaru dan memperbarui tabel
function refreshRestockTable() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "path_to_your_php_script.php", true); // Ganti dengan URL yang sesuai untuk mengambil data
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Update isi tabel dengan data terbaru
            document.getElementById('restockTable').getElementsByTagName('tbody')[0].innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}


document.addEventListener("DOMContentLoaded", function () {
    var unlockButton = document.getElementById("unlockButton");
    var table = document.getElementById("barangTable");
    var correctPassword = "admin123"; // Ganti dengan sandi yang lebih aman

    // Cek status dari localStorage
    if (localStorage.getItem("tableUnlocked") === "true") {
        table.style.display = "block";
        unlockButton.innerHTML = '<i class="fa fa-lock"></i> Lock';
        unlockButton.classList.remove("btn-danger");
        unlockButton.classList.add("btn-secondary");
    } else {
        table.style.display = "none";
    }

    unlockButton.addEventListener("click", function () {
        if (table.style.display === "none") {
            if (localStorage.getItem("tableUnlocked") !== "true") {
                let password = prompt("Masukkan sandi untuk membuka tabel:");
                if (password === correctPassword) {
                    localStorage.setItem("tableUnlocked", "true");
                    table.style.display = "block";
                    this.innerHTML = '<i class="fa fa-lock"></i> Lock';
                    this.classList.remove("btn-danger");
                    this.classList.add("btn-secondary");
                } else {
                    alert("Sandi salah! Tabel tetap terkunci.");
                }
            } else {
                table.style.display = "block";
                this.innerHTML = '<i class="fa fa-lock"></i> Lock';
                this.classList.remove("btn-danger");
                this.classList.add("btn-secondary");
            }
        } else {
            table.style.display = "none";
            localStorage.removeItem("tableUnlocked"); // Reset status saat dikunci
            this.innerHTML = '<i class="fa fa-unlock"></i> Unlock';
            this.classList.remove("btn-secondary");
            this.classList.add("btn-danger");
        }
    });
});
function setRestokId(id) {
    document.getElementById('restokId').value = id;
}
function refreshTable() {
    $.ajax({
        url: '', // The PHP script that returns the updated table
        method: 'GET',
        success: function(data) {
            $('#barangTable').html(data); // Update the table with the new data
        }
    });
}

// Call this function after a successful restock
</script>