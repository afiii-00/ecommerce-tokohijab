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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: dashboard.php'); exit(); }

$stmt_get = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$data   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt_get);

if (!$data) { header('Location: dashboard.php'); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    // CSRF check
    verifyCSRF();

    $nama  = htmlspecialchars(strip_tags(trim($_POST['nama'] ?? '')), ENT_QUOTES, 'UTF-8');
    $harga = (int)($_POST['harga'] ?? 0);
    $stok  = (int)($_POST['stok'] ?? 0);

    if (empty($nama) || $harga <= 0 || $stok < 0) {
        $error = 'Nama, harga, dan stok wajib diisi dengan benar.';
    } else {
        $imagePath = $data['image'];

        if (!empty($_FILES['gambar']['name'])) {
            $allowed_ext  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            $file_tmp  = $_FILES['gambar']['tmp_name'];
            $file_ext  = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $file_mime = mime_content_type($file_tmp);
            $file_size = $_FILES['gambar']['size'];

            if (!in_array($file_ext, $allowed_ext) || !in_array($file_mime, $allowed_mime)) {
                $error = 'File harus berupa gambar (JPG, PNG, WEBP, GIF).';
            } elseif ($file_size > 3 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 3MB.';
            } else {
                $safe_name = bin2hex(random_bytes(8)) . '.' . $file_ext;
                $dest = 'upload/' . $safe_name;
                if (move_uploaded_file($file_tmp, $dest)) {
                    // Hapus gambar lama
                    $old_path = __DIR__ . '/../' . $data['image'];
                    if (file_exists($old_path)) @unlink($old_path);
                    $imagePath = 'admin/upload/' . $safe_name;
                } else {
                    $error = 'Gagal mengupload gambar. Coba lagi.';
                }
            }
        }

        if (empty($error)) {
            $stmt_upd = mysqli_prepare($conn,
                "UPDATE products SET product_name = ?, price = ?, stock = ?, image = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt_upd, "siisi", $nama, $harga, $stok, $imagePath, $id);
            mysqli_stmt_execute($stmt_upd);
            mysqli_stmt_close($stmt_upd);

            header('Location: dashboard.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Produk</title>
<style>
body{font-family:Arial,sans-serif;padding:20px;background:#fff5f7;margin:0;}
h2{color:#333;margin-bottom:15px;font-size:26px;}
.btn-kembali{display:inline-block;text-decoration:none;background:#6c757d;color:white;padding:8px 15px;border-radius:6px;font-size:14px;font-weight:bold;margin-bottom:20px;transition:background 0.3s;}
.btn-kembali:hover{background:#5a6268;}
form{background:white;padding:25px;border-radius:15px;width:100%;max-width:500px;box-sizing:border-box;box-shadow:0 4px 15px rgba(0,0,0,0.05);}
input[type="text"],input[type="number"],input[type="file"]{width:100%;padding:12px;margin:10px 0 18px 0;box-sizing:border-box;border:1px solid #ddd;border-radius:8px;font-size:14px;}
input:focus{border-color:#b04d6d;outline:none;}
label{font-size:14px;font-weight:bold;color:#555;}
.gambar-preview{margin:8px 0 15px 0;}
.gambar-preview img{width:120px;height:120px;object-fit:cover;border-radius:10px;border:2px solid #ddd;display:block;}
.gambar-preview p{font-size:12px;color:#888;margin:5px 0 0 0;}
#preview-baru{width:120px;height:120px;object-fit:cover;border-radius:10px;border:2px dashed #b04d6d;display:none;margin-top:8px;}
button{width:100%;padding:12px;background:#b04d6d;color:white;border:none;border-radius:10px;font-size:15px;font-weight:bold;cursor:pointer;transition:background 0.3s;}
button:hover{background:#8d3b56;}
.error{background:#ffe0e6;color:#b04d6d;padding:12px;border-radius:8px;margin-bottom:15px;font-size:14px;}
@media(max-width:480px){body{padding:15px;}h2{font-size:22px;}form{padding:20px;}}
</style>
<script src="no-forward.js"></script>
</head>
<body>
<h2>Edit Produk</h2>
<a href="dashboard.php" class="btn-kembali">← Kembali</a>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label>Nama Produk</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($data['product_name']) ?>" required>

    <label>Harga (Rp)</label>
    <input type="number" name="harga" value="<?= (int)$data['price'] ?>" min="1" required>

    <label>Stok</label>
    <input type="number" name="stok" value="<?= (int)$data['stock'] ?>" min="0" required>

    <label>Gambar Produk Saat Ini</label>
    <div class="gambar-preview">
        <?php if (!empty($data['image'])): ?>
            <img src="../<?= htmlspecialchars($data['image']) ?>" alt="Gambar Produk">
            <p>Biarkan kosong jika tidak ingin mengganti gambar.</p>
        <?php else: ?>
            <p style="color:#aaa;font-style:italic;">Belum ada gambar.</p>
        <?php endif; ?>
    </div>

    <label>Ganti Gambar (opsional, JPG/PNG/WEBP, maks 3MB)</label>
    <input type="file" name="gambar" accept="image/*" onchange="previewGambar(this)">
    <img id="preview-baru" alt="Preview gambar baru">

    <button name="update">Simpan Perubahan</button>
</form>

<script>
function previewGambar(input) {
    const preview = document.getElementById('preview-baru');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

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
