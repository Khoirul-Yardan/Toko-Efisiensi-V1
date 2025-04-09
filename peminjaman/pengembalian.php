<?php
require 'koneksi.php';

// Ambil data pengembalian
$resultPengembalian = $conn->query("SELECT * FROM pengembalian ORDER BY id DESC");

// Ambil data peminjaman
$resultPeminjaman = $conn->query("SELECT * FROM peminjaman WHERE status='Dipinjam' ORDER BY id DESC");

// Proses pengembalian
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kembalikan'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tanggal_pengembalian = $_POST['tanggal_pengembalian'];
    $kondisi = $_POST['kondisi'];
    $denda = $_POST['denda'];

    // Ambil data peminjaman untuk mendapatkan informasi yang diperlukan
    $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id = ?");
    $stmt->bind_param("i", $id_peminjaman);
    $stmt->execute();
    $peminjamanData = $stmt->get_result()->fetch_assoc();

    // Masukkan data ke tabel pengembalian
    $stmt = $conn->prepare("INSERT INTO pengembalian (id_barang, nama_peminjam, jumlah, tanggal_peminjaman, tenggat_waktu, tanggal_pengembalian, kondisi, denda, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $status = 'Dikembalikan';
    $stmt->bind_param("issssssss", $peminjamanData['id_barang'], $peminjamanData['nama_peminjam'], $peminjamanData['jumlah'], $peminjamanData['tanggal_peminjaman'], $peminjamanData['tenggat_waktu'], $tanggal_pengembalian, $kondisi, $denda, $status);
    $stmt->execute();

    // Update status peminjaman
    $stmt = $conn->prepare("UPDATE peminjaman SET status='Dikembalikan' WHERE id=?");
    $stmt->bind_param("i", $id_peminjaman);
    $stmt->execute();

    // Update jumlah barang di gudang
    $id_barang = $peminjamanData['id_barang'];
    $jumlah_dikembalikan = $peminjamanData['jumlah'];

    // Ambil jumlah saat ini dari gudang
    $stmt = $conn->prepare("SELECT jumlah FROM gudang WHERE id = ?");
    $stmt->bind_param("i", $id_barang);
    $stmt->execute();
    $gudangData = $stmt->get_result()->fetch_assoc();

    // Update jumlah barang di gudang
    $jumlah_sekarang = $gudangData['jumlah'];
    $jumlah_baru = $jumlah_sekarang + $jumlah_dikembalikan;

    $stmt = $conn->prepare("UPDATE gudang SET jumlah = ? WHERE id = ?");
    $stmt->bind_param("ii", $jumlah_baru, $id_barang);
    $stmt->execute();

    // Redirect untuk menghindari pengiriman ulang form
    header("Location: pengembalian.php");
    exit();
}
// Process resetting pengembalian
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_pengembalian'])) {
    $password = $conn->real_escape_string($_POST['password']);
    $result = $conn->query("SELECT password FROM settings LIMIT 1");
    $current_password = $result->fetch_assoc()['password'] ?? '';

    if ($password === $current_password) {
        $conn->query("DELETE FROM pengembalian");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
    }
    exit();
}

