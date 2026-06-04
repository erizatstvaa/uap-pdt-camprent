<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Aksi Update status menjadi DIKEMBALIKAN (Ini akan memicu TRIGGER mengembalikan stok otomatis)
if (isset($_GET['kembali'])) {
    $id_penyewaan = intval($_GET['kembali']);
    $status_skrg = $_GET['status'];
    
    $tgl_kembali_real = date('Y-m-d');
    $denda = 0;
    
    // Jika statusnya 'terlambat', beri denda flat Rp 50.000 sebagai contoh logika bisnis
    if ($status_skrg == 'terlambat') {
        $denda = 50000.00;
    }
    
    // 1. Masukkan log ke tabel pengembalian
    mysqli_query($conn, "INSERT INTO pengembalian (id_penyewaan, tgl_dikembalikan, denda) VALUES ($id_penyewaan, '$tgl_kembali_real', $denda)");
    
    // 2. Update status penyewaan -> TRIGGER DB AKAN JALAN MENAMBAH STOK
    mysqli_query($conn, "UPDATE penyewaan SET status_penyewaan = 'dikembalikan' WHERE id_penyewaan = $id_penyewaan");
    
    echo "<script>alert('Alat Berhasil Dikembalikan! Stok Otomatis Bertambah via Trigger Database.'); window.location.href='proses_transaksi.php';</script>";
}

// Ambil semua daftar transaksi penyewaan aktif (disewa / terlambat)
$query = "SELECT p.*, pg.nama as nama_pelanggan, ac.nama_alat, dp.jumlah 
          FROM penyewaan p 
          JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna
          JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
          JOIN alat_camping ac ON dp.id_alat = ac.id_alat 
          WHERE p.status_penyewaan IN ('disewa', 'terlambat')
          ORDER BY p.id_penyewaan ASC";
$transaksi = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses Transaksi - CampRent</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background-color: #2c3e50; color: white; padding: 15px 20px; }
        .navbar a { color: white; text-decoration: none; float: right; }
        .container { padding: 30px; max-width: 1100px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #34495e; color: white; }
        .status { padding: 3px 7px; border-radius: 3px; font-weight: bold; font-size: 12px; }
        .status.disewa { background: #ffeaa7; color: #d63031; }
        .status.terlambat { background: #ff7675; color: white; }
        .btn-action { padding: 6px 12px; background: #2980b9; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; }
        .btn-action:hover { background: #2471a3; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 5px solid #17a2b8; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard_admin.php">⬅ Kembali ke Dashboard</a>
        <h2>Monitoring Transaksi & Pengembalian Alat</h2>
    </div>
    <div class="container">
        <div class="alert-info">
            <strong>Info Sistem:</strong> Ketika Anda menekan tombol <strong>"Proses Pengembalian"</strong>, aplikasi akan mengupdate status transaksi ke database. Sistem Database MySQL akan otomatis memicu <strong>TRIGGER (AFTER UPDATE)</strong> untuk menambahkan kembali stok item tersebut tanpa penulisan query tambah stok manual di file PHP ini.
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pelanggan</th>
                    <th>Item Alat (Qty)</th>
                    <th>Batas Pengembalian</th>
                    <th>Status</th>
                    <th>Aksi Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($transaksi) == 0): ?>
                    <tr><td colspan="6" style="text-align:center; color:gray;">Tidak ada transaksi sewa yang sedang aktif berjalan.</td></tr>
                <?php endif; ?>
                <?php while($row = mysqli_fetch_assoc($transaksi)): ?>
                <tr>
                    <td>TRX-0<?= $row['id_penyewaan']; ?></td>
                    <td><?= $row['nama_pelanggan']; ?></td>
                    <td><?= $row['nama_alat']; ?> (<strong><?= $row['jumlah']; ?></strong>)</td>
                    <td><?= $row['tgl_kembali_seharusnya']; ?></td>
                    <td><span class="status <?= $row['status_penyewaan']; ?>"><?= strtoupper($row['status_penyewaan']); ?></span></td>
                    <td>
                        <a href="proses_transaksi.php?kembali=<?= $row['id_penyewaan']; ?>&status=<?= $row['status_penyewaan']; ?>" class="btn-action" onclick="return confirm('Konfirmasi pengembalian alat?')">✔ Terima Pengembalian</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>