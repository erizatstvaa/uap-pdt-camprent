<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php"); exit;
}
require_once 'config.php';
$page_title = 'Backup Database';
$msg = '';

// ============================================================
// BACKUP DATABASE MANUAL
// Menggunakan mysqldump via PHP exec(), atau fallback PHP-native
// ============================================================
if (isset($_GET['do_backup'])) {
    $backup_dir  = __DIR__ . '/backups/';
    if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);

    $filename    = 'camprent_backup_' . date('Y-m-d_His') . '.sql';
    $filepath    = $backup_dir . $filename;

    // Coba gunakan mysqldump (tersedia di server Linux/Mac)
    $mysqldump_cmd = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > " . escapeshellarg($filepath) . " 2>&1";
    
    $use_mysqldump = false;
    if (function_exists('exec')) {
        exec("which mysqldump", $out, $ret);
        $use_mysqldump = ($ret === 0);
    }

    if ($use_mysqldump) {
        exec($mysqldump_cmd, $output, $return_code);
        $sukses = ($return_code === 0 && file_exists($filepath) && filesize($filepath) > 100);
    } else {
        // Fallback: PHP native backup
        $tables = mysqli_query($conn, "SHOW TABLES");
        $sql = "-- CampRent Database Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n-- Server: " . DB_HOST . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        
        while($t = mysqli_fetch_array($tables)) {
            $table = $t[0];
            // CREATE TABLE
            $create = mysqli_fetch_assoc(mysqli_query($conn, "SHOW CREATE TABLE `$table`"));
            $sql .= "\n-- Table: $table\nDROP TABLE IF EXISTS `$table`;\n";
            $sql .= $create['Create Table'] . ";\n\n";
            // Data
            $rows = mysqli_query($conn, "SELECT * FROM `$table`");
            $num_fields = mysqli_num_fields($rows);
            while($row = mysqli_fetch_row($rows)) {
                $sql .= "INSERT INTO `$table` VALUES (";
                for($i=0; $i<$num_fields; $i++) {
                    $val = $row[$i];
                    if ($val === null) $sql .= "NULL";
                    else $sql .= "'" . mysqli_real_escape_string($conn, $val) . "'";
                    if ($i < $num_fields - 1) $sql .= ",";
                }
                $sql .= ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($filepath, $sql);
        $sukses = file_exists($filepath) && filesize($filepath) > 50;
    }

    if ($sukses) {
        // Catat di log_backup
        $ukuran = round(filesize($filepath) / 1024, 2);
        mysqli_query($conn, "INSERT INTO log_backup (status, keterangan) VALUES ('sukses', 'Backup manual: $filename, Ukuran: {$ukuran}KB')");
        mysqli_query($conn, "INSERT INTO log_aktivitas (id_pengguna, aksi, detail) VALUES ({$_SESSION['id_pengguna']}, 'BACKUP', 'Backup database: $filename')");
        
        // Download langsung
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        $msg = "danger:Backup gagal! Pastikan direktori backups/ bisa ditulis.";
    }
}

// Log backup terakhir
$logs = mysqli_query($conn, "SELECT * FROM log_backup ORDER BY id_log DESC LIMIT 10");
$msg = isset($_GET['msg']) ? $_GET['msg'] : $msg;
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

        <?php if($msg): 
            list($type, $text) = explode(':', $msg, 2);
        ?>
        <div class="alert alert-<?= $type ?>"><i class="fas fa-info-circle"></i> <?= $text ?></div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

            <!-- Manual Backup -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-download" style="color:#2980b9;margin-right:8px;"></i> Backup Manual</span>
                </div>
                <div class="card-body">
                    <p style="font-size:14px;color:#5a6b5a;line-height:1.7;margin-bottom:20px;">
                        Klik tombol di bawah untuk langsung mengunduh file SQL backup database CampRent saat ini. 
                        Sistem akan menggunakan <strong>mysqldump</strong> (jika tersedia) atau <strong>PHP native backup</strong>.
                    </p>
                    <a href="backup_db.php?do_backup=1" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
                        <i class="fas fa-download"></i> Download Backup SQL Sekarang
                    </a>
                </div>
            </div>

            <!-- Backup Otomatis -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title"><i class="fas fa-clock" style="color:#27ae60;margin-right:8px;"></i> Backup Otomatis (Task Scheduler)</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" style="margin-bottom:16px;">
                        <i class="fas fa-calendar-check"></i>
                        <div>MySQL Event Scheduler <strong>evt_backup_harian</strong> berjalan setiap pukul 00:00 mencatat jadwal backup ke tabel <code>log_backup</code>.</div>
                    </div>
                    <p style="font-size:13px;color:#5a6b5a;margin-bottom:16px;">Untuk backup file SQL otomatis di server Linux, tambahkan cron job berikut:</p>
                    <div style="background:#1a3a2a;color:#a8d5b5;padding:14px 16px;border-radius:8px;font-family:monospace;font-size:12px;line-height:1.8;">
                        <div style="color:#c49a2a;font-size:10px;margin-bottom:6px;"># Crontab (crontab -e) — jalankan tiap malam pukul 00:00</div>
                        0 0 * * * php <?= __DIR__ ?>/scheduler/backup_scheduler.php<br>
                        <br>
                        <div style="color:#c49a2a;font-size:10px;margin-bottom:6px;"># Atau mysqldump langsung:</div>
                        0 0 * * * mysqldump -u root camprent > /var/backups/camprent_$(date +\%F).sql
                    </div>
                </div>
            </div>
        </div>

        <!-- Cara Restore -->
        <div class="card section-gap">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-undo" style="color:#e67e22;margin-right:8px;"></i> Cara Restore Database</span>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:#5a6b5a;margin-bottom:12px;">Untuk memulihkan database dari file backup SQL:</p>
                <div style="background:#1a3a2a;color:#a8d5b5;padding:14px 16px;border-radius:8px;font-family:monospace;font-size:12px;line-height:2;">
                    <span style="color:#c49a2a;"># Via terminal MySQL:</span><br>
                    mysql -u root -p camprent &lt; camprent_backup_YYYY-MM-DD.sql<br>
                    <br>
                    <span style="color:#c49a2a;"># Via phpMyAdmin:</span><br>
                    Pilih database camprent → klik "Import" → upload file .sql
                </div>
            </div>
        </div>

        <!-- Log Backup -->
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-list-alt" style="color:#5a8a5a;margin-right:8px;"></i> Riwayat Backup (10 Terakhir)</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu Backup</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($logs) === 0): ?>
                        <tr><td colspan="3"><div class="empty-state"><i class="fas fa-history"></i><p>Belum ada riwayat backup</p></div></td></tr>
                    <?php endif; ?>
                    <?php while($l = mysqli_fetch_assoc($logs)): ?>
                    <tr>
                        <td style="font-size:13px;"><?= date('d M Y H:i:s', strtotime($l['waktu_backup'])) ?></td>
                        <td><span class="badge <?= $l['status'] === 'sukses' ? 'badge-green' : 'badge-red' ?>"><?= $l['status'] ?></span></td>
                        <td style="font-size:12px;color:#5a6b5a;"><?= htmlspecialchars($l['keterangan']) ?></td>
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