<?php
require 'koneksi.php';

// Proses update password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $new_password = $conn->real_escape_string($_POST['new_password']);

    // Update password di tabel settings
    $sql = "INSERT INTO settings (password) VALUES ('$new_password') ON DUPLICATE KEY UPDATE password='$new_password'";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Password berhasil diperbarui!');</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . $conn->error . "');</script>";
    }
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
</head>
<body>

<div class="container mt-5">
    <h2>Pengaturan Password untuk Reset</h2>
    <form id="formSetting" method="POST" onsubmit="return false;">
        <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="text" name="new_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" onclick="updatePassword()">Update Password</button>
    </form>
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
                alert('Password berhasil diperbarui!');
            },
            error: function() {
                alert('Terjadi kesalahan saat memperbarui password.');
            }
        });
    }
</script>

</body>
</html>