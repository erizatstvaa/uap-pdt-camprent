<?php
require_once 'config.php';

// Turn on all error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== Debugging Return Process ===<br>";

// Let's get the active transaction ID
$res = mysqli_query($conn, "SELECT id_penyewaan, status_penyewaan FROM penyewaan WHERE status_penyewaan IN ('disewa', 'terlambat') LIMIT 1");
if (!$res) {
    echo "Query select failed: " . mysqli_error($conn) . "<br>";
    exit;
}

if (mysqli_num_rows($res) == 0) {
    echo "No active rent transaction found. Creating a dummy one first...<br>";
    // Insert dummy user if not exists
    $user_check = mysqli_query($conn, "SELECT id_pengguna FROM pengguna LIMIT 1");
    $user = mysqli_fetch_assoc($user_check);
    $id_user = $user['id_pengguna'];
    
    // Insert dummy gear if not exists
    $gear_check = mysqli_query($conn, "SELECT id_alat FROM alat_camping LIMIT 1");
    $gear = mysqli_fetch_assoc($gear_check);
    $id_alat = $gear['id_alat'];
    
    $tgl_sewa = date('Y-m-d');
    $tgl_kembali = date('Y-m-d', strtotime("+1 days"));
    
    $ins1 = mysqli_query($conn, "INSERT INTO penyewaan (id_pelanggan, tgl_sewa, tgl_kembali_seharusnya, total_bayar, status_penyewaan) VALUES ($id_user, '$tgl_sewa', '$tgl_kembali', 50000, 'disewa')");
    if (!$ins1) {
        echo "Insert to penyewaan failed: " . mysqli_error($conn) . "<br>";
        exit;
    }
    $id_penyewaan = mysqli_insert_id($conn);
    
    $ins2 = mysqli_query($conn, "INSERT INTO detail_penyewaan (id_penyewaan, id_alat, jumlah) VALUES ($id_penyewaan, $id_alat, 1)");
    if (!$ins2) {
        echo "Insert to detail_penyewaan failed: " . mysqli_error($conn) . "<br>";
        exit;
    }
    echo "Dummy transaction created with ID: $id_penyewaan<br>";
} else {
    $row = mysqli_fetch_assoc($res);
    $id_penyewaan = $row['id_penyewaan'];
    echo "Found active transaction ID: $id_penyewaan<br>";
}

// Now try to run the return queries
$tgl_kembali_real = date('Y-m-d');
$denda = 0;

echo "1. Inserting into pengembalian...<br>";
$q1 = mysqli_query($conn, "INSERT INTO pengembalian (id_penyewaan, tgl_dikembalikan, denda) VALUES ($id_penyewaan, '$tgl_kembali_real', $denda)");
if ($q1) {
    echo "Insert into pengembalian: SUCCESS<br>";
} else {
    echo "Insert into pengembalian: FAILED - " . mysqli_error($conn) . "<br>";
}

echo "2. Updating penyewaan status...<br>";
$q2 = mysqli_query($conn, "UPDATE penyewaan SET status_penyewaan = 'dikembalikan' WHERE id_penyewaan = $id_penyewaan");
if ($q2) {
    echo "Update penyewaan status: SUCCESS<br>";
} else {
    echo "Update penyewaan status: FAILED - " . mysqli_error($conn) . "<br>";
}

echo "=== Debug Done ===";
?>
