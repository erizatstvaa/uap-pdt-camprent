<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php"); exit;
}
require_once 'config.php';
$page_title = 'Proses Transaksi';
$msg = '';

// ============================================================
// PROSES PENGEMBALIAN
// Ketika admin menekan "Terima Pengembalian":
// 1. Insert ke tabel pengembalian
// 2. UPDATE status penyewaan -> 'dikembalikan'
// 3. TRIGGER MySQL otomatis menambah stok kembali
// ============================================================
if (isset($_GET['kembali'])) {
    $id    = intval($_GET['kembali']);
    $kondisi = mysqli_real_escape_string($conn, $_GET['kondisi'] ?? 'baik');

    // Ambil data penyewaan
    $sewa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM penyewaan WHERE id_penyewaan = $id"));
    if ($sewa) {
        $tgl_real   = date('Y-m-d');
        $denda      = hitung_denda($sewa['tgl_kembali_seharusnya'], $tgl_real, $sewa['total_bayar']);
        $ket        = $sewa['status_penyewaan'] === 'terlambat' ? "Dikembalikan terlambat, denda: " . rupiah($denda) : "Dikembalikan tepat waktu";

        // 1. Insert pengembalian
        mysqli_query($conn, "INSERT INTO pengembalian (id_penyewaan, tgl_dikembalikan, denda, kondisi_alat, keterangan) 
                              VALUES ($id, '$tgl_real', $denda, '$kondisi', '$ket')");

        // 2. Update status -> TRIGGER DB AKAN BERJALAN otomatis menambah stok
        mysqli_query($conn, "UPDATE penyewaan SET status_penyewaan = 'dikembalikan' WHERE id_penyewaan = $id");

        mysqli_query($conn, "INSERT INTO log_aktivitas (id_pengguna, aksi, detail) 
                              VALUES ({$_SESSION['id_pengguna']}, 'PROSES_KEMBALI', 'Pengembalian TRX-$id, denda: $denda')");

        $msg = "success:Alat berhasil dikembalikan! Stok otomatis bertambah via TRIGGER database. Denda: " . rupiah($denda);
    }
    header("Location: proses_transaksi.php?msg=" . urlencode($msg)); exit;
}

// Proses Sewa Baru oleh Admin
if (isset($_POST['buat_sewa'])) {
    $id_pelanggan = intval($_POST['id_pelanggan']);
    $id_alat      = intval($_POST['id_alat']);
    $jumlah       = intval($_POST['jumlah']);
    $durasi       = intval($_POST['durasi']);

    $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM alat_camping WHERE id_alat = $id_alat"));

    if (!$alat) {
        $msg = "danger:Alat tidak ditemukan!";
    } elseif ($alat['stok'] < $jumlah) {
        $msg = "danger:Stok tidak mencukupi! Stok tersedia: {$alat['stok']}";
    } else {
        $tgl_sewa    = date('Y-m-d');
        $tgl_kembali = date('Y-m-d', strtotime("+$durasi days"));
        $total       = $alat['harga_per_hari'] * $jumlah * $durasi;

        mysqli_query($conn, "INSERT INTO penyewaan (id_pelanggan, tgl_sewa, tgl_kembali_seharusnya, total_bayar, status_penyewaan) 
                              VALUES ($id_pelanggan, '$tgl_sewa', '$tgl_kembali', $total, 'disewa')");
        $id_sewa = mysqli_insert_id($conn);

        // Insert detail -> TRIGGER kurangi stok otomatis
        mysqli_query($conn, "INSERT INTO detail_penyewaan (id_penyewaan, id_alat, jumlah, harga_satuan) 
                              VALUES ($id_sewa, $id_alat, $jumlah, {$alat['harga_per_hari']})");

        $msg = "success:Penyewaan TRX-" . str_pad($id_sewa,4,'0',STR_PAD_LEFT) . " berhasil dibuat!";
    }
    header("Location: proses_transaksi.php?msg=" . urlencode($msg)); exit;
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Ambil transaksi aktif
$transaksi = mysqli_query($conn, "
    SELECT p.*, pg.nama as nama_pelanggan, pg.no_telepon, ac.nama_alat, dp.jumlah,
           DATEDIFF(CURDATE(), p.tgl_kembali_seharusnya) as hari_telat
    FROM penyewaan p
    JOIN pengguna pg ON p.id_pelanggan = pg.id_pengguna
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan
    JOIN alat_camping ac ON dp.id_alat = ac.id_alat
    WHERE p.status_penyewaan IN ('disewa', 'terlambat')
    ORDER BY p.tgl_kembali_seharusnya ASC
");

// Untuk form buat sewa
$pelanggan_list = mysqli_query($conn, "SELECT * FROM pengguna WHERE peran='pelanggan' ORDER BY nama");
$alat_list      = mysqli_query($conn, "SELECT * FROM alat_camping WHERE stok > 0 ORDER BY nama_alat");
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
        <a href="proses_transaksi.php" class="nav-link active"><i class="fas fa-exchange-alt fa-fw"></i> Proses Sewa & Kembali</a>
        <a href="laporan_transaksi.php" class="nav-link"><i class="fas fa-chart-bar fa-fw"></i> Laporan Transaksi</a>
        <a href="backup_db.php" class="nav-link"><i class="fas fa-database fa-fw"></i> Backup Database</a>
    </div>
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-title">Monitoring & Proses Transaksi</div>
        <button onclick="document.getElementById('modal-sewa').style.display='flex'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Sewa Baru
        </button>
    </div>
    <div class="page-body">

        <?php if($msg): 
            list($type, $text) = explode(':', $msg, 2);
        ?>
        <div class="alert alert-<?= $type ?>">
            <?= $type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?> <?= $text ?>
        </div>
        <?php endif; ?>

        <!-- Info Trigger -->
        <div class="alert alert-info section-gap">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Implementasi TRIGGER Database:</strong> Saat tombol "Terima Pengembalian" ditekan, sistem mengupdate status ke <code>dikembalikan</code>. 
                MySQL TRIGGER <code>trg_tambah_stok_setelah_dikembalikan</code> akan otomatis menambah stok alat tanpa query PHP tambahan.
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-exchange-alt" style="color:#5a8a5a;margin-right:8px;"></i> Transaksi Aktif (Disewa & Terlambat)</span>
                <span style="font-size:12px;color:#7a9a7a;"><?= mysqli_num_rows($transaksi) ?> transaksi berjalan</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Alat & Qty</th>
                            <th>Tgl Sewa</th>
                            <th>Batas Kembali</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($transaksi) === 0): ?>
                        <tr><td colspan="8"><div class="empty-state"><i class="fas fa-check-double"></i><p>Semua transaksi sudah selesai 🎉</p></div></td></tr>
                    <?php endif; ?>
                    <?php while($r = mysqli_fetch_assoc($transaksi)): 
                        $is_late = $r['status_penyewaan'] === 'terlambat';
                        $est_denda = $is_late ? hitung_denda($r['tgl_kembali_seharusnya'], date('Y-m-d'), $r['total_bayar']) : 0;
                    ?>
                    <tr style="<?= $is_late ? 'background:#fff5f5;' : '' ?>">
                        <td><span style="font-family:monospace;font-size:12px;font-weight:700;color:#1a3a2a;">TRX-<?= str_pad($r['id_penyewaan'],4,'0',STR_PAD_LEFT) ?></span></td>
                        <td>
                            <strong><?= htmlspecialchars($r['nama_pelanggan']) ?></strong>
                            <br><span style="font-size:11px;color:#7a9a7a;"><?= htmlspecialchars($r['no_telepon'] ?? '') ?></span>
                        </td>
                        <td>
                            <?= htmlspecialchars($r['nama_alat']) ?>
                            <span class="badge badge-blue" style="margin-left:4px;">×<?= $r['jumlah'] ?></span>
                        </td>
                        <td style="font-size:12px;"><?= tgl_indo($r['tgl_sewa']) ?></td>
                        <td style="font-size:12px;<?= $is_late ? 'color:#c0392b;font-weight:700;' : '' ?>">
                            <?= tgl_indo($r['tgl_kembali_seharusnya']) ?>
                            <?php if($is_late): ?>
                            <br><span style="font-size:10px;background:#fce4ec;color:#c62828;padding:2px 6px;border-radius:4px;">
                                Terlambat <?= $r['hari_telat'] ?> hari
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight:600;"><?= rupiah($r['total_bayar']) ?></div>
                            <?php if($est_denda > 0): ?>
                            <div style="font-size:11px;color:#e74c3c;">+<?= rupiah($est_denda) ?> denda</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $is_late ? 'badge-red' : 'badge-amber' ?>">
                                <?= strtoupper($r['status_penyewaan']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="proses_transaksi.php?kembali=<?= $r['id_penyewaan'] ?>&kondisi=baik"
                               onclick="return confirm('Konfirmasi pengembalian alat dari <?= htmlspecialchars($r['nama_pelanggan']) ?>?<?= $est_denda > 0 ? " Denda: ".rupiah($est_denda) : '' ?>')"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Terima Kembali
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Sewa Baru -->
<div id="modal-sewa" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:480px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <div style="padding:24px;border-bottom:1px solid #e8f0e8;">
            <h3 style="font-family:'Sora',sans-serif;font-size:16px;color:#1a3a2a;">Buat Penyewaan Baru</h3>
        </div>
        <form method="POST" style="padding:24px;">
            <div class="form-group">
                <label class="form-label">Pilih Pelanggan</label>
                <select name="id_pelanggan" class="form-control" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php while($p = mysqli_fetch_assoc($pelanggan_list)): ?>
                    <option value="<?= $p['id_pengguna'] ?>"><?= htmlspecialchars($p['nama']) ?> (<?= $p['email'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Pilih Alat</label>
                <select name="id_alat" class="form-control" required onchange="updateHarga(this)">
                    <option value="">-- Pilih Alat --</option>
                    <?php 
                    $alat_list = mysqli_query($conn, "SELECT * FROM alat_camping WHERE stok > 0 ORDER BY nama_alat");
                    while($a = mysqli_fetch_assoc($alat_list)): 
                    ?>
                    <option value="<?= $a['id_alat'] ?>" data-harga="<?= $a['harga_per_hari'] ?>" data-stok="<?= $a['stok'] ?>">
                        <?= htmlspecialchars($a['nama_alat']) ?> - <?= rupiah($a['harga_per_hari']) ?>/hari (stok: <?= $a['stok'] ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Jumlah Unit</label>
                    <input type="number" name="jumlah" id="input_jumlah" class="form-control" value="1" min="1" required onchange="hitungTotal()">
                </div>
                <div class="form-group">
                    <label class="form-label">Durasi (Hari)</label>
                    <input type="number" name="durasi" id="input_durasi" class="form-control" value="1" min="1" required onchange="hitungTotal()">
                </div>
            </div>
            <div id="info-total" style="background:#f0f4f0;border-radius:8px;padding:14px;margin-bottom:16px;font-size:13px;display:none;">
                <div>Harga/hari: <strong id="txt-harga">-</strong></div>
                <div>Total Estimasi: <strong id="txt-total" style="color:#1a3a2a;font-size:15px;">-</strong></div>
            </div>
            <div style="display:flex;gap:12px;">
                <button type="submit" name="buat_sewa" class="btn btn-primary" style="flex:1;justify-content:center;">
                    <i class="fas fa-paper-plane"></i> Buat Penyewaan
                </button>
                <button type="button" onclick="document.getElementById('modal-sewa').style.display='none'" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
var hargaPerHari = 0;
function updateHarga(sel) {
    var opt = sel.options[sel.selectedIndex];
    hargaPerHari = parseFloat(opt.dataset.harga) || 0;
    hitungTotal();
}
function hitungTotal() {
    var jumlah = parseInt(document.getElementById('input_jumlah').value) || 1;
    var durasi = parseInt(document.getElementById('input_durasi').value) || 1;
    if (hargaPerHari > 0) {
        var total = hargaPerHari * jumlah * durasi;
        document.getElementById('info-total').style.display = 'block';
        document.getElementById('txt-harga').textContent = 'Rp ' + hargaPerHari.toLocaleString('id-ID');
        document.getElementById('txt-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
}
</script>
</body>
</html>