<?php
include '../koneksi.php';

if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit(); }

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
    session_destroy(); header('Location: login.php?timeout=1'); exit();
}
$_SESSION['login_time'] = time();

// ============================================================
//  ANTI-CACHE — Cegah browser menyimpan halaman ini di cache,
//  supaya tombol Back tidak bisa menampilkan halaman admin lagi
//  setelah logout (harus request baru ke server tiap kali).
// ============================================================
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// ============================================================
//  HAPUS PRODUK — POST + CSRF (bukan GET)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    verifyCSRF();
    $del_id = (int)$_POST['hapus_id'];
    if ($del_id > 0) {
        // Ambil nama file gambar sebelum dihapus
        $stmt_img = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_img, "i", $del_id);
        mysqli_stmt_execute($stmt_img);
        $img_res = mysqli_stmt_get_result($stmt_img);
        $img_row = mysqli_fetch_assoc($img_res);
        mysqli_stmt_close($stmt_img);

        // Hapus file gambar dari disk jika ada
        if ($img_row && !empty($img_row['image'])) {
            $img_path = __DIR__ . '/../' . $img_row['image'];
            if (file_exists($img_path)) @unlink($img_path);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $del_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: dashboard.php');
    exit();
}

$produk = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin – Sakila Store</title>
<style>
body{font-family:Arial,sans-serif;background:#fff5f7;padding:20px;margin:0;}
h1{color:#333;margin-bottom:20px;font-size:28px;}
a{text-decoration:none;}
.btn-container{margin-bottom:20px;display:flex;flex-wrap:wrap;gap:10px;}
.btn{background:#b04d6d;color:white;padding:10px 15px;border-radius:8px;font-weight:bold;font-size:14px;display:inline-block;transition:background 0.3s;}
.btn:hover{background:#8d3b56;}
.btn-danger{background:#dc3545;}
.btn-danger:hover{background:#bd2130;}
.table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;background:white;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.05);}
table{width:100%;border-collapse:collapse;min-width:700px;}
table th,table td{border:1px solid #eee;padding:12px;text-align:left;font-size:14px;}
table th{background-color:#fcf6f7;color:#b04d6d;font-weight:bold;}
img{width:60px;height:60px;object-fit:cover;border-radius:6px;}
table td a{color:#b04d6d;font-weight:bold;}
table td a:hover{text-decoration:underline;}
.btn-hapus{background:#dc3545;color:white;border:none;padding:6px 12px;border-radius:6px;font-size:13px;font-weight:bold;cursor:pointer;transition:background 0.3s;}
.btn-hapus:hover{background:#b02535;}
/* Modal */
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:999;justify-content:center;align-items:center;}
.modal-overlay.active{display:flex;}
.modal-box{background:white;border-radius:14px;padding:28px 30px;max-width:360px;width:90%;text-align:center;}
.modal-box h3{margin:0 0 10px 0;color:#333;}
.modal-box p{color:#666;font-size:14px;margin:0 0 22px 0;}
.modal-actions{display:flex;gap:12px;justify-content:center;}
.btn-konfirmasi{background:#dc3545;color:white;border:none;padding:9px 22px;border-radius:8px;font-weight:bold;font-size:14px;cursor:pointer;}
.btn-batal{background:#eee;color:#555;border:none;padding:9px 22px;border-radius:8px;font-weight:bold;font-size:14px;cursor:pointer;}
@media(max-width:480px){body{padding:15px;}h1{font-size:24px;}.btn{flex:1;text-align:center;font-size:13px;padding:10px 8px;}}
</style>
<script src="no-forward.js"></script>
</head>
<body>

<h1>Dashboard Admin</h1>

<div class="btn-container">
    <a href="tambah_produk.php" class="btn">+ Tambah Produk</a>
    <a href="riwayat_pesanan.php" class="btn">Riwayat Pesanan</a>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>

<div class="table-responsive">
<table>
<tr>
    <th>No</th><th>Gambar</th><th>Nama</th><th>Harga</th><th>Stok</th><th>Aksi</th>
</tr>
<?php
$no = 1;
while ($row = mysqli_fetch_assoc($produk)):
    $img_src = '../' . htmlspecialchars($row['image']);
?>
<tr>
    <td><?= $no++ ?></td>
    <td><img src="<?= $img_src ?>" alt="Produk"></td>
    <td><?= htmlspecialchars($row['product_name']) ?></td>
    <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
    <td><?= (int)$row['stock'] ?></td>
    <td>
        <a href="edit_produk.php?id=<?= (int)$row['id'] ?>">Edit</a> |
        <button class="btn-hapus" onclick="konfirmasiHapus(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>')">Hapus</button>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
        <h3>Hapus Produk?</h3>
        <p id="modalPesan">Apakah kamu yakin ingin menghapus produk ini?</p>
        <div class="modal-actions">
            <!-- Form POST + CSRF untuk hapus -->
            <form method="POST" id="formHapus" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="hapus_id" id="hapus_id" value="">
                <button type="submit" class="btn-konfirmasi">Ya, Hapus</button>
            </form>
            <button class="btn-batal" onclick="tutupModal()">Batal</button>
        </div>
    </div>
</div>

<script>
function konfirmasiHapus(id, nama) {
    document.getElementById('modalPesan').textContent = 'Hapus "' + nama + '"? Aksi ini tidak bisa dibatalkan.';
    document.getElementById('hapus_id').value = id;
    document.getElementById('modalHapus').classList.add('active');
}
function tutupModal() {
    document.getElementById('modalHapus').classList.remove('active');
}
document.getElementById('modalHapus').addEventListener('click', function(e){
    if (e.target === this) tutupModal();
});

// Jaga-jaga: kalau browser tetap menampilkan halaman ini dari
// bfcache (tombol Back/Forward), paksa reload supaya PHP
// mengecek ulang status login.
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
</body>
</html>
