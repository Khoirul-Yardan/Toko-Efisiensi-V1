<?php
require 'koneksi.php'; // Ensure the database connection is correct

// Process adding items to the cash register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_kasir'])) {
    $id_barang = $conn->real_escape_string($_POST['id_barang']);
    $jumlah = $conn->real_escape_string($_POST['jumlah']);

    // Get the name and price of the item
    $result_barang = $conn->query("SELECT nama_barang, harga FROM gudang WHERE id = '$id_barang'");
    if ($result_barang->num_rows > 0) {
        $row_barang = $result_barang->fetch_assoc();
        $nama_barang = $row_barang['nama_barang'];
        $harga_barang = $row_barang['harga'];

        // Insert into the cash register table
        $sql = "INSERT INTO kasir (id_barang, nama_barang, jumlah, harga) VALUES ('$id_barang', '$nama_barang', '$jumlah', '$harga_barang')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
    }
    exit();
}

// Proses borrowing from the cash register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['proses_peminjaman'])) {
    $nama_peminjam = $conn->real_escape_string($_POST['nama_peminjam']);
    $tenggat_waktu = $conn->real_escape_string($_POST['tenggat_waktu']);

    // Get data from the cash register table
    $result_kasir = $conn->query("SELECT * FROM kasir");
    $peminjaman_data = [];
    while ($row_kasir = $result_kasir->fetch_assoc()) {
        $id_barang = $row_kasir['id_barang'];
        $jumlah = $row_kasir['jumlah'];

        // Check the available quantity of the item
        $result_barang = $conn->query("SELECT jumlah FROM gudang WHERE id = '$id_barang'");
        $row_barang = $result_barang->fetch_assoc();
        if ($row_barang['jumlah'] < $jumlah) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient quantity for ID: ' . $id_barang]);
            exit();
        }

        // Decrease the quantity of the item in the warehouse
        $conn->query("UPDATE gudang SET jumlah = jumlah - $jumlah WHERE id = '$id_barang'");

        // Save borrowing data for insertion
        $peminjaman_data[] = [
            'id_barang' => $id_barang,
            'jumlah' => $jumlah,
            'kode_unik' => uniqid('PMJ-')
        ];
    }

    // Insert borrowing
    foreach ($peminjaman_data as $data) {
        $sql = "INSERT INTO peminjaman (id_barang, nama_peminjam, jumlah, tanggal_peminjaman, tenggat_waktu, status, kode_unik) VALUES ('{$data['id_barang']}', '$nama_peminjam', '{$data['jumlah']}', CURDATE(), '$tenggat_waktu', 'Dipinjam', '{$data['kode_unik']}')";
        if ($conn->query($sql) !== TRUE) {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
            exit();
        }
    }

    // Commenting out the line that clears the cash register table
    // $conn->query("DELETE FROM kasir"); // Hapus atau komentari baris ini

    echo json_encode(['status' => 'success']);
    exit();
}

// Proses peminjaman
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['proses_peminjaman'])) {
    $nama_peminjam = $conn->real_escape_string($_POST['nama_peminjam']);
    $tenggat_waktu = $conn->real_escape_string($_POST['tenggat_waktu']);

    // Ambil data dari kasir
    $result_kasir = $conn->query("SELECT * FROM kasir");
    $peminjaman_data = [];
    $total = 0; // Inisialisasi total

    // Generate unique code for this borrowing session
    $kode_unik = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)); // Generate a shorter unique code

    while ($row_kasir = $result_kasir->fetch_assoc()) {
        $id_barang = $row_kasir['id_barang'];
        $jumlah = $row_kasir['jumlah'];

        // Cek ketersediaan barang
        $result_barang = $conn->query("SELECT jumlah, harga FROM gudang WHERE id = '$id_barang'");
        $row_barang = $result_barang->fetch_assoc();
        if ($row_barang['jumlah'] < $jumlah) {
            echo json_encode(['status' => 'error', 'message' => 'Kuantitas tidak mencukupi untuk ID: ' . $id_barang]);
            exit();
        }

        // Kurangi kuantitas barang di gudang
        $conn->query("UPDATE gudang SET jumlah = jumlah - $jumlah WHERE id = '$id_barang'");

        // Hitung total
        $total += $row_barang['harga'] * $jumlah;

        // Simpan data peminjaman
        $peminjaman_data[] = [
            'id_barang' => $id_barang,
            'jumlah' => $jumlah,
            'kode_unik' => $kode_unik // Menggunakan kode unik yang dihasilkan
        ];
    }

    // Insert peminjaman dan simpan ke tabel nota
    foreach ($peminjaman_data as $data) {
        // Simpan data ke tabel peminjaman
        $sql_peminjaman = "INSERT INTO peminjaman (id_barang, nama_peminjam, jumlah, tanggal_peminjaman, tenggat_waktu, status, kode_unik) 
                           VALUES ('{$data['id_barang']}', '$nama_peminjam', '{$data['jumlah']}', CURDATE(), '$tenggat_waktu', 'Dipinjam', '$kode_unik')";
        if ($conn->query($sql_peminjaman) !== TRUE) {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
            exit();
        }
    }

    // Clear the cash register table after borrowing
    $conn->query("DELETE FROM kasir");

    // Redirect ke nota.php dengan parameter nama peminjam, total, dan tenggat waktu
    header("Location: nota.php?nama_peminjam=$nama_peminjam&total=$total&tenggat_waktu=$tenggat_waktu&kode_unik=$kode_unik");
    exit();
}
// Process deleting items from the cash register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_kasir'])) {
    $id_kasir = $conn->real_escape_string($_POST['id_kasir']);
    $sql = "DELETE FROM kasir WHERE id = '$id_kasir'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit();
}

