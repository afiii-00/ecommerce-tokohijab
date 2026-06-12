<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$id = $_POST['id'];
$status = $_POST['status'];

mysqli_query(
    $conn,
    "UPDATE orders
     SET payment_status='$status'
     WHERE id='$id'"
);

header("Location: riwayat_pesanan.php");

?>