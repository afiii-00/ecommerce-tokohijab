<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header('Location: dashboard.php');
    exit;
}

if(isset($_POST['update'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);

    // Check if new image is uploaded
    if($_FILES['gambar']['name'] != ""){
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        move_uploaded_file($tmp, 'upload/'.$gambar);
        $imagePath = 'admin/upload/'.$gambar;
        
        mysqli_query($conn, "UPDATE products SET 
            product_name='$nama', 
            price='$harga', 
            stock='$stok', 
            category='$kategori', 
            image='$imagePath' 
            WHERE id='$id'");
    } else {
        mysqli_query($conn, "UPDATE products SET 
            product_name='$nama', 
            price='$harga', 
            stock='$stok', 
            category='$kategori' 
            WHERE id='$id'");
    }

    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Produk</title>
    <style>
        body{font-family:Arial;padding:20px;background:#fff5f7;}
        form{
            background:white;
            padding:20px;
            border-radius:15px;
            max-width:500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        input {
            width:100%;
            padding:10px;
            margin:10px 0;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-top: 10px;
        }
        button{
            padding:10px 20px;
            background:#b04d6d;
            color:white;
            border:none;
            border-radius:10px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
        }
        button:hover {
            background: #903c56;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #b04d6d;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <a href="dashboard.php" class="btn-back">&larr; Kembali ke Dashboard</a>

    <h2>Edit Produk</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Nama Produk</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($data['product_name']) ?>" required>
        
        <label>Harga (Rp)</label>
        <input type="number" name="harga" value="<?= $data['price'] ?>" required>
        
        <label>Stok</label>
        <input type="number" name="stok" value="<?= $data['stock'] ?>" required>
        
        <label>Kategori</label>
        <input type="text" name="kategori" value="<?= htmlspecialchars($data['category']) ?>" required>
        
        <label>Gambar Produk (Biarkan kosong jika tidak ingin diubah)</label>
        <input type="file" name="gambar">
        <?php if($data['image']): ?>
            <div style="margin-top: 10px;">
                <p style="margin: 0; font-size: 0.9em; color: #666;">Gambar Saat Ini:</p>
                <img src="../<?= htmlspecialchars($data['image']) ?>" width="100" style="margin-top: 5px; border-radius: 8px;">
            </div>
        <?php endif; ?>
        
        <button name="update">Update</button>
    </form>

</body>
</html>