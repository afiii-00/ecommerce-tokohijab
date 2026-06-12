<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$data = mysqli_query($conn, "
SELECT *
FROM orders
ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Riwayat Pesanan</title>
<style>
body{font-family:Arial;padding:20px;background:#fff5f7;}

table{
width:100%;
background:white;
border-collapse:collapse;
}
table th, table td{
border:1px solid #ddd;
padding:10px;
}
</style>
</head>
<body>

<h2>Riwayat Pesanan</h2>

<table>
<tr>
<tr>
<th>No</th>
<th>Pelanggan</th>
<th>Alamat</th>
<th>Produk</th>
<th>Jumlah Produk</th>
<th>Total</th>
<th>Pembayaran</th>
</tr>
</tr>

<?php
$no=1;
while($row=mysqli_fetch_assoc($data)){
?>
<tr>
<td><?= $no++ ?></td>

<td><?= $row['customer_name'] ?></td>

<td><?= $row['alamat'] ?></td>

<td><?= $row['items'] ?></td>

<td><?= $row['jumlah_produk'] ?></td>

<td>
Rp <?= number_format($row['total_price'],0,',','.') ?>
</td>

<td><?= $row['payment_method'] ?></td>
</tr>
<?php } ?>

</table>

</body>
</html>