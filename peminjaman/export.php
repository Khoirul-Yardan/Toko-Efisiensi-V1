<?php
require 'koneksi.php'; // Pastikan koneksi database sudah benar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $export_type = $_POST['export_type'];
    $date = $_POST['date'] ?? null;
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;

    // Siapkan query berdasarkan tipe ekspor
    if ($export_type == 'daily') {
        $sqlPeminjaman = "SELECT p.*, g.nama_barang, g.harga FROM peminjaman p JOIN gudang g ON p.id_barang = g.id WHERE p.status='Dipinjam' AND DATE(p.tanggal_peminjaman) = '$date'";
        $sqlPengembalian = "SELECT p.*, g.nama_barang, g.harga FROM pengembalian p JOIN gudang g ON p.id_barang = g.id WHERE DATE(p.tanggal_pengembalian) = '$date'";
    } elseif ($export_type == 'monthly') {
        $sqlPeminjaman = "SELECT p.*, g.nama_barang, g.harga FROM peminjaman p JOIN gudang g ON p.id_barang = g.id WHERE p.status='Dipinjam' AND MONTH(p.tanggal_peminjaman) = '$month' AND YEAR(p.tanggal_peminjaman) = '$year'";
        $sqlPengembalian = "SELECT p.*, g.nama_barang, g.harga FROM pengembalian p JOIN gudang g ON p.id_barang = g.id WHERE MONTH(p.tanggal_pengembalian) = '$month' AND YEAR(p.tanggal_pengembalian) = '$year'";
    } elseif ($export_type == 'yearly') {
        $sqlPeminjaman = "SELECT p.*, g.nama_barang, g.harga FROM peminjaman p JOIN gudang g ON p.id_barang = g.id WHERE p.status='Dipinjam' AND YEAR(p.tanggal_peminjaman) = '$year'";
        $sqlPengembalian = "SELECT p.*, g.nama_barang, g.harga FROM pengembalian p JOIN gudang g ON p.id_barang = g.id WHERE YEAR(p.tanggal_pengembalian) = '$year'";
    }

    // Ambil data peminjaman
    $resultPeminjaman = $conn->query($sqlPeminjaman);
    $peminjamanData = [];
    $totalKeuntungan = 0;

    while ($row = $resultPeminjaman->fetch_assoc()) {
        $peminjamanData[] = $row;
        // Hitung total keuntungan dari harga barang
        $totalKeuntungan += $row['harga'] * $row['jumlah'];
    }

    // Ambil data pengembalian
    $resultPengembalian = $conn->query($sqlPengembalian);
    $pengembalianData = [];
    $totalDenda = 0;
    $totalKerusakan = 0;

    while ($row = $resultPengembalian->fetch_assoc()) {
        $pengembalianData[] = $row;
        // Hitung total denda
        $totalDenda += $row['denda'];
        // Hitung total kerusakan berdasarkan kondisi
        if ($row['kondisi'] === 'Rusak') {
            $totalKerusakan += $row['jumlah'] * $row['harga']; // Menggunakan harga dari tabel gudang
        }
    }

    // Total keuntungan ditambahkan dengan total denda
    $totalKeuntungan += $totalDenda;

    // Buat file Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=laporan_peminjaman_pengembalian.xls");

    // Judul berdasarkan tipe ekspor
    if ($export_type == 'daily') {
        $tanggal = date("l, j F Y", strtotime($date)); // Format: Hari, Tanggal Bulan Tahun
        echo "<h2>Laporan Peminjaman Hari $tanggal</h2>";
    } elseif ($export_type == 'monthly') {
        $bulan = date("F", mktime(0, 0, 0, $month, 1)); // Nama bulan
        echo "<h2>Laporan Peminjaman Bulan $bulan $year</h2>";
    } elseif ($export_type == 'yearly') {
        echo "<h2>Laporan Peminjaman Tahun $year</h2>";
    }

    echo "<h3>Total Denda: Rp " . number_format($totalDenda, 2, ',', '.') . "</h3>";
    echo "<h3>Total Keuntungan: Rp " . number_format($totalKeuntungan, 2, ',', '.') . "</h3>";
    echo "<h3>Total Kerugian (Barang Rusak): Rp " . number_format($totalKerusakan, 2, ',', '.') . "</h3>";

    echo "<h3>Data Peminjaman</h3>";
    echo "<table border='1'>
            <tr>
                <th>ID Peminjaman</th>
                <th>Nama Barang</th>
                <th>Nama Peminjam</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Tanggal Peminjaman</th>
                <th>Tenggat Waktu</th>
                <th>Status</th>
            </tr>";

    foreach ($peminjamanData as $data) {
        echo "<tr>
                <td>{$data['id']}</td>
                <td>{$data['nama_barang']}</td>
                <td>{$data['nama_peminjam']}</td>
                <td>{$data['jumlah']}</td>
                <td>Rp " . number_format($data['harga'], 2, ',', '.') . "</td>
                <td>{$data['tanggal_peminjaman']}</td>
                <td>{$data['tenggat_waktu']}</td>
                <td>{$data['status']}</td>
              </tr>";
    }

    echo "</table><br>";

    echo "<h3>Data Pengembalian</h3>";
    echo "<table border='1'>
            <tr>
                <th>ID Pengembalian</th>
                <th>Nama Barang</th>
                <th>Nama Peminjam</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Tanggal Peminjaman</th>
                <th>Tanggal Pengembalian</th>
                <th>Kondisi</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>";

    foreach ($pengembalianData as $data) {
        echo "<tr>
                <td>{$data['id']}</td>
                <td>{$data['nama_barang']}</td>
                <td>{$data['nama_peminjam']}</td>
                <td>{$data['jumlah']}</td>
                <td>Rp " . number_format($data['harga'], 2, ',', '.') . "</td>
                <td>{$data['tanggal_peminjaman']}</td>
                <td>{$data['tanggal_pengembalian']}</td>
                <td>{$data['kondisi']}</td>
                <td>Rp " . number_format($data['denda'], 2, ',', '.') . "</td>
                <td>{$data['status']}</td>
              </tr>";
    }

    echo "</table>";
}
?>