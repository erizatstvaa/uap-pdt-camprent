<?php
require_once 'config.php';

echo "<h2>Tabel Pengguna</h2>";
$res = mysqli_query($conn, "SELECT * FROM pengguna");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id_pengguna'] . " | Nama: " . $row['nama'] . " | Peran: " . $row['peran'] . " | Email: " . $row['email'] . "<br>";
}

echo "<h2>Tabel Alat Camping</h2>";
$res = mysqli_query($conn, "SELECT * FROM alat_camping");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id_alat'] . " | Nama: " . $row['nama_alat'] . " | Stok: " . $row['stok'] . "<br>";
}

echo "<h2>Tabel Penyewaan</h2>";
$res = mysqli_query($conn, "SELECT * FROM penyewaan");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id_penyewaan'] . " | Pelanggan: " . $row['id_pelanggan'] . " | Tgl Sewa: " . $row['tgl_sewa'] . " | Tgl Kembali: " . $row['tgl_kembali_seharusnya'] . " | Total: " . $row['total_bayar'] . " | Status: " . $row['status_penyewaan'] . "<br>";
}

echo "<h2>Tabel Detail Penyewaan</h2>";
$res = mysqli_query($conn, "SELECT * FROM detail_penyewaan");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID Detail: " . $row['id_detail'] . " | ID Sewa: " . $row['id_penyewaan'] . " | ID Alat: " . $row['id_alat'] . " | Qty: " . $row['jumlah'] . "<br>";
}

echo "<h2>Tabel Pengembalian</h2>";
$res = mysqli_query($conn, "SELECT * FROM pengembalian");
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID Pengembalian: " . $row['id_pengembalian'] . " | ID Sewa: " . $row['id_penyewaan'] . " | Tgl Dikembalikan: " . $row['tgl_dikembalikan'] . " | Denda: " . $row['denda'] . "<br>";
}
?>
