<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$produk = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard Admin</title>
<style>
body{
font-family:Arial;
background:#fff5f7;
padding:20px;
}

a{
text-decoration:none;
}

.btn{
background:#b04d6d;
color:white;
padding:10px 15px;
border-radius:8px;
}

table{
width:100%;
background:white;
border-collapse:collapse;
margin-top:20px;
}

table th, table td{
border:1px solid #ddd;
padding:10px;
}

img{
width:70px;
}
</style>
</head>
<body>

<h1>Dashboard Admin</h1>

<a href="tambah_produk.php" class="btn">Tambah Produk</a>
<a href="riwayat_pesanan.php" class="btn">Riwayat Pesanan</a>
<a href="logout.php" class="btn">Logout</a>

<table>
<tr>
<th>No</th>
<th>Gambar</th>
<th>Nama</th>
<th>Harga</th>
<th>Stok</th>
<th>Aksi</th>
</tr>

<?php
$no = 1;
while($row = mysqli_fetch_assoc($produk)){
?>
<tr>
<td><?= $no++ ?></td>
<td><img src="../<?= $row['image'] ?>"></td>
<td><?= $row['product_name'] ?></td>
<td>Rp <?= number_format($row['price']) ?></td>
<td><?= $row['stock'] ?></td>
<td>
<a href="edit_produk.php?id=<?= $row['id'] ?>">Edit</a> |
<a href="hapus_produk.php?id=<?= $row['id'] ?>">Hapus</a>
</td>
</tr>
<?php } ?>

</table>

</body>
</html>