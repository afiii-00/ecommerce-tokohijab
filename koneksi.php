<?php
// ============================================================
//  KONEKSI DATABASE - HOSTINGER
//  Ganti nilai di bawah ini sesuai panel Hostinger kamu
// ============================================================
$host = "localhost";
$user = "root";   // WAJIB GANTI: bukan root, buat user khusus di cPanel
$pass = "";  // WAJIB GANTI: password kuat
$db   = "sakila_store";

// ============================================================
//  SESSION HARDENING — harus dipanggil SEBELUM session_start()
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => true,      // Hanya lewat HTTPS
        'httponly' => true,      // Tidak bisa diakses JavaScript
        'samesite' => 'Strict'   // Proteksi CSRF tambahan
    ]);
    session_start();
}

// ============================================================
//  CSRF TOKEN — dibuat sekali per sesi, dipakai semua form
// ============================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
//  KONEKSI DATABASE
// ============================================================
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    error_log("Koneksi gagal: " . mysqli_connect_error());
    die("Terjadi kesalahan sistem. Silakan coba lagi.");
}

// Set charset UTF-8 untuk keamanan encoding
mysqli_set_charset($conn, "utf8mb4");

// ============================================================
//  HELPER: Validasi CSRF Token
// ============================================================
function verifyCSRF() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die("Permintaan tidak valid (CSRF).");
    }
}
