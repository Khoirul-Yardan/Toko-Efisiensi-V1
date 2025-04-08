<?php
session_start();
if (!empty($_SESSION['admin'])) {
    require '../../config.php';

    // Hapus Kategori
    if (!empty(htmlentities($_GET['kategori']))) {
        $id = htmlentities($_GET['id']);
        $sql = 'DELETE FROM kategori WHERE id_kategori=?';
        $stmt = $config->prepare($sql);
        $stmt->execute([$id]);
        echo '<script>window.location="../../index.php?page=kategori&&remove=hapus-data"</script>';
    }

    // Hapus Barang
    if (!empty(htmlentities($_GET['barang']))) {
        $id = htmlentities($_GET['id']);
        $sql = 'DELETE FROM barang WHERE id_barang=?';
        $stmt = $config->prepare($sql);
        $stmt->execute([$id]);
        echo '<script>window.location="../../index.php?page=barang&&remove=hapus-data"</script>';
    }

    // Hapus Penjualan (dan update stok barang)
    if (!empty(htmlentities($_GET['jual']))) {
        $id = htmlentities($_GET['id']);
        $barangId = htmlentities($_GET['brg']);

        // Ambil stok barang sebelum penjualan
        $sql = 'SELECT stok FROM barang WHERE id_barang=?';
        $stmt = $config->prepare($sql);
        $stmt->execute([$barangId]);
        $barang = $stmt->fetch();

        // Hapus transaksi penjualan
        $sql = 'DELETE FROM penjualan WHERE id_penjualan=?';
        $stmt = $config->prepare($sql);
        $stmt->execute([$id]);

        echo '<script>window.location="../../index.php?page=jual"</script>';
    }

    // Hapus Semua Penjualan
    if (!empty(htmlentities($_GET['penjualan']))) {
        $sql = 'DELETE FROM penjualan';
        $stmt = $config->prepare($sql);
        $stmt->execute();
        echo '<script>window.location="../../index.php?page=jual"</script>';
    }

    // Hapus Laporan (dari tabel `nota`)
    if (!empty(htmlentities($_GET['laporan']))) {
        $sql = 'DELETE FROM nota';
        $stmt = $config->prepare($sql);
        $stmt->execute();
        echo '<script>window.location="../../index.php?page=laporan&remove=hapus"</script>';
    }

    // Hapus Riwayat Restok
    if (isset($_GET['reset_riwayat'])) {
        $sql = "DELETE FROM riwayat_restok";
        $stmt = $config->prepare($sql);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
    }

    // Cek tanggal reset terakhir di database
    $sql = "SELECT tanggal_reset FROM last_reset ORDER BY id DESC LIMIT 1";
    $stmt = $config->query($sql);
    $lastResetDate = $stmt->fetchColumn();

    // Hitung selisih waktu (1 tahun = 365 hari)
    $today = date("Y-m-d");
    $resetRequired = ($lastResetDate === false || strtotime($today) - strtotime($lastResetDate) > 365 * 24 * 60 * 60);

    if ($resetRequired) {
        // Hapus semua data di tabel `nota`
        $sql = 'DELETE FROM nota';
        $stmt = $config->prepare($sql);
        $stmt->execute();

        // Simpan tanggal reset terbaru
        $sql = 'INSERT INTO last_reset (tanggal_reset) VALUES (?)';
        $stmt = $config->prepare($sql);
        $stmt->execute([$today]);

        echo '<script>alert("Data laporan telah dihapus otomatis.");</script>';
    }
   
}


?>

