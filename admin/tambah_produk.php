<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

if(isset($_POST['simpan'])){

$nama = $_POST['nama'];
$harga = $_POST['harga'];
$stok = $_POST['stok'];
$kategori = $_POST['kategori'];

$gambar = $_FILES['gambar']['name'];
$tmp = $_FILES['gambar']['tmp_name'];

move_uploaded_file($tmp, 'upload/'.$gambar);

$imagePath = 'admin/upload/'.$gambar;

mysqli_query($conn, "INSERT INTO products(product_name,price,image,category,stock)
VALUES('$nama','$harga','$imagePath','$kategori','$stok')");

header('Location: dashboard.php');
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Tambah Produk</title>
<style>
body{font-family:Arial;padding:20px;background:#fff5f7;}
form{
background:white;
padding:20px;
border-radius:15px;
max-width:500px;
}
input{
width:100%;
padding:10px;
margin:10px 0;
}
button{
padding:10px 20px;
background:#b04d6d;
color:white;
border:none;
border-radius:10px;
}
</style>
</head>
<body>

<h2>Tambah Produk</h2>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="nama" placeholder="Nama Produk">
<input type="number" name="harga" placeholder="Harga">
<input type="number" name="stok" placeholder="Stok">
<input type="text" name="kategori" placeholder="Kategori">
<input type="file" name="gambar">
<button name="simpan">Simpan</button>
</form>

</body>
</html>