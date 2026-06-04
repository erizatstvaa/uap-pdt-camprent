<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php"); exit;
}
require_once 'config.php';
$page_title = 'Kelola Alat';
$msg = '';

// Tambah Alat
if (isset($_POST['tambah'])) {
    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama_alat']));
    $kategori  = mysqli_real_escape_string($conn, trim($_POST['kategori']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga     = floatval($_POST['harga_per_hari']);
    $stok      = intval($_POST['stok']);

    mysqli_query($conn, "INSERT INTO alat_camping (nama_alat, kategori, deskripsi, harga_per_hari, stok, stok_awal) 
                          VALUES ('$nama', '$kategori', '$deskripsi', $harga, $stok, $stok)");
    mysqli_query($conn, "INSERT INTO log_aktivitas (id_pengguna, aksi, detail) VALUES ({$_SESSION['id_pengguna']}, 'TAMBAH_ALAT', 'Tambah alat: $nama')");
    $msg = "success:Alat <strong>$nama</strong> berhasil ditambahkan!";
    header("Location: kelola_alat.php?msg=" . urlencode($msg)); exit;
}

// Edit Alat
if (isset($_POST['edit'])) {
    $id        = intval($_POST['id_alat']);
    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama_alat']));
    $kategori  = mysqli_real_escape_string($conn, trim($_POST['kategori']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga     = floatval($_POST['harga_per_hari']);
    $stok      = intval($_POST['stok']);

    mysqli_query($conn, "UPDATE alat_camping SET nama_alat='$nama', kategori='$kategori', deskripsi='$deskripsi', harga_per_hari=$harga, stok=$stok WHERE id_alat=$id");
    $msg = "success:Alat berhasil diperbarui!";
    header("Location: kelola_alat.php?msg=" . urlencode($msg)); exit;
}

// Hapus Alat
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $alat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_alat FROM alat_camping WHERE id_alat=$id"));
    $aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM detail_penyewaan dp JOIN penyewaan p ON dp.id_penyewaan=p.id_penyewaan WHERE dp.id_alat=$id AND p.status_penyewaan IN ('disewa','terlambat')"))['t'];
    if ($aktif > 0) {
        $msg = "danger:Tidak bisa menghapus alat yang sedang disewa!";
    } else {
        mysqli_query($conn, "DELETE FROM alat_camping WHERE id_alat=$id");
        $msg = "success:Alat berhasil dihapus.";
    }
    header("Location: kelola_alat.php?msg=" . urlencode($msg)); exit;
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$result = mysqli_query($conn, "SELECT * FROM alat_camping ORDER BY id_alat ASC");
$kategori_list = ['Tenda', 'Tas', 'Perlengkapan Tidur', 'Memasak', 'Penerangan', 'Aksesoris', 'Lainnya'];
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
        <a href="kelola_alat.php" class="nav-link active"><i class="fas fa-box fa-fw"></i> Kelola Alat</a>
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
        <div class="topbar-title">Kelola Inventaris Alat Camping</div>
        <button onclick="document.getElementById('modal-tambah').style.display='flex'" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Alat Baru
        </button>
    </div>
    <div class="page-body">

        <?php if($msg): 
            list($type, $text) = explode(':', $msg, 2);
            $cls = $type === 'success' ? 'alert-success' : 'alert-danger';
        ?>
        <div class="alert <?= $cls ?>"><?= $type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?> <?= $text ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-box" style="color:#5a8a5a;margin-right:8px;"></i> Daftar Alat Camping</span>
                <span style="font-size:12px;color:#7a9a7a;"><?= mysqli_num_rows($result) ?> item terdaftar</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Alat</th>
                            <th>Kategori</th>
                            <th>Harga/Hari</th>
                            <th>Stok</th>
                            <th>Kondisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="fas fa-box-open"></i><p>Belum ada alat camping</p></div></td></tr>
                    <?php endif; ?>
                    <?php 
                    $result = mysqli_query($conn, "SELECT * FROM alat_camping ORDER BY id_alat ASC");
                    while($row = mysqli_fetch_assoc($result)): 
                        $stok_persen = $row['stok_awal'] > 0 ? ($row['stok'] / $row['stok_awal']) * 100 : 100;
                        $stok_cls = $row['stok'] === 0 ? 'badge-red' : ($row['stok'] <= 2 ? 'badge-amber' : 'badge-green');
                    ?>
                    <tr>
                        <td style="font-family:monospace;font-size:12px;color:#7a9a7a;">#<?= $row['id_alat'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_alat']) ?></strong>
                            <?php if($row['deskripsi']): ?>
                            <br><span style="font-size:11px;color:#7a9a7a;"><?= htmlspecialchars(substr($row['deskripsi'],0,50)) ?>...</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-blue"><?= htmlspecialchars($row['kategori']) ?></span></td>
                        <td style="font-weight:600;"><?= rupiah($row['harga_per_hari']) ?></td>
                        <td>
                            <span class="badge <?= $stok_cls ?>"><?= $row['stok'] ?> unit</span>
                            <?php if($row['stok_awal'] > 0): ?>
                            <div style="margin-top:4px;height:4px;background:#e8f5e9;border-radius:2px;width:80px;">
                                <div style="height:100%;width:<?= min(100,$stok_persen) ?>%;background:<?= $stok_persen < 20 ? '#e74c3c' : '#27ae60' ?>;border-radius:2px;"></div>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $k_cls = $row['kondisi'] === 'baik' ? 'badge-green' : ($row['kondisi'] === 'rusak_ringan' ? 'badge-amber' : 'badge-red');
                            $k_lbl = str_replace('_', ' ', ucfirst($row['kondisi']));
                            ?>
                            <span class="badge <?= $k_cls ?>"><?= $k_lbl ?></span>
                        </td>
                        <td>
                            <button onclick='openEdit(<?= json_encode($row) ?>)' class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="kelola_alat.php?hapus=<?= $row['id_alat'] ?>" 
                               onclick="return confirm('Hapus alat <?= htmlspecialchars($row['nama_alat']) ?>?')"
                               class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
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

<!-- Modal Tambah -->
<div id="modal-tambah" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <div style="padding:24px;border-bottom:1px solid #e8f0e8;">
            <h3 style="font-family:'Sora',sans-serif;font-size:16px;color:#1a3a2a;">Tambah Alat Camping Baru</h3>
        </div>
        <form method="POST" style="padding:24px;">
            <div class="form-group">
                <label class="form-label">Nama Alat</label>
                <input type="text" name="nama_alat" class="form-control" placeholder="cth: Tenda Dome 4 Orang" required>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <?php foreach($kategori_list as $k): ?>
                        <option><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga / Hari (Rp)</label>
                    <input type="number" name="harga_per_hari" class="form-control" placeholder="50000" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Stok Awal</label>
                <input type="number" name="stok" class="form-control" placeholder="10" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi (opsional)</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat alat..."></textarea>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" name="tambah" class="btn btn-primary" style="flex:1;justify-content:center;">
                    <i class="fas fa-plus"></i> Tambah Alat
                </button>
                <button type="button" onclick="document.getElementById('modal-tambah').style.display='none'" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto;">
        <div style="padding:24px;border-bottom:1px solid #e8f0e8;">
            <h3 style="font-family:'Sora',sans-serif;font-size:16px;color:#1a3a2a;">Edit Alat Camping</h3>
        </div>
        <form method="POST" style="padding:24px;">
            <input type="hidden" name="id_alat" id="edit_id">
            <div class="form-group">
                <label class="form-label">Nama Alat</label>
                <input type="text" name="nama_alat" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" id="edit_kategori" class="form-control">
                        <?php foreach($kategori_list as $k): ?>
                        <option><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga / Hari (Rp)</label>
                    <input type="number" name="harga_per_hari" id="edit_harga" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Stok Saat Ini</label>
                <input type="number" name="stok" id="edit_stok" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" name="edit" class="btn btn-primary" style="flex:1;justify-content:center;">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <button type="button" onclick="document.getElementById('modal-edit').style.display='none'" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(data) {
    document.getElementById('edit_id').value = data.id_alat;
    document.getElementById('edit_nama').value = data.nama_alat;
    document.getElementById('edit_harga').value = data.harga_per_hari;
    document.getElementById('edit_stok').value = data.stok;
    document.getElementById('edit_deskripsi').value = data.deskripsi || '';
    var sel = document.getElementById('edit_kategori');
    for(var i=0; i<sel.options.length; i++) {
        if(sel.options[i].value === data.kategori) sel.selectedIndex = i;
    }
    document.getElementById('modal-edit').style.display = 'flex';
}
</script>
</body>
</html>