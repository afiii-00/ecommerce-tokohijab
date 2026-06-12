<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
$payment = mysqli_real_escape_string($conn, $_POST['payment']);

// Membangun daftar item, total harga, dan pesan WhatsApp dari sisi server
$items_arr = [];
$total = 0;
$message = "Halo Sakila Store, saya ingin memesan:\n\n";
$no = 1;

foreach ($_SESSION['cart'] as $id => $qty) {
    $id = intval($id);
    $query = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $row = mysqli_fetch_assoc($query);
    if ($row) {
        $subtotal = $row['price'] * $qty;
        $total += $subtotal;
        $items_arr[] = $row['product_name'];
        $message .= "$no. " . $row['product_name'] . " x$qty - Rp " . number_format($subtotal, 0, ',', '.') . "\n";
        $no++;
    }
}

$items = implode(', ', $items_arr);
$jumlah_produk = count($items_arr);

// Simpan pelanggan
mysqli_query($conn, "INSERT INTO customers(nama, email, no_hp, alamat) VALUES('$nama', '$email', '$no_hp', '$alamat')");

// Simpan pesanan
mysqli_query($conn, "INSERT INTO orders(customer_name, alamat, items, jumlah_produk, total_price, payment_method, order_date) 
                    VALUES('$nama', '$alamat', '$items', '$jumlah_produk', '$total', '$payment', NOW())");

// Kurangi stok produk
foreach ($_SESSION['cart'] as $id => $qty) {
    $id = intval($id);
    mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = '$id'");
}

// Susun pesan WhatsApp selengkapnya
$message .= "\n*Total: Rp " . number_format($total, 0, ',', '.') . "*";
$message .= "\n\nMetode Pembayaran: $payment";

if ($payment === 'Transfer BCA') {
    $message .= "\nBank BCA\nNo Rekening: 6821700148\nAtas Nama: Sakila Store";
}

$message .= "\n\nNama: $nama";
$message .= "\nEmail: $email";
$message .= "\nNo HP: $no_hp";
$message .= "\nAlamat: $alamat";
$message .= "\n\nMohon diproses ya, terima kasih!";

$phoneNumber = "6285723163866";
$encodedMessage = urlencode($message);

// Bersihkan keranjang belanja
unset($_SESSION['cart']);

// Redirect langsung ke WhatsApp
header("Location: https://wa.me/$phoneNumber?text=$encodedMessage");
exit;
?>