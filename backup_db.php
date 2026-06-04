<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['peran'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Database Backup - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f6f9;
        }

        .navbar {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            float: right;
        }

        .container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }

        .btn-backup {
            padding: 15px 30px;
            background-color: #e67e22;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-backup:hover {
            background-color: #d35400;
        }

        pre {
            text-align: left;
            background: #eaeded;
            padding: 15px;
            border-radius: 5px;
            font-family: Courier, monospace;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="dashboard_admin.php">⬅ Kembali ke Dashboard</a>
        <h2>Konfigurasi Backup Data CampRent</h2>
    </div>
    <div class="container">
        <div class="card">
            <h2>Sistem Backup Otomatis (Cron Job Server)</h2>
            <p>Sistem utama dikonfigurasi untuk melakukan pencadangan otomatis setiap malam pukul <b>00.00</b> pada core
                server OS Anda.</p>

            <p style="color: #7f8c8d;">Gunakan perintah Cron Job berikut di lingkungan Linux / Server Panel Anda:</p>
            <pre>0 0 * * * mysqldump -u root -p camprent > /var/backups/camprent_$(date +\%F).sql</pre>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

            <h3>Ingin melakukan backup instan sekarang?</h3>
            <p>Klik tombol di bawah untuk mendownload struktur file SQL database terkini secara langsung.</p>
            <a href="database.sql" download class="btn-backup">💾 Ekspor & Download File SQL</a>
        </div>
    </div>
</body>

</html>