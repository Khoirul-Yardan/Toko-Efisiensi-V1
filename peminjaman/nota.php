<?php
require 'koneksi.php'; // Pastikan koneksi database sudah benar

// Ambil data dari URL parameters
$nama_peminjam = isset($_GET['nama_peminjam']) ? $conn->real_escape_string($_GET['nama_peminjam']) : '';

// Fetch data from the peminjaman table
$result_peminjaman = $conn->query("SELECT * FROM peminjaman WHERE nama_peminjam = '$nama_peminjam'");

if ($result_peminjaman && $result_peminjaman->num_rows > 0) {
    // Informasi Toko
    $nama_toko = "Toko ABC"; // Ganti dengan nama toko Anda
    $alamat_toko = "Jl. Contoh Alamat No. 123, Kota"; // Ganti dengan alamat toko Anda

    // Membuat kode transaksi otomatis
    $kode_transaksi = date("YmdHis"); // Format: YYYYMMDDHHMMSS
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Peminjaman</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .container { width: 80mm; max-width: 100%; border: 1px solid #000; padding: 10px; box-sizing: border-box; }
        .header { display: flex; align-items: center; margin-bottom: 10px; }
        .logo { width: 60px; margin-right: 10px; }
        .store-info { flex-grow: 1; }
        .text-center { text-align: center; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table td, .table th { border-bottom: 1px dashed black; padding: 5px; text-align: left; }
        .footer { margin-top: 10px; text-align: center; font-size: 12px; }

        @media print {
            body { width: auto; }
            .container { border: none; margin: 0; padding: 0; }
            @page {
                size: auto; /* auto is the initial value */
                margin: 10mm; /* margin for the printed page */
            }
            .no-print { display: none; } /* Sembunyikan elemen yang tidak perlu saat dicetak */
        }
    </style>
</head>
<body>
    <script>window.print();</script>
    <div class="container">
        <div class="header">
            <img src="logo.jpg" alt="Logo Toko" class="logo">
            <div class="store-info">
                <p><strong><?php echo $nama_toko; ?></strong></p>
                <p>Alamat: <?php echo $alamat_toko; ?></p>
            </div>
        </div>
        <p>Tanggal: <span id="current-time"></span></p>
        <p>Peminjam: <?php echo $nama_peminjam; ?></p>
        <p>Kode Transaksi: <?php echo $kode_transaksi; ?></p>
        
        <table class="table">
            <tr>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Total Harga</th>
                <th>ID Peminjaman</th> <!-- Ganti Kode Unik menjadi ID Peminjaman -->
                <th>Tenggat Waktu</th>
            </tr>
            <?php 
            $grand_total = 0; 
            while ($row_peminjaman = $result_peminjaman->fetch_assoc()) {
                $id_barang = $row_peminjaman['id_barang'];
                $jumlah = $row_peminjaman['jumlah'];
                $tenggat_waktu = $row_peminjaman['tenggat_waktu'];
                $id_peminjaman = $row_peminjaman['id']; // Ambil ID Peminjaman

                // Fetch item details from kasir based on id_barang
                $result_kasir = $conn->query("SELECT nama_barang, harga FROM kasir WHERE id_barang = '$id_barang'");
                $row_kasir = $result_kasir->fetch_assoc();

                if ($row_kasir) {
                    $nama_barang = $row_kasir['nama_barang'];
                    $harga = $row_kasir['harga'];
                    $total_harga = $harga * $jumlah;

                    $grand_total += $total_harga; // Add to grand total

                    echo "<tr>";
                    echo "<td>$id_barang</td>";
                    echo "<td>$nama_barang</td>";
                    echo "<td>$jumlah</td>";
                    echo "<td>Rp " . number_format($harga, 2, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($total_harga, 2, ',', '.') . "</td>";
                    echo "<td>$id_peminjaman</td>"; // Display ID Peminjaman
                    echo "<td>$tenggat_waktu</td>"; // Display tenggat waktu
                    echo "</tr>";
                }
            }
            ?>
        </table>

        <p><strong>Total Keseluruhan: Rp. <?php echo number_format($grand_total, 2, ',', '.'); ?>,-</strong></p>

        <div class="footer">
            <p>Terima Kasih Telah Berbelanja di Toko Kami!</p>
        </div>
    </div>

    <script>
        // Menampilkan waktu saat ini secara real-time
        function updateTime() {
            const now = new Date();
            const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            document.getElementById('current-time').innerText = now.toLocaleString('id-ID', options);
        }
        setInterval(updateTime, 1000); // Update setiap detik
        updateTime(); // Panggil fungsi untuk pertama kali
    </script>
</body>
</html>

<?php
} else {
    echo "Tidak ada data peminjaman untuk peminjam ini.";
}
?>