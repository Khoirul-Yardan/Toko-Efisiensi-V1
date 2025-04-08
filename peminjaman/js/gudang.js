$(document).ready(function () {
    $('#barangTable').DataTable();
    $('#historyRestokTable').DataTable();
});

let lastTotal = 0;

// Mengatur untuk memeriksa perubahan setiap 5 detik
setInterval(function() {
    $.ajax({
        url: '',
        type: 'GET',
        data: { check_update: true },
        success: function(response) {
            var result = JSON.parse(response);
            if (result.total !== lastTotal) {
                lastTotal = result.total;
                location.reload(); // Refresh halaman jika ada perubahan
            }
        },
        error: function() {
            console.error('Error checking for updates.');
        }
    });
}, 5000); // Cek setiap 5 detik

function tambahBarang() {
    var formData = $('#formTambahBarang').serialize();
    $.ajax({
        url: '',
        type: 'POST',
        data: formData + '&tambah_barang=1', // Menambahkan parameter untuk menandai permintaan
        success: function(response) {
            // Tidak perlu memeriksa status, langsung refresh
            location.reload(); // Reload halaman untuk melihat perubahan
        },
        error: function() {
            alert('Terjadi kesalahan saat menambah data.');
        }
    });
}

function editHapusBarang(id, nama, harga, jumlah) {
    document.getElementById("id_barang").value = id;
    document.getElementById("edit_nama_barang").value = nama;
    document.getElementById("edit_harga").value = harga;
    document.getElementById("edit_jumlah").value = jumlah;
    new bootstrap.Modal(document.getElementById('modalBarang')).show();
}

function simpanPerubahan() {
    var id = document.getElementById("id_barang").value;
    var nama = document.getElementById("edit_nama_barang").value;
    var harga = document.getElementById("edit_harga").value;
    var jumlah = document.getElementById("edit_jumlah").value;

    $.ajax({
        url: '',
        type: 'POST',
        data: {
            action: 'edit',
            id: id,
            nama_barang: nama,
            harga: harga,
            jumlah: jumlah
        },
        success: function(response) {
            // Tidak perlu memeriksa status, langsung refresh
            location.reload(); // Reload halaman untuk melihat perubahan
        },
        error: function() {
            alert('Terjadi kesalahan saat menyimpan data.');
        }
    });
}

function hapusBarang() {
    var id = document.getElementById("id_barang").value;

    Swal.fire({
        title: "Yakin ingin menghapus?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Hapus"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'hapus',
                    id: id
                },
                success: function(response) {
                    // Tidak perlu memeriksa status, langsung refresh
                    location.reload(); // Reload halaman untuk melihat perubahan
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus data.');
                }
            });
        }
    });
}

function restokBarang(id) {
    document.getElementById("id_barang_restok").value = id;
    new bootstrap.Modal(document.getElementById('modalRestok')).show();
}

function restokBarangSubmit() {
    var id_barang = document.getElementById("id_barang_restok").value;
    var jumlah = document.getElementById("jumlah_restok").value;
    var supplier = document.getElementById("supplier").value;

    $.ajax({
        url: '',
        type: 'POST',
        data: {
            action: 'restok',
            id_barang: id_barang,
            jumlah: jumlah,
            supplier: supplier
        },
        success: function(response) {
            // Tidak perlu memeriksa status, langsung refresh
            location.reload(); // Reload halaman untuk melihat perubahan
        },
        error: function() {
            alert('Terjadi kesalahan saat melakukan restok.');
        }
    });
}

function resetHistory() {
    Swal.fire({
        title: "Yakin ingin mereset history restok?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Reset"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'reset_history'
                },
                success: function(response) {
                    // Tidak perlu memeriksa status, langsung refresh
                    location.reload(); // Reload halaman untuk melihat perubahan
                },
                error: function() {
                    alert('Terjadi kesalahan saat mereset history.');
                }
            });
        }
    });
}

