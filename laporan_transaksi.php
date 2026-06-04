<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Ambil data gabungan laporan transaksi dari database
$query = "SELECT p.id_penyewaan, pg.nama as nama_pelanggan, ac.nama_alat, dp.jumlah, p.tgl_sewa, p.total_bayar, k.tgl_dikembalikan, k.denda
          FROM penyewaan p
          JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna
          JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
          JOIN alat_camping ac ON dp.id_alat = ac.id_alat
          LEFT JOIN pengembalian k ON p.id_penyewaan = k.id_penyewaan
          WHERE p.status_penyewaan = 'dikembalikan'
          ORDER BY k.tgl_dikembalikan DESC";
$result = mysqli_query($conn, $query);

$total_pendapatan = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - CampRent</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background-color: #2c3e50; color: white; padding: 15px 20px; }
        .navbar a { color: white; text-decoration: none; float: right; }
        .container { padding: 30px; max-width: 1100px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #34495e; color: white; }
        .summary-box { background: #27ae60; color: white; padding: 20px; border-radius: 6px; font-size: 20px; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard_admin.php">⬅ Kembali ke Dashboard</a>
        <h2>Laporan Finansial & Transaksi Selesai</h2>
    </div>
    <div class="container">
        <h3>Arsip Transaksi Sukses (Selesai Dikembalikan)</h3>
        
        <table>
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Alat & Qty</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Biaya Sewa</th>
                    <th>Denda Terlambat</th>
                    <th>Total Sub-Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($result) == 0): 
                ?>
                    <tr><td colspan="8" style="text-align:center; color:gray;">Belum ada data transaksi yang selesai (dikembalikan).</td></tr>
                <?php 
                endif;
                while($row = mysqli_fetch_assoc($result)): 
                    $sub_total = $row['total_bayar'] + $row['denda'];
                    $total_pendapatan += $sub_total;
                ?>
                <tr>
                    <td>TRX-0<?= $row['id_penyewaan']; ?></td>
                    <td><?= $row['nama_pelanggan']; ?></td>
                    <td><?= $row['nama_alat']; ?> (x<?= $row['jumlah']; ?>)</td>
                    <td><?= $row['tgl_sewa']; ?></td>
                    <td><?= $row['tgl_dikembalikan']; ?></td>
                    <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                    <td>Rp <?= number_format($row['denda'], 0, ',', '.'); ?></td>
                    <td><strong>Rp <?= number_format($sub_total, 0, ',', '.'); ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <br>
        <div class="summary-box">
            Total Akumulasi Pendapatan Bersih: Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?>
        </div>
    </div>
</body>
</html>