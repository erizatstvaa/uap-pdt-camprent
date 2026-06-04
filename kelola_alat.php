<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'config.php';

// Tambah Alat
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_alat']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga = $_POST['harga_per_hari'];
    $stok = $_POST['stok'];
    
    mysqli_query($conn, "INSERT INTO alat_camping (nama_alat, kategori, harga_per_hari, stok) VALUES ('$nama', '$kategori', $harga, $stok)");
    header("Location: kelola_alat.php");
}

// Hapus Alat
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM alat_camping WHERE id_alat = $id");
    header("Location: kelola_alat.php");
}

$result = mysqli_query($conn, "SELECT * FROM alat_camping");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Alat - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f6f9; }
        .navbar { background-color: #2c3e50; color: white; padding: 15px 20px; }
        .navbar a { color: white; text-decoration: none; float: right; }
        .container { padding: 30px; max-width: 1000px; margin: 0 auto; }
        .form-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        input, select { padding: 8px; margin-right: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 8px 15px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #34495e; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard_admin.php">⬅ Kembali ke Dashboard</a>
        <h2>Kelola Inventaris Alat Camping</h2>
    </div>
    <div class="container">
        <div class="form-box">
            <h3>Tambah Alat Camping Baru</h3>
            <form action="" method="POST">
                <input type="text" name="nama_alat" placeholder="Nama Alat" required>
                <input type="text" name="kategori" placeholder="Kategori" required>
                <input type="number" name="harga_per_hari" placeholder="Harga / Hari" required>
                <input type="number" name="stok" placeholder="Stok Awal" required>
                <button type="submit" name="tambah">Tambah Alat</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Alat</th>
                    <th>Kategori</th>
                    <th>Harga / Hari</th>
                    <th>Stok Berjalan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id_alat']; ?></td>
                    <td><?= $row['nama_alat']; ?></td>
                    <td><?= $row['kategori']; ?></td>
                    <td>Rp <?= number_format($row['harga_per_hari'], 0, ',', '.'); ?></td>
                    <td><?= $row['stok']; ?> unit</td>
                    <td>
                        <a href="kelola_alat.php?hapus=<?= $row['id_alat']; ?>" onclick="return confirm('Hapus alat ini?')" style="color:red; text-decoration:none; font-weight:bold;">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>