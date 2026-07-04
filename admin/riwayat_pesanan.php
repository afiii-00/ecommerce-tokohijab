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

// ============================================================
//  HAPUS RIWAYAT — POST + CSRF (bukan GET)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    verifyCSRF();
    $hapus_id = (int)$_POST['hapus_id'];
    if ($hapus_id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM orders WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $hapus_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: riwayat_pesanan.php');
    exit();
}

$data = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Pesanan</title>
<style>
body{font-family:Arial,sans-serif;padding:20px;background:#fff5f7;margin:0;}
h2{color:#333;margin-bottom:15px;font-size:26px;}
.btn-kembali{display:inline-block;text-decoration:none;background:#b04d6d;color:white;padding:8px 15px;border-radius:6px;font-size:14px;font-weight:bold;margin-bottom:20px;transition:background 0.3s;}
.btn-kembali:hover{background:#8d3b56;}
.table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;background:white;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.05);}
table{width:100%;border-collapse:collapse;min-width:1000px;}
table th,table td{border:1px solid #eee;padding:12px 10px;text-align:left;font-size:14px;}
table th{background-color:#fcf6f7;color:#b04d6d;font-weight:bold;}
table tr:nth-child(even){background-color:#fdfbfb;}
.btn-hapus{display:inline-block;background:#dc3545;color:white;padding:6px 12px;border-radius:6px;font-size:13px;font-weight:bold;border:none;cursor:pointer;transition:background 0.3s;}
.btn-hapus:hover{background:#b02535;}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.45);z-index:999;justify-content:center;align-items:center;}
.modal-overlay.active{display:flex;}
.modal-box{background:white;border-radius:14px;padding:28px 30px;max-width:360px;width:90%;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,0.15);}
.modal-box h3{margin:0 0 10px 0;color:#333;font-size:18px;}
.modal-box p{color:#666;font-size:14px;margin:0 0 22px 0;}
.modal-actions{display:flex;gap:12px;justify-content:center;}
.btn-konfirmasi{background:#dc3545;color:white;border:none;padding:9px 22px;border-radius:8px;font-weight:bold;font-size:14px;cursor:pointer;}
.btn-batal{background:#eee;color:#555;border:none;padding:9px 22px;border-radius:8px;font-weight:bold;font-size:14px;cursor:pointer;}
@media(max-width:480px){body{padding:15px;}h2{font-size:22px;}}
</style>
<script src="no-forward.js"></script>
</head>
<body>

<h2>Riwayat Pesanan</h2>
<a href="dashboard.php" class="btn-kembali">← Kembali ke Dashboard</a>

<div class="table-responsive">
<table>
<tr>
    <th>No</th><th>Pelanggan</th><th>Alamat</th><th>Produk</th><th>Jumlah</th>
    <th>Total</th><th>Pembayaran</th><th>Tanggal</th><th>Aksi</th>
</tr>
<?php
$no = 1;
while ($row = mysqli_fetch_assoc($data)):
    $items = json_decode($row['items'], true);
?>
<tr>
    <td><?= $no++ ?></td>
    <td><strong><?= htmlspecialchars($row['customer_name']) ?></strong></td>
    <td><?= htmlspecialchars($row['alamat']) ?></td>
    <td>
        <?php if ($items): foreach ($items as $item): ?>
            <?= htmlspecialchars($item['name']) ?> x<?= (int)$item['qty'] ?><br>
        <?php endforeach; endif; ?>
    </td>
    <td><?= (int)$row['jumlah_produk'] ?> Pcs</td>
    <td><strong>Rp <?= number_format($row['total_price'], 0, ',', '.') ?></strong></td>
    <td><?= htmlspecialchars($row['payment_method']) ?></td>
    <td><?= htmlspecialchars($row['order_date'] ?? '-') ?></td>
    <td>
        <button class="btn-hapus" onclick="konfirmasiHapus(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['customer_name'], ENT_QUOTES) ?>')">🗑 Hapus</button>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>

<!-- Modal Konfirmasi Hapus dengan Form POST + CSRF -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
        <h3>Hapus Riwayat?</h3>
        <p id="modalPesan">Apakah kamu yakin ingin menghapus pesanan ini?</p>
        <div class="modal-actions">
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
    document.getElementById('modalPesan').textContent = 'Hapus pesanan dari "' + nama + '"? Aksi ini tidak bisa dibatalkan.';
    document.getElementById('hapus_id').value = id;
    document.getElementById('modalHapus').classList.add('active');
}
function tutupModal() {
    document.getElementById('modalHapus').classList.remove('active');
}
document.getElementById('modalHapus').addEventListener('click', function(e){
    if (e.target === this) tutupModal();
});

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
