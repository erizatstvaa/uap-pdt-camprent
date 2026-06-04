<?php
session_start();
require_once 'config.php';

$error = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Dalam sistem nyata, gunakan password_verify

    $query = "SELECT * FROM pengguna WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        // Untuk kemudahan demonstrasi prototype, kita bandingkan teks langsung jika belum di-hash, 
        // atau gunakan password dummy 'admin123'
        if ($password == 'admin123' || password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['id_pengguna'] = $row['id_pengguna'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['peran'] = $row['peran'];

            if ($row['peran'] == 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_pelanggan.php");
            }
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak terdaftar!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - CampRent</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #27ae60; border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #219653; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 15px; }
        .info { font-size: 12px; color: #7f8c8d; text-align: center; margin-top: 15px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>CampRent Login</h2>
        <?php if($error): ?>
            <div class="error"><?= $error; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="admin@camprent.com atau budi@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Gunakan: admin123" required>
            </div>
            <button type="submit" name="login">Masuk</button>
        </form>
        <div class="info">
            <strong>Akun Demo:</strong><br>
            Admin: admin@camprent.com (Pass: admin123)<br>
            Pelanggan: budi@gmail.com (Pass: admin123)
        </div>
    </div>
</body>
</html>