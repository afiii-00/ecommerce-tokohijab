<?php
session_start();
include '../koneksi.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id']);

mysqli_query($conn, "DELETE FROM products WHERE id='$id'");

header('Location: dashboard.php');
?>