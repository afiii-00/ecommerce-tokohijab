<?php
// hapus_produk.php sudah tidak digunakan lagi.
// Fungsi hapus dipindahkan ke dashboard.php via POST + CSRF.
// File ini dipertahankan agar tidak ada 404, tapi tidak melakukan apa-apa.
include '../koneksi.php';
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit(); }
header('Location: dashboard.php');
exit();