// Proses reset berdasarkan jenis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_type'])) {
    $password = $conn->real_escape_string($_POST['password']);
    $result = $conn->query("SELECT password FROM settings LIMIT 1");
    $current_password = $result->fetch_assoc()['password'] ?? '';

    if ($password === $current_password) {
        if ($_POST['reset_type'] === 'pengembalian') {
            $conn->query("DELETE FROM pengembalian");
        } elseif ($_POST['reset_type'] === 'peminjaman') {
            $conn->query("DELETE FROM peminjaman");
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
    }
    exit();
}
// Ambil data peminjaman dengan nama barang
$resultPeminjaman = $conn->query("SELECT p.*, g.nama_barang FROM peminjaman p JOIN gudang g ON p.id_barang = g.id WHERE p.status='Dipinjam' ORDER BY p.id DESC");
// Ambil data pengembalian dengan nama barang
$resultPengembalian = $conn->query("SELECT p.*, g.nama_barang FROM pengembalian p JOIN gudang g ON p.id_barang = g.id ORDER BY p.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian - Manajemen Barang</title>
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
    <h4 class="text-center mb-4">Pengembalian</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="peminjaman.php">Peminjaman</a>
    <a href="pengembalian.php">Pengembalian</a>
    <a href="../index.php">Home</a>
</div>

<!-- Konten -->
<div class="content">
    <h2 class="mb-4">📋 Manajemen Pengembalian</h2>

   <!-- Tabel Data Pengembalian -->
<div class="card mt-4 p-4 shadow">
    <h5 class="mb-3 d-inline">📋 Daftar Pengembalian</h5>
    <button class="btn btn-danger btn-sm float-end" onclick="showResetPopup('pengembalian')">Reset</button>
    <table id="pengembalianTable" class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Nama Peminjam</th>
                <th>Jumlah</th>
                <th>Tanggal Peminjaman</th>
                <th>Tenggat Waktu</th>
                <th>Tanggal Pengembalian</th>
                <th>Kondisi</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = $resultPengembalian->fetch_assoc()) {
                echo "<tr>
                        <td>$no</td>
                        <td>{$row['id_barang']}</td>
                        <td>{$row['nama_barang']}</td>
                        <td>{$row['nama_peminjam']}</td>
                        <td>{$row['jumlah']}</td>
                        <td>{$row['tanggal_peminjaman']}</td>
                        <td>{$row['tenggat_waktu']}</td>
                        <td>{$row['tanggal_pengembalian']}</td>
                        <td>{$row['kondisi']}</td>
                        <td>{$row['denda']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Tabel Data Peminjaman -->
<div class="card mt-4 p-4 shadow">
    <h5 class="mb-3 d-inline">📋 Daftar Peminjaman</h5>
    <button class="btn btn-danger btn-sm float-end" onclick="showResetPopup('peminjaman')">Reset</button>
    <table id="peminjamanTable" class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>ID Peminjaman</th>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Nama Peminjam</th>
                <th>Jumlah</th>
                <th>Tanggal Peminjaman</th>
                <th>Tenggat Waktu</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = $resultPeminjaman->fetch_assoc()) {
                echo "<tr>
                        <td>$no</td>
                        <td>{$row['id']}</td>
                        <td>{$row['id_barang']}</td>
                        <td>{$row['nama_barang']}</td>
                        <td>{$row['nama_peminjam']}</td>
                        <td>{$row['jumlah']}</td>
                        <td>{$row['tanggal_peminjaman']}</td>
                        <td>{$row['tenggat_waktu']}</td>
                        <td>{$row['status']}</td>
                        <td>
                            <form method='POST' action=''>
                                <input type='hidden' name='id_peminjaman' value='{$row['id']}'>
                                <select name='kondisi' required>
                                    <option value='Baik'>Baik</option>
                                    <option value='Rusak'>Rusak</option>
                                    <option value='Hilang'>Hilang</option>
                                </select>
                                <input type='number' name='denda' placeholder='Denda' min='0' required>
                                <input type='date' name='tanggal_pengembalian' required>
                                <button type='submit' name='kembalikan' class='btn btn-success btn-sm'>Kembalikan</button>
                            </form>
                        </td>
                    </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>
     <!-- Ekspor Excel -->
<div class="card mt-4 p-4 shadow">
    <h5 class="mb-3">Ekspor Data Peminjaman</h5>
    <form method="POST" action="export.php">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Pilih Tipe Ekspor</label>
                <select name="export_type" class="form-select" required>
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div class="col-md-4" id="date-picker">
                <label class="form-label">Tanggal</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-4" id="month-picker" style="display: none;">
                <label class="form-label">Bulan</label>
                <select name="month" class="form-select" required>
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        echo "<option value='$m'>" . date("F", mktime(0, 0, 0, $m, 1)) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4" id="year-picker-month" style="display: none;">
                <label class="form-label">Tahun</label>
                <select name="year_month" class="form-select" required>
                    <?php
                    for ($y = date("Y"); $y >= 2000; $y--) {
                        echo "<option value='$y'>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4" id="year-picker" style="display: none;">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select" required>
                    <?php
                    for ($y = date("Y"); $y >= 2000; $y--) {
                        echo "<option value='$y'>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Ekspor</button>
            </div>
        </div>
    </form>
</div>

<script>
    document.querySelector('select[name="export_type"]').addEventListener('change', function() {
        const value = this.value;
        document.getElementById('date-picker').style.display = value === 'daily' ? 'block' : 'none';
        document.getElementById('month-picker').style.display = value === 'monthly' ? 'block' : 'none';
        document.getElementById('year-picker-month').style.display = value === 'monthly' ? 'block' : 'none';
        document.getElementById('year-picker').style.display = value === 'yearly' ? 'block' : 'none';
    });
</script>
<!-- Bootstrap 5 & DataTables -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#pengembalianTable').DataTable();
        $('#peminjamanTable').DataTable();
    });

    function showResetPopup(type) {
    Swal.fire({
        title: 'Masukkan Password',
        input: 'password',
        inputPlaceholder: 'Password',
        showCancelButton: true,
        confirmButtonText: 'Reset',
        cancelButtonText: 'Batal',
        preConfirm: (password) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: '', // Ganti dengan URL yang sesuai
                    type: 'POST',
                    data: {
                        password: password,
                        reset_type: type // Menentukan jenis reset
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire('Berhasil!', 'Data berhasil direset.', 'success').then(() => {
                                location.reload(); // Refresh halaman
                            });
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan saat mereset data.', 'error');
                    }
                });
            });
        }
    });
}
</script>

</body>
</html>