<?php
require 'koneksi.php';

// Ambil password dari database
$result = $conn->query("SELECT password FROM settings LIMIT 1");
$row = $result->fetch_assoc();
$hashed_password = $row['password']; // Password yang di-hash dari database

// Data statistik utama
$total_barang = $conn->query("SELECT COUNT(*) AS total FROM gudang")->fetch_assoc()['total'];
$dipinjam = $conn->query("SELECT COUNT(*) AS total FROM peminjaman WHERE status='Dipinjam'")->fetch_assoc()['total'];
$dikembalikan = $conn->query("SELECT COUNT(*) AS total FROM pengembalian")->fetch_assoc()['total'];
$rusak = $conn->query("SELECT COUNT(*) AS total FROM pengembalian WHERE kondisi='Rusak'")->fetch_assoc()['total'];

// Total denda = keuntungan
$total_denda = $conn->query("SELECT SUM(denda) AS total FROM pengembalian")->fetch_assoc()['total'];
$total_denda = $total_denda ?: 0;

// Total peminjaman
$total_peminjaman = $conn->query("SELECT COUNT(*) AS total FROM peminjaman")->fetch_assoc()['total'];

// Total barang rusak
$data_rusak = $conn->query("
    SELECT g.nama_barang, SUM(p.jumlah) AS jumlah_rusak
    FROM pengembalian p
    JOIN gudang g ON p.id_barang = g.id
    WHERE p.kondisi = 'Rusak'
    GROUP BY g.nama_barang
");

// Keuntungan dari peminjaman (jumlah * harga)
$keuntungan = $conn->query("
    SELECT SUM(p.jumlah * g.harga) AS total
    FROM peminjaman p
    JOIN gudang g ON p.id_barang = g.id
")->fetch_assoc()['total'] ?: 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            display: flex;
            background-color: #f8f9fa;
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
            padding: 30px;
            width: 100%;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">DASHBOARD</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="#" class="open-modal" data-page="gudang">Manajemen Barang</a>
    <a href="peminjaman.php">Peminjaman</a>
    <a href="pengembalian.php">Pengembalian</a>
    <a href="#" class="open-modal" data-page="setting">Settings</a>
    <a href="../index.php">Home</a>
</div>

<!-- Konten -->
<div class="content">
    <h2 class="mb-4">📊 Dashboard Gudang</h2>

    <!-- Info Box -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card p-3 text-white bg-primary">
                <h5>Total Barang</h5>
                <h3><?= $total_barang ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card p-3 text-white bg-warning">
                <h5>Dipinjam</h5>
                <h3><?= $dipinjam ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card p-3 text-white bg-success">
                <h5>Dikembalikan</h5>
                <h3><?= $dikembalikan ?></h3>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card p-3 text-white bg-danger">
                <h5>Rusak</h5>
                <h3><?= $rusak ?></h3>
            </div>
        </div>
    </div>

    <!-- Grafik Chart.js -->
    <div class="card p-4 mb-4">
        <h5 class="mb-3">📈 Statistik Barang</h5>
        <canvas id="barangChart" height="100"></canvas>
    </div>

    <!-- Keuntungan -->
    <div class="card p-4 mb-4">
        <h5 class="mb-3">💰 Total Keuntungan dari Denda</h5>
        <h3 class="text-success">Rp <?= number_format($total_denda, 0, ',', '.') ?></h3>
    </div>

    <!-- Keuntungan Peminjaman -->
    <div class="card shadow-sm border-0 p-3 mb-4">
        <h5>💰 Keuntungan Peminjaman</h5>
        <h4>Rp <?= number_format($keuntungan, 0, ',', '.') ?></h4>
    </div>

    <!-- Barang Rusak -->
    <div class="card shadow-sm border-0 p-3">
        <h5>⚠️ Barang Rusak</h5>
        <?php if ($data_rusak->num_rows > 0): ?>
            <ul class="mb-0">
                <?php while ($row = $data_rusak->fetch_assoc()): ?>
                    <li><?= $row['nama_barang'] ?> - <?= $row['jumlah_rusak'] ?> unit</li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">Tidak ada barang rusak.</p>
        <?php endif; ?>
    </div>
</div>

<script>
const ctx = document.getElementById('barangChart').getContext('2d');
const barangChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Dipinjam', 'Dikembalikan', 'Rusak'],
        datasets: [{
            label: 'Jumlah',
            data: [<?= $dipinjam ?>, <?= $dikembalikan ?>, <?= $rusak ?>],
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ],
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

document.querySelectorAll('.open-modal').forEach(item => {
    item.addEventListener('click', function() {
        const page = this.getAttribute('data-page');
        Swal.fire({
            title: 'Masukkan Password',
            input: 'password',
            inputLabel: 'Password',
            inputPlaceholder: 'Masukkan password',
            showCancelButton: true,
            confirmButtonText: 'Masuk',
            cancelButtonText: 'Batal',
            preConfirm: (password) => {
                return new Promise((resolve) => {
                    // Verifikasi password
                    if (password === '<?= $hashed_password ?>') {
                        window.location.href = page + '.php'; // Redirect ke halaman yang diminta
                    } else {
                        Swal.showValidationMessage('Password salah!');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>