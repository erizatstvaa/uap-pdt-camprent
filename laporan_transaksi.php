<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php"); exit;
}
require_once 'config.php';
$page_title = 'Laporan Transaksi';

// Filter bulan/tahun
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : intval(date('m'));
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));

// ============================================================
// IMPLEMENTASI FRAGMENTASI:
// Menggunakan VIEW fragment_penyewaan_selesai (fragmentasi logis)
// untuk menampilkan hanya data transaksi yang selesai
// ============================================================
$filter_bulan = "AND MONTH(p.tgl_sewa) = $bulan AND YEAR(p.tgl_sewa) = $tahun";

$query = "
    SELECT p.id_penyewaan, pg.nama AS nama_pelanggan, pg.email, pg.no_telepon,
           ac.nama_alat, ac.kategori, dp.jumlah, dp.harga_satuan,
           p.tgl_sewa, p.tgl_kembali_seharusnya, p.total_bayar,
           k.tgl_dikembalikan, IFNULL(k.denda,0) AS denda,
           (p.total_bayar + IFNULL(k.denda,0)) AS grand_total,
           k.kondisi_alat
    FROM fragment_penyewaan_selesai p
    JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
    JOIN alat_camping ac ON dp.id_alat = ac.id_alat
    LEFT JOIN pengembalian k ON p.id_penyewaan = k.id_penyewaan
    WHERE 1=1 $filter_bulan
    ORDER BY k.tgl_dikembalikan DESC
";
$result = mysqli_query($conn, $query);
if (!$result) {
    // fallback if view not available
    $result = mysqli_query($conn, str_replace('fragment_penyewaan_selesai', "penyewaan WHERE status_penyewaan='dikembalikan'", "SELECT p.id_penyewaan, pg.nama AS nama_pelanggan, pg.email, pg.no_telepon, ac.nama_alat, ac.kategori, dp.jumlah, dp.harga_satuan, p.tgl_sewa, p.tgl_kembali_seharusnya, p.total_bayar, k.tgl_dikembalikan, IFNULL(k.denda,0) AS denda, (p.total_bayar + IFNULL(k.denda,0)) AS grand_total, k.kondisi_alat FROM penyewaan p JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan JOIN alat_camping ac ON dp.id_alat = ac.id_alat LEFT JOIN pengembalian k ON p.id_penyewaan = k.id_penyewaan WHERE p.status_penyewaan='dikembalikan' $filter_bulan ORDER BY k.tgl_dikembalikan DESC"));
}

// Summary
$summary = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(DISTINCT p.id_penyewaan) AS total_trx,
        SUM(p.total_bayar) AS total_sewa,
        SUM(IFNULL(k.denda,0)) AS total_denda,
        SUM(p.total_bayar + IFNULL(k.denda,0)) AS grand_total
    FROM penyewaan p
    LEFT JOIN pengembalian k ON p.id_penyewaan = k.id_penyewaan
    WHERE p.status_penyewaan = 'dikembalikan'
      AND MONTH(p.tgl_sewa) = $bulan AND YEAR(p.tgl_sewa) = $tahun
"));

