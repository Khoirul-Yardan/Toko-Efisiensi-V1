<?php 
@ob_start();
session_start();
if(!isset($_SESSION['admin'])){
    echo '<script>window.location="login.php";</script>';
    exit;
}

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=data-laporan-".date('Y-m-d').".xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false); 

require 'config.php';
include $view;
$lihat = new view($config);

// Laporan Penjualan
$bulan_tes = array(
    '01'=>"Januari",
    '02'=>"Februari",
    '03'=>"Maret",
    '04'=>"April",
    '05'=>"Mei",
    '06'=>"Juni",
    '07'=>"Juli",
    '08'=>"Agustus",
    '09'=>"September",
    '10'=>"Oktober",
    '11'=>"November",
    '12'=>"Desember"
);

// Sanitasi input
$bulan = filter_input(INPUT_GET, 'bln', FILTER_SANITIZE_NUMBER_INT);
$tahun = filter_input(INPUT_GET, 'thn', FILTER_SANITIZE_NUMBER_INT);
$hari = filter_input(INPUT_GET, 'hari', FILTER_SANITIZE_STRING);
$tanggal = filter_input(INPUT_GET, 'tgl', FILTER_SANITIZE_STRING);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan dan Keuangan</title>
</head>
<body>
    <!-- Laporan Penjualan -->
    <h3 style="text-align:center;"> 
        <?php if(!empty($bulan) && !empty($tahun)){ ?>
            Data Laporan Penjualan <?= $bulan_tes[$bulan];?> <?= $tahun;?>
        <?php } elseif(!empty($hari)){ ?>
            Data Laporan Penjualan <?= $tanggal;?>
        <?php } else { ?>
            Data Laporan Penjualan <?= $bulan_tes[date('m')];?> <?= date('Y');?>
        <?php } ?>
    </h3>
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <thead>
            <tr bgcolor="yellow">
                <th>No</th>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th style="width:10%;">Jumlah</th>
                <th style="width:10%;">Modal</th>
                <th style="width:10%;">Total</th>
                <th>Kasir</th>
                <th>Tanggal Input</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $no = 1; 
                $bayar = 0;
                $jumlah = 0;
                $modal = 0;

                if(!empty($bulan) && !empty($tahun)){
                    $periode = $bulan.'-'.$tahun;
                    $hasil = $lihat->periode_jual($periode);
                } elseif(!empty($hari)){
                    $hasil = $lihat->hari_jual($tanggal);
                } else {
                    $hasil = $lihat->jual();
                }

                foreach($hasil as $isi){ 
                    $bayar += $isi['total'];
                    $modal += $isi['harga_beli'] * $isi['jumlah'];
                    $jumlah += $isi['jumlah'];
            ?>
            <tr>
                <td><?php echo $no;?></td>
                <td><?php echo htmlspecialchars($isi['id_barang']);?></td>
                <td><?php echo htmlspecialchars($isi['nama_barang']);?></td>
                <td><?php echo $isi['jumlah'];?> </td>
                <td>Rp.<?php echo number_format($isi['harga_beli'] * $isi['jumlah'], 0, ',', '.');?>,-</td>
                <td>Rp.<?php echo number_format($isi['total'], 0, ',', '.');?>,-</td>
                <td><?php echo htmlspecialchars($isi['nm_member']);?></td>
                <td><?php echo htmlspecialchars($isi['tanggal_input']);?></td>
            </tr>
            <?php $no++; }?>
            <tr>
                <td>-</td>
                <td>-</td>
                <td><b>Total Terjual</b></td>
                <td><b><?php echo $jumlah;?></b></td>
                <td><b>Rp.<?php echo number_format($modal, 0, ',', '.');?>,-</b></td>
                <td><b>Rp.<?php echo number_format($bayar, 0, ',', '.');?>,-</b></td>
                <td><b>Keuntungan</b></td>
                <td><b>Rp.<?php echo number_format($bayar - $modal, 0, ',', '.');?>,-</b></td>
            </tr>
        </tbody>
    </table>

    <!-- Laporan Keuangan -->
    <h3 style="text-align:center;">Laporan Keuangan <?= date('F Y'); ?></h3>
    
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <thead>
            <tr bgcolor="yellow">
                <th>No</th>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Stok Tersedia</th>
                <th>Stok Gudang</th>
                <th>Harga Jual</th>
                <th>Harga Beli</th>
                <th>Nilai Persediaan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $no = 1;
                $total_nilai_persediaan = 0;
                $total_penjualan = $bayar; // Menggunakan total penjualan aktual dari laporan penjualan
                $total_modal_penjualan = $modal; // Menggunakan modal aktual dari laporan penjualan
                $total_stok_gudang = 0;
                $biaya_operasional = 500000; // Biaya tetap per bulan

                $data_barang = $lihat->barang();

                if(empty($data_barang)) {
                    die("<h3 style='color:red; text-align:center;'>Tidak ada data barang yang tersedia.</h3>");
                }

                foreach($data_barang as $barang) { 
                    $stok_tersedia = intval($barang['stok']);
                    $stok_gudang = intval($barang['stok_gudang']);
                    $harga_beli = intval($barang['harga_beli']);
                    $nilai_persediaan = ($stok_tersedia + $stok_gudang) * $harga_beli;
                    
                    $total_nilai_persediaan += $nilai_persediaan;
                    $total_stok_gudang += $stok_gudang;
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($barang['id_barang']); ?></td>
                <td><?= htmlspecialchars($barang['nama_barang']); ?></td>
                <td><?= number_format($stok_tersedia); ?></td>
                <td><?= number_format($stok_gudang); ?></td>
                <td>Rp.<?= number_format($barang['harga_jual'], 0, ',', '.'); ?>,-</td>
                <td>Rp.<?= number_format($harga_beli, 0, ',', '.'); ?>,-</td>
                <td>Rp.<?= number_format($nilai_persediaan, 0, ',', '.'); ?>,-</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3 style="text-align:center;">Ringkasan Laporan Keuangan</h3>
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <tr>
            <td><b>Total Penjualan</b></td>
            <td>Rp.<?= number_format($total_penjualan, 0, ',', '.'); ?>,-</td>
        </tr>
        <tr>
            <td><b>Modal Penjualan</b></td>
            <td>Rp.<?= number_format($total_modal_penjualan, 0, ',', '.'); ?>,-</td>
        </tr>
        <tr>
            <td><b>Keuntungan Kotor</b></td>
            <td>Rp.<?= number_format($total_penjualan - $total_modal_penjualan, 0, ',', '.'); ?>,-</td>
        </tr>
        <tr>
            <td><b>Biaya Operasional</b></td>
            <td>Rp.<?= number_format($biaya_operasional, 0, ',', '.'); ?>,-</td>
        </tr>
        <tr>
            <td><b>Laba Bersih</b></td>
            <td>Rp.<?= number_format(($total_penjualan - $total_modal_penjualan) - $biaya_operasional, 0, ',', '.'); ?>,-</td>
        </tr>
    </table>

    <!-- Tabel Keuntungan Asli -->
    <h3 style="text-align:center;">Keuntungan Asli</h3>
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <thead>
            <tr bgcolor="yellow">
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Pendapatan</td>
                <td>Rp.<?= number_format($total_penjualan, 0, ',', '.'); ?>,-</td>
            </tr>
            <tr>
                <td>Total Modal Penjualan</td>
                <td>Rp.<?= number_format($total_modal_penjualan, 0, ',', '.'); ?>,-</td>
            </tr>
            <tr>
                <td>Biaya Operasional</td>
                <td>Rp.<?= number_format($biaya_operasional, 0, ',', '.'); ?>,-</td>
            </tr>
            <tr>
                <td><b>Keuntungan Bersih</b></td>
                <td><b>Rp.<?= number_format(($total_penjualan - $total_modal_penjualan) - $biaya_operasional, 0, ',', '.'); ?>,-</b></td>
            </tr>
        </tbody>
    </table>

    <!-- Laporan Arus Kas -->
    <h3 style="text-align:center;">Laporan Arus Kas</h3>
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <thead>
            <tr bgcolor="yellow">
                <th>No</th>
                <th>Deskripsi</th>
                <th>Kas Masuk</th>
                <th>Kas Keluar</th>
                <th>Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $no = 1;
                $kas_masuk = $total_penjualan; // Total penjualan aktual sebagai kas masuk
                $kas_keluar = $total_modal_penjualan + $biaya_operasional; // Modal penjualan dan biaya operasional sebagai kas keluar
                $saldo_akhir = $kas_masuk - $kas_keluar;
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td>Kas dari Penjualan</td>
                <td>Rp.<?= number_format($kas_masuk, 0, ',', '.'); ?>,-</td>
                <td>-</td>
                <td rowspan="2">Rp.<?= number_format($saldo_akhir, 0, ',', '.'); ?>,-</td>
            </tr>
            <tr>
                <td><?= $no++; ?></td>
                <td>Kas Keluar (Modal Penjualan + Biaya Operasional)</td>
                <td>-</td>
                <td>Rp.<?= number_format($kas_keluar, 0, ',', '.'); ?>,-</td>
            </tr>
        </tbody>
    </table>

    <!-- Laporan Persediaan -->
    <h3 style="text-align:center;">Laporan Persediaan</h3>
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <thead>
            <tr bgcolor="yellow">
                <th>No</th>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Stok Tersedia</th>
                <th>Stok Gudang</th>
                <th>Total Stok</th>
                <th>Nilai Persediaan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $no = 1;
                $total_semua_stok = 0;
                $total_nilai_semua_persediaan = 0;
                
                foreach($data_barang as $barang) { 
                    $stok_tersedia = intval($barang['stok']);
                    $stok_gudang = intval($barang['stok_gudang']);
                    $total_stok = $stok_tersedia + $stok_gudang;
                    $harga_beli = intval($barang['harga_beli']);
                    $nilai_persediaan = $total_stok * $harga_beli;
                    
                    $total_semua_stok += $total_stok;
                    $total_nilai_semua_persediaan += $nilai_persediaan;
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($barang['id_barang']); ?></td>
                <td><?= htmlspecialchars($barang['nama_barang']); ?></td>
                <td><?= number_format($stok_tersedia); ?></td>
                <td><?= number_format($stok_gudang); ?></td>
                <td><?= number_format($total_stok); ?></td>
                <td>Rp.<?= number_format($nilai_persediaan, 0, ',', '.'); ?>,-</td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="5"><b>Total</b></td>
                <td><b><?= number_format($total_semua_stok); ?></b></td>
                <td><b>Rp.<?= number_format($total_nilai_semua_persediaan, 0, ',', '.'); ?>,-</b></td>
            </tr>
        </tbody>
    </table>

    <!-- Neraca Keuangan -->
    <h3 style="text-align:center;">Neraca Keuangan</h3>
    <table border="1" width="100%" cellpadding="3" cellspacing="4">
        <tr>
            <th colspan="2">Aset</th>
            <th colspan="2">Kewajiban & Ekuitas</th>
        </tr>
        <tr>
            <td>Kas (Laba Bersih)</td>
            <td>Rp.<?= number_format(($total_penjualan - $total_modal_penjualan) - $biaya_operasional, 0, ',', '.'); ?>,-</td>
            <td>Kewajiban</td>
            <td>Rp.0,-</td>
        </tr>
        <tr>
            <td>Nilai Persediaan</td>
            <td>Rp.<?= number_format($total_nilai_semua_persediaan, 0, ',', '.'); ?>,-</td>
            <td>Ekuitas</td>
            <td>Rp.<?= number_format(($total_penjualan - $total_modal_penjualan) - $biaya_operasional + $total_nilai_semua_persediaan, 0, ',', '.'); ?>,-</td>
        </tr>
        <tr>
            <td><b>Total Aset</b></td>
            <td><b>Rp.<?= number_format(($total_penjualan - $total_modal_penjualan) - $biaya_operasional + $total_nilai_semua_persediaan, 0, ',', '.'); ?>,-</b></td>
            <td><b>Total Kewajiban & Ekuitas</b></td>
            <td><b>Rp.<?= number_format(($total_penjualan - $total_modal_penjualan) - $biaya_operasional + $total_nilai_semua_persediaan, 0, ',', '.'); ?>,-</b></td>
        </tr>
    </table>
</body>
</html>