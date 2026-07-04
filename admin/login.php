<?php
include '../koneksi.php';

// Sudah login → ke dashboard
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit();
}

// ============================================================
//  ANTI-CACHE — Supaya halaman login tidak tersimpan di cache
//  browser (menjaga token CSRF selalu fresh & konsisten dengan
//  halaman admin lainnya).
// ============================================================
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    // ============================================================
    //  CSRF CHECK pada form login
    // ============================================================
    verifyCSRF();

    // Rate limiting login: max 5 percobaan per 15 menit
    if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = [];
    $now = time();
    $_SESSION['login_attempts'] = array_filter(
        $_SESSION['login_attempts'],
        fn($t) => ($now - $t) < 900
    );

    if (count($_SESSION['login_attempts']) >= 5) {
        $error = 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.';
    } else {
        $_SESSION['login_attempts'][] = $now;

        $username = trim($_POST['username'] ?? '');

        $stmt = mysqli_prepare($conn, "SELECT * FROM admin WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin  = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $password_input = $_POST['password'] ?? '';
        $valid = false;

        if ($admin) {
            if (password_verify($password_input, $admin['password'])) {
                $valid = true;
            } elseif ($admin['password'] === MD5($password_input)) {
                $valid = true;
                // Upgrade otomatis ke password_hash
                $hash = password_hash($password_input, PASSWORD_DEFAULT);
                $upd = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE username = ?");
                mysqli_stmt_bind_param($upd, "ss", $hash, $username);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            }
        }

        if ($valid) {
            session_regenerate_id(true);
            $_SESSION['admin']      = $admin['username'];
            $_SESSION['login_time'] = time();
            $_SESSION['login_attempts'] = [];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Username atau Password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin – Sakila Store</title>
    <style>
        body{font-family:Arial,sans-serif;background:#fff5f7;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
        .box{background:white;padding:30px;border-radius:15px;box-shadow:0 4px 20px rgba(0,0,0,0.08);width:85%;max-width:350px;box-sizing:border-box;}
        .box h2{margin-top:0;margin-bottom:20px;color:#b04d6d;text-align:center;font-size:24px;}
        input{width:100%;padding:12px;margin:10px 0;box-sizing:border-box;border:1px solid #ddd;border-radius:8px;font-size:14px;}
        input:focus{border-color:#b04d6d;outline:none;}
        button{width:100%;padding:12px;background:#b04d6d;color:white;border:none;border-radius:10px;cursor:pointer;font-weight:bold;font-size:15px;margin-top:10px;transition:background 0.3s ease;}
        button:hover{background:#8d3b56;}
        .error{background:#ffe0e6;color:#b04d6d;padding:10px;border-radius:8px;margin-bottom:10px;font-size:14px;text-align:center;}
    </style>
<script src="no-forward.js"></script>
</head>
<body>
<div class="box">
    <h2>Login Admin</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text"     name="username" placeholder="Username" required autocomplete="username">
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        <button name="login">Login</button>
    </form>
</div>
</body>
</html>
