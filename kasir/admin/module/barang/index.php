<h4>Data Barang</h4>
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

<?php 
$sql = "SELECT * FROM barang ORDER BY tgl_input ASC"; // Order by tgl_input ascending
$row = $config->prepare($sql);
$row->execute();
$results = $row->fetchAll();
?>
<!-- Similar warning for low stock -->
<?php 
$sql=" select * from barang where stok <= 3";
$row = $config -> prepare($sql);
$row -> execute();
$r = $row -> rowCount();
if($r > 0){
    echo "
    <div class='alert alert-warning'>
        <span class='glyphicon glyphicon-info-sign'></span> Ada <span style='color:red'>$r</span> barang yang Stok tersisa sudah kurang dari 3 items. silahkan ambil di gudang !!
       
    </div>
    ";    
}
?>

<!-- Trigger the modal with a button -->
<a href="index.php?page=barang" class="btn btn-success btn-md">
    <i class="fa fa-refresh"></i> Refresh Data</a>
<a href="index.php?page=barang/gudang" class="btn btn-info btn-md mr-2">
    <i class="fa fa-warehouse"></i> Gudang
</a>

<!-- Button to show hidden items -->
<button id="showHiddenButton" class="btn btn-secondary btn-md mr-2" style="display: none;">Show Hidden Items</button>

<div class="clearfix"></div>
<br />
<!-- view barang -->
<div class="card card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="example1">
            <thead>
                <tr style="background:#DFF0D8;color:#333;">
                    <th>No.</th>
                    <th>ID Barang</th>
                    <th>Kategori</th>
                    <th>Nama Barang</th>
                    <th>Merk</th>
                    <th>Stok</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Satuan</th>
                    <th>Tanggal Input</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="barangTableBody">
<?php 
$no = 1;
foreach ($results as $isi) {
?>
    <tr class="barang-row" data-id="<?php echo $isi['id_barang']; ?>">
        <td><?php echo $no; ?></td>
        <td><?php echo $isi['id_barang']; ?></td>
        <td><?php echo $isi['nama_kategori']; ?></td>
        <td><?php echo $isi['nama_barang']; ?></td>
        <td><?php echo $isi['merk']; ?></td>
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
        <td><?php echo date("j F Y, G:i", strtotime($isi['tgl_input'])); ?></td>
        <td>
            <!-- Tombol Detail -->
            <button class="btn btn-secondary btn-xs hide-button">Hide</button>
            <!-- Form Restok -->
            <form method="POST" action="fungsi/edit/edit.php?stok=edit" style="display:inline-block;">
                <input type="hidden" name="id" value="<?php echo $isi['id_barang']; ?>">
                <input type="number" name="restok" class="form-control" placeholder="Jumlah Restok" required>
                <button type="submit" class="btn btn-success btn-xs">Restok</button>
            </form>

            <!-- Hide Button -->
          
        </td>
    </tr>
<?php 
    $no++; 
}
?>
</tbody>

<tfoot>
    <tr>
        <th colspan="5">Total</th>
        <th>
            <?php 
            // Calculate total stock
            $totalStok = array_sum(array_column($results, 'stok'));
            echo $totalStok; 
            ?>
        </th>
        <th>
            <?php 
            // Calculate total purchase price (stok * harga_beli)
            $totalHargaBeli = 0;
            foreach ($results as $item) {
                $totalHargaBeli += $item['stok'] * $item['harga_beli'];
            }
            echo 'Rp.' . number_format($totalHargaBeli) . ',-'; 
            ?>
        </th>
        <th>
            <?php 
            // Calculate total selling price (stok * harga_jual)
            $totalHargaJual = 0;
            foreach ($results as $item) {
                $totalHargaJual += $item['stok'] * $item['harga_jual'];
            }
            echo 'Rp.' . number_format($totalHargaJual) . ',-'; 
            ?>
        </th>
        <th colspan="2" style="background:#ddd"></th>
    </tr>
</tfoot>
        </table>
    </div>
</div>
<!-- end view barang -->

<div id="selectHiddenModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="border-radius:0px;">
            <div class="modal-header" style="background:#285c64;color:#fff;">
                <h5 class="modal-title">Select Hidden Items to Show</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-striped bordered">
                    <thead>
                        <tr>
                            <th>ID Barang</th>
                            <th>Nama Barang</th>
                            <th>Select</th>
                        </tr>
                    </thead>
                    <tbody id="hiddenItemsList">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="showSelectedButton">Show Selected</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Retrieve hidden items from localStorage
    const hiddenItems = JSON.parse(localStorage.getItem('hiddenItems')) || [];

    // Hide rows based on stored hidden items
    hiddenItems.forEach(function(id) {
        document.querySelectorAll('.barang-row').forEach(function(row) {
            if (row.getAttribute('data-id') === id) {
                row.style.display = 'none';
            }
        });
    });

    // Show button if there are hidden items
    if (hiddenItems.length > 0) {
        document.getElementById('showHiddenButton').style.display = 'inline-block';
    }

    // Hide button functionality
    document.querySelectorAll('.hide-button').forEach(function(button) {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const idBarang = row.getAttribute('data-id'); // Get ID Barang

            // Hide the row
            row.style.display = 'none';

            // Add to hidden items if not already present
            if (!hiddenItems.includes(idBarang)) {
                hiddenItems.push(idBarang);
                localStorage.setItem('hiddenItems', JSON.stringify(hiddenItems)); // Store in localStorage
            }

            // Show the "Show Hidden Items" button
            document.getElementById('showHiddenButton').style.display = 'inline-block';
        });
    });

    // Show hidden items functionality
    document.getElementById('showHiddenButton').addEventListener('click', function() {
        // Show the modal dialog
        $('#selectHiddenModal').modal('show');

        // Clear the previous list
        const hiddenItemsList = document.getElementById('hiddenItemsList');
        hiddenItemsList.innerHTML = ''; // Clear previous entries

        // Populate the list of hidden items
        hiddenItems.forEach(function(id) {
            // Find the row with the corresponding ID
            document.querySelectorAll('.barang-row').forEach(function(row) {
                if (row.getAttribute('data-id') === id) {
                    const namaBarang = row.querySelector('td:nth-child(4)').textContent;
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = id;

                    const rowItem = document.createElement('tr');
                    rowItem.innerHTML = `
                        <td>${id}</td>
                        <td>${namaBarang}</td>
                        <td>${checkbox.outerHTML}</td>
                    `;
                    hiddenItemsList.appendChild(rowItem);
                }
            });
        });
    });

    // Show selected items functionality
    document.getElementById('showSelectedButton').addEventListener('click', function() {
        // Get the selected items
        const checkboxes = document.querySelectorAll('#hiddenItemsList input[type="checkbox"]:checked');
        const selectedItems = Array.from(checkboxes).map(function(checkbox) {
            return checkbox.value;
        });

        // Show the selected items
        selectedItems.forEach(function(id) {
            document.querySelectorAll('.barang-row').forEach(function(row) {
                if (row.getAttribute('data-id') === id) {
                    row.style.display = ''; // Show the row
                }
            });
        });

        // Remove the selected items from the hidden items list
        const newHiddenItems = hiddenItems.filter(function(item) {
            return !selectedItems.includes(item);
        });
        localStorage.setItem('hiddenItems', JSON.stringify(newHiddenItems));

        // Clear the hidden items list in the modal
        hiddenItemsList.innerHTML = ''; // Clear the list in the modal

        // Close the modal dialog
        $('#selectHiddenModal').modal('hide');

        // Refresh the page to reflect changes
        location.reload();
    });
});
</script>