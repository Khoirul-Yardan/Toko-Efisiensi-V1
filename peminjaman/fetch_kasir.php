<?php
require 'koneksi.php'; // Pastikan koneksi database sudah benar

// Fetch cash register data
$result_kasir = $conn->query("SELECT * FROM kasir");
$no = 1;
$grand_total = 0; // Inisialisasi total harga keseluruhan
$output = '';

while ($row_kasir = $result_kasir->fetch_assoc()) {
    $total_harga = $row_kasir['jumlah'] * $row_kasir['harga']; // Hitung total harga per item
    $grand_total += $total_harga; // Tambahkan ke total keseluruhan
    $output .= "<tr>
                    <td>$no</td>
                    <td>{$row_kasir['nama_barang']}</td>
                    <td>{$row_kasir['jumlah']}</td>
                    <td>{$row_kasir['harga']}</td>
                    <td>{$total_harga}</td> <!-- Tampilkan total harga per item -->
                    <td>
                        <button class='btn btn-danger btn-sm' onclick='hapusKasir({$row_kasir['id']})'>Hapus</button>
                    </td>
                </tr>";
    $no++;
}

$output .= "<tr>
                <td colspan='4' class='text-end'>Total Harga Keseluruhan:</td>
                <td colspan='2'>Rp " . number_format($grand_total, 2, ',', '.') . "</td>
            </tr>";

echo $output;
?>