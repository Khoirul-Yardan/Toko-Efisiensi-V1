<?php
session_start();
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    if(isset($_GET['pengaturan']) && $_GET['pengaturan'] == 'ubah'){
        $nama_toko    = $_POST['namatoko'];
        $alamat_toko  = $_POST['alamat'];
        $tlp          = $_POST['kontak'];
        $pemilik      = $_POST['pemilik'];
        $password_laporan = $_POST['password_laporan']; // Ambil password laporan dari form
        $password_sidebar = $_POST['password_sidebar']; // Ambil password sidebar dari form

        // Proses upload file
        $logo         = $_FILES['logo']['name'];
        $tmp_logo     = $_FILES['logo']['tmp_name'];

        // Apabila user tidak upload logo baru, $logo akan kosong
        if(!empty($logo)){
            // Buat nama file unik (misal dengan time())
            $ext = pathinfo($logo, PATHINFO_EXTENSION);
            $newLogoName = 'logo_'.time().'.'.$ext;

            // Pindahkan ke folder images/logo
            move_uploaded_file($tmp_logo, '../../images/logo/'.$newLogoName);

            // Update query dengan logo
            $q = "UPDATE toko SET 
                    nama_toko='$nama_toko',
                    alamat_toko='$alamat_toko',
                    tlp='$tlp',
                    nama_pemilik='$pemilik',
                    logo='$newLogoName'";
        } else {
            // Update query tanpa logo
            $q = "UPDATE toko SET 
                    nama_toko='$nama_toko',
                    alamat_toko='$alamat_toko',
                    tlp='$tlp',
                    nama_pemilik='$pemilik'";
        }

        // Jika password laporan tidak kosong, tambahkan ke query
        if (!empty($password_laporan)) {
            $q .= ", password_laporan='$password_laporan'"; // Menggunakan kolom password_laporan
        }

        // Jika password sidebar tidak kosong, tambahkan ke query
        if (!empty($password_sidebar)) {
            $q .= ", password_sidebar='$password_sidebar'"; // Menggunakan kolom password_sidebar
        }

        $q .= " WHERE id_toko='1'"; // Pastikan id_toko sesuai dengan yang Anda inginkan

        $sql = $config->query($q);
        if($sql){
            header('Location: ../../pengaturan.php?success=1');
        } else {
            echo "Gagal update pengaturan";
        }
    }

    if (!empty($_GET['kategori'])) {
        $nama= htmlentities($_POST['kategori']);
        $id= htmlentities($_POST['id']);
        $data[] = $nama;
        $data[] = $id;
        $sql = 'UPDATE kategori SET  nama_kategori=? WHERE id_kategori=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=kategori&uid='.$id.'&success-edit=edit-data"</script>';
    }

    if (!empty($_GET['stok'])) {
        $restok = htmlentities($_POST['restok']);
        $id = htmlentities($_POST['id']);
        $dataS[] = $id;
    
        // Get current stock and warehouse stock
        $sqlS = 'SELECT * FROM barang WHERE id_barang=?';
        $rowS = $config->prepare($sqlS);
        $rowS->execute($dataS);
        $hasil = $rowS->fetch();
    
        // Calculate new stock values
        $newStok = $restok + $hasil['stok']; // Update the stock
        $newStokGudang = $hasil['stok_gudang'] - $restok; // Increase the warehouse stock
    
        // Update the stock in the database
        $data[] = $newStok;
        $data[] = $newStokGudang;
        $data[] = $id;
    
        $sql = 'UPDATE barang SET stok=?, stok_gudang=? WHERE id_barang=?';
        $row = $config->prepare($sql);
        $row->execute($data);
        
        echo '<script>window.location="../../index.php?page=barang&success-stok=stok-data"</script>';
    }
    if (isset($_GET['restok_gudang'])) {
        $id = $_POST['id'];
        $restok = $_POST['restok'];
        $faktur = $_POST['faktur'];
        $tgl_masuk = $_POST['tgl_masuk'];
    
        // Update hanya stok gudang
        $sql = "UPDATE barang SET stok_gudang = stok_gudang + ? WHERE id_barang = ?";
        $stmt = $config->prepare($sql);
        $stmt->execute([$restok, $id]);
    
        // Simpan riwayat restok
        $sqlRiwayat = "INSERT INTO riwayat_restok (id_barang, jumlah_restok, faktur, tgl_masuk) VALUES (?, ?, ?, ?)";
        $stmtRiwayat = $config->prepare($sqlRiwayat);
        $stmtRiwayat->execute([$id, $restok, $faktur, $tgl_masuk]);
    
        echo '<script>window.location="../../index.php?page=barang/gudang&success-stok=stok-data"</script>';
    }


    if (!empty($_GET['barang'])) {
        $id = htmlentities($_POST['id']);
        $kategori = htmlentities($_POST['kategori']);
        $nama = htmlentities($_POST['nama']);
        $merk = htmlentities($_POST['merk']);
        $beli = htmlentities($_POST['beli']);
        $jual = htmlentities($_POST['jual']);
        $satuan = htmlentities($_POST['satuan']);
        $stok = htmlentities($_POST['stok']);
        $tgl = htmlentities($_POST['tgl']);

        $data[] = $kategori;
        $data[] = $nama;
        $data[] = $merk;
        $data[] = $beli;
        $data[] = $jual;
        $data[] = $satuan;
        $data[] = $stok;
        $data[] = $tgl;
        $data[] = $id;
        $sql = 'UPDATE barang SET id_kategori=?, nama_barang=?, merk=?, 
				harga_beli=?, harga_jual=?, satuan_barang=?, stok=?, tgl_update=?  WHERE id_barang=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=barang/edit&barang='.$id.'&success=edit-data"</script>';
    }

    if (!empty($_GET['gambar'])) {
        $id = htmlentities($_POST['id']);
        set_time_limit(0);
        $allowedImageType = array("image/gif", "image/JPG", "image/jpeg", "image/pjpeg", "image/png", "image/x-png", 'image/webp');
        $filepath = $_FILES['foto']['tmp_name'];
        $fileSize = filesize($filepath);
        $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
        $filetype = finfo_file($fileinfo, $filepath);
        $allowedTypes = [
            'image/png'   => 'png',
            'image/jpeg'  => 'jpg',
            'image/gif'   => 'gif',
            'image/jpg'   => 'jpeg',
            'image/webp'  => 'webp'
        ];
        if(!in_array($filetype, array_keys($allowedTypes))) {
            echo '<script>alert("You can only upload JPG, PNG and GIF file");window.location="../../index.php?page=user"</script>';
            exit;
        }else if ($_FILES['foto']["error"] > 0) {
            echo '<script>alert("You can only upload JPG, PNG and GIF file");window.location="../../index.php?page=user"</script>';
            exit;
        } elseif (!in_array($_FILES['foto']["type"], $allowedImageType)) {
            // echo "You can only upload JPG, PNG and GIF file";
            // echo "<font face='Verdana' size='2' ><BR><BR><BR>
            // 		<a href='../../index.php?page=user'>Back to upform</a><BR>";
            echo '<script>alert("You can only upload JPG, PNG and GIF file");window.location="../../index.php?page=user"</script>';
            exit;
        } elseif (round($_FILES['foto']["size"] / 1024) > 4096) {
            // echo "WARNING !!! Besar Gambar Tidak Boleh Lebih Dari 4 MB";
            // echo "<font face='Verdana' size='2' ><BR><BR><BR>
            // 		<a href='../../index.php?page=user'>Back to upform</a><BR>";
            echo '<script>alert("WARNING !!! Besar Gambar Tidak Boleh Lebih Dari 4 MB");window.location="../../index.php?page=user"</script>';
            exit;
        } else {
            $dir = '../../assets/img/user/';
            $tmp_name = $_FILES['foto']['tmp_name'];
            $name = time().basename($_FILES['foto']['name']);
            if (move_uploaded_file($tmp_name, $dir.$name)) {
                //post foto lama
                $foto2 = $_POST['foto2'];
                //remove foto di direktori
                unlink('../../assets/img/user/'.$foto2.'');
                //input foto
                $id = $_POST['id'];
                $data[] = $name;
                $data[] = $id;
                $sql = 'UPDATE member SET gambar=?  WHERE member.id_member=?';
                $row = $config -> prepare($sql);
                $row -> execute($data);
                echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
            } else {
                echo '<script>alert("Masukan Gambar !");window.location="../../index.php?page=user"</script>';
                exit;
            }
        }
    }

    if (!empty($_GET['profil'])) {
        $id = htmlentities($_POST['id']);
        $nama = htmlentities($_POST['nama']);
        $alamat = htmlentities($_POST['alamat']);
        $tlp = htmlentities($_POST['tlp']);
        $email = htmlentities($_POST['email']);
        $nik = htmlentities($_POST['nik']);

        $data[] = $nama;
        $data[] = $alamat;
        $data[] = $tlp;
        $data[] = $email;
        $data[] = $nik;
        $data[] = $id;
        $sql = 'UPDATE member SET nm_member=?,alamat_member=?,telepon=?,email=?,NIK=? WHERE id_member=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
    }
    
    if (!empty($_GET['pass'])) {
        $id = htmlentities($_POST['id']);
        $user = htmlentities($_POST['user']);
        $pass = htmlentities($_POST['pass']);

        $data[] = $user;
        $data[] = $pass;
        $data[] = $id;
        $sql = 'UPDATE login SET user=?,pass=md5(?) WHERE id_member=?';
        $row = $config -> prepare($sql);
        $row -> execute($data);
        echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
    }

    if (!empty($_GET['jual'])) {
        $id = htmlentities($_POST['id']);
        $id_barang = htmlentities($_POST['id_barang']);
        $jumlah = htmlentities($_POST['jumlah']);

        $sql_tampil = "select *from barang where barang.id_barang=?";
        $row_tampil = $config -> prepare($sql_tampil);
        $row_tampil -> execute(array($id_barang));
        $hasil = $row_tampil -> fetch();

        if ($hasil['stok'] > $jumlah) {
            $jual = $hasil['harga_jual'];
            $total = $jual * $jumlah;
            $data1[] = $jumlah;
            $data1[] = $total;
            $data1[] = $id;
            $sql1 = 'UPDATE penjualan SET jumlah=?,total=? WHERE id_penjualan=?';
            $row1 = $config -> prepare($sql1);
            $row1 -> execute($data1);
            echo '<script>window.location="../../index.php?page=jual#keranjang"</script>';
        } else {
            echo '<script>alert("Keranjang Melebihi Stok Barang Anda !");
					window.location="../../index.php?page=jual#keranjang"</script>';
        }
    }


