<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Ambil statistik ringkas
$total_alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alat_camping"))['total'];
$total_sewa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status_penyewaan='disewa'"))['total'];
$total_terlambat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penyewaan WHERE status_penyewaan='terlambat'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - CampRent</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background-color: #2c3e50; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 15px; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #2c3e50; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 25px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 5px solid #27ae60; }
        .card.warning { border-left-color: #e67e22; }
        .card.danger { border-left-color: #e74c3c; }
        .card h3 { margin: 0 0 10px 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .card p { margin: 0; font-size: 28px; font-weight: bold; color: #2c3e50; }
        .menu-box { margin-top: 40px; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .menu-list { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px; }
        .btn-menu { padding: 12px 20px; background-color: #34495e; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-menu:hover { background-color: #2c3e50; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>CampRent Panel Admin</h2>
        <div>
            <span>Halo, <?= $_SESSION['nama']; ?> (Admin)</span>
            <a href="logout.php" style="background:#e74c3c; padding:5px 10px; border-radius:4px;">Logout</a>
        </div>
    </div>
    <div class="container">
        <h1>Selamat Datang di Sistem Manajemen CampRent</h1>
        <p>Gunakan panel ini untuk mengelola alat, memproses pengembalian, dan melihat laporan keuangan harian.</p>
        
        <div class="cards">
            <div class="card">
                <h3>Total Jenis Alat</h3>
                <p><?= $total_alat; ?></p>
            </div>
            <div class="card warning">
                <h3>Sedang Disewa</h3>
                <p><?= $total_sewa; ?></p>
            </div>
            <div class="card danger">
                <h3>Penyewaan Terlambat</h3>
                <p><?= $total_terlambat; ?></p>
            </div>
        </div>

        <div class="menu-box">
            <h2>Pintasan Manajemen Sistem</h2>
            <div class="menu-list">
                <a href="kelola_alat.php" class="btn-menu">📦 Kelola Alat Camping</a>
                <a href="proses_transaksi.php" class="btn-menu">🔄 Proses Sewa & Kembali</a>
                <a href="laporan_transaksi.php" class="btn-menu">📊 Laporan Transaksi</a>
                <a href="backup_db.php" class="btn-menu">💾 Backup Database Manual</a>
            </div>
        </div>
    </div>
</body>
</html>