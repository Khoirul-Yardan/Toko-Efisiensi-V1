<?php
class View
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Fungsi untuk mengambil data member
    public function member()
    {
        $sql = "SELECT member.*, login.*
                FROM member INNER JOIN login ON member.id_member = login.id_member";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetchAll();
    }

    // Fungsi untuk mengedit member
    public function member_edit($id)
    {
        $sql = "SELECT member.*, login.*
                FROM member INNER JOIN login ON member.id_member = login.id_member
                WHERE member.id_member = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        return $row->fetch();
    }

    // Fungsi untuk mengambil data toko
    public function toko()
    {
        $sql = "SELECT * FROM toko WHERE id_toko = '1'";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk mengambil kategori
    public function kategori()
    {
        $sql = "SELECT * FROM kategori";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetchAll();
    }

    // Fungsi untuk mengambil data barang
    public function barang()
    {
        $sql = "SELECT barang.*, kategori.id_kategori, kategori.nama_kategori
                FROM barang INNER JOIN kategori ON barang.id_kategori = kategori.id_kategori 
                ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetchAll();
    }

    // Fungsi untuk mengambil data barang dengan stok rendah
    public function barang_stok()
    {
        $sql = "SELECT barang.*, kategori.id_kategori, kategori.nama_kategori
                FROM barang INNER JOIN kategori ON barang.id_kategori = kategori.id_kategori 
                WHERE stok <= 3 
                ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetchAll();
    }

    // Fungsi untuk mengedit barang
    public function barang_edit($id)
    {
        $sql = "SELECT barang.*, kategori.id_kategori, kategori.nama_kategori
                FROM barang INNER JOIN kategori ON barang.id_kategori = kategori.id_kategori
                WHERE id_barang = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        return $row->fetch();
    }

    // Fungsi untuk mencari barang
    public function barang_cari($cari)
    {
        $sql = "SELECT barang.*, kategori.id_kategori, kategori.nama_kategori
                FROM barang INNER JOIN kategori ON barang.id_kategori = kategori.id_kategori
                WHERE id_barang LIKE ? OR nama_barang LIKE ? OR merk LIKE ?";
        $row = $this->db->prepare($sql);
        $row->execute(array("%$cari%", "%$cari%", "%$cari%"));
        return $row->fetchAll();
    }

    // Fungsi untuk mendapatkan ID barang baru
    public function barang_id()
    {
        $sql = 'SELECT * FROM barang ORDER BY id DESC';
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();

        $urut = substr($hasil['id_barang'], 2, 3);
        $tambah = (int)$urut + 1;
        if (strlen($tambah) == 1) {
            return 'BR00' . $tambah;
        } elseif (strlen($tambah) == 2) {
            return 'BR0' . $tambah;
        } else {
            $ex = explode('BR', $hasil['id_barang']);
            $no = (int)$ex[1] + 1;
            return 'BR' . $no;
        }
    }

    // Fungsi untuk mengedit kategori
    public function kategori_edit($id)
    {
        $sql = "SELECT * FROM kategori WHERE id_kategori = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        return $row->fetch();
    }

    // Fungsi untuk menghitung jumlah kategori
    public function kategori_row()
    {
        $sql = "SELECT * FROM kategori";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->rowCount();
    }

    // Fungsi untuk menghitung jumlah barang
    public function barang_row()
    {
        $sql = "SELECT * FROM barang";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->rowCount();
    }

    // Fungsi untuk menghitung total stok barang
    public function barang_stok_row()
    {
        $sql = "SELECT SUM(stok) AS jml FROM barang";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk menghitung total harga beli barang
    public function barang_beli_row()
    {
        $sql = "SELECT SUM(harga_beli) AS beli FROM barang";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk menghitung total penjualan
    public function jual_row()
    {
        $sql = "SELECT SUM(jumlah) AS stok FROM nota";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk mengambil data penjualan
    public function jual()
    {
        $sql = "SELECT nota.*, barang.id_barang, barang.nama_barang, barang.harga_beli, member.id_member,
                member.nm_member FROM nota 
                LEFT JOIN barang ON barang.id_barang = nota.id_barang 
                LEFT JOIN member ON member.id_member = nota.id_member 
                WHERE nota.periode = ?
                ORDER BY id_nota DESC";
        $row = $this->db->prepare($sql);
        $row->execute(array(date('m-Y')));
        return $row->fetchAll();
    }

    // Fungsi untuk mengambil data penjualan berdasarkan periode
    public function periode_jual($periode)
    {
        $sql = "SELECT nota.*, barang.id_barang, barang.nama_barang, barang.harga_beli, member.id_member,
                member.nm_member FROM nota 
                LEFT JOIN barang ON barang.id_barang = nota.id_barang 
                LEFT JOIN member ON member.id_member = nota.id_member 
                WHERE nota.periode = ? 
                ORDER BY id_nota ASC";
        $row = $this->db->prepare($sql);
        $row->execute(array($periode));
        return $row->fetchAll();
    }

    // Fungsi untuk mengambil data penjualan berdasarkan hari
    public function hari_jual($hari)
    {
        $ex = explode('-', $hari);
        $monthNum  = $ex[1];
        $monthName = date('F', mktime(0, 0, 0, $monthNum, 10));
        $tgl = str_pad($ex[2], 2, '0', STR_PAD_LEFT);
        $cek = $tgl . ' ' . $monthName . ' ' . $ex[0];
        $param = "%{$cek}%";
        $sql = "SELECT nota.*, barang.id_barang, barang.nama_barang, barang.harga_beli, member.id_member,
                member.nm_member FROM nota 
                LEFT JOIN barang ON barang.id_barang = nota.id_barang 
                LEFT JOIN member ON member.id_member = nota.id_member 
                WHERE nota.tanggal_input LIKE ? 
                ORDER BY id_nota ASC";
        $row = $this->db->prepare($sql);
        $row->execute(array($param));
        return $row->fetchAll();
    }

    // Fungsi untuk mengambil data penjualan
    public function penjualan()
    {
        $sql = "SELECT penjualan.*, barang.id_barang, barang.nama_barang, member.id_member,
                member.nm_member FROM penjualan 
                LEFT JOIN barang ON barang.id_barang = penjualan.id_barang 
                LEFT JOIN member ON member.id_member = penjualan.id_member
                ORDER BY id_penjualan";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetchAll();
    }

    // Fungsi untuk menghitung total pembayaran dari penjualan
    public function jumlah()
    {
        $sql = "SELECT SUM(total) AS bayar FROM penjualan";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk menghitung total pembayaran dari nota
    public function jumlah_nota()
    {
        $sql = "SELECT SUM(total) AS bayar FROM nota";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk menghitung total harga beli berdasarkan stok
    public function jml()
    {
        $sql = "SELECT SUM(harga_beli * stok) AS byr FROM barang";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }

    // Fungsi untuk mengambil data gudang
    public function gudang()
    {
        $sql = "SELECT gudang.*, kategori.id_kategori, kategori.nama_kategori
                FROM gudang INNER JOIN kategori ON gudang.id_kategori = kategori.id_kategori 
                ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetchAll();
    }

    // Fungsi untuk mengedit data gudang
    public function gudang_edit($id)
    {
        $sql = "SELECT gudang.*, kategori.id_kategori, kategori.nama_kategori
                FROM gudang INNER JOIN kategori ON gudang.id_kategori = kategori.id_kategori
                WHERE id_barang = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        return $row->fetch();
    }

    // Fungsi untuk mendapatkan ID barang baru untuk gudang
    public function gudang_id()
    {
        $sql = 'SELECT * FROM gudang ORDER BY id DESC';
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();

        $urut = substr($hasil['id_barang'], 2, 3);
        $tambah = (int)$urut + 1;
        if (strlen($tambah) == 1) {
            return 'GD00' . $tambah;
        } elseif (strlen($tambah) == 2) {
            return 'GD0' . $tambah;
        } else {
            $ex = explode('GD', $hasil['id_barang']);
            $no = (int)$ex[1] + 1;
            return 'GD' . $no;
        }
    }

    // Fungsi untuk menghitung jumlah barang di gudang
    public function gudang_row()
    {
        $sql = "SELECT * FROM gudang";
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->rowCount();
    }

    // Fungsi untuk menghitung total stok di gudang
    public function gudang_stok_row()
    {
        $sql = "SELECT SUM(stok_gudang) AS jml FROM gudang"; // Pastikan menggunakan stok_gudang
        $row = $this->db->prepare($sql);
        $row->execute();
        return $row->fetch();
    }
    public function nota_per_periode($periode) {
        $sql = "SELECT * FROM nota WHERE DATE_FORMAT(tanggal, '%Y-%m') = :periode";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':periode', $periode);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function barang_detail($id_barang) {
        $sql = "SELECT * FROM barang WHERE id_barang = :id_barang";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function generateNomorTransaksi() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM nota WHERE DATE(tanggal_input) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
        $result = $stmt->fetch();
    
        $count = $result['count'] + 1; // Menambahkan 1 untuk nomor transaksi baru
        $nomor_transaksi = date('Ymd') . sprintf('%03d', $count); // Format: YYYYMMDDXXX
        return $nomor_transaksi;
    }

    
}

?>