<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php"); exit;
}
require_once 'config.php';

// Atur judul halaman untuk dibaca oleh header.php
$page_title = 'Dashboard Admin';

// Ambil statistik ringkas
$total_alat      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM alat_camping"))['t'];
$sedang_disewa   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM penyewaan WHERE status_penyewaan='disewa'"))['t'];
$total_terlambat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM penyewaan WHERE status_penyewaan='terlambat'"))['t'];
$pendapatan      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(p.total_bayar + IFNULL(k.denda,0)),0) as t FROM penyewaan p LEFT JOIN pengembalian k ON p.id_penyewaan=k.id_penyewaan WHERE p.status_penyewaan='dikembalikan'"))['t'];

// 5 transaksi terbaru
$transaksi_baru = mysqli_query($conn, "
    SELECT p.id_penyewaan, pg.nama, ac.nama_alat, p.tgl_kembali_seharusnya, p.status_penyewaan
    FROM penyewaan p
    JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
    JOIN alat_camping ac ON dp.id_alat = ac.id_alat
    ORDER BY p.id_penyewaan DESC LIMIT 5
");

// Fungsi helper rupiah (jika belum didefinisikan di config.php)
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// 1. PANGGIL HEADER DI SINI
require_once 'header.php'; 
?>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">⛺</div>
        <h1>CampRent</h1>
        <div class="tagline">Panel Admin</div>
    </div>
    <div class="sidebar-user">
        <div class="user-role">Administrator</div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        <a href="dashboard_admin.php" class="nav-link active"><i class="fas fa-home fa-fw"></i> Dashboard</a>
        <a href="kelola_alat.php" class="nav-link"><i class="fas fa-box fa-fw"></i> Kelola Alat</a>
        <a href="proses_transaksi.php" class="nav-link"><i class="fas fa-exchange-alt fa-fw"></i> Proses Sewa & Kembali</a>
        <a href="laporan_transaksi.php" class="nav-link"><i class="fas fa-chart-bar fa-fw"></i> Laporan Transaksi</a>
        <a href="backup_db.php" class="nav-link"><i class="fas fa-database fa-fw"></i> Backup Database</a>
    </div>
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    
    <div class="topbar">
        <div class="topbar-title">Dashboard Utama</div>
        <div class="topbar-badge">
            <i class="fas fa-calendar-alt"></i> <?= date('d F Y') ?>
        </div>
    </div>

    <div class="page-body">

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-box"></i></div>
                <div>
                    <div class="stat-label">Total Jenis Alat</div>
                    <div class="stat-value"><?= $total_alat ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fas fa-tent"></i></div>
                <div>
                    <div class="stat-label">Sedang Disewa</div>
                    <div class="stat-value"><?= $sedang_disewa ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <div class="stat-label">Terlambat</div>
                    <div class="stat-value"><?= $total_terlambat ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value" style="font-size: 18px; margin-top: 8px;"><?= rupiah($pendapatan) ?></div>
                </div>
            </div>
        </div>

        <?php if ($total_terlambat > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                Ada <b><?= $total_terlambat ?> penyewaan</b> yang terlambat dikembalikan!
                <a href="proses_transaksi.php" style="margin-left:8px; font-weight:600; color: var(--warning); text-decoration: underline;">Lihat Detail &rarr;</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card section-gap">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-clock"></i> Transaksi Terbaru</div>
                <a href="proses_transaksi.php" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Alat</th>
                                <th>Batas Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($transaksi_baru) == 0): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>Belum ada transaksi terbaru saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php while ($row = mysqli_fetch_assoc($transaksi_baru)):
                            $s = $row['status_penyewaan'];
                            $badge = $s === 'disewa' ? 'badge-amber' : ($s === 'terlambat' ? 'badge-red' : 'badge-green');
                        ?>
                        <tr>
                            <td><b>#<?= $row['id_penyewaan'] ?></b></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['nama_alat']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tgl_kembali_seharusnya'])) ?></td>
                            <td><span class="badge <?= $badge ?>"><?= strtoupper($s) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>