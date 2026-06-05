<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php"); exit;
}
require_once 'config.php';

// ============================================================
// BACKUP DATABASE
// Pakai variabel dari config.php: $host, $user, $pass, $db
// ============================================================
if (isset($_GET['do_backup'])) {
    $backup_dir = __DIR__ . '/backups/';
    if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);

    $filename = 'camprent_backup_' . date('Y-m-d_His') . '.sql';
    $filepath = $backup_dir . $filename;

    // Coba pakai mysqldump dulu
    $use_dump = false;
    if (function_exists('exec')) {
        exec("which mysqldump 2>&1", $out, $ret);
        // Di Windows Laragon, mysqldump ada di PATH
        if ($ret !== 0) {
            exec("mysqldump --version 2>&1", $out2, $ret2);
            $use_dump = ($ret2 === 0);
        } else {
            $use_dump = true;
        }
    }

    if ($use_dump) {
        $cmd = "mysqldump --user={$user} --password={$pass} --host={$host} {$db} > " . escapeshellarg($filepath) . " 2>&1";
        exec($cmd, $output, $rc);
        $sukses = ($rc === 0 && file_exists($filepath) && filesize($filepath) > 100);
    } else {
        $sukses = false;
    }

    // Fallback: PHP native backup (selalu bisa jalan)
    if (!$sukses) {
        $tables = mysqli_query($conn, "SHOW TABLES");
        $sql  = "-- CampRent Database Backup\n";
        $sql .= "-- Dibuat: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        while ($t = mysqli_fetch_array($tables)) {
            $tbl = $t[0];

            // Struktur tabel
            $create_res = mysqli_query($conn, "SHOW CREATE TABLE `$tbl`");
            $create_row = mysqli_fetch_assoc($create_res);
            $sql .= "-- Tabel: $tbl\n";
            $sql .= "DROP TABLE IF EXISTS `$tbl`;\n";
            $sql .= $create_row['Create Table'] . ";\n\n";

            // Data tabel
            $rows = mysqli_query($conn, "SELECT * FROM `$tbl`");
            $ncols = mysqli_num_fields($rows);
            while ($row = mysqli_fetch_row($rows)) {
                $vals = [];
                for ($i = 0; $i < $ncols; $i++) {
                    if ($row[$i] === null) {
                        $vals[] = "NULL";
                    } else {
                        $vals[] = "'" . mysqli_real_escape_string($conn, $row[$i]) . "'";
                    }
                }
                $sql .= "INSERT INTO `$tbl` VALUES (" . implode(",", $vals) . ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filepath, $sql);
        $sukses = file_exists($filepath) && filesize($filepath) > 50;
    }

    if ($sukses) {
        $ukuran = round(filesize($filepath) / 1024, 2);
        mysqli_query($conn, "INSERT INTO log_backup (status, keterangan) 
                              VALUES ('sukses', 'Backup: $filename | Ukuran: {$ukuran} KB')");

        // Download file ke browser
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        $pesan = urlencode("danger:Backup gagal. Coba cek folder backups/ bisa ditulis.");
        header("Location: backup_db.php?msg=$pesan");
        exit;
    }
}

