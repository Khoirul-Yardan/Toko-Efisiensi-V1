<?php
// index.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOKO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen p-4">
    <div class="bg-white bg-opacity-10 backdrop-blur-lg p-8 rounded-xl shadow-lg text-center w-full max-w-2xl">
        <h2 class="text-2xl font-semibold mb-6">Silakan Pilih Layanan</h2>
        <div class="grid grid-cols-2 gap-4">
            <a href="peminjaman/index.php" class="relative group">
                <img src="images/peminjaman.png" alt="Peminjaman" class="rounded-lg shadow-lg transform group-hover:scale-105 transition">
                <span class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xl font-semibold opacity-0 group-hover:opacity-100 transition">Peminjaman</span>
            </a>
            <a href="kasir/index.php" class="relative group">
                <img src="images/kasir.png" alt="Penjualan" class="rounded-lg shadow-lg transform group-hover:scale-105 transition">
                <span class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xl font-semibold opacity-0 group-hover:opacity-100 transition">Penjualan</span>
            </a>
        </div>
    </div>
</body>
</html>
