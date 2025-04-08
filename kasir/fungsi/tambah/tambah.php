<?php

session_start();
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    if (!empty($_GET['kategori'])) {
        $nama= htmlentities(htmlentities($_POST['kategori']));
        $tgl= date("j F Y, G:i");
        $data[] = $nama;
        $data[] = $tgl;
        $sql = 'INSERT INTO kategori (nama_kategori,tgl_input) VALUES(?,?)';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=kategori&&success=tambah-data"</script>';
    }
    
    if (!empty($_GET['jual'])) {
        $id = $_GET['id'];

        // get tabel barang id_barang
        $sql = 'SELECT * FROM barang WHERE id_barang = ?';
        $row = $config->prepare($sql);
        $row->execute(array($id));
        $hsl = $row->fetch();

        if ($hsl['stok'] > 0) {
            $kasir =  $_GET['id_kasir'];
            $jumlah = 1;
            $total = $hsl['harga_jual'];
            $tgl = date("j F Y, G:i");

            $data1[] = $id;
            $data1[] = $kasir;
            $data1[] = $jumlah;
            $data1[] = $total;
            $data1[] = $tgl;

            $sql1 = 'INSERT INTO penjualan (id_barang,id_member,jumlah,total,tanggal_input) VALUES (?,?,?,?,?)';
            $row1 = $config -> prepare($sql1);
            $row1 -> execute($data1);

            echo '<script>window.location="../../index.php?page=jual&success=tambah-data"</script>';
        } else {
            echo '<script>alert("Stok Barang Anda Telah Habis !");
					window.location="../../index.php?page=jual#keranjang"</script>';
        }
    }
}




// Include database connection
if (!empty($_SESSION['admin'])) {
    require '../../config.php'; // Include your database configuration

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get form data
        $id_barang = $_POST['id'];
        $kategori = $_POST['kategori'];
        $nama_barang = $_POST['nama'];
        $merk = $_POST['merk'];
        $harga_beli = $_POST['beli'];
        $harga_jual = $_POST['jual'];
        $satuan_barang = $_POST['satuan'];
        $stok = $_POST['stok']; // This is the stock to be set in the barang table
        $stok_gudang = $_POST['stok_gudang']; // This is the initial stock in the warehouse
        $tgl = date("j F Y, G:i"); // Current date and time

        // Prepare SQL statement to insert into barang
        $sql_barang = "INSERT INTO barang (id_barang, id_kategori, nama_barang, merk, harga_beli, harga_jual, satuan_barang, stok, stok_gudang, tgl_input) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_barang = $config->prepare($sql_barang);
        
        // Execute the statement for barang
        if ($stmt_barang->execute([$id_barang, $kategori, $nama_barang, $merk, $harga_beli, $harga_jual, $satuan_barang, $stok, $stok_gudang, $tgl])) {
            // Update stok_gudang after inserting into barang
            $new_stok_gudang = $stok_gudang - $stok; // Calculate new stok_gudang
            
            // Prepare SQL statement to update stok_gudang in barang
            $sql_update = "UPDATE barang SET stok_gudang=? WHERE id_barang=?";
            $stmt_update = $config->prepare($sql_update);
            
            // Execute the update statement
            if ($stmt_update->execute([$new_stok_gudang, $id_barang])) {
                header("Location: ../../index.php?page=barang/gudang&success=1"); // Redirect with success message
            } else {
                header("Location: ../../index.php?page=barang/gudang&error=1"); // Redirect with error message
            }
        } else {
            header("Location: ../../index.php?page=barang/gudang&error=1"); // Redirect with error message
        }
    } 
  
   
   
  
}



?>

  
    
