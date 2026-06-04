<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'pelanggan') {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$id_pelanggan = $_SESSION['id_pengguna'];
$msg = '';

// Proses Pemesanan / Sewa Langsung
if (isset($_POST['sewa_alat'])) {
    $id_alat = intval($_POST['id_alat']);
    $jumlah  = intval($_POST['jumlah']);
    $durasi  = intval($_POST['durasi']);
    
    // Cek Stok dan Ambil Harga Alat
    $cek_stok = mysqli_query($conn, "SELECT * FROM alat_camping WHERE id_alat = $id_alat");
    if (mysqli_num_rows($cek_stok) > 0) {
        $alat = mysqli_fetch_assoc($cek_stok);
        $harga_satuan = $alat['harga_per_hari'];
        
        if ($alat['stok'] >= $jumlah) {
            $tgl_sewa = date('Y-m-d');
            $tgl_kembali = date('Y-m-d', strtotime("+$durasi days"));
            $total_bayar = $harga_satuan * $jumlah * $durasi;
            
            // 1. Insert ke tabel penyewaan
            mysqli_query($conn, "INSERT INTO penyewaan (id_pelanggan, tgl_sewa, tgl_kembali_seharusnya, total_bayar, status_penyewaan) VALUES ($id_pelanggan, '$tgl_sewa', '$tgl_kembali', $total_bayar, 'disewa')");
            $id_penyewaan = mysqli_insert_id($conn);
            
            // 2. Insert ke detail_penyewaan dengan harga_satuan
            mysqli_query($conn, "INSERT INTO detail_penyewaan (id_penyewaan, id_alat, jumlah, harga_satuan) VALUES ($id_penyewaan, $id_alat, $jumlah, $harga_satuan)");
            
            // 3. Potong stok alat
            mysqli_query($conn, "UPDATE alat_camping SET stok = stok - $jumlah WHERE id_alat = $id_alat");
            
            $msg = "success:Pemesanan berhasil dibuat! Unit siap diambil.";
        } else {
            $msg = "danger:Stok alat tidak mencukupi untuk jumlah yang diminta!";
        }
    } else {
        $msg = "danger:Alat tidak ditemukan!";
    }
}

// Ambil Katalog Alat
$katalog = mysqli_query($conn, "SELECT * FROM alat_camping ORDER BY nama_alat ASC");

// Ambil Riwayat Sewa Pelanggan
$riwayat = mysqli_query($conn, "SELECT p.*, ac.nama_alat, dp.jumlah FROM penyewaan p 
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan 
    JOIN alat_camping ac ON dp.id_alat = ac.id_alat 
    WHERE p.id_pelanggan = $id_pelanggan ORDER BY p.id_penyewaan DESC");

// Helper Rupiah
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// Panggil header global berisi base CSS
include 'header.php';
?>

<style>
    body {
        padding: 0;
        margin: 0;
        background-color: var(--bg-body, #f4f7f5);
    }
    
    /* Navbar Atas */
    .customer-navbar {
        background-color: var(--forest, #1e3f20);
        color: white;
        padding: 12px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    
    .nav-brand { display: flex; align-items: center; gap: 12px; }
    .nav-brand .logo-icon { font-size: 24px; background: rgba(255,255,255,0.15); padding: 6px; border-radius: 8px; line-height: 1; }
    .nav-brand-text h1 { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 700; color: white; margin: 0; line-height: 1.2; }
    .nav-brand-text .tagline { font-size: 11px; color: rgba(255,255,255,0.7); font-weight: 500; }
    .nav-user-info { display: flex; align-items: center; gap: 20px; }
    .user-details { text-align: right; }
    .user-details .user-role { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.6); font-weight: 600; }
    .user-details .user-name { font-size: 14px; font-weight: 600; color: white; }

    /* Container Konten Utama */
    .customer-container { max-width: 1350px; margin: 30px auto; padding: 0 24px; }
    .customer-grid { display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 24px; }

    @media (max-width: 1100px) {
        .customer-grid { grid-template-columns: 1fr; }
        .customer-navbar { padding: 12px 20px; }
    }

    /* Form Order Styling Khusus (Penjelasan Kotak) */
    .order-box-container {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .order-label {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-muted, #7a8a7a);
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .input-group-custom {
        display: flex;
        align-items: center;
        border: 1px solid var(--border, #e1ede2);
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }
    .input-group-custom input {
        width: 45px;
        padding: 6px 4px;
        border: none;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        outline: none;
    }
    .input-group-custom .addon-text {
        background: #f4f7f5;
        font-size: 11px;
        padding: 6px 8px;
        color: var(--forest-mid, #2d5a27);
        font-weight: 600;
        border-left: 1px solid var(--border, #e1ede2);
    }
</style>

<div class="customer-navbar">
    <div class="nav-brand">
        <div class="logo-icon">⛺</div>
        <div class="nav-brand-text">
            <h1>CampRent</h1>
        </div>
    </div>
    
    <div class="nav-user-info">
        <div class="user-details">
            <div class="user-role">Pelanggan / Member</div>
            <div class="user-name"><?= htmlspecialchars($_SESSION['nama']) ?></div>
        </div>
        <a href="logout.php" class="btn-logout" style="background: rgba(255,255,255,0.15); color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 500; font-size: 13px; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</div>

<div class="customer-container">

    <?php if(!empty($msg)): 
        list($type, $text) = explode(':', $msg, 2);
        $cls = $type === 'success' ? 'alert-success' : 'alert-danger';
    ?>
    <div class="alert <?= $cls ?>" style="margin-bottom: 24px;">
        <?= $type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?> 
        <div><?= $text ?></div>
    </div>
    <?php endif; ?>

    <div class="customer-grid">
        
        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-boxes" style="color:var(--forest-mid, #2d5a27); margin-right:8px;"></i> Katalog Tersedia</span>
                <span style="font-size:12px; color:var(--text-muted, #7a8a7a); font-weight: 500;"><?= mysqli_num_rows($katalog) ?> item terdaftar</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Alat</th>
                            <th>Harga/Hari</th>
                            <th>Stok</th>
                            <th style="min-width: 260px;">Atur Penyewaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($katalog)): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--forest, #1e3f20);"><?= htmlspecialchars($row['nama_alat']); ?></strong>
                                <br><span style="font-size:11px; color:var(--text-muted, #7a8a7a);"><i class="fas fa-tags"></i> <?= htmlspecialchars($row['kategori']); ?></span>
                            </td>
                            <td style="font-weight:600;"><?= rupiah($row['harga_per_hari']); ?></td>
                            <td>
                                <?php if($row['stok'] > 0): ?>
                                    <span class="badge badge-green" style="font-weight: 500;"><?= $row['stok']; ?> unit</span>
                                <?php else: ?>
                                    <span class="badge badge-red" style="font-weight: 500;">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['stok'] > 0): ?>
                                <form action="" method="POST" style="display: inline-flex; align-items: flex-end; gap: 12px;">
                                    <input type="hidden" name="id_alat" value="<?= $row['id_alat']; ?>">
                                    
                                    <div class="order-box-container">
                                        <label class="order-label">Jumlah</label>
                                        <div class="input-group-custom">
                                            <input type="number" name="jumlah" value="1" min="1" max="<?= $row['stok']; ?>" title="Berapa banyak barang yang disewa" required>
                                            <span class="addon-text">Unit</span>
                                        </div>
                                    </div>

                                    <div class="order-box-container">
                                        <label class="order-label">Durasi</label>
                                        <div class="input-group-custom">
                                            <input type="number" name="durasi" value="1" min="1" title="Berapa hari lama pinjam" required>
                                            <span class="addon-text">Hari</span>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="sewa_alat" class="btn btn-primary btn-sm" style="padding: 7px 14px; border-radius: 6px; height: 31px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-shopping-cart"></i> Sewa
                                    </button>
                                </form>
                                <?php else: ?>
                                    <button class="btn btn-sm" style="background:#ccc; color:#777; cursor:not-allowed; border:none;" disabled>Sewa</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-history" style="color:var(--forest-mid, #2d5a27); margin-right:8px;"></i> Riwayat Transaksi</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Alat (Qty)</th>
                            <th>Batas Kembali</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($riwayat) == 0): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color: var(--text-muted, #7a8a7a); padding: 30px 10px;">
                                    <i class="fas fa-receipt fa-2x" style="display:block; margin-bottom:8px; color:#ccc;"></i>
                                    Belum ada riwayat sewa.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php while($rw = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td>
                                <span style="font-weight:600; color: var(--forest, #1e3f20); display:block; font-size:13px;"><?= htmlspecialchars($rw['nama_alat']); ?></span>
                                <span style="color: var(--text-muted, #7a8a7a); font-size:11px;">Jumlah: <strong><?= $rw['jumlah']; ?> pcs</strong></span>
                            </td>
                            <td style="font-size: 12px; font-weight: 500;"><?= date('d M Y', strtotime($rw['tgl_kembali_seharusnya'])); ?></td>
                            <td style="font-weight:600; font-size:13px;"><?= rupiah($rw['total_bayar']); ?></td>
                            <td>
                                <?php 
                                $st = $rw['status_penyewaan'];
                                $st_cls = $st === 'disewa' ? 'badge-amber' : ($st === 'dikembalikan' ? 'badge-green' : 'badge-red');
                                ?>
                                <span class="badge <?= $st_cls ?>" style="font-size: 10px; padding: 2px 8px;">
                                    <?= $st === 'disewa' ? 'Aktif' : strtoupper($st); ?>
                                </span>
                            </td>
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