$nama_bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<?php include 'header.php'; ?>
</head>
<body>
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
        <a href="dashboard_admin.php" class="nav-link"><i class="fas fa-home fa-fw"></i> Dashboard</a>
        <a href="kelola_alat.php" class="nav-link"><i class="fas fa-box fa-fw"></i> Kelola Alat</a>
        <a href="proses_transaksi.php" class="nav-link"><i class="fas fa-exchange-alt fa-fw"></i> Proses Sewa & Kembali</a>
        <a href="laporan_transaksi.php" class="nav-link active"><i class="fas fa-chart-bar fa-fw"></i> Laporan Transaksi</a>
        <a href="backup_db.php" class="nav-link"><i class="fas fa-database fa-fw"></i> Backup Database</a>
    </div>
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-title">Laporan Transaksi — <?= $nama_bulan[$bulan] ?> <?= $tahun ?></div>
        <div style="display:flex;gap:12px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;align-items:center;">
                <select name="bulan" class="form-control" style="width:130px;padding:8px 12px;">
                    <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?=$m==$bulan?'selected':''?>><?= $nama_bulan[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="tahun" class="form-control" style="width:90px;padding:8px 12px;">
                    <?php for($y=date('Y');$y>=2024;$y--): ?>
                    <option value="<?=$y?>" <?=$y==$tahun?'selected':''?>><?=$y?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            </form>
            <button onclick="window.print()" class="btn btn-outline btn-sm"><i class="fas fa-print"></i> Cetak</button>
        </div>
    </div>

    <div class="page-body">

        <!-- Fragmentasi info -->
        <div class="alert alert-info section-gap">
            <i class="fas fa-layer-group"></i>
            <div><strong>Implementasi Fragmentasi Database:</strong> Laporan ini menggunakan VIEW <code>fragment_penyewaan_selesai</code> (fragmentasi horizontal) yang memisahkan data penyewaan selesai dari penyewaan aktif.</div>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
                <div><div class="stat-label">Total Transaksi</div><div class="stat-value"><?= $summary['total_trx'] ?? 0 ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-money-bill"></i></div>
                <div><div class="stat-label">Pendapatan Sewa</div><div class="stat-value" style="font-size:18px;"><?= rupiah($summary['total_sewa'] ?? 0) ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-gavel"></i></div>
                <div><div class="stat-label">Total Denda</div><div class="stat-value" style="font-size:18px;"><?= rupiah($summary['total_denda'] ?? 0) ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fas fa-wallet"></i></div>
                <div><div class="stat-label">Total Pendapatan</div><div class="stat-value" style="font-size:18px;color:#2d5a3d;"><?= rupiah($summary['grand_total'] ?? 0) ?></div></div>
            </div>
        </div>

        <!-- Tabel Laporan -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-table" style="color:#5a8a5a;margin-right:8px;"></i> Detail Transaksi Selesai</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Alat & Qty</th>
                            <th>Tgl Sewa</th>
                            <th>Tgl Kembali</th>
                            <th>Biaya Sewa</th>
                            <th>Denda</th>
                            <th>Total</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="9"><div class="empty-state"><i class="fas fa-file-invoice"></i><p>Tidak ada transaksi selesai pada periode ini</p></div></td></tr>
                    <?php endif; ?>
                    <?php while($r = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><span style="font-family:monospace;font-size:12px;font-weight:700;color:#1a3a2a;">TRX-<?= str_pad($r['id_penyewaan'],4,'0',STR_PAD_LEFT) ?></span></td>
                        <td>
                            <strong><?= htmlspecialchars($r['nama_pelanggan']) ?></strong>
                            <br><span style="font-size:11px;color:#7a9a7a;"><?= htmlspecialchars($r['email']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($r['nama_alat']) ?> <span class="badge badge-blue">×<?= $r['jumlah'] ?></span></td>
                        <td style="font-size:12px;"><?= tgl_indo($r['tgl_sewa']) ?></td>
                        <td style="font-size:12px;"><?= tgl_indo($r['tgl_dikembalikan']) ?></td>
                        <td><?= rupiah($r['total_bayar']) ?></td>
                        <td><?= $r['denda'] > 0 ? '<span style="color:#c0392b;font-weight:600;">'.rupiah($r['denda']).'</span>' : '<span style="color:#7a9a7a;">-</span>' ?></td>
                        <td><strong><?= rupiah($r['grand_total']) ?></strong></td>
                        <td>
                            <?php $k = $r['kondisi_alat'] ?? 'baik'; ?>
                            <span class="badge <?= $k === 'baik' ? 'badge-green' : 'badge-red' ?>"><?= ucfirst($k) ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar button, .alert { display: none !important; }
    .main-content { margin-left: 0 !important; }
}
</style>
</body>
</html>