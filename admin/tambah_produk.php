<?php
include '../koneksi.php';

if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit(); }
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
    session_destroy(); header('Location: login.php?timeout=1'); exit();
}
$_SESSION['login_time'] = time();

// ============================================================
//  ANTI-CACHE — Cegah tombol Back menampilkan halaman ini lagi
//  setelah logout.
// ============================================================
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    // CSRF check
    verifyCSRF();

    $nama     = htmlspecialchars(strip_tags(trim($_POST['nama'] ?? '')), ENT_QUOTES, 'UTF-8');
    $harga    = (int)($_POST['harga'] ?? 0);
    $stok     = (int)($_POST['stok'] ?? 0);
    $kategori = htmlspecialchars(strip_tags(trim($_POST['kategori'] ?? '')), ENT_QUOTES, 'UTF-8');

    if (empty($nama) || $harga <= 0 || $stok < 0) {
        $error = 'Nama, harga, dan stok wajib diisi dengan benar.';
    } elseif (empty($_FILES['gambar']['name'])) {
        $error = 'Gambar produk wajib diupload.';
    } else {
        $allowed_ext  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $file_name   = $_FILES['gambar']['name'];
        $file_tmp    = $_FILES['gambar']['tmp_name'];
        $file_size   = $_FILES['gambar']['size'];
        $file_ext    = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_mime   = mime_content_type($file_tmp);

        if (!in_array($file_ext, $allowed_ext) || !in_array($file_mime, $allowed_mime)) {
            $error = 'File harus berupa gambar (JPG, PNG, WEBP, GIF).';
        } elseif ($file_size > 3 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 3MB.';
        } else {
            $safe_name  = bin2hex(random_bytes(8)) . '.' . $file_ext;
            $upload_dir = 'upload/';
            $dest       = $upload_dir . $safe_name;

            if (move_uploaded_file($file_tmp, $dest)) {
                $imagePath = 'admin/upload/' . $safe_name;

                $stmt = mysqli_prepare($conn,
                    "INSERT INTO products (product_name, price, image, category, stock) VALUES (?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "sissi", $nama, $harga, $imagePath, $kategori, $stok);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Gagal mengupload gambar. Coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk</title>
<style>
body{font-family:Arial,sans-serif;padding:20px;background:#fff5f7;margin:0;}
h2{color:#333;margin-bottom:15px;font-size:26px;}
.btn-kembali{display:inline-block;text-decoration:none;background:#6c757d;color:white;padding:8px 15px;border-radius:6px;font-size:14px;font-weight:bold;margin-bottom:20px;transition:background 0.3s;}
.btn-kembali:hover{background:#5a6268;}
form{background:white;padding:25px;border-radius:15px;width:100%;max-width:500px;box-sizing:border-box;box-shadow:0 4px 15px rgba(0,0,0,0.05);}
input[type="text"],input[type="number"],input[type="file"]{width:100%;padding:12px;margin:10px 0 18px 0;box-sizing:border-box;border:1px solid #ddd;border-radius:8px;font-size:14px;}
input:focus{border-color:#b04d6d;outline:none;}
label{font-size:14px;font-weight:bold;color:#555;}
button{width:100%;padding:12px;background:#b04d6d;color:white;border:none;border-radius:10px;font-size:15px;font-weight:bold;cursor:pointer;transition:background 0.3s;}
button:hover{background:#8d3b56;}
.error{background:#ffe0e6;color:#b04d6d;padding:12px;border-radius:8px;margin-bottom:15px;font-size:14px;}
@media(max-width:480px){body{padding:15px;}h2{font-size:22px;}form{padding:20px;}}
</style>
<script src="no-forward.js"></script>
</head>
<body>
<h2>Tambah Produk</h2>
<a href="dashboard.php" class="btn-kembali">← Kembali</a>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label>Nama Produk</label>
    <input type="text" name="nama" placeholder="Masukkan nama produk" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">

    <label>Harga (Rp)</label>
    <input type="number" name="harga" placeholder="Masukkan harga" min="1" required value="<?= (int)($_POST['harga'] ?? 0) ?>">

    <label>Stok</label>
    <input type="number" name="stok" placeholder="Masukkan jumlah stok" min="0" required value="<?= (int)($_POST['stok'] ?? 0) ?>">

    <label>Kategori</label>
    <input type="text" name="kategori" placeholder="Masukkan kategori hijab" value="<?= htmlspecialchars($_POST['kategori'] ?? '') ?>">

    <label>Gambar Produk (JPG/PNG/WEBP, maks 3MB)</label>
    <input type="file" name="gambar" accept="image/*" required>

    <button name="simpan">Simpan Produk</button>
</form>

<script>
// Jaga-jaga: kalau browser menampilkan halaman ini dari bfcache
// (tombol Back/Forward), paksa reload supaya PHP mengecek ulang
// status login.
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
</body>
</html>