// Search for automatic add to cart
if (!empty($_GET['cari_barang'])) {
    $cari = trim(strip_tags($_POST['keyword']));
    if ($cari != '') {
        $sql = "SELECT barang.*, kategori.id_kategori, kategori.nama_kategori
                FROM barang 
                INNER JOIN kategori ON barang.id_kategori = kategori.id_kategori
                WHERE barang.id_barang LIKE ? 
                OR barang.nama_barang LIKE ? 
                OR barang.merk LIKE ? 
                OR barang.kode_barcode LIKE ?"; // Added barcode search
        $row = $config->prepare($sql);
        $row->execute(array("%$cari%", "%$cari%", "%$cari%", "%$cari%")); // Pass the search term for all fields
        $hasil1 = $row->fetchAll();
        
        // Display results in a table
        ?>
        <table class="table table-stripped" width="100%" id="example2">
            <tr>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Harga Jual</th>
                <th>Aksi</th>
            </tr>
        <?php foreach ($hasil1 as $hasil) {?>
            <tr>
                <td><?php echo $hasil['id_barang'];?></td>
                <td><?php echo $hasil['nama_barang'];?></td>
                <td><?php echo $hasil['merk'];?></td>
                <td>Rp. <?php echo number_format($hasil['harga_jual']);?></td>
                <td>
                    <a href="fungsi/tambah/tambah.php?jual=jual&id=<?php echo $hasil['id_barang'];?>&id_kasir=<?php echo $_SESSION['admin']['id_member'];?>" 
                       class="btn btn-success add-to-cart" data-id="<?php echo $hasil['id_barang'];?>">
                       <i class="fa fa-shopping-cart"></i>
                    </a>
                </td>
            </tr>
        <?php }?>
        </table>
        <?php
    }
}}
// Search for sorted items
// Ambil data barang yang disembunyikan dari localStorage
$hiddenItems = json_decode($_POST['hiddenItems'], true); // Pastikan Anda mengirimkan data ini dari frontend

