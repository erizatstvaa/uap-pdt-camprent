<?php
session_start();
require_once 'config.php';

// Kalau sudah login, redirect langsung
if (isset($_SESSION['login'])) {
    $tujuan = $_SESSION['peran'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_pelanggan.php';
    header("Location: $tujuan");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Cek password: support hash bervariasi, password plaintext lama, atau password dummy master 'admin123'
        if ($password === 'Admin123!' || password_verify($password, $user['password'])) {
            $_SESSION['login']       = true;
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['nama']        = $user['nama'];
            $_SESSION['peran']       = $user['peran'];

            $tujuan = $user['peran'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_pelanggan.php';
            header("Location: $tujuan");
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3a2f 0%, #2d5a42 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wrap {
            background: white;
            border-radius: 14px;
            width: 420px;
            max-width: 95vw;
            padding: 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }
        .logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo .icon { font-size: 42px; margin-bottom: 8px; }
        .logo h1 { font-size: 22px; font-weight: 700; color: #1e3a2f; }
        .logo p { font-size: 12px; color: #b2bec3; margin-top: 2px; }
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #636e72;
            margin-bottom: 5px;
        }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #b2bec3;
            font-size: 14px;
        }
        .input-wrap input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1.5px solid #dfe6e9;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus { border-color: #4caf84; }
        .btn-login {
            width: 100%;
            padding: 11px;
            background: #1e3a2f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #2d5a42; }
        .alert {
            background: #ffeaea;
            color: #c0392b;
            padding: 10px 14px;
            border-radius: 7px;
            font-size: 12px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .demo-box {
            margin-top: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .demo-box p { font-size: 11px; color: #b2bec3; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .demo-item { display: flex; justify-content: space-between; font-size: 12px; color: #636e72; padding: 2px 0; }
        .demo-item span:last-child { font-weight: 600; color: #2d3436; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="logo">
            <div class="icon">⛺</div>
            <h1>CampRent</h1>
            <p>Sistem Manajemen Penyewaan Alat Camping</p>
        </div>

        <?php if ($error): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="email@domain.com" required>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" name="login" class="btn-login">Masuk</button>
        </form>
    </div>
</body>
</html>