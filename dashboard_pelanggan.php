<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'pelanggan') {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

$id_pelanggan = $_SESSION['id_pengguna'];

// Proses Pemesanan / Sewa Langsung (Simulasi Fitur Transaksi)
if (isset($_POST['sewa_alat'])) {
    $id_alat = $_POST['id_alat'];
    $jumlah = intval($_POST['jumlah']);
    $durasi = intval($_POST['durasi']);
    
    // Cek Stok Alat
    $cek_stok = mysqli_query($conn, "SELECT * FROM alat_camping WHERE id_alat = $id_alat");
    $alat = mysqli_fetch_assoc($cek_stok);
    
    if ($alat['stok'] >= $jumlah) {
        $tgl_sewa = date('Y-m-d');
        $tgl_kembali = date('Y-m-d', strtotime("+$durasi days"));
        $total_bayar = $alat['harga_per_hari'] * $jumlah * $durasi;
        
        // Insert ke tabel penyewaan
        $insert_sewa = mysqli_query($conn, "INSERT INTO penyewaan (id_pelanggan, tgl_sewa, tgl_kembali_seharusnya, total_bayar, status_penyewaan) VALUES ($id_pelanggan, '$tgl_sewa', '$tgl_kembali', $total_bayar, 'disewa')");
        $id_penyewaan = mysqli_insert_id($conn);
        
        // Insert ke detail
        mysqli_query($conn, "INSERT INTO detail_penyewaan (id_penyewaan, id_alat, jumlah) VALUES ($id_penyewaan, $id_alat, $jumlah)");
        
        // Potong stok alat
        mysqli_query($conn, "UPDATE alat_camping SET stok = stok - $jumlah WHERE id_alat = $id_alat");
        
        echo "<script>alert('Pemesanan Berhasil Terbuat! Status: Aktif Disewa.'); window.location.href='dashboard_pelanggan.php';</script>";
    } else {
        echo "<script>alert('Stok alat tidak mencukupi!');</script>";
    }
}

// Ambil Katalog Alat
$katalog = mysqli_query($conn, "SELECT * FROM alat_camping");

// Ambil Riwayat Sewa Pelanggan
$riwayat = mysqli_query($conn, "SELECT p.*, ac.nama_alat, dp.jumlah FROM penyewaan p 
    JOIN detail_penyewaan dp ON p.id_penyewaan = dp.id_penyewaan 
    JOIN alat_camping ac ON dp.id_alat = ac.id_alat 
    WHERE p.id_pelanggan = $id_pelanggan ORDER BY p.id_penyewaan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelanggan - CampRent</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background-color: #27ae60; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; }
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        h2 { color: #2c3e50; border-bottom: 2px solid #27ae60; padding-bottom: 5px; }
        .card-panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; }
        .btn-sm { padding: 5px 10px; background: #27ae60; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .status { padding: 3px 7px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status.disewa { background: #ffeaa7; color: #d63031; }
        .status.dikembalikan { background: #badc58; color: #6ab04c; }
        .status.terlambat { background: #ff7675; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>CampRent Portal Pelanggan</h2>
        <div>
            <span>Selamat Datang, <strong><?= $_SESSION['nama']; ?></strong></span> | 
            <a href="logout.php" style="background:#hd3232; padding:5px 10px; border-radius:4px;">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Panel Katalog Alat -->
        <div class="card-panel">
            <h2>Katalog Alat Camping Tersedia</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama Alat</th>
                        <th>Harga / Hari</th>
                        <th>Stok</th>
                        <th>Sewa Alat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($katalog)): ?>
                    <tr>
                        <td><?= $row['nama_alat']; ?> <br><small style="color:gray;">Kategori: <?= $row['kategori']; ?></small></td>
                        <td>Rp <?= number_format($row['harga_per_hari'], 0, ',', '.'); ?></td>
                        <td><?= $row['stok']; ?> pcs</td>
                        <td>
                            <?php if($row['stok'] > 0): ?>
                            <form action="" method="POST" style="display:inline-flex; gap: 5px;">
                                <input type="hidden" name="id_alat" value="<?= $row['id_alat']; ?>">
                                <input type="number" name="jumlah" value="1" min="1" max="<?= $row['stok']; ?>" style="width:40px;" required>
                                <input type="number" name="durasi" value="1" min="1" placeholder="Hari" style="width:50px;" required>
                                <button type="submit" name="sewa_alat" class="btn-sm">Sewa</button>
                            </form>
                            <?php else: ?>
                                <span style="color:red; font-weight:bold;">Habis</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Panel Riwayat Penyewaan -->
        <div class="card-panel">
            <h2>Riwayat Penyewaan Anda</h2>
            <table>
                <thead>
                    <tr>
                        <th>Alat (Qty)</th>
                        <th>Tgl Kembali</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($riwayat) == 0): ?>
                        <tr><td colspan="4" style="text-align:center; color:gray;">Belum ada riwayat sewa.</td></tr>
                    <?php endif; ?>
                    <?php while($rw = mysqli_fetch_assoc($riwayat)): ?>
                    <tr>
                        <td><?= $rw['nama_alat']; ?> (<strong><?= $rw['jumlah']; ?></strong>)</td>
                        <td><?= $rw['tgl_kembali_seharusnya']; ?></td>
                        <td>Rp <?= number_format($rw['total_bayar'], 0, ',', '.'); ?></td>
                        <td><span class="status <?= $rw['status_penyewaan']; ?>"><?= strtoupper($rw['status_penyewaan']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>