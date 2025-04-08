<?php
require 'koneksi.php';

// Ambil data peminjaman dengan harga barang
$resultPeminjaman = $conn->query("SELECT p.*, g.nama_barang, g.harga FROM peminjaman p JOIN gudang g ON p.id_barang = g.id WHERE p.status='Dipinjam'");
$peminjamanData = [];
$totalKeuntungan = 0;

while ($row = $resultPeminjaman->fetch_assoc()) {
    $peminjamanData[] = $row;
    // Hitung total keuntungan dari harga barang
    $totalKeuntungan += $row['harga'] * $row['jumlah'];
}

// Ambil data pengembalian dengan harga barang
$resultPengembalian = $conn->query("SELECT p.*, g.nama_barang, g.harga FROM pengembalian p JOIN gudang g ON p.id_barang = g.id");
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
            <td>{$data['harga']}</td>
            <td>{$data['tanggal_peminjaman']}</td>
            <td>{$data['tenggat_waktu']}</td>
            <td>{$data['status']}</td>
          </tr>";
}

echo "</table><br>";

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
            <td>{$data['harga']}</td>
            <td>{$data['tanggal_peminjaman']}</td>
            <td>{$data['tanggal_pengembalian']}</td>
            <td>{$data['kondisi']}</td>
            <td>{$data['denda']}</td>
            <td>{$data['status']}</td>
          </tr>";
}

echo "</table><br>";
echo "<h3>Total Denda: Rp " . number_format($totalDenda, 2, ',', '.') . "</h3>";
echo "<h3>Total Keuntungan: Rp " . number_format($totalKeuntungan, 2, ',', '.') . "</h3>";
echo "<h3>Total Kerugian (Barang Rusak): Rp " . number_format($totalKerusakan, 2, ',', '.') . "</h3>";
?>