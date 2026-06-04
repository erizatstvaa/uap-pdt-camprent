<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "camprent";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
 
// Helper: Format Rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
 
// Helper: Format Tanggal Indonesia
function tgl_indo($tgl) {
    if (!$tgl) return '-';
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    $d = explode('-', $tgl);
    return $d[2] . ' ' . $bulan[(int)$d[1]] . ' ' . $d[0];
}
 
// Helper: Hitung denda keterlambatan
function hitung_denda($tgl_seharusnya, $tgl_kembali_real, $total_bayar) {
    $selisih = (strtotime($tgl_kembali_real) - strtotime($tgl_seharusnya)) / 86400;
    if ($selisih <= 0) return 0;
    // Denda 10% dari total bayar per hari terlambat
    return round($selisih * ($total_bayar * 0.1));
}
?>