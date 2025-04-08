function searchBarang() {
    var input = document.getElementById("search_barang").value.toLowerCase();
    var rows = document.querySelectorAll("#barang-list tr");
    rows.forEach(function(row) {
        var itemName = row.cells[0].textContent.toLowerCase();
        if (itemName.includes(input)) {
            row.style.display = ""; // Show row
        } else {
            row.style.display = "none"; // Hide row
        }
    });
}

function addToKasir(id, nama) {
    var jumlah = document.getElementById('jumlah-input').value || 1; // Ambil jumlah dari input, default 1 jika tidak ada

    $.ajax({
        url: '',
        type: 'POST',
        data: {
            id_barang: id,
            jumlah: jumlah,
            tambah_kasir: 1
        },
        success: function(response) {
            // Langsung refresh halaman setelah menambahkan barang
            location.reload();
        },
        error: function() {
            Swal.fire('Error!', 'Terjadi kesalahan saat menambahkan barang.', 'error');
        }
    });
}

function updateJumlah(id, jumlahBaru, harga) {
    // Hitung total harga
    var total_harga = jumlahBaru * harga; 
    document.getElementById('total-harga-' + id).innerText = 'Rp ' + total_harga.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); // Update total harga di tabel

    // Update grand total
    var grandTotal = 0;
    var rows = document.querySelectorAll("#data-kasir tr");
    rows.forEach(function(row) {
        var total = parseFloat(row.querySelector('td[id^="total-harga-"]').innerText.replace('Rp ', '').replace('.', '').replace(',', '.'));
        grandTotal += total; // Tambahkan ke grand total
    });
    document.getElementById('grand-total').innerText = grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); // Update grand total di tampilan
}

function hapusKasir(id) {
    $.ajax({
        url: '',
        type: 'POST',
        data: { id_kasir: id, hapus_kasir: 1 },
        success: function(response) {
            // Langsung refresh halaman setelah menghapus barang
            location.reload();
        },
        error: function() {
            Swal.fire('Error!', 'Terjadi kesalahan saat menghapus barang.', 'error');
        }
    });
}

function prosesPeminjaman() {
    // Ambil data dari form
    var nama_peminjam = $("input[name='nama_peminjam']").val();
    var tenggat_waktu = $("input[name='tenggat_waktu']").val();

    // Ambil data dari kasir
    var kasirData = [];
    $('#data-kasir tr').each(function() {
        var id_barang = $(this).attr('id').split('-')[1]; // Ambil ID barang dari ID elemen
        var jumlah = parseInt($(this).find('span[id^="jumlah-"]').text()); // Ambil jumlah dari elemen span
        kasirData.push({ id_barang: id_barang, jumlah: jumlah });
    });

    // Pastikan ada data di kasir
    if (kasirData.length === 0) {
        alert("Tidak ada barang di kasir.");
        return;
    }

    // Kirim data peminjaman
    $.post('peminjaman.php', {
        proses_peminjaman: true,
        nama_peminjam: nama_peminjam,
        tenggat_waktu: tenggat_waktu
    }, function(response) {
        var data = JSON.parse(response);
        if (data.status === 'success') {
            // Redirect ke nota.php dengan parameter nama peminjam
            window.location.href = 'nota.php?nama_peminjam=' + encodeURIComponent(nama_peminjam);
        } else {
            alert(data.message);
        }
    });
}

function resetKasir() {
    Swal.fire({
        title: 'Konfirmasi',
        text: "Apakah Anda yakin ingin mereset kasir?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '',
                type: 'POST',
                data: { reset_kasir: 1 },
                success: function(response) {
                    // Langsung refresh halaman setelah mereset kasir
                    location.reload();
                },
                error: function() {
                    Swal.fire('Error!', 'Terjadi kesalahan saat mereset kasir.', 'error');
                }
            });
        }
    });
}