// Ambil riwayat backup
$logs = mysqli_query($conn, "SELECT * FROM log_backup ORDER BY id_log DESC LIMIT 10");
$msg  = isset($_GET['msg']) ? $_GET['msg'] : '';
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
        <a href="laporan_transaksi.php" class="nav-link"><i class="fas fa-chart-bar fa-fw"></i> Laporan Transaksi</a>
        <a href="backup_db.php" class="nav-link active"><i class="fas fa-database fa-fw"></i> Backup Database</a>
    </div>
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar"><div class="topbar-title">Backup & Pemulihan Database</div></div>
    <div class="page-body">

        <?php if(!empty($msg)): 
            list($type, $text) = explode(':', $msg, 2);
            $alert_class = ($type === 'success' || $type === 'sukses') ? 'alert-success' : 'alert-danger';
            $icon_class = ($type === 'success' || $type === 'sukses') ? 'fa-check-circle' : 'fa-times-circle';
        ?>
        <div class="alert <?= $alert_class ?>" style="display: flex; align-items: center; gap: 10px; padding: 14px 20px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            <i class="fas <?= $icon_class ?> fa-lg"></i> 
            <div><?= $text ?></div>
        </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">

            <div class="card">
                <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border);">
                    <span class="card-title" style="font-family:'Sora', sans-serif; font-weight:700; color:var(--forest);"><i class="fas fa-download" style="color:var(--forest-mid); margin-right:8px;"></i> Backup Manual</span>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <p style="font-size:13px; color:var(--text-muted); line-height:1.6; margin-bottom:24px;">
                        Klik tombol di bawah untuk langsung membuat dan mengunduh berkas salinan cadangan berkode `.sql` dari database CampRent saat ini. File cadangan ini dapat digunakan untuk memulihkan seluruh data jika sewaktu-waktu terjadi kerusakan sistem.
                    </p>
                    <a href="backup_db.php?do_backup=1" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px; font-weight:600; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-cloud-download-alt"></i> Download Backup SQL Sekarang
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border);">
                    <span class="card-title" style="font-family:'Sora', sans-serif; font-weight:700; color:var(--forest);"><i class="fas fa-clock" style="color:var(--earth-light); margin-right:8px;"></i> Otomatisasi (Task Scheduler)</span>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <div class="alert alert-info" style="margin-bottom:16px; background:#e3f2fd; color:#0d47a1; border-left:4px solid #1976d2; padding:12px 16px; border-radius:6px; font-size:12px; display:flex; gap:10px; align-items:flex-start;">
                        <i class="fas fa-calendar-check" style="margin-top:2px;"></i>
                        <div>MySQL Event Scheduler <code>evt_backup_harian</code> dikonfigurasi berjalan otomatis di latar belakang setiap pukul 00:00 malam.</div>
                    </div>
                    <p style="font-size:12px; color:var(--text-muted); margin-bottom:10px; font-weight:500;">Perintah otomatisasi Cron Job pada sistem operasi Server Linux:</p>
                    <div style="background:#1e2522; color:#a3bdae; padding:14px; border-radius:8px; font-family:monospace; font-size:11px; line-height:1.6; border:1px solid #2d3833;">
                        <span style="color:#c49a2a; font-size:11px; display:block; margin-bottom:4px;"># Eksekusi via scheduler php internal</span>
                        0 0 * * * php <?= __DIR__ ?>/scheduler/backup_scheduler.php
                    </div>
                </div>
            </div>
        </div>

        <div class="card section-gap" style="margin-bottom:24px;">
            <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border);">
                <span class="card-title" style="font-family:'Sora', sans-serif; font-weight:700; color:var(--forest);"><i class="fas fa-undo" style="color:var(--warning); margin-right:8px;"></i> Panduan Pemulihan Data (Restore)</span>
            </div>
            <div class="card-body" style="padding: 24px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">Jika ingin memulihkan keadaan basis data menggunakan file berkas SQL hasil unduhan:</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div style="background:#f8faf8; border:1px solid var(--border); padding:14px; border-radius:8px;">
                        <strong style="font-size:12px; color:var(--forest); display:block; margin-bottom:6px;"><i class="fas fa-terminal"></i> Metode Perintah Terminal CLI:</strong>
                        <code style="font-size:11px; color:var(--danger); background:#fff; padding:4px 6px; border:1px solid #f0f0f0; border-radius:4px; display:block; word-break:break-all;">mysql -u root -p camprent < berkas_backup.sql</code>
                    </div>
                    <div style="background:#f8faf8; border:1px solid var(--border); padding:14px; border-radius:8px;">
                        <strong style="font-size:12px; color:var(--forest); display:block; margin-bottom:6px;"><i class="fas fa-globe"></i> Metode Antarmuka phpMyAdmin:</strong>
                        <span style="font-size:12px; color:var(--text-muted);">Pilih DB <strong>camprent</strong> &rarr; Klik tab menu <strong>Import</strong> &rarr; Pilih file SQL &rarr; Klik tombol <strong>Kirim/Go</strong>.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <span class="card-title" style="font-family:'Sora', sans-serif; font-weight:700; color:var(--forest);"><i class="fas fa-list-alt" style="color:var(--forest-mid); margin-right:8px;"></i> Log Riwayat Aktivitas Salinan Sistem</span>
                <span style="font-size:11px; color:var(--text-muted); background:#fafafa; border:1px solid var(--border); padding:2px 8px; border-radius:4px;">10 log terakhir</span>
            </div>
            <div class="table-wrapper">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:14px;">
                    <thead>
                        <tr>
                            <th style="padding:12px 16px;">Waktu Operasi</th>
                            <th style="padding:12px 16px; width:120px;">Status</th>
                            <th style="padding:12px 16px;">Rincian Keterangan File</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($logs) === 0): ?>
                        <tr>
                            <td colspan="3">
                                <div class="empty-state" style="text-align:center; padding:40px; color:var(--text-muted);">
                                    <i class="fas fa-history fa-2x" style="opacity:0.3; margin-bottom:10px; display:block;"></i>
                                    <p style="font-size:13px;">Belum ditemukan adanya catatan riwayat backup dalam database.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php while($l = mysqli_fetch_assoc($logs)): ?>
                    <tr>
                        <td style="padding:14px 16px; font-size:13px; font-weight:500; color:var(--text);"><?= date('d M Y H:i:s', strtotime($l['waktu_backup'])) ?></td>
                        <td style="padding:14px 16px;">
                            <?php $badge_status = ($l['status'] === 'sukses') ? 'badge-green' : 'badge-red'; ?>
                            <span class="badge <?= $badge_status ?>" style="font-size:11px; font-weight:600; text-transform:uppercase;"><?= htmlspecialchars($l['status']) ?></span>
                        </td>
                        <td style="padding:14px 16px; font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($l['keterangan']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>