if (!empty($_GET['cari_barang_sorted'])) {
    $cari = trim(strip_tags($_POST['keyword']));
    if ($cari != '') {
        // Buat placeholder untuk parameter yang akan digunakan dalam query
        $placeholders = implode(',', array_fill(0, count($hiddenItems), '?'));
        
        // Query untuk mencari barang yang tidak ada di hidden items
        $sql = "SELECT barang.*, kategori.id_kategori, kategori.nama_kategori
                FROM barang 
                INNER JOIN kategori ON barang.id_kategori = kategori.id_kategori
                WHERE (barang.id_barang LIKE ? 
                OR barang.nama_barang LIKE ? 
                OR barang.merk LIKE ? 
                OR barang.kode_barcode LIKE ?)
                AND barang.id_barang NOT IN ($placeholders)"; // Tambahkan kondisi untuk mengecualikan barang yang disembunyikan

        $params = array_merge(["%$cari%", "%$cari%", "%$cari%", "%$cari%"], $hiddenItems);
        $row = $config->prepare($sql);
        $row->execute($params);
        $hasil1 = $row->fetchAll();
        
        // Tampilkan hasil pencarian
        ?>
        <table class="table table-stripped" width="100%" id="example2">
            <tr>
                <th>ID Barang</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Harga Jual</th>
                <th>Aksi</th>
            </tr>
        <?php foreach ($hasil1 as $hasil) {?>
            <tr>
                <td><?php echo $hasil['id_barang'];?></td>
                <td><?php echo $hasil['nama_barang'];?></td>
                <td><?php echo $hasil['merk'];?></td>
                <td>Rp. <?php echo number_format($hasil['harga_jual']);?></td>
                <td>
                    <a href="fungsi/tambah/tambah.php?jual=jual&id=<?php echo $hasil['id_barang'];?>&id_kasir=<?php echo $_SESSION['admin']['id_member'];?>" 
                       class="btn btn-success">
                       <i class="fa fa-shopping-cart"></i>
                    </a>
                </td>
            </tr>
        <?php }?>
        </table>
        <?php
    }
}
    