// Process resetting borrowing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_peminjaman'])) {
    $password = $conn->real_escape_string($_POST['password']);
    $result = $conn->query("SELECT password FROM settings LIMIT 1");
    $current_password = $result->fetch_assoc()['password'] ?? '';

    if ($password === $current_password) {
        $conn->query("DELETE FROM peminjaman");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
    }
    exit();
}


// Process resetting kasir
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_kasir'])) {
    // Hapus semua data dari tabel kasir
    $conn->query("DELETE FROM kasir");
    echo json_encode(['status' => 'success']);
    exit();
}

// Fetch item data from the warehouse for searching
$result_barang = $conn->query("SELECT * FROM gudang ORDER BY id DESC");

// Fetch borrowing data
$result_peminjaman = $conn->query("SELECT p.*, g.nama_barang FROM peminjaman p JOIN gudang g ON p.id_barang = g.id ORDER BY p.id DESC");

// Fetch cash register data
$result_kasir = $conn->query("SELECT * FROM kasir");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman dan Kasir - Manajemen Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .search-results {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ccc;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
        }
        
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Peminjaman</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="gudang.php">Manajemen Barang</a>
    <a href="peminjaman.php">Peminjaman</a>
    <a href="pengembalian.php">Pengembalian</a>
    <a href="../index.php">Home</a>
</div>

<!-- Content -->
<div class="content">
    <h2 class="mb-4">📋 Manajemen Kasir</h2>

    <!-- Form Add Item to Cash Register -->
    <div class="card p-4 shadow">
        <h5 class="mb-3">Tambah Barang ke Kasir</h5>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Cari Barang</label>
                <input type="text" id="search_barang" class="form-control" onkeyup="searchBarang()" placeholder="Cari Barang" required>
                <input type="hidden" id="id_barang" value=""> <!-- Hidden input for item ID -->
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Jumlah</label>
                        <input type="number" id="jumlah-input" value="1" min="1" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h5>Daftar Barang Tersedia</h5>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Aksi</th> <!-- Kolom untuk aksi -->
                        </tr>
                    </thead>
                    <tbody id="barang-list">
    <?php
    // Fetch available items from the gudang table
    $result_barang = $conn->query("SELECT * FROM gudang ORDER BY id DESC LIMIT 5");
    while ($row_barang = $result_barang->fetch_assoc()) {
        $jumlah_barang = $row_barang['jumlah'];
        $status_barang = $jumlah_barang > 0 ? $jumlah_barang : 'Habis';
        $disabled = $jumlah_barang <= 0 ? 'disabled' : ''; // Nonaktifkan tombol jika habis
        echo "<tr>
                <td>{$row_barang['nama_barang']}</td>
                <td>Rp " . number_format($row_barang['harga'], 2, ',', '.') . "</td>
                <td>{$status_barang}</td>
                <td>
                    <button class='btn btn-success btn-sm' onclick='addToKasir({$row_barang['id']}, \"{$row_barang['nama_barang']}\")' {$disabled}>
                        <i class='fas fa-plus'></i> Add
                    </button>
                </td>
              </tr>";
    }
    ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daftar Barang di Kasir -->
    <div class="card mt-4 p-4 shadow">
        <h5 class="mb-3">Daftar Barang di Kasir</h5>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="data-kasir">
                <?php
                // Fetch cash register data
                $result_kasir = $conn->query("SELECT * FROM kasir");
                $grand_total = 0; // Inisialisasi total harga keseluruhan
                while ($row_kasir = $result_kasir->fetch_assoc()) {
                    $total_harga = $row_kasir['jumlah'] * $row_kasir['harga'];
                    $grand_total += $total_harga; // Tambahkan ke total keseluruhan
                    echo "<tr id='kasir-{$row_kasir['id']}'>
                            <td>{$row_kasir['nama_barang']}</td>
                           <td>
                                <span id='jumlah-{$row_kasir['id']}'>{$row_kasir['jumlah']}</span>
                           </td>
                            <td>Rp " . number_format($row_kasir['harga'], 2, ',', '.') . "</td>
                            <td id='total-harga-{$row_kasir['id']}'>Rp " . number_format($total_harga, 2, ',', '.') . "</td>
                            <td>
                                <button class='btn btn-danger btn-sm' onclick='hapusKasir({$row_kasir['id']})'>Hapus</button>
                            </td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="mt-3">
            <h5>Total Keseluruhan: Rp <span id="grand-total"><?php echo number_format($grand_total, 2, ',', '.'); ?></span></h5> <!-- Tampilkan total harga keseluruhan -->
        </div>
    </div>

    <!-- Borrowing Form -->
    <div class="card mt-4 p-4 shadow">
        <h5 class="mb-3">Peminjaman Barang</h5>
        <form id="formPeminjaman" method="POST" onsubmit="return false;">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tenggat Waktu</label>
                    <input type="date" name="tenggat_waktu" class="form-control" required>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-success" onclick="prosesPeminjaman()"><i class="fas fa-check"></i> Proses Peminjaman</button>
                </div>
            </div>
        </form>
    </div>

   <!-- Modal untuk menampilkan nota -->
<div class="modal fade" id="notaModal" tabindex="-1" aria-labelledby="notaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notaModalLabel">Nota Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notaContent">
                <!-- Konten nota akan dimasukkan di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    <script src="js/peminjaman.js"></script>

</body>
</html>