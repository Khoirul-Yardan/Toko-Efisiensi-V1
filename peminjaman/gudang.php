<?php
require 'koneksi.php';

// Proses tambah barang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_barang'])) {
    $nama_barang = $conn->real_escape_string($_POST['nama_barang']);
    $harga = $conn->real_escape_string($_POST['harga']);
    $jumlah = $conn->real_escape_string($_POST['jumlah']);

    $sql = "INSERT INTO gudang (nama_barang, harga, jumlah) VALUES ('$nama_barang', '$harga', '$jumlah')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit();
}

// Proses edit dan hapus barang
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == 'edit') {
        $id = $conn->real_escape_string($_POST['id']);
        $nama_barang = $conn->real_escape_string($_POST['nama_barang']);
        $harga = $conn->real_escape_string($_POST['harga']);
        $jumlah = $conn->real_escape_string($_POST['jumlah']);

        $sql = "UPDATE gudang SET nama_barang='$nama_barang', harga='$harga', jumlah='$jumlah', updated_at=CURRENT_TIMESTAMP WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    } elseif ($action == 'hapus') {
        $id = $conn->real_escape_string($_POST['id']);

        $sql = "DELETE FROM gudang WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    } elseif ($action == 'restok') {
        $id_barang = $conn->real_escape_string($_POST['id_barang']);
        $jumlah = $conn->real_escape_string($_POST['jumlah']);
        $supplier = $conn->real_escape_string($_POST['supplier']);

        $sql = "INSERT INTO restok (id_barang, jumlah, tanggal_restok, supplier) VALUES ('$id_barang', '$jumlah', CURDATE(), '$supplier')";
        if ($conn->query($sql) === TRUE) {
            // Update jumlah di tabel gudang
            $conn->query("UPDATE gudang SET jumlah = jumlah + $jumlah, updated_at=CURRENT_TIMESTAMP WHERE id = $id_barang");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    } elseif ($action == 'reset_history') {
        $sql = "DELETE FROM restok";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    }
}

// Endpoint untuk memeriksa pembaruan
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['check_update'])) {
    $result = $conn->query("SELECT COUNT(*) as total FROM gudang");
    $row = $result->fetch_assoc();
    echo json_encode(['total' => $row['total']]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gudang - Manajemen Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body {
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #343a40;
            color: white;
            padding: 15px;
            position: fixed;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .content {
            margin-left: 260px;
            width: 100%;
            padding: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">📦 Gudang</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="gudang.php">Manajemen Barang</a>
    <a href="peminjaman.php">Peminjaman</a>
    <a href="pengembalian.php">Pengembalian</a>
    <a href="../index.php">Home</a>
</div>

<!-- Konten -->
<div class="content">
    <h2 class="mb-4">📋 Manajemen Gudang</h2>

    <!-- Form Tambah Barang -->
    <div class="card p-4 shadow">
        <h5 class="mb-3">Tambah Barang</h5>
        <form id="formTambahBarang" method="POST" onsubmit="return false;">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" required>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" name="tambah_barang" class="btn btn-primary" onclick="tambahBarang()"><i class="fas fa-plus"></i> Tambah</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Data Barang -->
<div class="card mt-4 p-4 shadow">
    <h5 class="mb-3">📋 Daftar Barang</h5>
    <table id="barangTable" class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM gudang ORDER BY id DESC");
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>$no</td>
                        <td>{$row['nama_barang']}</td>
                        <td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>
                        <td>{$row['jumlah']}</td>
                        <td>
                            <button class='btn btn-sm btn-warning' onclick='editHapusBarang({$row['id']}, \"{$row['nama_barang']}\", {$row['harga']}, {$row['jumlah']})'>
                                <i class='fas fa-cog'></i> Edit / Hapus
                            </button>
                            <button class='btn btn-sm btn-info' onclick='restokBarang({$row['id']})'>
                                <i class='fas fa-plus'></i> Restok
                            </button>
                        </td>
                    </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
    <div class="mt-3">
        <h5>Informasi Barang:</h5>
        <p>Total Barang: <?php echo $conn->query("SELECT COUNT(*) FROM gudang")->fetch_assoc()['COUNT(*)']; ?></p>
        <p>Total Barang Dipinjam: <?php echo $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status='Dipinjam'")->fetch_assoc()['COUNT(*)']; ?></p>
        <p>Total Barang Dikembalikan: <?php echo $conn->query("SELECT COUNT(*) FROM pengembalian")->fetch_assoc()['COUNT(*)']; ?></p>
        <p>Total Barang Rusak: <?php echo $conn->query("SELECT COUNT(*) FROM pengembalian WHERE kondisi='Rusak'")->fetch_assoc()['COUNT(*)']; ?></p>
    </div>
</div>
    <!-- Tabel History Restok -->
    <div class="card mt-4 p-4 shadow">
        <h5 class="mb-3">📋 History Restok</h5>
        <button class="btn btn-danger mb-3" onclick="resetHistory()">Reset History Restok</button>
        <table id="historyRestokTable" class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>ID Barang</th>
                    <th>Jumlah Restok</th>
                    <th>Tanggal Restok</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM restok ORDER BY id DESC");
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>$no</td>
                            <td>{$row['id_barang']}</td>
                            <td>{$row['jumlah']}</td>
                            <td>{$row['tanggal_restok']}</td>
                            <td>{$row['supplier']}</td>
                        </tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit / Hapus -->
<div class="modal fade" id="modalBarang" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit / Hapus Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditBarang" onsubmit="return false;">
                    <input type="hidden" id="id_barang">
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" id="edit_nama_barang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" id="edit_harga" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" id="edit_jumlah" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success" onclick="simpanPerubahan()">Simpan Perubahan</button>
                    <button type="button" class="btn btn-danger" onclick="hapusBarang()">Hapus Barang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Restok -->
<div class="modal fade" id="modalRestok" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restok Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRestok" onsubmit="return false;">
                    <input type="hidden" id="id_barang_restok">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Restok</label>
                        <input type="number" id="jumlah_restok" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" id="supplier" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success" onclick="restokBarangSubmit()">Restok</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="js/gudang.js"></script>
<!-- Bootstrap 5 & DataTables -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>



</body>
</html>