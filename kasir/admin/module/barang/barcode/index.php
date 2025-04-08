<?php
session_start();
require 'config.php'; // Pastikan koneksi ke database benar
require 'vendor/autoload.php'; // Pastikan sudah menginstal library

use Picqer\Barcode\BarcodeGeneratorPNG;

// Fungsi untuk membuat barcode dengan format PNG
function generateBarcode($code) {
    $generator = new BarcodeGeneratorPNG();
    return base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 50));
}

// Fungsi untuk membuat barcode acak (minimal 8 digit)
function generateRandomBarcode($length = 8) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

$status = "";

// Proses update atau generate barcode baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang = trim($_POST['id_barang']);

    if (isset($_POST['update_barcode'])) {
        $kode_barcode = trim($_POST['kode_barcode']);

        if (!empty($kode_barcode) && is_numeric($kode_barcode) && strlen($kode_barcode) >= 8) {
            $sql = "UPDATE barang SET kode_barcode = ? WHERE id_barang = ?";
            $stmt = $config->prepare($sql);
            $stmt->execute([$kode_barcode, $id_barang]);
            $status = "<div class='alert alert-success'>✅ Kode Barcode berhasil diperbarui!</div>";
        } else {
            $status = "<div class='alert alert-danger'>❌ Kode Barcode harus berupa angka minimal 8 digit!</div>";
        }
    }

    if (isset($_POST['create_barcode'])) {
        $kode_barcode = generateRandomBarcode();
        $sql = "UPDATE barang SET kode_barcode = ? WHERE id_barang = ?";
        $stmt = $config->prepare($sql);
        $stmt->execute([$kode_barcode, $id_barang]);
        $status = "<div class='alert alert-success'>✅ Kode Barcode berhasil dibuat: $kode_barcode</div>";
    }
}

// Ambil semua data barang untuk ditampilkan
$all_barang = $config->query("SELECT * FROM barang")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        table {
            width: 100%;
            text-align: center;
        }
        .input-barcode {
            width: 220px;
        }
        .barcode-image {
            max-width: 250px;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h4>Data Barang</h4>
        <a href="index.php?page=barang/gudang" class="btn btn-success btn-md mr-2">
            <i class="fa fa-back"></i> Back
        </a>
        <br />
        <?php if ($status) echo $status; ?>

        <div class="card card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="example2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Barang</th>
                            <th>Barcode</th>
                            <th>Tanggal Input</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_barang as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id_barang']) ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['kode_barcode']) ?>
                                    <?php 
                                        if (!empty($row['kode_barcode']) && is_numeric($row['kode_barcode'])) {
                                            echo "<br>";
                                            echo '<img src="data:image/png;base64,' . generateBarcode($row['kode_barcode']) . '" class="barcode-image" alt="Barcode">';
                                        } else {
                                            echo "<br><span class='text-danger'>Barcode tidak valid</span>";
                                        }
                                    ?>
                                </td>
                                <td><?= date("j F Y, G:i", strtotime($row['tgl_input'])) ?></td>
                                <td>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id_barang" value="<?= htmlspecialchars($row['id_barang']) ?>">
                                        <div class="input-group">
                                            <input type="text" name="kode_barcode" class="form-control input-barcode" placeholder="Masukkan Kode Barcode" required>
                                            <button type="submit" name="update_barcode" class="btn btn-success">Update</button>
                                        </div>
                                    </form>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id_barang" value="<?= htmlspecialchars($row['id_barang']) ?>">
                                        <button type="submit" name="create_barcode" class="btn btn-info">Create Barcode</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>