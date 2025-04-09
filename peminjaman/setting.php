<?php
require 'koneksi.php';

// Proses update password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_password = $conn->real_escape_string($_POST['new_password']);

    // Update password di tabel settings
    $sql = "INSERT INTO settings (password) VALUES ('$new_password') ON DUPLICATE KEY UPDATE password='$new_password'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Password berhasil diperbarui!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $conn->error]);
    }
    exit; // Pastikan untuk menghentikan eksekusi script setelah mengirim respons
}

// Proses reset data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_settings'])) {
    $sql = "DELETE FROM settings";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $conn->error]);
    }
    exit; // Pastikan untuk menghentikan eksekusi script setelah mengirim respons
}

// Ambil password yang ada
$result = $conn->query("SELECT password FROM settings LIMIT 1");
$current_password = $result->fetch_assoc()['password'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Manajemen Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">DASHBOARD</h4>
    <a href="dashboard.php">Dashboard</a>
    <a href="peminjaman.php">Peminjaman</a>
    <a href="pengembalian.php">Pengembalian</a>
    <a href="setting.php">Settings</a>
    <a href="../index.php">Home</a>
</div>

<!-- Konten -->
<div class="content">
    <div class="container mt-5">
        <h2>Pengaturan Password untuk Reset</h2>
        <form id="formSetting" method="POST" onsubmit="return false;">
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="text" name="new_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" onclick="updatePassword()">Update Password</button>
            <button type="button" class="btn btn-danger" onclick="resetSettings()">Reset</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updatePassword() {
        var formData = $('#formSetting').serialize();
        $.ajax({
            url: '',
            type: 'POST',
            data: formData + '&update_password=1',
            success: function(response) {
                var result = JSON.parse(response);
                Swal.fire(result.status === 'success' ? 'Berhasil!' : 'Error!', result.message, result.status);
            },
            error: function() {
                Swal.fire('Error!', 'Terjadi kesalahan saat memperbarui password.', 'error');
            }
        });
    }

    function resetSettings() {
        if (confirm('Apakah Anda yakin ingin menghapus semua data di tabel settings?')) {
            $.ajax({
                url: '',
                type: 'POST',
                data: { reset_settings: 1 },
                success: function(response) {
                    var result = JSON.parse(response);
                    Swal.fire(result.status === 'success' ? 'Berhasil!' : 'Error!', result.message, result.status);
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data.', 'error');
                }
            });
        }
    }
</script>

</body>
</html>