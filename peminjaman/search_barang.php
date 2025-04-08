<?php
require 'koneksi.php';

if (isset($_POST['query'])) {
    $query = $conn->real_escape_string($_POST['query']);
    $result = $conn->query("SELECT * FROM gudang WHERE nama_barang LIKE '%$query%'");

    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>ID</th><th>Nama Barang</th><th>Aksi</th></tr></thead>';
        echo '<tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['id'] . '</td>';
            echo '<td>' . $row['nama_barang'] . '</td>';
            echo '<td><button class="btn btn-success btn-sm" onclick="selectBarang(' . $row['id'] . ', \'' . $row['nama_barang'] . '\')">Pilih</button></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning">Barang tidak ditemukan.</div>';
    }
}
?>